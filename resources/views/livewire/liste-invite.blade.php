@php $euros = fn ($montant) => number_format((float) $montant, 2, ',', ' ').' €'; @endphp
<div>
    @if ($message)
        <p class="message" role="status">{{ $message }}</p>
    @endif

    @if ($liste->estPassee())
        <p class="message message-info">La date est passée : la liste est fermée. Merci à tous !</p>
    @endif

    @forelse ($liste->cadeaux as $cadeau)
        @php
            $mienne = $cadeau->reservations->firstWhere('guest_id', $inviteId);
            $maPart = $cadeau->contributions->firstWhere('guest_id', $inviteId);
            $ouverte = ! $liste->estPassee();
            $partageable = $cadeau->price && $cadeau->quantity === 1 && ! $mienne && $cadeau->reservations->isEmpty();
        @endphp
        <article class="cadeau-fiche" wire:key="cadeau-{{ $cadeau->id }}">
            @if ($cadeau->image_url)
                <img src="{{ $cadeau->image_url }}" alt="" loading="lazy" width="72" height="72" class="cadeau-photo">
            @else
                <span class="cadeau-icone teinte-or"><x-icone nom="cadeau" /></span>
            @endif

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

                @if ($partageable && $cadeau->contributions->isNotEmpty())
                    @php
                        $collecte = $cadeau->montantCollecte();
                        $pourcent = min(100, round($collecte / (float) $cadeau->price * 100));
                    @endphp
                    <div class="parts">
                        <span>
                            <strong>{{ $euros($collecte) }} sur {{ $euros($cadeau->price) }}</strong>
                            <span>{{ $cadeau->estComplet() ? 'Financé' : 'Il manque '.$euros((float) $cadeau->price - $collecte) }}</span>
                        </span>
                        <span class="jauge" role="progressbar" aria-label="Participations" aria-valuenow="{{ $pourcent }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $pourcent }}%"></span></span>
                        <span class="legende">Avec {{ $cadeau->contributions->pluck('invite.name')->join(', ') }}</span>
                    </div>
                @endif
            </div>

            <div class="cadeau-actions">
                @if ($cadeau->favorite)
                    <span class="pastille pastille-rose">Coup de cœur</span>
                @endif

                @if ($cadeau->secret)
                    <span class="pastille pastille-or">Idée secrète de {{ $cadeau->ajouteePar?->name }}</span>
                @endif

                @if ($maPart)
                    <span class="pastille pastille-menthe">Ta part : {{ $euros($maPart->amount) }}</span>
                    @if ($ouverte) <button type="button" class="lien" wire:click="annuler({{ $cadeau->id }})">Annuler</button> @endif
                @elseif ($mienne)
                    <span class="pastille pastille-menthe">Tu t'en occupes{{ $mienne->quantity > 1 ? ' (×'.$mienne->quantity.')' : '' }}</span>
                    @if ($ouverte)
                        @unless ($cadeau->estComplet())
                            <button type="button" class="lien" wire:click="reserver({{ $cadeau->id }})">Un de plus</button>
                        @endunless
                        <button type="button" class="lien" wire:click="annuler({{ $cadeau->id }})">Annuler</button>
                    @endif
                @elseif ($cadeau->estComplet())
                    <span class="pastille pastille-or">{{ $cadeau->reservations->isNotEmpty() ? 'Réservé par '.$cadeau->reservations->pluck('invite.name')->join(', ') : 'Financé par les parts' }}</span>
                @elseif ($ouverte)
                    @if ($cadeau->quantity > 1)
                        <span class="legende">{{ $cadeau->unitesPrises() }} sur {{ $cadeau->quantity }} pris</span>
                    @endif
                    @if ($cadeau->contributions->isEmpty())
                        <button type="button" class="bouton bouton-encre bouton-mini" wire:click="reserver({{ $cadeau->id }})">Je m'en occupe</button>
                    @endif
                @else
                    <span class="pastille pastille-menthe">Disponible</span>
                @endif

                @if ($ouverte && ! $cadeau->estComplet() && ($partageable || $maPart))
                    <form class="part-saisie" wire:submit="participer({{ $cadeau->id }})">
                        <label class="sr-only" for="part-{{ $cadeau->id }}">Ta part en euros</label>
                        <input id="part-{{ $cadeau->id }}" type="number" min="1" step="1" inputmode="decimal" placeholder="Ta part (€)" wire:model="parts.{{ $cadeau->id }}">
                        <button type="submit" class="bouton bouton-encre bouton-mini">{{ $maPart ? 'Changer' : 'Je participe' }}</button>
                    </form>
                @endif
                @error("parts.$cadeau->id")
                    <p class="erreur">{{ $message }}</p>
                @enderror
            </div>
        </article>
    @empty
        <p class="legende">La liste est encore vide. Repasse un peu plus tard !</p>
    @endforelse
</div>
