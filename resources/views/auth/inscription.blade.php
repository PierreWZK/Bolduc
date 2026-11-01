<x-layout titre="Créer mon compte" :noindex="true">
    <x-formulaire titre="Ta première liste, <em>dans deux minutes.</em>" :action="route('inscription')" bouton="Créer mon compte">
        <x-champ nom="name" libelle="Ton prénom" autocomplete="given-name" required autofocus />
        <x-champ nom="email" libelle="Ton e-mail" type="email" autocomplete="email" required />
        <x-champ nom="password" libelle="Mot de passe" type="password" autocomplete="new-password" aide="8 caractères au moins." required />
        <x-champ nom="password_confirmation" libelle="Encore une fois" type="password" autocomplete="new-password" required />
        <x-slot:pied>
            <p class="compte-pied">Tes invités n'auront pas besoin de compte. Déjà inscrit ? <a href="{{ route('login') }}">Connecte-toi</a></p>
        </x-slot:pied>
    </x-formulaire>
</x-layout>
