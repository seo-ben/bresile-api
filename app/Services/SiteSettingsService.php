<?php

namespace App\Services;

use App\Models\AppSetting;

class SiteSettingsService
{
    public const KEY = 'site.branding';

    public static function defaults(): array
    {
        return [
            'site_name'       => 'Brasil·É',
            'tagline_fr'      => 'ÉCOLE DE PORTUGAIS & MOBILITÉ AU BRÉSIL',
            'tagline_en'      => 'PORTUGUESE SCHOOL & STUDENT MOBILITY IN BRAZIL',
            'tagline_pt'      => 'ESCOLA DE PORTUGUÊS & MOBILIDADE NO BRASIL',
            'logo_path'       => null,
            'contact_email'        => 'contact@brasil-e.com',
            'contact_phone'        => '+55 61 99999-0000',
            'contact_address'      => 'Brasília, Distrito Federal — Brésil',
            'website_url'          => 'www.brasil-e.com',
            'cert_director_name'   => 'Direction Pédagogique Brasil·É',
            'cert_director_title'  => 'Direction des Admissions & Mobilité Internationale',
            'cert_weekly_hours'    => '20 heures / semaine (Minimum consulaire VITEM IV respecté)',
            'cert_legal_footer'    => 'Document officiel sécurisé et vérifiable par QR Code. Valable pour les démarches consulaires et administratives.',
        ];
    }

    public static function get(): array
    {
        $defaults = self::defaults();

        try {
            $setting = AppSetting::where('setting_key', self::KEY)->first();
            if ($setting && $setting->value_json) {
                $decoded = is_array($setting->value_json)
                    ? $setting->value_json
                    : json_decode((string) $setting->value_json, true);
                if (is_array($decoded)) {
                    return array_merge($defaults, $decoded);
                }
            }
        } catch (\Throwable) {
            // Fallback to defaults
        }

        return $defaults;
    }

    public static function save(array $data): array
    {
        $merged = array_merge(self::get(), $data);

        AppSetting::updateOrCreate(
            ['setting_key' => self::KEY],
            [
                'value_json'  => json_encode($merged, JSON_UNESCAPED_UNICODE),
                'description' => 'Identité du site : Logo, nom, slogan multilingue et coordonnées de contact',
                'updated_at'  => now(),
            ]
        );

        return $merged;
    }

    public static function getLogoUrl(?array $settings = null): string
    {
        $settings = $settings ?? self::get();
        if (! empty($settings['logo_path'])) {
            return asset('storage/' . ltrim($settings['logo_path'], '/'));
        }

        return asset('logo.png');
    }

    public static function getTaglineForLocale(string $lang = 'fr', ?array $settings = null): string
    {
        $settings = $settings ?? self::get();
        $lang = strtolower(substr($lang, 0, 2));

        return match ($lang) {
            'en' => $settings['tagline_en'] ?: $settings['tagline_fr'],
            'pt' => $settings['tagline_pt'] ?: $settings['tagline_fr'],
            default => $settings['tagline_fr'],
        };
    }

    public static function toPublicArray(string $lang = 'fr'): array
    {
        $s = self::get();

        return [
            'site_name'       => $s['site_name'],
            'tagline'         => self::getTaglineForLocale($lang, $s),
            'tagline_fr'      => $s['tagline_fr'],
            'tagline_en'      => $s['tagline_en'],
            'tagline_pt'      => $s['tagline_pt'],
            'logo_url'        => self::getLogoUrl($s),
            'contact_email'       => $s['contact_email'],
            'contact_phone'       => $s['contact_phone'],
            'contact_address'     => $s['contact_address'],
            'website_url'         => $s['website_url'],
            'cert_director_name'  => $s['cert_director_name'],
            'cert_director_title' => $s['cert_director_title'],
            'cert_weekly_hours'   => $s['cert_weekly_hours'],
            'cert_legal_footer'   => $s['cert_legal_footer'],
        ];
    }
}
