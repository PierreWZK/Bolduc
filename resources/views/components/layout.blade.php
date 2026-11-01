@props(['titre' => null, 'description' => 'Crée ta liste de cadeaux, envoie le lien. Tes proches réservent ce qu\'ils offrent, et toi tu ne vois ni qui, ni quoi.', 'noindex' => false])
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titre ? $titre.' · Bolduc' : 'Bolduc · La liste de cadeaux qui garde la surprise' }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($noindex)
        <meta name="robots" content="noindex">
    @endif
    <meta name="theme-color" content="#FFF8F3">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=bricolage-grotesque:700,800|figtree:400,500,600,700|instrument-serif:400i&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body>
    <header class="entete conteneur">
        <a href="{{ route('accueil') }}" class="logo" aria-label="Bolduc, accueil">
            <x-noeud class="logo-noeud" />
            <span>Bolduc</span>
        </a>
        @auth
            <a href="{{ route('listes') }}" class="bouton bouton-contour">Mes listes</a>
        @else
            <nav class="entete-liens">
                <a href="{{ route('login') }}" class="lien">Connexion</a>
                <a href="{{ route('inscription') }}" class="bouton bouton-contour">Créer ma liste</a>
            </nav>
        @endauth
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="pied conteneur">
        <a href="{{ route('accueil') }}" class="logo logo-petit" aria-label="Bolduc, accueil">
            <x-noeud class="logo-noeud" />
            <span>Bolduc</span>
        </a>
        <p>Bolduc · un projet <a href="https://projectp.fr/">30 jours, 30 projets</a></p>
    </footer>

    @livewireScripts
</body>
</html>
