<?php

namespace App\Support;

class DocumentoHelper
{
    public static function normalizarCpf(mixed $valor): string
    {
        if (! is_string($valor) && ! is_numeric($valor)) {
            return 'INVALID';
        }

        return preg_replace('/\D+/', '', (string) $valor) ?? '';
    }

    public static function formatarCpf(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if ((! is_string($valor) && ! is_numeric($valor)) || ! preg_match('/^[\d.\-\s]+$/', (string) $valor)) {
            return 'INVALID';
        }

        $cpf = self::normalizarCpf($valor);

        if ($cpf === '') {
            return null;
        }

        if (strlen($cpf) !== 11) {
            return $cpf;
        }

        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.'
            . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    public static function isCpfValido(mixed $valor): bool
    {
        if ((! is_string($valor) && ! is_numeric($valor)) || ! preg_match('/^[\d.\-\s]+$/', (string) $valor)) {
            return false;
        }

        $cpf = self::normalizarCpf($valor);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($posicao = 9; $posicao <= 10; $posicao++) {
            $soma = 0;

            for ($indice = 0; $indice < $posicao; $indice++) {
                $soma += (int) $cpf[$indice] * (($posicao + 1) - $indice);
            }

            $digito = ($soma * 10) % 11;
            $digito = $digito === 10 ? 0 : $digito;

            if ((int) $cpf[$posicao] !== $digito) {
                return false;
            }
        }

        return true;
    }

    public static function normalizarCnpj(mixed $valor): string
    {
        if (! is_string($valor) && ! is_numeric($valor)) {
            return 'INVALID';
        }

        $valor = (string) $valor;

        if (! preg_match('/^[A-Z0-9.\/\-\s]*$/i', $valor)) {
            return strtoupper($valor);
        }

        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $valor) ?? '');
    }

    public static function formatarCnpj(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $cnpj = self::normalizarCnpj($valor);

        if ($cnpj === '') {
            return null;
        }

        if (! preg_match('/^[A-Z0-9]{12}\d{2}$/', $cnpj)) {
            return $cnpj;
        }

        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.'
            . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-'
            . substr($cnpj, 12, 2);
    }

    public static function isCnpjValido(mixed $valor): bool
    {
        $cnpj = self::normalizarCnpj($valor);

        if (
            ! preg_match('/^[A-Z0-9]{12}\d{2}$/', $cnpj)
            || preg_match('/^([A-Z0-9])\1{11}\d{2}$/', $cnpj)
        ) {
            return false;
        }

        $digito1 = self::calcularDigitoCnpj(substr($cnpj, 0, 12));
        $digito2 = self::calcularDigitoCnpj(substr($cnpj, 0, 12) . $digito1);

        return substr($cnpj, 12, 2) === $digito1 . $digito2;
    }

    private static function calcularDigitoCnpj(string $base): int
    {
        $pesos = [2, 3, 4, 5, 6, 7, 8, 9];
        $soma = 0;

        foreach (array_reverse(str_split($base)) as $indice => $caractere) {
            $soma += (ord($caractere) - 48) * $pesos[$indice % count($pesos)];
        }

        $resto = $soma % 11;

        return $resto < 2 ? 0 : 11 - $resto;
    }
}
