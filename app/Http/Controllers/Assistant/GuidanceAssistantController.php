<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Services\GuidanceAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuidanceAssistantController extends Controller
{
    public function chat(Request $request, GuidanceAssistantService $assistant): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:500'],
            'previous_response_id' => ['nullable', 'string', 'max:255'],
            'current_path' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = $assistant->chat(
            user: $request->user(),
            message: $data['message'],
            previousResponseId: $data['previous_response_id'] ?? null,
            currentPath: $data['current_path'] ?? null,
        );

        return response()->json($payload);
    }

    public function boot(Request $request, GuidanceAssistantService $assistant): JsonResponse
    {
        return response()->json($assistant->initialPayload($request->user()));
    }
}
