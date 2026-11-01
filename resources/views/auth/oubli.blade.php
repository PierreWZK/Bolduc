<x-layout titre="Mot de passe oublié" :noindex="true">
    <x-formulaire titre="Un trou de <em>mémoire ?</em>" :action="route('password.request')" bouton="Recevoir un lien">
        <p class="compte-texte">Donne ton e-mail, on t'envoie un lien pour choisir un nouveau mot de passe.</p>
        <x-champ nom="email" libelle="Ton e-mail" type="email" autocomplete="email" required autofocus />
        <x-slot:pied>
            <p class="compte-pied"><a href="{{ route('login') }}">Retour à la connexion</a></p>
        </x-slot:pied>
    </x-formulaire>
</x-layout>
