<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidPrestataire implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
        public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!\App\Models\Prestataire::find($value)) {
            $fail('The selected :attribute is not a valid prestataire.');
        }
    }
}
