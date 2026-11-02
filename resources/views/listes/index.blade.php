<x-layout titre="Mes listes" :noindex="true">
    <section class="section conteneur">
        <p class="surtitre">Mes listes</p>
        <h1 class="titre-page">Bonjour {{ auth()->user()->name }}, <em>on prépare quoi ?</em></h1>

        <div class="liste-actions">
            <a href="{{ route('secret-santa') }}" class="bouton bouton-contour bouton-mini">Mes Secret Santa</a>
        </div>

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

        @if ($archives->isNotEmpty())
            <h2 class="h3 cadeaux-titre">Archives</h2>
            <ul class="listes">
                @foreach ($archives as $liste)
                    <li class="carte liste-carte">
                        <span class="pastille">{{ $liste->libelleEvenement() }}</span>
                        <strong>{{ $liste->title }}</strong>
                        <div class="liste-actions">
                            <a href="{{ route('listes.show', $liste) }}" class="lien">Voir</a>
                            <form method="POST" action="{{ route('listes.archiver', $liste) }}">
                                @csrf
                                <button type="submit" class="bouton bouton-contour bouton-mini">Ressortir</button>
                            </form>
                            <form method="POST" action="{{ route('listes.destroy', $liste) }}" onsubmit="return confirm('Supprimer « {{ addslashes($liste->title) }} » pour de bon ? Les cadeaux et les réservations partent avec.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bouton bouton-encre bouton-mini">Supprimer</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('deconnexion') }}" class="deconnexion">
            @csrf
            <button type="submit" class="lien">Me déconnecter</button>
        </form>
    </section>
</x-layout>
