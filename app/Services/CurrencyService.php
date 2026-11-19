<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    public const EUR = 'EUR';
    public const XOF = 'XOF';
    public const BRL = 'BRL';

    public const OFFICIAL_EUR_TO_XOF = 655.957;
    public const OFFICIAL_EUR_TO_BRL = 6.15;

    /**
     * Get the live conversion rate between EUR and XOF from Internet, with fallback to 655.957.
     */
    public static function getEurToXofRate(): float
    {
        return Cache::remember('currency_rate_eur_to_xof', 3600 * 6, function () {
            try {
                $response = Http::timeout(3)->get('https://open.er-api.com/v6/latest/EUR');
                if ($response->successful()) {
                    $rates = $response->json('rates');
                    if (isset($rates['XOF']) && is_numeric($rates['XOF']) && (float) $rates['XOF'] > 0) {
                        return (float) $rates['XOF'];
                    }
                }
            } catch (\Throwable $e) {
                // Fallback to official parity
            }

            return self::OFFICIAL_EUR_TO_XOF;
        });
    }

    /**
     * Get the live conversion rate between EUR and BRL (Brazilian Real) from Internet, with fallback to 6.15.
     */
    public static function getEurToBrlRate(): float
    {
        return Cache::remember('currency_rate_eur_to_brl', 3600 * 6, function () {
            try {
                $response = Http::timeout(3)->get('https://open.er-api.com/v6/latest/EUR');
                if ($response->successful()) {
                    $rates = $response->json('rates');
                    if (isset($rates['BRL']) && is_numeric($rates['BRL']) && (float) $rates['BRL'] > 0) {
                        return (float) $rates['BRL'];
                    }
                }
            } catch (\Throwable $e) {
                // Fallback to official parity
            }

            return self::OFFICIAL_EUR_TO_BRL;
        });
    }

    /**
     * Convert an amount between currencies (EUR, XOF, BRL).
     */
    public static function convert(float $amount, string $from, string $to): float
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($from === $to) {
            return $to === self::XOF ? round($amount, 0) : round($amount, 2);
        }

        $xofRate = self::getEurToXofRate();
        $brlRate = self::getEurToBrlRate();

        // Step 1: Convert from source currency to EUR pivot
        $inEur = match ($from) {
            self::XOF => $amount / $xofRate,
            self::BRL => $amount / $brlRate,
            default   => $amount,
        };

        // Step 2: Convert from EUR pivot to target currency
        return match ($to) {
            self::XOF => round($inEur * $xofRate, 0), // FCFA has no decimal cents
            self::BRL => round($inEur * $brlRate, 2),
            default   => round($inEur, 2),
        };
    }

    /**
     * Format an amount with its currency symbol or name.
     */
    public static function format(float $amount, ?string $currency = null): string
    {
        $currency = $currency ? strtoupper(trim($currency)) : self::getActiveCurrency();

        if ($currency === self::XOF) {
            return number_format($amount, 0, ',', ' ') . ' FCFA';
        }

        if ($currency === self::BRL) {
            return 'R$ ' . number_format($amount, 2, ',', ' ');
        }

        return number_format($amount, 2, ',', ' ') . ' €';
    }

    /**
     * Format an amount converted into the currently active global currency.
     */
    public static function formatInActiveCurrency(float $baseAmount, string $baseCurrency = self::EUR): string
    {
        $activeCurrency = self::getActiveCurrency();
        $converted = self::convert($baseAmount, $baseCurrency, $activeCurrency);

        return self::format($converted, $activeCurrency);
    }

    /**
     * Format the equivalents in the other two currencies for subtitle / description displays.
     */
    public static function formatSubEquivalents(float $baseAmount, string $baseCurrency = self::EUR): string
    {
        $activeCurrency = self::getActiveCurrency();
        $otherCurrencies = array_values(array_diff([self::EUR, self::XOF, self::BRL], [$activeCurrency]));

        $parts = [];
        foreach ($otherCurrencies as $other) {
            $converted = self::convert($baseAmount, $baseCurrency, $other);
            $parts[] = self::format($converted, $other);
        }

        return '≈ ' . implode(' • ', $parts);
    }

    /**
     * Format with EUR, FCFA, and R$ (BRL) equivalents.
     */
    public static function formatDual(float $amount, string $baseCurrency = self::EUR): string
    {
        $baseCurrency = strtoupper(trim($baseCurrency));
        $primary = self::format($amount, $baseCurrency);

        $others = array_values(array_diff([self::EUR, self::XOF, self::BRL], [$baseCurrency]));
        $subParts = [];
        foreach ($others as $other) {
            $converted = self::convert($amount, $baseCurrency, $other);
            $subParts[] = self::format($converted, $other);
        }

        return $primary . ' (' . implode(' • ', $subParts) . ')';
    }

    /**
     * Helper text for form inputs showing live direct internet conversion.
     */
    public static function getConversionHelper(?float $amount, ?string $currency): ?string
    {
        if (! $amount || $amount <= 0) {
            return 'Saisissez un montant pour voir la conversion directe en temps réel (€, FCFA, R$).';
        }

        $currency = $currency ? strtoupper(trim($currency)) : self::getActiveCurrency();
        $rateXof = self::getEurToXofRate();
        $rateBrl = self::getEurToBrlRate();

        $others = array_values(array_diff([self::EUR, self::XOF, self::BRL], [$currency]));
        $conversions = [];
        foreach ($others as $other) {
            $converted = self::convert($amount, $currency, $other);
            $conversions[] = self::format($converted, $other);
        }

        $joined = implode(' • ', $conversions);
        $rateInfo = "1 € = " . number_format($rateXof, 2, ',', ' ') . " FCFA = R$ " . number_format($rateBrl, 2, ',', ' ');

        return "≈ {$joined} (Taux direct : {$rateInfo})";
    }

    /**
     * Get active global currency (EUR, XOF, or BRL).
     */
    public static function getActiveCurrency(): string
    {
        if (session()->has('active_currency')) {
            $sessionCurrency = strtoupper(trim((string) session('active_currency')));
            if (in_array($sessionCurrency, [self::EUR, self::XOF, self::BRL], true)) {
                return $sessionCurrency;
            }
        }

        try {
            $setting = AppSetting::where('setting_key', 'system.default_currency')->first();
            if ($setting) {
                $val = strtoupper(trim((string) $setting->value_json, "\"' \t\n\r\0\x0B"));
                if (in_array($val, [self::EUR, self::XOF, self::BRL], true)) {
                    return $val;
                }
            }
        } catch (\Throwable $e) {
            // Fallback to EUR
        }

        return self::EUR;
    }

    /**
     * Set active global currency in session and DB.
     */
    public static function setActiveCurrency(string $currency): void
    {
        $currency = strtoupper(trim($currency));
        if (! in_array($currency, [self::EUR, self::XOF, self::BRL], true)) {
            $currency = self::EUR;
        }

        session(['active_currency' => $currency]);

        AppSetting::updateOrCreate(
            ['setting_key' => 'system.default_currency'],
            [
                'value_json'  => json_encode($currency),
                'description' => 'Devise active globale du système (EUR, XOF/FCFA ou BRL/R$)',
                'updated_at'  => now(),
            ]
        );
    }
}
