<x-layout :titre="$liste->title" :noindex="true">
    @php
        // « 89 € » pour un prix rond, « 89,50 € » sinon.
        $euros = fn ($montant) => number_format((float) $montant, fmod((float) $montant, 1) == 0 ? 0 : 2, ',', ' ').' €';
        $teintes = ['rose', 'menthe', 'or'];
    @endphp
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

        <div class="liste-actions">
            <a href="{{ route('listes.edit', $liste) }}" class="bouton bouton-contour bouton-mini">Modifier les réglages</a>
            <form method="POST" action="{{ route('listes.archiver', $liste) }}">
                @csrf
                <button type="submit" class="bouton bouton-contour bouton-mini">{{ $liste->archived_at ? 'Ressortir des archives' : 'Archiver' }}</button>
            </form>
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

        @if ($passee)
            <h2 class="h3 cadeaux-titre">Remerciements</h2>
            <p class="legende">La date est passée : voici qui a offert quoi. Coche « merci envoyé » une fois fait.</p>

            @forelse ($liste->cadeaux as $cadeau)
                @php $donneurs = $cadeau->reservations->pluck('invite.name'); @endphp
                <article class="cadeau-fiche">
                    <div class="cadeau-texte">
                        <strong>{{ $cadeau->title }}</strong>
                        <span>
                            @if ($cadeau->secret)
                                Idée de {{ $cadeau->ajouteePar?->name }}, en secret
                            @elseif ($donneurs->isNotEmpty())
                                Offert par {{ $donneurs->join(', ') }}
                            @elseif ($cadeau->contributions->isNotEmpty())
                                Financé à plusieurs, les noms restent entre invités
                            @else
                                Personne ne l'a réservé
                            @endif
                        </span>
                    </div>
                    <form method="POST" action="{{ route('cadeaux.merci', [$liste, $cadeau]) }}">
                        @csrf
                        <button type="submit" class="pastille {{ $cadeau->thanked ? 'pastille-menthe' : 'pastille-rose' }}" aria-pressed="{{ $cadeau->thanked ? 'true' : 'false' }}">
                            {{ $cadeau->thanked ? '✓ Merci envoyé' : 'Merci pas encore envoyé' }}
                        </button>
                    </form>
                </article>
            @empty
                <p class="legende">Aucun cadeau dans cette liste.</p>
            @endforelse
        @else
        <h2 class="h3 cadeaux-titre">Les cadeaux <span class="legende">({{ $liste->cadeaux->count() }})</span></h2>

        <div class="emballages">
        @forelse ($liste->cadeaux as $cadeau)
            {{-- Côté créateur, le paquet reste fermé : aucune info de réservation n'apparaît. --}}
            <article class="emballage">
                <div class="emballage-visuel teinte-{{ $teintes[$cadeau->id % 3] }}">
                    @if ($cadeau->image_url)
                        <img src="{{ $cadeau->image_url }}" alt="" loading="lazy" width="400" height="267" class="emballage-photo">
                    @else
                        <x-icone nom="cadeau" />
                    @endif

                    @if ($cadeau->favorite)
                        <span class="emballage-badge"><x-icone nom="coeur" /> Coup de cœur</span>
                    @endif
                </div>

                <div class="emballage-corps">
                <div class="cadeau-texte">
                    <strong>{{ $cadeau->title }}</strong>
                    <span>
                        @if ($cadeau->price) {{ $euros($cadeau->price) }} @endif
                        @if ($cadeau->quantity > 1) · {{ $cadeau->quantity }} exemplaires @endif
                        @if ($cadeau->note) · {{ $cadeau->note }} @endif
                    </span>
                    @if ($cadeau->url)
                        <a href="{{ $cadeau->url }}" rel="nofollow noopener" target="_blank" class="lien">Voir la boutique</a>
                    @endif
                </div>

                <details class="cadeau-edition">
                    <summary class="lien">Modifier</summary>
                    <form method="POST" action="{{ route('cadeaux.update', [$liste, $cadeau]) }}" class="formulaire">
                        @csrf
                        @method('PUT')
                        <div class="champ">
                            <label for="titre-{{ $cadeau->id }}">Titre</label>
                            <input id="titre-{{ $cadeau->id }}" type="text" name="title" value="{{ $cadeau->title }}" maxlength="120" required>
                        </div>
                        <div class="champ">
                            <label for="url-{{ $cadeau->id }}">Lien de la boutique</label>
                            <input id="url-{{ $cadeau->id }}" type="url" name="url" value="{{ $cadeau->url }}">
                        </div>
                        <div class="ajout-ligne">
                            <div class="champ">
                                <label for="prix-{{ $cadeau->id }}">Prix (€)</label>
                                <input id="prix-{{ $cadeau->id }}" type="number" name="price" value="{{ $cadeau->price }}" step="0.01" min="0">
                            </div>
                            <div class="champ">
                                <label for="quantite-{{ $cadeau->id }}">Quantité</label>
                                <input id="quantite-{{ $cadeau->id }}" type="number" name="quantity" value="{{ $cadeau->quantity }}" min="{{ max(1, $cadeau->unitesPrises()) }}" max="99" required>
                            </div>
                        </div>
                        <div class="champ">
                            <label for="note-{{ $cadeau->id }}">Une précision (taille, couleur…)</label>
                            <input id="note-{{ $cadeau->id }}" type="text" name="note" value="{{ $cadeau->note }}" maxlength="120">
                        </div>
                        <label class="coeur">
                            <input type="checkbox" name="favorite" value="1" @checked($cadeau->favorite)>
                            Coup de cœur
                        </label>
                        @error('quantity', 'modification-'.$cadeau->id)
                            <p class="erreur">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="bouton bouton-encre bouton-mini">Enregistrer</button>
                    </form>
                </details>
                <form method="POST" action="{{ route('cadeaux.destroy', [$liste, $cadeau]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="lien">Retirer</button>
                </form>
                </div>
            </article>
        @empty
            <p class="legende">Pas encore de cadeau. Colle un lien de boutique ci-dessous.</p>
        @endforelse
        </div>
        @unless ($passee)
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
                <button type="submit" class="bouton bouton-plein">Ajouter à la liste</button>
            </form>
        </div>
        @endunless
        @endif
    </section>
</x-layout>
