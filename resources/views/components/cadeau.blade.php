@props(['nom', 'prix', 'icone' => 'cadeau', 'teinte' => 'rose', 'statut' => null, 'parts' => null])
@php
    $statuts = [
        'disponible' => ['Disponible', 'pastille-menthe'],
        'surprise' => ['Surprise', 'pastille-rose'],
        'plusieurs' => ['À plusieurs', 'pastille-encre'],
        'secret' => ['Ajouté en secret', 'pastille-or'],
    ];
    // « Réservé par Inès » : tout statut inconnu de la liste est un nom de réservation.
    [$libelle, $classe] = $statuts[$statut] ?? [$statut ? 'Réservé par '.$statut : null, 'pastille-or'];
@endphp
<li class="cadeau">
    <div class="cadeau-ligne">
        <span class="cadeau-icone teinte-{{ $teinte }}"><x-icone :nom="$icone" /></span>
        <span class="cadeau-texte">
            <strong>{{ $nom }}</strong>
            <span>{{ $prix }} €</span>
        </span>
        @if ($libelle)
            <span class="pastille {{ $classe }}">{{ $libelle }}</span>
        @endif
    </div>
    @if ($parts)
        @php [$prises, $total] = $parts; @endphp
        <div class="parts">
            <span><strong>{{ $prises }} parts sur {{ $total }}</strong><span>Il en reste {{ $total - $prises }}</span></span>
            <span class="jauge" role="progressbar" aria-label="Parts prises" aria-valuenow="{{ $prises }}" aria-valuemin="0" aria-valuemax="{{ $total }}"><span style="width: {{ round($prises / $total * 100) }}%"></span></span>
        </div>
    @endif
</li>
