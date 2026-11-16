@if (request()->routeIs('filament.*.auth.*'))
<style>
    /* 1. Masquer le logo au-dessus du "Sign in" uniquement sur les pages d'authentification */
    .fi-simple-header .fi-logo {
        display: none !important;
    }

    /* 2. Fond de page d'authentification */
    body.fi-body {
        background-color: #0d1527 !important;
        min-height: 100vh !important;
        position: relative !important;
    }

    /* 3. Logo centré en arrière-plan uniquement sur la page de connexion */
    body.fi-body::before {
        content: "" !important;
        position: fixed !important;
        inset: 0 !important;
        z-index: 0 !important;
        background-image: 
            radial-gradient(circle at center, rgba(13, 21, 39, 0.45) 0%, rgba(13, 21, 39, 0.88) 100%),
            url('/logo.png') !important;
        background-repeat: no-repeat !important;
        background-position: center center !important;
        background-size: min(580px, 80vw) auto !important;
        pointer-events: none !important;
    }

    /* 4. Layout d'authentification transparent pour laisser voir le fond */
    .fi-simple-layout,
    .fi-simple-main-ctn {
        background: transparent !important;
        position: relative !important;
        z-index: 1 !important;
    }

    /* 5. Carte de connexion en verre dépoli */
    main#fi-main-content,
    .fi-simple-main {
        background: rgba(255, 255, 255, 0.92) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        border-radius: 1.5rem !important;
        border: 1px solid rgba(255, 255, 255, 0.6) !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7) !important;
    }

    /* Titre Sign in */
    .fi-simple-header-heading {
        font-size: 1.85rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        letter-spacing: -0.02em !important;
    }
</style>
@endif
