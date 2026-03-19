<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $isSocialAccount = $user && method_exists($user, 'isSocialAccount') && $user->isSocialAccount();

        if ($isSocialAccount) {
            return [
                'new_password' => 'required|min:8|confirmed',
            ];
        }

        return [
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ];
    }
}
