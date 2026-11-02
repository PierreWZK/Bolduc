<x-layout :titre="$groupe->name" :noindex="true">
    <section class="section conteneur">
        <p class="surtitre">Secret Santa</p>
        <h1 class="titre-page">{{ $groupe->name }}</h1>

        @if (session('statut'))
            <p class="message" role="status">{{ session('statut') }}</p>
        @endif

        @if (! $membre)
            <div class="carte compte-carte invite-prenom">
                <h2 class="h3">Qui es-tu, <em>et que voudrais-tu ?</em></h2>
                <p class="compte-texte">Ton prénom et tes envies. Ta cible ne saura pas qui t'a tiré.</p>
                @if ($groupe->estTire())
                    <p class="message message-info">Le tirage a déjà eu lieu : il n'est plus possible de rejoindre.</p>
                @else
                    <form method="POST" action="{{ route('santa.rejoindre', $groupe->share_token) }}" class="formulaire" novalidate>
                        @csrf
                        <x-champ nom="name" libelle="Ton prénom" autocomplete="given-name" required autofocus />
                        <x-champ nom="email" libelle="Ton e-mail (facultatif)" type="email" autocomplete="email" aide="Pour être prévenu quand le tirage est fait." />
                        <x-champ nom="magic_word" libelle="Ton mot magique" type="password" autocomplete="new-password" aide="Un petit mot à toi, pour te retrouver depuis un autre appareil." required />
                        <div class="champ">
                            <label for="wishes">Tes envies (facultatif)</label>
                            <textarea id="wishes" name="wishes" rows="3" maxlength="240" placeholder="Un livre, une plante, un thé…"></textarea>
                        </div>
                        <button type="submit" class="bouton bouton-plein">Participer</button>
                    </form>
                @endif
            </div>
            @if ($noms->isNotEmpty())
                <x-retrouver :action="route('santa.retrouver', $groupe->share_token)" :noms="$noms" />
            @endif
        @elseif (! $groupe->estTire())
            <div class="carte santa-attente">
                <h2 class="h3">Tu es dans la boucle, <em>{{ $membre->name }} !</em></h2>
                <p>Le tirage n'a pas encore eu lieu. Reviens ici après, pour découvrir ta cible.</p>
            </div>
        @elseif ($cible)
            <div class="carte santa-cible">
                <p class="surtitre">Tu offres à</p>
                <p class="santa-nom">{{ $cible->name }}</p>
                <p class="legende">Ses envies : {{ $cible->wishes ?: 'elle n\'en a pas noté, surprends-la !' }}</p>
            </div>
        @endif

        @if ($membre && $groupe->estTire())
            <div class="carte santa-attente">
                <p class="surtitre">Mode soirée</p>
                <p><a class="bouton bouton-encre" href="{{ route('santa.indices', $groupe->share_token) }}">Écrire mes indices</a> <a class="bouton bouton-plein" href="{{ route('santa.soiree', $groupe->share_token) }}">Jouer</a></p>
            </div>
        @endif
    </section>
</x-layout>
