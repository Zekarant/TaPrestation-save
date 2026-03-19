<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify reCAPTCHA v3 token.
     *
     * Returns an array with keys:
     * - enabled (bool)
     * - success (bool)
     * - score (float|null)
     * - action (string|null)
     * - error_codes (array|null)
     */
    public function verifyV3(Request $request, string $expectedAction = 'register'): array
    {
        $enabled = (bool) config('recaptcha.enabled');
        $secret = (string) config('recaptcha.secret_key');

        if (!$enabled || $secret === '') {
            return [
                'enabled' => false,
                'success' => true,
                'score' => null,
                'action' => null,
                'error_codes' => null,
            ];
        }

        $token = (string) $request->input('recaptcha_token', '');

        if ($token === '') {
            Log::warning('reCAPTCHA: token manquant', [
                'action' => $expectedAction,
                'ip' => $request->ip(),
            ]);

            return [
                'enabled' => true,
                'success' => false,
                'score' => null,
                'action' => null,
                'error_codes' => ['missing-input-response'],
            ];
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (!$response->ok()) {
            Log::warning('reCAPTCHA: API Google indisponible', [
                'action' => $expectedAction,
                'ip' => $request->ip(),
                'status' => $response->status(),
            ]);

            return [
                'enabled' => true,
                'success' => false,
                'score' => null,
                'action' => null,
                'error_codes' => ['recaptcha-unavailable'],
            ];
        }

        $data = (array) $response->json();

        $success = (bool) ($data['success'] ?? false);
        $score = isset($data['score']) ? (float) $data['score'] : null;
        $action = isset($data['action']) ? (string) $data['action'] : null;
        $errorCodes = isset($data['error-codes']) ? (array) $data['error-codes'] : null;

        $minScore = (float) config('recaptcha.score_threshold', 0.5);

        if (!$success) {
            Log::warning('reCAPTCHA: vérification échouée', [
                'action' => $expectedAction,
                'ip' => $request->ip(),
                'score' => $score,
                'error_codes' => $errorCodes,
            ]);

            return [
                'enabled' => true,
                'success' => false,
                'score' => $score,
                'action' => $action,
                'error_codes' => $errorCodes,
            ];
        }

        // Action must match when provided
        if ($action !== null && $action !== '' && $action !== $expectedAction) {
            Log::warning('reCAPTCHA: action mismatch', [
                'expected' => $expectedAction,
                'received' => $action,
                'ip' => $request->ip(),
                'score' => $score,
            ]);

            return [
                'enabled' => true,
                'success' => false,
                'score' => $score,
                'action' => $action,
                'error_codes' => ['action-mismatch'],
            ];
        }

        // Score must be above threshold when provided
        if ($score !== null && $score < $minScore) {
            Log::warning('reCAPTCHA: score trop bas', [
                'action' => $expectedAction,
                'ip' => $request->ip(),
                'score' => $score,
                'threshold' => $minScore,
            ]);

            return [
                'enabled' => true,
                'success' => false,
                'score' => $score,
                'action' => $action,
                'error_codes' => ['score-too-low'],
            ];
        }

        return [
            'enabled' => true,
            'success' => true,
            'score' => $score,
            'action' => $action,
            'error_codes' => $errorCodes,
        ];
    }
}
