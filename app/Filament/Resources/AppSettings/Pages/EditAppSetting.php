<?php

namespace App\Filament\Resources\AppSettings\Pages;

use App\Filament\Resources\AppSettings\AppSettingResource;
use App\Services\CurrencyService;
use App\Services\SiteSettingsService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAppSetting extends EditRecord
{
    protected static string $resource = AppSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $key = $data['setting_key'] ?? '';
        $raw = $data['value_json'] ?? '';

        if ($key === 'site.branding') {
            $decoded = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
            $merged = array_merge(SiteSettingsService::defaults(), $decoded);

            $data['branding_site_name']       = $merged['site_name'];
            $data['branding_logo_path']       = $merged['logo_path'];
            $data['branding_tagline_fr']      = $merged['tagline_fr'];
            $data['branding_tagline_en']      = $merged['tagline_en'];
            $data['branding_tagline_pt']      = $merged['tagline_pt'];
            $data['branding_contact_email']   = $merged['contact_email'];
            $data['branding_contact_phone']   = $merged['contact_phone'];
            $data['branding_contact_address'] = $merged['contact_address'];
            $data['branding_website_url']     = $merged['website_url'];
        } elseif ($key === 'system.default_currency') {
            $data['currency_value'] = strtoupper(trim((string) $raw, "\"' \t\n\r\0\x0B")) ?: 'EUR';
        } elseif ($key === 'bank_transfer.confirmation_delay_hours') {
            $data['hours_value'] = (int) trim((string) $raw, "\"' ");
        } elseif ($key === 'registration.require_terms_acceptance') {
            $data['bool_value'] = in_array(strtolower(trim((string) $raw, "\"' ")), ['true', '1', 'yes'], true);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $key = $this->record->setting_key ?? ($data['setting_key'] ?? '');

        if ($key === 'site.branding') {
            $existing = SiteSettingsService::get();
            $payload = array_merge($existing, [
                'site_name'       => $data['branding_site_name'] ?? 'Brasil·É',
                'logo_path'       => $data['branding_logo_path'] ?: null,
                'tagline_fr'      => $data['branding_tagline_fr'] ?? '',
                'tagline_en'      => $data['branding_tagline_en'] ?? '',
                'tagline_pt'      => $data['branding_tagline_pt'] ?? '',
                'contact_email'   => $data['branding_contact_email'] ?? '',
                'contact_phone'   => $data['branding_contact_phone'] ?? '',
                'contact_address' => $data['branding_contact_address'] ?? '',
                'website_url'     => $data['branding_website_url'] ?? '',
            ]);
            unset($payload['logo_url']);
            $data['value_json'] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        } elseif ($key === 'system.default_currency') {
            $curr = strtoupper((string) ($data['currency_value'] ?? 'EUR'));
            $data['value_json'] = json_encode($curr);
            session(['active_currency' => $curr]);
        } elseif ($key === 'bank_transfer.confirmation_delay_hours') {
            $data['value_json'] = (string) ((int) ($data['hours_value'] ?? 72));
        } elseif ($key === 'registration.require_terms_acceptance') {
            $data['value_json'] = ! empty($data['bool_value']) ? 'true' : 'false';
        }

        // Retirer les champs virtuels avant enregistrement SQL
        unset(
            $data['branding_site_name'],
            $data['branding_logo_path'],
            $data['branding_tagline_fr'],
            $data['branding_tagline_en'],
            $data['branding_tagline_pt'],
            $data['branding_contact_email'],
            $data['branding_contact_phone'],
            $data['branding_contact_address'],
            $data['branding_website_url'],
            $data['currency_value'],
            $data['hours_value'],
            $data['bool_value']
        );

        return $data;
    }
}
