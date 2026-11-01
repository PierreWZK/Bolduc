<x-layout titre="Nouveau mot de passe" :noindex="true">
    <x-formulaire titre="Un nouveau <em>mot de passe.</em>" :action="route('password.update')" bouton="Enregistrer">
        <input type="hidden" name="token" value="{{ $token }}">
        <x-champ nom="email" libelle="Ton e-mail" type="email" autocomplete="email" :value="request('email')" required />
        <x-champ nom="password" libelle="Nouveau mot de passe" type="password" autocomplete="new-password" aide="8 caractères au moins." required autofocus />
        <x-champ nom="password_confirmation" libelle="Encore une fois" type="password" autocomplete="new-password" required />
    </x-formulaire>
</x-layout>
