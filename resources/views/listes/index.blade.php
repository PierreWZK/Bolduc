<x-layout titre="Mes listes" :noindex="true">
    <section class="section conteneur">
        <p class="surtitre">Mes listes</p>
        <h1 class="titre-page">Bonjour {{ auth()->user()->name }}, <em>on prépare quoi ?</em></h1>

        @if (session('statut'))
            <p class="message" role="status">{{ session('statut') }}</p>
        @endif

        @if ($listes->isEmpty())
            <div class="carte vide">
                <span class="rond teinte-rose"><x-icone nom="cadeau" /></span>
                <h2 class="h3">Pas encore de liste</h2>
                <p>Noël, anniversaire, mariage, crémaillère, naissance : crée ta première liste et envoie le lien à tes proches.</p>
                <a href="{{ route('listes.create') }}" class="bouton bouton-plein">Créer ma liste</a>
            </div>
        @else
            <ul class="listes">
                @foreach ($listes as $liste)
                    <li>
                        <a href="{{ route('listes.show', $liste) }}" class="carte liste-carte">
                            <span class="pastille pastille-rose">{{ $liste->libelleEvenement() }}</span>
                            <strong>{{ $liste->title }}</strong>
                            <span class="legende">{{ $liste->event_date ? $liste->event_date->locale('fr')->isoFormat('D MMMM YYYY') : 'Sans date' }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <a href="{{ route('listes.create') }}" class="bouton bouton-contour">Nouvelle liste</a>
        @endif

        <form method="POST" action="{{ route('deconnexion') }}" class="deconnexion">
            @csrf
            <button type="submit" class="lien">Me déconnecter</button>
        </form>
    </section>
</x-layout>
