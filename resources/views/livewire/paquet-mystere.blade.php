<div class="carte jeu">
    <p class="surtitre">Paquet mystère</p>

    <figure class="jeu-photo">
        <img src="{{ asset($objet->image) }}" alt="{{ $termine ? $objet->name : 'Un objet emballé' }}" loading="lazy" style="filter: blur({{ $flou }}px)">
        <figcaption class="legende">
            @if ($termine)
                {{ $objet->name }}@if ($objet->credit) · {{ $objet->credit }} @endif
            @else
                Il reste {{ $couches }} couche(s) de papier.
            @endif
        </figcaption>
    </figure>

    @if ($termine)
        @if ($gagne)
            <p class="santa-nom">Trouvé avec {{ $couches }} couche(s) restante(s) !</p>
        @else
            <p class="santa-nom">Perdu. C'était : {{ $objet->name }}.</p>
        @endif
        <p class="legende">Série : 🔥 {{ $serie }} jour(s)</p>

        <div x-data="{ copie: false }">
            <pre class="jeu-partage">{{ $partage }}</pre>
            <button type="button" class="bouton bouton-encre bouton-mini" @click="navigator.clipboard.writeText(@js($partage)); copie = true" x-text="copie ? 'Copié' : 'Copier le résultat'">Copier le résultat</button>
        </div>
    @else
        {{-- Pas de <datalist> : Safari sur iPhone ne l'affiche pas. Liste filtrée maison, un bouton par objet. --}}
        <div class="champ" x-data="{ q: '', noms: @js($objets), get trouves() { const t = this.q.trim().toLowerCase(); return t ? this.noms.filter(n => n.toLowerCase().includes(t)) : this.noms } }">
            <label for="choix-paquet">Qu'est-ce qu'il y a dedans ?</label>
            <input id="choix-paquet" type="search" x-model="q" autocomplete="off" placeholder="Filtrer : bougie, casque…">
            <ul class="jeu-choix">
                <template x-for="nom in trouves" :key="nom">
                    <li><button type="button" class="bouton bouton-contour bouton-mini" @click="$wire.$set('choix', nom, false); $wire.deviner()" x-text="nom"></button></li>
                </template>
            </ul>
            @error('choix') <small class="message">{{ $message }}</small> @enderror
        </div>
    @endif
</div>
