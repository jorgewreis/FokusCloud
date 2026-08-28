<?php

namespace App\Services;

final class CatalogPricing
{
    public static function suggestedMonthly(float $rawMonthly): float
    {
        return self::commercialRound($rawMonthly * 0.9);
    }

    public static function annualFromMonthly(float $monthly): float
    {
        return self::commercialRound($monthly * 9);
    }

    private static function commercialRound(float $amount): float
    {
        $cents = (int) round($amount * 100);
        if ($cents <= 0) return 0.0;
        return (intdiv($cents, 500) * 500 - 10) / 100;
    }
}
