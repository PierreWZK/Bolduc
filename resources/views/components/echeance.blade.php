@props(['liste'])
{{-- Date de l'événement et compte à rebours, communs à la vue propriétaire et à la vue invité. --}}
@if ($liste->event_date)
    <p>
        <strong>{{ $liste->event_date->locale('fr')->isoFormat('D MMMM YYYY') }}</strong>
        @if ($liste->joursAvant() > 0)
            · J-{{ $liste->joursAvant() }}
        @elseif ($liste->joursAvant() === 0)
            · C'est aujourd'hui !
        @else
            · La date est passée
        @endif
    </p>
@else
    <p>Pas de date fixée.</p>
@endif
