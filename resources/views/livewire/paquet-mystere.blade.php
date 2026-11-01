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
        <form wire:submit="deviner" class="formulaire">
            <div class="champ">
                <label for="choix-paquet">Qu'est-ce qu'il y a dedans ?</label>
                <input id="choix-paquet" type="text" list="objets-paquet" wire:model="choix" autocomplete="off" placeholder="Commence à taper…">
                <datalist id="objets-paquet">
                    @foreach ($objets as $nom)
                        <option value="{{ $nom }}"></option>
                    @endforeach
                </datalist>
                @error('choix') <small class="message">{{ $message }}</small> @enderror
            </div>
            <button type="submit" class="bouton bouton-plein">Deviner</button>
        </form>
    @endif
</div>
