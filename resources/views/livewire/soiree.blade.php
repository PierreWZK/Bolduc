<div wire:poll.5s>
    @if ($message)
        <p class="message" role="status">{{ $message }}</p>
    @endif

    @if (! $donneur)
        <p class="message message-info">Le tirage n'a pas encore donné de donneur.</p>
    @else
        <div class="carte santa-cible">
            <p class="surtitre">Qui m'a gâté ?</p>
            @if ($mancheQui->solved)
                <p class="santa-nom">Trouvé, +{{ $mancheQui->points }} point(s) !</p>
            @else
                <ul>
                    @foreach ($cluesQui as $indice)
                        <li>{{ $indice ?? '…' }}</li>
                    @endforeach
                </ul>
                @if ($mancheQui->revealed < 3)
                    <button type="button" class="bouton" wire:click="indiceSuivantQui">Indice suivant</button>
                @endif
                <p class="legende">Qui devine ? Choisis la personne :</p>
                @foreach ($candidats as $candidat)
                    <button type="button" class="bouton bouton-encre bouton-mini" wire:click="devinerQui({{ $candidat->id }})">{{ $candidat->name }}</button>
                @endforeach
            @endif
        </div>
    @endif

    @if ($cible)
        <div class="carte santa-cible">
            <p class="surtitre">Devine ton cadeau</p>
            @if (! $mancheCadeau)
                <p class="legende">Ton donneur n'a pas encore lancé le jeu.</p>
            @elseif ($mancheCadeau->solved)
                <p class="santa-nom">Trouvé, +{{ $mancheCadeau->points }} point(s) !</p>
            @else
                <ul>
                    @foreach ($cluesCadeau as $indice)
                        <li>{{ $indice ?? '…' }}</li>
                    @endforeach
                </ul>
                @if ($mancheCadeau->revealed < 3)
                    <button type="button" class="bouton" wire:click="indiceSuivantCadeau">Indice suivant</button>
                @endif
                @if ($mancheCadeau->guess)
                    <p class="legende">Ta devinette est en attente : « {{ $mancheCadeau->guess }} »</p>
                @else
                    <form wire:submit="devinerCadeau" class="formulaire">
                        <div class="champ">
                            <label for="devinette">Ton cadeau, c'est quoi ?</label>
                            <input id="devinette" type="text" wire:model="devinetteCadeau" maxlength="120">
                        </div>
                        <button type="submit" class="bouton bouton-plein">Envoyer</button>
                    </form>
                @endif
            @endif
        </div>

        <div class="carte santa-attente">
            <p class="surtitre">Tu offres à {{ $cible->name }}</p>
            @if (! $mancheCible)
                <button type="button" class="bouton bouton-plein" wire:click="lancerCadeau({{ $cible->id }})">Lancer le jeu du cadeau</button>
            @elseif ($mancheCible->solved)
                <p class="santa-nom">Trouvé, bravo !</p>
            @elseif ($mancheCible->guess)
                <p>Devinette proposée : « {{ $mancheCible->guess }} »</p>
                <button type="button" class="bouton bouton-plein" wire:click="valider(true)">C'est ça</button>
                <button type="button" class="bouton bouton-encre" wire:click="valider(false)">Pas tout à fait</button>
            @else
                <p class="legende">En attente de sa devinette.</p>
            @endif
        </div>
    @endif

    <div class="carte">
        <p class="surtitre">Classement</p>
        <ol>
            @foreach ($classement as $ligne)
                <li>{{ $ligne['nom'] }} : {{ $ligne['points'] }} point(s)</li>
            @endforeach
        </ol>
    </div>
</div>
