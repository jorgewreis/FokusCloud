<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

final class PasswordSecurity
{
    /** @var array<string, true> */
    private const COMMON = [
        '123456789012', '123456789123', '1234567890', '12345678', '123456789',
        'password123', 'password1234', 'senha123456', 'senha12345678', 'admin123456',
        'qwerty123456', 'fokuscloud123', 'abcdef123456', 'iloveyou123', 'welcome12345',
        'changeme1234', 'senha@123456', '123456789101', 'brasil123456', 'administrador',
    ];

    public function validate(string $password): void
    {
        if (mb_strlen($password) < 12) {
            throw ValidationException::withMessages(['password' => 'A senha deve ter ao menos 12 caracteres.']);
        }

        if (isset(array_flip(self::COMMON)[mb_strtolower($password)])) {
            throw ValidationException::withMessages(['password' => 'Escolha uma senha que não seja comum.']);
        }

        $hash = strtoupper(sha1($password));
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);

        try {
            $response = Http::accept('text/plain')->timeout(5)->retry(1, 100)
                ->get("https://api.pwnedpasswords.com/range/{$prefix}");
        } catch (\Throwable) {
            throw ValidationException::withMessages(['password' => 'Não foi possível verificar a segurança da senha. Tente novamente.']);
        }

        if (! $response->successful()) {
            throw ValidationException::withMessages(['password' => 'Não foi possível verificar a segurança da senha. Tente novamente.']);
        }

        foreach (preg_split('/\r?\n/', $response->body()) ?: [] as $line) {
            [$candidate] = explode(':', trim($line), 2);
            if (hash_equals($suffix, strtoupper($candidate))) {
                throw ValidationException::withMessages(['password' => 'Esta senha foi exposta em vazamentos. Escolha outra.']);
            }
        }
    }
}
