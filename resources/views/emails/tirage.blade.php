<x-mail::message>
# Bonjour {{ $membre->name }},

Le tirage de **{{ $groupe->name }}** est fait. Découvre à qui tu dois offrir un cadeau.

<x-mail::button :url="$lien">
Découvrir à qui j'offre
</x-mail::button>

**N'oublie pas ton petit mot.** Quelqu'un du groupe t'a tiré, et il n'a que tes envies pour te trouver un cadeau. Écris-lui un mot sur la même page : tu peux l'ajouter ou le changer même après le tirage.

**Prépare le mode soirée.** Le jour de la fête, on devine qui a offert quoi. Remplis tes indices avant : un emoji, un surnom, une phrase et trois indices sur ton cadeau.

<x-mail::button :url="$indices" color="success">
Écrire mes indices
</x-mail::button>

Sur un autre appareil, choisis ton prénom et tape ton mot magique.

À bientôt sur Bolduc
</x-mail::message>
