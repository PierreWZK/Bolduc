<x-layout :titre="$groupe->name" :noindex="true">
    @php $lien = route('santa.invite', $groupe->share_token); @endphp
    <section class="section conteneur">
        <a href="{{ route('secret-santa') }}" class="lien">Mes groupes</a>

        @if (session('statut'))
            <p class="message" role="status">{{ session('statut') }}</p>
        @endif

        <p class="surtitre">Secret Santa</p>
        <h1 class="titre-page">{{ $groupe->name }}</h1>

        <div class="carte lien-secret" x-data="{ copie: false }">
            <h2 class="h3">Le lien pour les participants</h2>
            <p>Envoie-le à tout le monde. Chacun donne son prénom et ses envies, sans compte.</p>
            <div class="lien-partage">
                <input type="text" value="{{ $lien }}" readonly aria-label="Lien du Secret Santa" @focus="$el.select()">
                <button type="button" class="bouton bouton-encre bouton-mini" @click="navigator.clipboard.writeText(@js($lien)); copie = true" x-text="copie ? 'Copié' : 'Copier'">Copier</button>
            </div>
        </div>

        <h2 class="h3 cadeaux-titre">Participants ({{ $groupe->membres->count() }})</h2>
        @error('participants')
            <p class="erreur">{{ $message }}</p>
        @enderror
        @forelse ($groupe->membres as $membre)
            <article class="cadeau-fiche" x-data="{ edition: false }">
                <div class="cadeau-texte">
                    <strong>{{ $membre->name }}</strong>
                    <span>
                        {{ $membre->magic_word ? ($membre->wishes ?: 'Pas encore d\'envies') : 'Ajouté par toi : il rejoint avec ce prénom' }}
                        @if ($membre->exclus->isNotEmpty())
                            · ne tire pas : {{ $membre->exclus->pluck('name')->join(', ') }}
                        @endif
                    </span>
                </div>
                <div class="liste-actions" x-show="!edition">
                    <button type="button" class="lien" @click="edition = true">Renommer</button>
                    @unless ($groupe->estTire())
                        <form method="POST" action="{{ route('santa.membres.retirer', [$groupe, $membre]) }}" onsubmit="return confirm('Retirer {{ addslashes($membre->name) }} du groupe ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="lien">Retirer</button>
                        </form>
                    @endunless
                </div>
                <form method="POST" action="{{ route('santa.membres.renommer', [$groupe, $membre]) }}" class="liste-actions" x-show="edition" x-cloak>
                    @csrf
                    @method('PUT')
                    <label class="sr-only" for="nom-{{ $membre->id }}">Nouveau prénom</label>
                    <input id="nom-{{ $membre->id }}" type="text" name="name" value="{{ $membre->name }}" maxlength="40" required class="champ-mini">
                    <button type="submit" class="bouton bouton-encre bouton-mini">OK</button>
                    <button type="button" class="lien" @click="edition = false">Annuler</button>
                </form>
            </article>
        @empty
            <p class="legende">Pas encore de participant. Partage le lien ci-dessus, ou ajoute quelqu'un.</p>
        @endforelse

        @unless ($groupe->estTire())
            <form method="POST" action="{{ route('santa.membres.ajouter', $groupe) }}" class="liste-actions santa-ajout">
                @csrf
                <label class="sr-only" for="nouveau-membre">Prénom à ajouter</label>
                <input id="nouveau-membre" type="text" name="name" maxlength="40" required placeholder="Prénom de la personne" class="champ-mini">
                <button type="submit" class="bouton bouton-contour bouton-mini">Ajouter</button>
            </form>
        @endunless

        @if ($groupe->estTire())
            <div class="carte santa-tire">
                <p><strong>Le tirage a eu lieu.</strong> Tu ne vois pas qui tire qui : chacun découvre sa cible avec son lien.</p>
                <p><a class="bouton bouton-plein" href="{{ route('santa.soiree', $groupe) }}">Lancer le mode soirée</a></p>
            </div>
        @else
            <div class="carte ajout-cadeau">
                <h2 class="h3">Ne pas tirer ensemble</h2>
                <p class="legende">Un couple, des colocataires… Les exclus ne se tirent pas, dans aucun sens.</p>
                @php
                    // Chaque exclusion est stockée dans les deux sens : on n'affiche la paire qu'une fois.
                    $paires = $groupe->membres->flatMap(fn ($m) => $m->exclus->where('id', '>', $m->id)->map(fn ($e) => [$m, $e]));
                @endphp
                @foreach ($paires as [$a, $b])
                    <div class="liste-actions">
                        <span>{{ $a->name }} et {{ $b->name }}</span>
                        <form method="POST" action="{{ route('santa.exclusions.retirer', $groupe) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="membre" value="{{ $a->id }}">
                            <input type="hidden" name="exclu" value="{{ $b->id }}">
                            <button type="submit" class="lien">Retirer</button>
                        </form>
                    </div>
                @endforeach
                @if ($groupe->membres->count() >= 2)
                    <form method="POST" action="{{ route('santa.exclure', $groupe) }}" class="formulaire">
                        @csrf
                        <div class="ajout-ligne">
                            <div class="champ">
                                <label for="membre">Première personne</label>
                                <select id="membre" name="membre" required>
                                    @foreach ($groupe->membres as $membre)
                                        <option value="{{ $membre->id }}">{{ $membre->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="champ">
                                <label for="exclu">Ne tire pas</label>
                                <select id="exclu" name="exclu" required>
                                    @foreach ($groupe->membres as $membre)
                                        <option value="{{ $membre->id }}">{{ $membre->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="bouton bouton-contour">Ajouter l'exclusion</button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('santa.tirer', $groupe) }}" class="santa-tirage">
                @csrf
                @error('tirage')
                    <p class="erreur">{{ $message }}</p>
                @enderror
                <button type="submit" class="bouton bouton-plein bouton-grand" @disabled($groupe->membres->count() < 3)>Lancer le tirage</button>
                @if ($groupe->membres->count() < 3)
                    <p class="legende">Il faut au moins trois participants.</p>
                @endif
            </form>
        @endif

        <form method="POST" action="{{ route('santa.destroy', $groupe) }}" class="deconnexion" onsubmit="return confirm('Supprimer « {{ addslashes($groupe->name) }} » pour de bon ? Les participants, leurs envies et le tirage partent avec.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bouton bouton-encre bouton-mini">Supprimer ce Secret Santa</button>
        </form>
    </section>
</x-layout>
