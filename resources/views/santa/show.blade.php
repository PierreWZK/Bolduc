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
        @forelse ($groupe->membres as $membre)
            <article class="cadeau-fiche">
                <div class="cadeau-texte">
                    <strong>{{ $membre->name }}</strong>
                    <span>
                        {{ $membre->wishes ?: 'Pas encore d\'envies' }}
                        @if ($membre->exclus->isNotEmpty())
                            · ne tire pas : {{ $membre->exclus->pluck('name')->join(', ') }}
                        @endif
                    </span>
                </div>
            </article>
        @empty
            <p class="legende">Pas encore de participant. Partage le lien ci-dessus.</p>
        @endforelse

        @if ($groupe->estTire())
            <div class="carte santa-tire">
                <p><strong>Le tirage a eu lieu.</strong> Tu ne vois pas qui tire qui : chacun découvre sa cible avec son lien.</p>
            </div>
        @else
            <div class="carte ajout-cadeau">
                <h2 class="h3">Ne pas tirer ensemble</h2>
                <p class="legende">Un couple, des colocataires… Les exclus ne se tirent pas, dans aucun sens.</p>
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
    </section>
</x-layout>
