@php
    $activeCurrency = \App\Services\CurrencyService::getActiveCurrency();
    $rate = \App\Services\CurrencyService::getEurToXofRate();
@endphp

<div style="display: flex; flex-direction: row; align-items: center; gap: 10px; margin: 0 10px;">
    {{-- Sélecteur de Devise EUR / FCFA avec conversion en direct --}}
    <div style="display: inline-flex; align-items: center; background-color: #f1f5f9; border-radius: 8px; padding: 2px; border: 1px solid #cbd5e1;" title="Taux direct internet : 1 € = {{ number_format($rate, 2, ',', ' ') }} FCFA">
        <a href="{{ route('admin.switch-currency', 'EUR') }}" 
           title="Basculer tout le système en Euros (€)"
           style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-decoration: none; transition: all 0.15s ease; {{ $activeCurrency === 'EUR' ? 'background-color: #ffffff; color: #0f172a; box-shadow: 0 1px 2px rgba(0,0,0,0.1);' : 'color: #64748b;' }}">
            <i class="fa-solid fa-euro-sign" style="font-size: 10px; color: {{ $activeCurrency === 'EUR' ? '#059669' : '#94a3b8' }};"></i>
            <span>EUR (€)</span>
        </a>
        <a href="{{ route('admin.switch-currency', 'XOF') }}" 
           title="Basculer tout le système en Francs CFA (FCFA)"
           style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-decoration: none; transition: all 0.15s ease; {{ $activeCurrency === 'XOF' ? 'background-color: #ffffff; color: #0f172a; box-shadow: 0 1px 2px rgba(0,0,0,0.1);' : 'color: #64748b;' }}">
            <i class="fa-solid fa-coins" style="font-size: 10px; color: {{ $activeCurrency === 'XOF' ? '#d97706' : '#94a3b8' }};"></i>
            <span>FCFA (XOF)</span>
        </a>
    </div>

    {{-- Taux de change en direct selon Internet --}}
    <div style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; white-space: nowrap;" title="Taux de parité garanti & cours direct internet">
        <i class="fa-solid fa-globe" style="font-size: 10px; color: #059669;"></i>
        <span>1 € = {{ number_format($rate, 2, ',', ' ') }} FCFA</span>
    </div>

    {{-- Indicateur Brasília --}}
    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 9999px; font-size: 12px; font-weight: 600; background-color: #f8fafc; color: #334155; border: 1px solid #e2e8f0; white-space: nowrap;">
        <i class="fa-solid fa-circle" style="font-size: 7px; color: #10b981;"></i>
        <span>Brasília</span>
        <span style="color: #94a3b8; font-weight: 400; font-size: 11px;">UTC-3</span>
    </div>

    {{-- Bouton vers le site public --}}
    <a href="http://localhost:5173" target="_blank" rel="noopener noreferrer" 
       title="Ouvrir le portail public dans un nouvel onglet"
       style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; background-color: #ffffff; color: #1e293b; border: 1px solid #cbd5e1; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: all 0.15s ease;">
        <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 12px; color: #059669;"></i>
        <span>Site Public</span>
    </a>

    {{-- Bouton Inscriptions --}}
    <a href="{{ url('/admin/enrollments') }}" 
       title="Gérer les dossiers d’inscription"
       style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; background-color: #059669; color: #ffffff; text-decoration: none; cursor: pointer; white-space: nowrap; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); border: 1px solid #047857; transition: all 0.15s ease;">
        <i class="fa-solid fa-graduation-cap" style="font-size: 12px; color: #ffffff;"></i>
        <span>Inscriptions</span>
    </a>
</div>
