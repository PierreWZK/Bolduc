<x-layout :titre="$liste->title" :noindex="true">
    <section class="section conteneur">
        <a href="{{ route('listes') }}" class="lien">Mes listes</a>

        @if (session('statut'))
            <p class="message" role="status">{{ session('statut') }}</p>
        @endif

        <p class="surtitre">{{ $liste->libelleEvenement() }}</p>
        <h1 class="titre-page">{{ $liste->title }}</h1>

        <div class="liste-infos">
            <x-echeance :liste="$liste" />
            <p class="legende">Gérée par {{ $liste->proprietaires->pluck('name')->join(' et ') }}</p>
        </div>

        @php $lien = route('invite', $liste->share_token); @endphp
        <div class="carte lien-secret" x-data="{ copie: false }">
            <h2 class="h3">Le lien pour tes proches</h2>
            <p>Envoie-le par message ou par mail. Tes proches réservent, toi tu ne vois rien.</p>
            <div class="lien-partage">
                <input type="text" value="{{ $lien }}" readonly aria-label="Lien de la liste" @focus="$el.select()">
                <button type="button" class="bouton bouton-encre bouton-mini" @click="navigator.clipboard.writeText(@js($lien)); copie = true" x-text="copie ? 'Copié' : 'Copier'">Copier</button>
            </div>
        </div>

        <h2 class="h3 cadeaux-titre">Les cadeaux <span class="legende">({{ $liste->cadeaux->count() }})</span></h2>

        @forelse ($liste->cadeaux as $cadeau)
            <article class="cadeau-fiche">
                @if ($cadeau->image_url)
                    <img src="{{ $cadeau->image_url }}" alt="" loading="lazy" width="72" height="72" class="cadeau-photo">
                @else
                    <span class="cadeau-icone teinte-or"><x-icone nom="cadeau" /></span>
                @endif

                <div class="cadeau-texte">
                    <strong>{{ $cadeau->title }}</strong>
                    <span>
                        @if ($cadeau->price) {{ number_format($cadeau->price, 2, ',', ' ') }} € · @endif
                        {{ $cadeau->quantity }} {{ $cadeau->quantity > 1 ? 'exemplaires' : 'exemplaire' }}
                        @if ($cadeau->note) · {{ $cadeau->note }} @endif
                    </span>
                    @if ($cadeau->url)
                        <a href="{{ $cadeau->url }}" rel="nofollow noopener" target="_blank" class="lien">Voir la boutique</a>
                    @endif
                </div>

                @if ($cadeau->favorite)
                    <span class="pastille pastille-rose">Coup de cœur</span>
                @endif
                @if ($cadeau->shared)
                    <span class="pastille pastille-encre">À plusieurs</span>
                @endif

                <form method="POST" action="{{ route('cadeaux.destroy', [$liste, $cadeau]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="lien">Retirer</button>
                </form>
            </article>
        @empty
            <p class="legende">Pas encore de cadeau. Colle un lien de boutique ci-dessous.</p>
        @endforelse

        <div class="carte ajout-cadeau">
            <h2 class="h3">Ajouter un cadeau</h2>
            <form method="POST" action="{{ route('cadeaux.store', $liste) }}" class="formulaire">
                @csrf
                <x-champ nom="url" libelle="Lien de la boutique" type="url" aide="Titre, image et prix sont lus tout seuls." />
                <x-champ nom="title" libelle="Titre (si la boutique ne se lit pas)" />
                <div class="ajout-ligne">
                    <x-champ nom="price" libelle="Prix (€)" type="number" step="0.01" min="0" />
                    <x-champ nom="quantity" libelle="Quantité" type="number" min="1" max="99" value="1" />
                </div>
                <x-champ nom="note" libelle="Une précision (taille, couleur…)" />
                <label class="coeur">
                    <input type="checkbox" name="favorite" value="1">
                    Coup de cœur
                </label>
                <label class="coeur">
                    <input type="checkbox" name="shared" value="1">
                    À plusieurs : tes proches se partagent le prix
                </label>
                @error('shared')
                    <p class="erreur">{{ $message }}</p>
                @enderror
                <button type="submit" class="bouton bouton-plein">Ajouter à la liste</button>
            </form>
        </div>
    </section>
</x-layout>
