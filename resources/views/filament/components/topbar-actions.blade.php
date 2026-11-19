@php
    $activeCurrency = \App\Services\CurrencyService::getActiveCurrency();
    $rateXof = \App\Services\CurrencyService::getEurToXofRate();
    $rateBrl = \App\Services\CurrencyService::getEurToBrlRate();
@endphp

<div style="display: flex; flex-direction: row; align-items: center; gap: 10px; margin: 0 10px;">
    {{-- Liste déroulante de sélection de Devise (€ / XOF / R$) --}}
    <div style="position: relative; display: inline-flex; align-items: center;">
        <select
            aria-label="Sélectionner la devise du système"
            title="Changer la devise globale du système"
            onchange="window.location.href = this.value"
            style="appearance: none; -webkit-appearance: none; -moz-appearance: none; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 8px; padding: 5px 28px 5px 12px; font-size: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); outline: none; transition: all 0.15s ease;">
            <option value="{{ route('admin.switch-currency', 'EUR') }}" {{ $activeCurrency === 'EUR' ? 'selected' : '' }}>
                €
            </option>
            <option value="{{ route('admin.switch-currency', 'XOF') }}" {{ $activeCurrency === 'XOF' ? 'selected' : '' }}>
                XOF
            </option>
            <option value="{{ route('admin.switch-currency', 'BRL') }}" {{ $activeCurrency === 'BRL' ? 'selected' : '' }}>
                R$
            </option>
        </select>
        <i class="fa-solid fa-chevron-down"
           style="position: absolute; right: 10px; font-size: 9px; color: #64748b; pointer-events: none;"></i>
    </div>

    {{-- Taux de change en direct selon Internet --}}
    <div style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; white-space: nowrap;" title="Taux de parité garanti & cours direct internet">
        <i class="fa-solid fa-globe" style="font-size: 10px; color: #059669;"></i>
        <span>1 € = {{ number_format($rateXof, 2, ',', ' ') }} FCFA • R$ {{ number_format($rateBrl, 2, ',', ' ') }}</span>
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
