@props(['titre', 'action', 'bouton'])
{{-- Carte de formulaire des pages de compte : titre, message de statut, champs, bouton principal. --}}
<section class="compte conteneur">
    <div class="carte compte-carte">
        <h1>{!! $titre !!}</h1>
        @if (session('statut'))
            <p class="message" role="status">{{ session('statut') }}</p>
        @endif
        <form method="POST" action="{{ $action }}" class="formulaire" novalidate>
            @csrf
            {{ $slot }}
            <button type="submit" class="bouton bouton-plein">{{ $bouton }}</button>
        </form>
        {{ $pied ?? '' }}
    </div>
</section>
