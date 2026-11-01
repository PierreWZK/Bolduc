<x-layout titre="Secret Santa" :noindex="true">
    <section class="section conteneur">
        <p class="surtitre">Secret Santa</p>
        <h1 class="titre-page">Le tirage au sort, <em>sans chapeau.</em></h1>

        @if (session('statut'))
            <p class="message" role="status">{{ session('statut') }}</p>
        @endif

        <div class="carte santa-creation">
            <h2 class="h3">Un nouveau groupe</h2>
            <form method="POST" action="{{ route('santa.store') }}" class="formulaire" novalidate>
                @csrf
                <x-champ nom="name" libelle="Le nom du groupe" aide="Par exemple « Noël en famille 2026 »." required autofocus />
                <button type="submit" class="bouton bouton-plein">Créer le groupe</button>
            </form>
        </div>

        @if ($groupes->isNotEmpty())
            <h2 class="h3 cadeaux-titre">Tes groupes</h2>
            <ul class="listes">
                @foreach ($groupes as $groupe)
                    <li>
                        <a href="{{ route('santa.show', $groupe) }}" class="carte liste-carte">
                            <span class="pastille {{ $groupe->estTire() ? 'pastille-menthe' : 'pastille-rose' }}">{{ $groupe->estTire() ? 'Tiré' : 'Pas encore tiré' }}</span>
                            <strong>{{ $groupe->name }}</strong>
                            <span class="legende">{{ $groupe->membres()->count() }} participant(s)</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layout>
