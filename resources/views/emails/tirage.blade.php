<x-mail::message>
# Bonjour {{ $membre->name }},

Le tirage de **{{ $groupe->name }}** est fait. Ouvre le lien pour découvrir à qui tu offres.

<x-mail::button :url="$lien">
Découvrir ma cible
</x-mail::button>

Sur un autre appareil, choisis ton prénom et tape ton mot magique.

À bientôt sur Bolduc
</x-mail::message>
