{{-- Font Awesome 6 Pro / Free CDN --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    /* Sécurité stricte pour les dimensions du logo dans le header et la topbar */
    .fi-logo img,
    .fi-sidebar-header img,
    .fi-topbar-header img,
    .fi-topbar img {
        max-height: 40px !important;
        height: 38px !important;
        width: auto !important;
        object-fit: contain !important;
        display: block !important;
    }

    /* Sécurité pour les actions en haut à droite */
    .fi-topbar-actions {
        display: flex !important;
        align-items: center !important;
    }

    @if (request()->routeIs('filament.*.pages.dashboard'))
    /* Masquer le titre Dashboard en haut de page */
    .fi-header,
    .fi-header-heading {
        display: none !important;
    }
    @endif
</style>
