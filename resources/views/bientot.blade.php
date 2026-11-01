<x-layout :titre="$titre" :noindex="true">
    <section class="bientot conteneur">
        <p class="surtitre">Bientôt</p>
        <h1>{{ $titre }}, <em>ça arrive.</em></h1>
        <p>Cette page se prépare. En attendant, fais le tour de Bolduc.</p>
        <a href="{{ route('accueil') }}" class="bouton bouton-plein">Retour à l'accueil</a>
    </section>
</x-layout>
