<div>
    @if ($message)
        <p class="message" role="status">{{ $message }}</p>
    @endif

    @if (! $membre)
        <div class="carte santa-attente">
            <p>Personne n'a encore écrit ses indices. Ils se remplissent depuis le lien du Secret Santa.</p>
        </div>
    @else
        <div class="carte">
            <p class="surtitre">{{ $jeu === 1 ? 'Qui m\'a offert le cadeau ?' : 'Quel cadeau ?' }} · indice {{ $revele }} sur {{ $total }}</p>
            <ul class="soiree-indices">
                @foreach ($indices as $indice)
                    <li class="soiree-indice">{{ $indice ?? '…' }}</li>
                @endforeach
            </ul>
            @if ($revele < $total)
                <button type="button" class="bouton bouton-encre" wire:click="indiceSuivant">Indice suivant</button>
            @endif
        </div>

        @if ($trouve && $jeu === 1)
            <button type="button" class="bouton bouton-plein bouton-grand" wire:click="passerAuCadeau">Passer au cadeau</button>
        @elseif (! $trouve)
            <div class="carte">
                <p class="surtitre">{{ $jeu === 1 ? 'Qui est-ce ?' : 'Qui offre ce cadeau ?' }}</p>
                <div class="soiree-choix">
                    @foreach ($candidats as $candidat)
                        <button type="button" class="bouton bouton-plein soiree-bouton" wire:click="deviner({{ $candidat->id }})">{{ $candidat->name }}</button>
                    @endforeach
                </div>
            </div>
        @endif

        <button type="button" class="lien" wire:click="autre">Une autre personne</button>
    @endif
</div>
