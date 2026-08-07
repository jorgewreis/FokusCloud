<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The production schema uses MySQL's binary ASCII collation for
        // prefixed ULID identifiers. Register the equivalent comparator when
        // the feature suite runs against its isolated SQLite memory database.
        if (config('database.default') === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateCollation('ascii_bin', static fn (string $left, string $right): int => strcmp($left, $right));
        }
    }
}
