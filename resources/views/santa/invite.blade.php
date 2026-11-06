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
                            <label for="wishes">Tes envies et un petit mot (facultatif)</label>
                            <textarea id="wishes" name="wishes" rows="3" maxlength="500" placeholder="Un livre, une plante, un thé…">{{ old('wishes') }}</textarea>
                            <p class="aide">C'est tout ce qu'aura la personne qui te tire pour te trouver un cadeau. Modifiable ensuite.</p>
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
                <p class="legende">Ses envies et son petit mot : {{ $cible->wishes ?: 'rien pour l\'instant, repasse plus tard ou surprends-la !' }}</p>
            </div>
        @endif

        @if ($membre)
            <div class="carte santa-attente">
                <h2 class="h3">Ton petit mot <em>pour ton Père Noël secret</em></h2>
                <p>Quelqu'un du groupe va te chercher un cadeau, et il n'aura que ce mot pour s'inspirer. Écris tes envies, tes goûts, ce qui te ferait plaisir. Tu peux le changer quand tu veux, même après le tirage.</p>
                <form method="POST" action="{{ route('santa.envies', $groupe->share_token) }}" class="formulaire">
                    @csrf
                    <div class="champ">
                        <label for="wishes">Tes envies et ton petit mot</label>
                        <textarea id="wishes" name="wishes" rows="4" maxlength="500" placeholder="J'adore le thé et les romans policiers. Pas de bougie, j'en ai trop !">{{ old('wishes', $membre->wishes) }}</textarea>
                        @error('wishes')
                            <p class="erreur">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bouton bouton-plein">Enregistrer</button>
                </form>
            </div>
        @endif

        @if ($membre && $groupe->estTire())
            <div class="carte santa-attente">
                <p class="surtitre">Mode soirée</p>
                <p>Le jour de la fête, l'organisatrice fait deviner les indices de chacun. Remplis les tiens avant : un emoji, un surnom, une phrase, et trois indices sur ton cadeau.</p>
                <p><a class="bouton bouton-encre" href="{{ route('santa.indices', $groupe->share_token) }}">Écrire mes indices</a></p>
            </div>
        @endif
    </section>
</x-layout>
