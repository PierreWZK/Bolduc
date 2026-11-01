<x-layout :titre="$liste->title" :noindex="true">
    @php $proprietaires = $liste->proprietaires->pluck('name')->join(' et '); @endphp
    <section class="section conteneur">
        <p class="surtitre">{{ $liste->libelleEvenement() }}</p>
        <h1 class="titre-page">{{ $liste->title }}</h1>

        <div class="liste-infos">
            <x-echeance :liste="$liste" />
            <p class="legende">La liste de {{ $proprietaires }}</p>
        </div>

        @if ($invite)
            <p class="invite-bonjour">Bonjour {{ $invite->name }} ! Réserve ce que tu offres : {{ $proprietaires }} ne verra rien.</p>
            <livewire:liste-invite :liste="$liste" :invite="$invite" />
        @else
            <div class="carte compte-carte invite-prenom">
                <h2 class="h3">Avant de réserver, <em>qui es-tu ?</em></h2>
                <p class="compte-texte">Ton prénom s'affiche pour les autres invités, jamais pour {{ $proprietaires }}.</p>
                <form method="POST" action="{{ route('invite.prenom', $liste->share_token) }}" class="formulaire" novalidate>
                    @csrf
                    <x-champ nom="name" libelle="Ton prénom" autocomplete="given-name" required autofocus />
                    <x-champ nom="email" libelle="Ton e-mail (facultatif)" type="email" autocomplete="email" aide="Pour un rappel avant la fête, rien d'autre." />
                    <button type="submit" class="bouton bouton-plein">Voir la liste</button>
                </form>
            </div>
        @endif
    </section>
</x-layout>
