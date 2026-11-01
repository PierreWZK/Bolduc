<x-layout :titre="'Mode soirée · '.$groupe->name" :noindex="true">
    <section class="section conteneur">
        <p class="surtitre">Mode soirée</p>
        <h1 class="titre-page">{{ $groupe->name }}</h1>

        <livewire:soiree :groupe="$groupe" :membre="$membre" />

        <p class="legende"><a href="{{ route('santa.indices', $groupe->share_token) }}">Écrire mes indices</a> · <a href="{{ route('santa.invite', $groupe->share_token) }}">Retour au Secret Santa</a></p>
    </section>
</x-layout>
