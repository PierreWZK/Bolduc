@php
    // « 89 € » pour un prix rond, « 89,50 € » sinon.
    $euros = fn ($montant) => number_format((float) $montant, fmod((float) $montant, 1) == 0 ? 0 : 2, ',', ' ').' €';
    $teintes = ['rose', 'menthe', 'or'];
@endphp
<div>
    @if ($message)
        <div class="toast" role="status" wire:key="toast">
            <span>{{ $message }}</span>
            @if ($cadeauRecent && ! $liste->estPassee())
                <button type="button" class="lien" wire:click="annulerRecent">Annuler</button>
            @endif
            <button type="button" class="toast-fermer" wire:click="fermerToast" aria-label="Fermer">×</button>
        </div>
    @endif

    @if ($liste->estPassee())
        <p class="message message-info">La date est passée : la liste est fermée. Merci à tous !</p>
    @endif

    <div class="recap">
        <p><strong>Ce que j'offre</strong></p>
        @if ($recap['nombre'] > 0)
            <p class="legende">{{ $recap['nombre'] }} {{ $recap['nombre'] > 1 ? 'cadeaux' : 'cadeau' }} · {{ $euros($recap['total']) }} engagés</p>
        @else
            <p class="legende">Rien encore. Choisis un cadeau ci-dessous.</p>
        @endif
    </div>

    <div class="filtres filtres-invite">
        <div class="champ">
            <label for="filtre-budget">Budget</label>
            <select id="filtre-budget" wire:model.live="budget">
                <option value="">Tous les budgets</option>
                @foreach (\App\Models\Idee::BUDGETS as $cle => $nom)
                    <option value="{{ $cle }}">{{ $nom }}</option>
                @endforeach
            </select>
        </div>
        <label class="case-filtre">
            <input type="checkbox" wire:model.live="libresSeulement">
            <span>Seulement les cadeaux encore libres</span>
        </label>
    </div>

    <div class="emballages">
        @forelse ($cadeaux as $cadeau)
            @php
                $mienne = $cadeau->reservations->firstWhere('guest_id', $inviteId);
                $maPart = $cadeau->contributions->firstWhere('guest_id', $inviteId);
                $ouverte = ! $liste->estPassee();
                $partageable = $cadeau->price && $cadeau->quantity === 1 && ! $mienne && $cadeau->reservations->isEmpty();
                // Concept A : un cadeau pris a son ruban noué ; en bolduc si c'est le tien.
                $noue = $mienne || $maPart || $cadeau->estComplet();
            @endphp
            <article @class(['emballage', 'emballage-noue' => $noue, 'emballage-mien' => $mienne || $maPart]) wire:key="cadeau-{{ $cadeau->id }}">
                <div class="emballage-visuel teinte-{{ $teintes[$cadeau->id % 3] }}">
                    @if ($cadeau->image_url)
                        <img src="{{ $cadeau->image_url }}" alt="" loading="lazy" width="400" height="267" class="emballage-photo">
                    @else
                        <x-icone nom="cadeau" />
                    @endif

                    @if ($cadeau->favorite)
                        <span class="emballage-badge"><x-icone nom="coeur" /> Coup de cœur</span>
                    @endif

                    @if ($noue)
                        <span class="emballage-ruban" aria-hidden="true">
                            <svg class="emballage-noeud" viewBox="0 0 64 44"><path d="M32 20C24 6 8 3 5 11s13 13 27 9Z"/><path d="M32 20c8-14 24-17 27-9s-13 13-27 9Z"/><path d="M29 21 21 41M35 21l8 20" fill="none" stroke-width="5" stroke-linecap="round"/><circle cx="32" cy="20" r="6"/></svg>
                        </span>
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
                </div>
            </article>
        @empty
            <p class="legende">La liste est encore vide. Repasse un peu plus tard !</p>
        @endforelse
    </div>
</div>
