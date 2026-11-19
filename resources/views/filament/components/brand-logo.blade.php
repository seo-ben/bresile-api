@php
    $branding = \App\Services\SiteSettingsService::get();
    $logoUrl = \App\Services\SiteSettingsService::getLogoUrl($branding);
    $siteName = $branding['site_name'] ?: 'Brasil·É';
    $tagline = $branding['tagline_fr'] ?: 'École de Langue & Mobilité';
@endphp

<div style="display: flex; align-items: center; gap: 10px; height: 42px; max-height: 42px; overflow: hidden;">
    <img src="{{ $logoUrl }}" 
         alt="{{ $siteName }}" 
         style="height: 38px; width: auto; max-height: 38px; max-width: 48px; object-fit: contain; display: block; flex-shrink: 0;" />
    <div style="display: flex; flex-direction: column; text-align: left; line-height: 1.15; justify-content: center;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-size: 1.1rem; font-weight: 900; letter-spacing: -0.02em; color: #0f172a;">{{ $siteName }}</span>
            <span style="display: inline-block; padding: 1px 5px; border-radius: 4px; font-size: 9px; font-weight: 800; background-color: #d1fae5; color: #065f46; text-transform: uppercase; letter-spacing: 0.05em; line-height: 1.3;">Admin</span>
        </div>
        <span style="font-size: 10px; font-weight: 600; color: #64748b; white-space: nowrap; max-width: 175px; overflow: hidden; text-overflow: ellipsis;">{{ $tagline }}</span>
    </div>
</div>
