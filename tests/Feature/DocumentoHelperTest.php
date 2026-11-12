<?php

use App\Rules\CnpjValido;
use App\Rules\CpfValido;
use App\Support\DocumentoHelper;
use Illuminate\Support\Facades\Validator;

it('valida CPF pelos dois dígitos verificadores', function () {
    expect(DocumentoHelper::isCpfValido('529.982.247-25'))->toBeTrue()
        ->and(DocumentoHelper::isCpfValido('529.982.247-24'))->toBeFalse()
        ->and(DocumentoHelper::isCpfValido('111.111.111-11'))->toBeFalse()
        ->and(DocumentoHelper::isCpfValido('abc52998224725'))->toBeFalse();
});

it('valida CNPJ numérico e alfanumérico e formata a entrada em maiúsculas', function () {
    expect(DocumentoHelper::isCnpjValido('11.222.333/0001-81'))->toBeTrue()
        ->and(DocumentoHelper::normalizarCnpj('12.abc.345/01de-35'))->toBe('12ABC34501DE35')
        ->and(DocumentoHelper::isCnpjValido('12.abc.345/01de-35'))->toBeTrue()
        ->and(DocumentoHelper::formatarCnpj('12abc34501de35'))->toBe('12.ABC.345/01DE-35')
        ->and(DocumentoHelper::isCnpjValido('12.ABC.345/01DE-36'))->toBeFalse()
        ->and(DocumentoHelper::isCnpjValido('12ABC34501DE3A'))->toBeFalse();
});

it('aplica as regras de documento também na validação Laravel', function () {
    expect(Validator::make(['cpf' => '52998224725'], ['cpf' => [new CpfValido]])->passes())->toBeTrue()
        ->and(Validator::make(['cpf' => '52998224724'], ['cpf' => [new CpfValido]])->fails())->toBeTrue()
        ->and(Validator::make(['cnpj' => '12.ABC.345/01DE-35'], ['cnpj' => [new CnpjValido]])->passes())->toBeTrue();
});
