<x-layout :titre="$liste->title" :noindex="true">
    <section class="section conteneur">
        <a href="{{ route('listes') }}" class="lien">Mes listes</a>

        @if (session('statut'))
            <p class="message" role="status">{{ session('statut') }}</p>
        @endif

        <p class="surtitre">{{ $liste->libelleEvenement() }}</p>
        <h1 class="titre-page">{{ $liste->title }}</h1>

        <div class="liste-infos">
            @if ($liste->event_date)
                <p>
                    <strong>{{ $liste->event_date->locale('fr')->isoFormat('D MMMM YYYY') }}</strong>
                    @if ($liste->joursAvant() > 0)
                        · J-{{ $liste->joursAvant() }}
                    @elseif ($liste->joursAvant() === 0)
                        · C'est aujourd'hui !
                    @else
                        · La date est passée
                    @endif
                </p>
            @else
                <p>Pas de date fixée.</p>
            @endif
            <p class="legende">Gérée par {{ $liste->proprietaires->pluck('name')->join(' et ') }}</p>
        </div>

        <div class="carte lien-secret">
            <h2 class="h3">Le lien pour tes proches</h2>
            <p>Il arrive avec la vue invité, à l'étape suivante : tes proches y réservent, et toi tu ne vois rien.</p>
        </div>
    </section>
</x-layout>
