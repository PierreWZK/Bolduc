<x-layout :titre="$liste->title" :noindex="true">
    @php $proprietaires = $liste->proprietaires->pluck('name')->join(' et '); @endphp
    <section class="section conteneur">
        <p class="surtitre">{{ $liste->libelleEvenement() }}</p>
        <h1 class="titre-page">{{ $liste->title }}</h1>

        <div class="liste-infos">
            <x-echeance :liste="$liste" />
            <p class="legende">La liste de {{ $proprietaires }}</p>
        </div>

        @if (session('statut'))
            <p class="message" role="status">{{ session('statut') }}</p>
        @endif

        @if ($invite)
            <div class="carte bienvenue">
                <h2 class="h3">Bonjour {{ $invite->name }} !</h2>
                <p class="compte-texte">Réserve ce que tu offres : {{ $proprietaires }} ne verra rien. Plusieurs invités peuvent aussi participer à un même cadeau.</p>
                <p class="bienvenue-lien">Une idée à glisser en secret ? <a href="{{ route('invite.idees', $liste->share_token) }}" class="lien">Voir les idées</a></p>
            </div>
            <livewire:liste-invite :liste="$liste" :invite="$invite" />
        @else
            <div class="carte compte-carte invite-prenom">
                <h2 class="h3">Avant de réserver, <em>qui es-tu ?</em></h2>
                <p class="compte-texte">Ton prénom s'affiche pour les autres invités, jamais pour {{ $proprietaires }}.</p>
                <form method="POST" action="{{ route('invite.prenom', $liste->share_token) }}" class="formulaire" novalidate>
                    @csrf
                    <x-champ nom="name" libelle="Ton prénom" autocomplete="given-name" required autofocus />
                    <x-champ nom="email" libelle="Ton e-mail (facultatif)" type="email" autocomplete="email" aide="Pour un rappel avant la fête, rien d'autre." />
                    <x-champ nom="magic_word" libelle="Ton mot magique" type="password" autocomplete="new-password" aide="Un petit mot à toi, pour te retrouver depuis un autre appareil." required />
                    <x-piege />
                    <button type="submit" class="bouton bouton-plein">Voir la liste</button>
                </form>
            </div>
            @if ($noms->isNotEmpty())
                <x-retrouver :action="route('invite.retrouver', $liste->share_token)" :noms="$noms" />
            @endif
        @endif
    </section>
</x-layout>
