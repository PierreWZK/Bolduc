<x-layout titre="Mes listes" :noindex="true">
    <section class="section conteneur">
        <p class="surtitre">Mes listes</p>
        <h1 class="titre-page">Bonjour {{ auth()->user()->name }}, <em>on prépare quoi ?</em></h1>

        <div class="carte vide">
            <span class="rond teinte-rose"><x-icone nom="cadeau" /></span>
            <h2 class="h3">Pas encore de liste</h2>
            <p>Noël, anniversaire, mariage, crémaillère, naissance : crée ta première liste et envoie le lien à tes proches.</p>
            {{-- Le formulaire de création arrive à l'étape 2. --}}
            <span class="bouton bouton-plein" aria-disabled="true">Créer ma liste</span>
        </div>

        <form method="POST" action="{{ route('deconnexion') }}" class="deconnexion">
            @csrf
            <button type="submit" class="lien">Me déconnecter</button>
        </form>
    </section>
</x-layout>
