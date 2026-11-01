<x-layout titre="Jeux du jour">
    <section class="section conteneur">
        <p class="surtitre">Jeux du jour</p>
        <h1 class="titre-page">Un objet, <em>un jour, pour tout le monde.</em></h1>
        <p class="legende">Une partie par jour et par jeu. Reviens demain pour un nouvel objet et garde ta série 🔥.</p>

        <div class="jeux-grille">
            <livewire:juste-un-prix :visiteur="$visiteur" />
            <livewire:paquet-mystere :visiteur="$visiteur" />
        </div>
    </section>
</x-layout>
