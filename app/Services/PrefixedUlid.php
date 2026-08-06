<?php

namespace App\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;

final class PrefixedUlid
{
    public static function make(string $prefix): string
    {
        $prefix = strtoupper($prefix);

        if (! preg_match('/^[A-Z]{3}$/', $prefix)) {
            throw new InvalidArgumentException('O prefixo deve conter três letras maiúsculas.');
        }

        return $prefix.'-'.strtoupper((string) Str::ulid());
    }
}
