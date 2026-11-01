<x-mail::message>
# Bonjour {{ $invite->name }},

Plus que sept jours avant **{{ $liste->title }}**.

@if ($libres > 0)
Il reste **{{ $libres }} {{ $libres > 1 ? 'cadeaux libres' : 'cadeau libre' }}** dans la liste. Tu peux en réserver un, ou proposer une idée en secret.
@else
Tous les cadeaux de la liste sont déjà pris. Merci d'avoir participé !
@endif

<x-mail::button :url="$lien">
Voir la liste
</x-mail::button>

À bientôt sur Bolduc
</x-mail::message>
