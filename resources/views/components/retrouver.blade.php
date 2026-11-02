@props(['action', 'noms'])
{{-- Se retrouver depuis un autre appareil : son prénom dans la liste, et son mot magique. --}}
<div class="carte compte-carte invite-prenom">
    <h2 class="h3">Déjà inscrit ? <em>Retrouve-toi.</em></h2>
    <p class="compte-texte">Sur un autre téléphone ou ordinateur : choisis ton prénom et tape ton mot magique.</p>
    <form method="POST" action="{{ $action }}" class="formulaire" novalidate>
        @csrf
        <div class="champ">
            <label for="nom">Ton prénom</label>
            <select id="nom" name="nom" required>
                @foreach ($noms as $nom)
                    <option value="{{ $nom }}" @selected(old('nom') === $nom)>{{ $nom }}</option>
                @endforeach
            </select>
        </div>
        <x-champ nom="mot_magique" libelle="Ton mot magique" type="password" autocomplete="current-password" required />
        <button type="submit" class="bouton bouton-contour">Me retrouver</button>
    </form>
</div>
