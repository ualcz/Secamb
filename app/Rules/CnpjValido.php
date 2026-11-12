<?php

namespace App\Rules;

use App\Support\DocumentoHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CnpjValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! DocumentoHelper::isCnpjValido($value)) {
            $fail('Informe um CNPJ válido.');
        }
    }
}
