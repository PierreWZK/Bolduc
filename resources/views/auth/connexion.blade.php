<x-layout titre="Connexion" :noindex="true">
    <x-formulaire titre="Content de te <em>revoir.</em>" :action="route('login')" bouton="Me connecter">
        <x-champ nom="email" libelle="Ton e-mail" type="email" autocomplete="email" required autofocus />
        <x-champ nom="password" libelle="Mot de passe" type="password" autocomplete="current-password" required />
        <x-slot:pied>
            <p class="compte-pied"><a href="{{ route('password.request') }}">Mot de passe oublié ?</a> · Pas encore de compte ? <a href="{{ route('inscription') }}">Crée-le</a></p>
        </x-slot:pied>
    </x-formulaire>
</x-layout>
