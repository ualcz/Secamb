<?php

namespace App\Rules;

use App\Support\DocumentoHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! DocumentoHelper::isCpfValido($value)) {
            $fail('Informe um CPF válido.');
        }
    }
}
