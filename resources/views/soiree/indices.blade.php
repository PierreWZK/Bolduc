<x-layout :titre="'Mes indices · '.$groupe->name" :noindex="true">
    <section class="section conteneur">
        <p class="surtitre">Mode soirée</p>
        <h1 class="titre-page">Tes <em>indices</em></h1>
        <p class="legende">Ils serviront à deviner qui tu es, et ce que tu offres à {{ $cible->name }}. Ta cible ne les voit qu'au moment du jeu.</p>

        @php
            $champs = [
                'emoji' => ['Ton emoji', 'Un seul emoji qui te représente.'],
                'surnom' => ['Ton surnom', 'Un surnom que tes proches te donnent.'],
                'phrase' => ['Une phrase sur toi', 'Un indice qui te rend reconnaissable.'],
                'cadeau1' => ['Indice 1 sur ton cadeau', 'Le plus vague, par exemple « ça se boit ».'],
                'cadeau2' => ['Indice 2 sur ton cadeau', 'Un peu plus précis.'],
                'cadeau3' => ['Indice 3 sur ton cadeau', 'Le plus parlant.'],
            ];
        @endphp

        <form method="POST" action="{{ route('santa.indices.sauver', $groupe->share_token) }}" class="formulaire" novalidate>
            @csrf
            @foreach ($champs as $nom => [$libelle, $aide])
                <div class="champ">
                    <label for="{{ $nom }}">{{ $libelle }}</label>
                    <input id="{{ $nom }}" name="{{ $nom }}" type="text" value="{{ old($nom, $valeurs[$nom] ?? '') }}" maxlength="{{ $nom === 'emoji' ? 20 : 120 }}" required>
                    <small class="legende">{{ $aide }}</small>
                </div>
            @endforeach
            <button type="submit" class="bouton bouton-plein">Noter mes indices</button>
        </form>
    </section>
</x-layout>
