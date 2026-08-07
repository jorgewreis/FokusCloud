<?php

namespace App\Support;

final class BrazilianDocuments
{
    public static function digits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    public static function cpf(string $value): bool
    {
        $cpf = self::digits($value);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }

        for ($length = 9; $length <= 10; $length++) {
            $sum = 0;
            for ($index = 0; $index < $length; $index++) {
                $sum += (int) $cpf[$index] * ($length + 1 - $index);
            }
            $digit = ($sum * 10) % 11;
            $digit = $digit === 10 ? 0 : $digit;
            if ($digit !== (int) $cpf[$length]) {
                return false;
            }
        }

        return true;
    }

    public static function cnpj(string $value): bool
    {
        $cnpj = self::digits($value);
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }

        foreach ([12 => 5, 13 => 6] as $length => $factor) {
            $sum = 0;
            for ($index = 0; $index < $length; $index++) {
                $sum += (int) $cnpj[$index] * $factor;
                $factor = $factor === 2 ? 9 : $factor - 1;
            }
            $remainder = $sum % 11;
            $digit = $remainder < 2 ? 0 : 11 - $remainder;
            if ($digit !== (int) $cnpj[$length]) {
                return false;
            }
        }

        return true;
    }
}
