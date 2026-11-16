<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brasil·É — Console Artisan & Maintenance</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            padding: 2rem 1rem;
            line-height: 1.5;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #1e293b;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .header h1 {
            font-size: 1.4rem;
            font-weight: 800;
            color: #ffffff;
        }
        .header h1 span { color: #10b981; }
        .db-badge {
            background: #1e293b;
            border: 1px solid #334155;
            padding: 0.4rem 0.85rem;
            border-radius: 999px;
            font-size: 0.82rem;
            color: #94a3b8;
        }
        .db-badge strong { color: #38bdf8; }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .grid-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 0.85rem;
        }
        .cmd-btn {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.25rem;
            width: 100%;
            padding: 0.9rem 1.1rem;
            border-radius: 10px;
            border: 1px solid #475569;
            background: #0f172a;
            color: #f8fafc;
            cursor: pointer;
            text-align: left;
            transition: all 0.18s ease;
        }
        .cmd-btn:hover {
            border-color: #38bdf8;
            background: #172554;
            transform: translateY(-1px);
        }
        .cmd-btn.primary {
            background: #065f46;
            border-color: #10b981;
        }
        .cmd-btn.primary:hover {
            background: #047857;
        }
        .cmd-btn.warning {
            background: #7c2d12;
            border-color: #f97316;
        }
        .cmd-btn.warning:hover {
            background: #9a3412;
        }
        .cmd-btn .label {
            font-weight: 700;
            font-size: 0.95rem;
        }
        .cmd-btn .code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.76rem;
            color: #94a3b8;
        }
        .cmd-btn.primary .code, .cmd-btn.warning .code {
            color: #e2e8f0;
        }
        .custom-form {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .input-prefix-wrap {
            flex: 1;
            min-width: 240px;
            display: flex;
            align-items: center;
            background: #0f172a;
            border: 1px solid #475569;
            border-radius: 10px;
            padding: 0 0.9rem;
        }
        .input-prefix-wrap span {
            font-family: monospace;
            color: #10b981;
            font-weight: 700;
            margin-right: 0.5rem;
            white-space: nowrap;
        }
        .input-prefix-wrap input {
            width: 100%;
            background: transparent;
            border: none;
            color: #ffffff;
            font-family: monospace;
            font-size: 0.92rem;
            padding: 0.8rem 0;
            outline: none;
        }
        .submit-btn {
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.92rem;
        }
        .submit-btn:hover { background: #1d4ed8; }
        .terminal {
            background: #020617;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 1.25rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            white-space: pre-wrap;
            word-break: break-word;
            color: #a7f3d0;
            min-height: 180px;
            max-height: 520px;
            overflow-y: auto;
        }
        .terminal.error { color: #fca5a5; border-color: #ef4444; }
        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.65rem;
            font-size: 0.85rem;
            color: #94a3b8;
        }
        .badge-ok { color: #4ade80; font-weight: 700; }
        .badge-err { color: #f87171; font-weight: 700; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Brasil<span>·É</span> — Console Artisan</h1>
        <div class="db-badge">
            Base connectée : <strong>{{ config('database.connections.mysql.database') }}</strong> ({{ config('database.connections.mysql.host') }})
        </div>
    </div>

    <div class="card">
        <div class="card-title">Actions Rapides (1 clic)</div>
        <div class="grid-buttons">
            <form method="POST" action="/ops">
                <input type="hidden" name="preset" value="migrate_and_seed">
                <button type="submit" class="cmd-btn primary">
                    <span class="label">🚀 Migrer + Seeder (Complet)</span>
                    <span class="code">migrate --force && db:seed --force</span>
                </button>
            </form>

            <form method="POST" action="/ops">
                <input type="hidden" name="preset" value="migrate">
                <button type="submit" class="cmd-btn">
                    <span class="label">📦 Lancer les nouvelles migrations</span>
                    <span class="code">php artisan migrate --force</span>
                </button>
            </form>

            <form method="POST" action="/ops">
                <input type="hidden" name="preset" value="seed_all">
                <button type="submit" class="cmd-btn">
                    <span class="label">🌱 Lancer tous les Seeders</span>
                    <span class="code">php artisan db:seed --force</span>
                </button>
            </form>

            <form method="POST" action="/ops">
                <input type="hidden" name="preset" value="seed_admin">
                <button type="submit" class="cmd-btn">
                    <span class="label">👤 Créer / Vérifier Compte Admin</span>
                    <span class="code">db:seed --class=AdminUserSeeder</span>
                </button>
            </form>

            <form method="POST" action="/ops">
                <input type="hidden" name="preset" value="seed_forms">
                <button type="submit" class="cmd-btn primary">
                    <span class="label">📝 Seeder Formulaire d'Inscription</span>
                    <span class="code">RegistrationFormAndSettingsSeeder</span>
                </button>
            </form>

            <form method="POST" action="/ops">
                <input type="hidden" name="preset" value="seed_academic">
                <button type="submit" class="cmd-btn">
                    <span class="label">🎓 Seeder Niveaux, Villes & Universités</span>
                    <span class="code">AcademicReference + PartnerUniversity</span>
                </button>
            </form>

            <form method="POST" action="/ops">
                <input type="hidden" name="preset" value="migrate_status">
                <button type="submit" class="cmd-btn">
                    <span class="label">📋 État des Migrations</span>
                    <span class="code">php artisan migrate:status</span>
                </button>
            </form>

            <form method="POST" action="/ops">
                <input type="hidden" name="preset" value="optimize_clear">
                <button type="submit" class="cmd-btn">
                    <span class="label">🧹 Vider tous les Caches</span>
                    <span class="code">php artisan optimize:clear</span>
                </button>
            </form>

            <form method="POST" action="/ops">
                <input type="hidden" name="preset" value="filament_assets">
                <button type="submit" class="cmd-btn">
                    <span class="label">🎨 Publier Assets Filament</span>
                    <span class="code">php artisan filament:assets</span>
                </button>
            </form>

            <form method="POST" action="/ops" onsubmit="return confirm('Attention : cela va supprimer toutes les tables et recréer la base à zéro. Confirmer ?');">
                <input type="hidden" name="preset" value="migrate_fresh_seed">
                <button type="submit" class="cmd-btn warning">
                    <span class="label">⚠️ Réinitialiser Base (Fresh + Seed)</span>
                    <span class="code">migrate:fresh --seed --force</span>
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Commande Artisan Personnalisée</div>
        <form method="POST" action="/ops" class="custom-form">
            <div class="input-prefix-wrap">
                <span>php artisan</span>
                <input type="text" name="custom_command" placeholder="ex: migrate --force, db:seed --class=PartnerUniversitySeeder --force, route:list..." value="{{ $executedCommand ?? '' }}" required>
            </div>
            <button type="submit" class="submit-btn">Exécuter ▶</button>
        </form>
    </div>

    <div class="card">
        <div class="status-bar">
            <span>Sortie Console (Terminal)</span>
            @if(isset($status))
                @if($status === 'success')
                    <span class="badge-ok">● Exécuté avec succès</span>
                @else
                    <span class="badge-err">● Erreur lors de l'exécution</span>
                @endif
            @endif
        </div>
        <div class="terminal {{ (isset($status) && $status === 'error') ? 'error' : '' }}">{{ $output ?? "Prêt. Cliquez sur un bouton ci-dessus ou tapez une commande Artisan." }}</div>
    </div>
</div>
</body>
</html>
