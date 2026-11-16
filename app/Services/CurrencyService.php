<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    public const EUR = 'EUR';
    public const XOF = 'XOF';

    public const OFFICIAL_EUR_TO_XOF = 655.957;

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
     * Convert an amount between currencies.
     */
    public static function convert(float $amount, string $from, string $to): float
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($from === $to) {
            return $amount;
        }

        $rate = self::getEurToXofRate();

        if ($from === self::EUR && $to === self::XOF) {
            return round($amount * $rate, 0); // FCFA has no decimal cents
        }

        if ($from === self::XOF && $to === self::EUR) {
            return round($amount / $rate, 2);
        }

        return $amount;
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

        return number_format($amount, 2, ',', ' ') . ' €';
    }

    /**
     * Format with both EUR and FCFA equivalents.
     */
    public static function formatDual(float $amount, string $baseCurrency = self::EUR): string
    {
        $baseCurrency = strtoupper(trim($baseCurrency));
        if ($baseCurrency === self::XOF) {
            $eur = self::convert($amount, self::XOF, self::EUR);
            return number_format($amount, 0, ',', ' ') . ' FCFA (' . number_format($eur, 2, ',', ' ') . ' €)';
        }

        $xof = self::convert($amount, self::EUR, self::XOF);
        return number_format($amount, 2, ',', ' ') . ' € (' . number_format($xof, 0, ',', ' ') . ' FCFA)';
    }

    /**
     * Helper text for form inputs showing live direct internet conversion.
     */
    public static function getConversionHelper(?float $amount, ?string $currency): ?string
    {
        if (! $amount || $amount <= 0) {
            return 'Saisissez un montant pour voir la conversion directe en temps réel.';
        }

        $currency = $currency ? strtoupper(trim($currency)) : self::getActiveCurrency();
        $rate = self::getEurToXofRate();

        if ($currency === self::EUR) {
            $converted = self::convert($amount, self::EUR, self::XOF);
            $formatted = number_format($converted, 0, ',', ' ') . ' FCFA';
            return "≈ {$formatted} (Conversion directe internet : 1 € = " . number_format($rate, 2, ',', ' ') . " FCFA)";
        }

        $converted = self::convert($amount, self::XOF, self::EUR);
        $formatted = number_format($converted, 2, ',', ' ') . ' €';
        return "≈ {$formatted} (Conversion directe internet : 1 € = " . number_format($rate, 2, ',', ' ') . " FCFA)";
    }

    /**
     * Get active global currency.
     */
    public static function getActiveCurrency(): string
    {
        if (session()->has('active_currency')) {
            return session('active_currency');
        }

        $setting = AppSetting::where('setting_key', 'system.default_currency')->first();
        if ($setting && in_array(trim($setting->value_json, '"\''), [self::EUR, self::XOF])) {
            return trim($setting->value_json, '"\'');
        }

        return self::EUR;
    }

    /**
     * Set active global currency in session and DB.
     */
    public static function setActiveCurrency(string $currency): void
    {
        $currency = strtoupper(trim($currency));
        if (! in_array($currency, [self::EUR, self::XOF])) {
            $currency = self::EUR;
        }

        session(['active_currency' => $currency]);

        AppSetting::updateOrCreate(
            ['setting_key' => 'system.default_currency'],
            [
                'value_json' => json_encode($currency),
                'description' => 'Devise active globale du système (EUR ou XOF/FCFA)',
                'updated_at' => now(),
            ]
        );
    }
}

