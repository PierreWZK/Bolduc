@props(['titre' => null, 'description' => 'Crée ta liste de cadeaux, envoie le lien. Tes proches réservent ce qu\'ils offrent, et toi tu ne vois ni qui, ni quoi.', 'noindex' => false])
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php($titreComplet = $titre ? $titre.' · Bolduc' : 'Bolduc · La liste de cadeaux qui garde la surprise')
    <title>{{ $titreComplet }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($noindex)
        <meta name="robots" content="noindex">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Bolduc">
    <meta property="og:title" content="{{ $titreComplet }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('og.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Bolduc, la liste de cadeaux qui garde la surprise">
    <meta name="twitter:card" content="summary_large_image">
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
