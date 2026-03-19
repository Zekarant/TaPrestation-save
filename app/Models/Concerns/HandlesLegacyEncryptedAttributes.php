<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Crypt;

trait HandlesLegacyEncryptedAttributes
{
    protected function encryptNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        return Crypt::encryptString($normalized);
    }

    protected function decryptNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return (string) $value;
        }

        $normalized = trim($value);

        if ($normalized === '') {
            return null;
        }

        try {
            return Crypt::decryptString($normalized);
        } catch (\Throwable) {
            return $normalized;
        }
    }

    protected function encryptNullableArray(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : ['value' => $value];
        }

        if (!is_array($value)) {
            return null;
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return null;
        }

        return Crypt::encryptString($json);
    }

    protected function decryptNullableArray(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        if ($normalized === '') {
            return null;
        }

        $json = $normalized;

        try {
            $json = Crypt::decryptString($normalized);
        } catch (\Throwable) {
            $json = $normalized;
        }

        $decoded = json_decode($json, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded)
            ? $decoded
            : null;
    }
}
