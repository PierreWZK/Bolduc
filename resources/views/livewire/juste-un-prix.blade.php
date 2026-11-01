<div class="carte jeu">
    <p class="surtitre">Le Juste Prix</p>

    <figure class="jeu-photo">
        <img src="{{ asset($objet->image) }}" alt="{{ $objet->name }}" loading="lazy">
        <figcaption class="legende">{{ $objet->name }}@if ($objet->credit) · {{ $objet->credit }} @endif</figcaption>
    </figure>

    @if ($termine)
        @if ($gagne)
            <p class="santa-nom">Gagné en {{ count($essais) }} essai(s) !</p>
        @else
            <p class="santa-nom">Perdu. Le prix était {{ number_format($prix, 0, ',', ' ') }} €.</p>
        @endif
        <p class="legende">Série : 🔥 {{ $serie }} jour(s)</p>

        <div x-data="{ copie: false }">
            <pre class="jeu-partage">{{ $partage }}</pre>
            <button type="button" class="bouton bouton-encre bouton-mini" @click="navigator.clipboard.writeText(@js($partage)); copie = true" x-text="copie ? 'Copié' : 'Copier le résultat'">Copier le résultat</button>
        </div>
    @else
        <p class="legende">Quel est son prix ? 6 essais, gagné à 5 % près.</p>
        <form wire:submit="proposer" class="formulaire">
            <div class="champ">
                <label for="prix-jeu">Ton prix, en euros</label>
                <input id="prix-jeu" type="text" inputmode="decimal" wire:model="saisie" autocomplete="off">
                @error('saisie') <small class="message">Entre un prix en euros.</small> @enderror
            </div>
            <button type="submit" class="bouton bouton-plein">Proposer</button>
        </form>
    @endif

    @if ($essais)
        <ol class="jeu-essais">
            @foreach ($essais as $essai)
                <li>{{ number_format($essai, 2, ',', ' ') }} € · {{ $essai < $prix ? 'c\'est plus' : ($essai > $prix ? 'c\'est moins' : 'juste !') }}</li>
            @endforeach
        </ol>
    @endif
    <p class="legende">{{ count($essais) }}/6 essais</p>
</div>
