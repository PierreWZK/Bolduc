<x-layout :titre="'Mode soirée · '.$groupe->name" :noindex="true">
    <section class="section conteneur">
        <p class="surtitre">Mode soirée</p>
        <h1 class="titre-page">{{ $groupe->name }}</h1>
        <p class="section-texte">Les indices d'une personne tirée au hasard. Devine qui c'est, puis quel cadeau elle offre.</p>

        <livewire:soiree-organisatrice :groupe="$groupe" />

        <p class="legende"><a href="{{ route('santa.show', $groupe) }}">Retour au Secret Santa</a></p>
    </section>
</x-layout>
