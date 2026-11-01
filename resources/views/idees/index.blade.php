<x-layout titre="Idées de cadeaux" :noindex="$liste !== null">
    @php
        $actionFiltres = $liste ? route('invite.idees', $liste->share_token) : route('idees');
        $proprietaires = $liste?->proprietaires->pluck('name')->join(' et ');
    @endphp
    <section class="section conteneur">
        <p class="surtitre">Idées de cadeaux</p>
        <h1 class="titre-page">Un trou d'inspiration ? <em>On a des idées.</em></h1>

        @if ($liste)
            <p class="section-texte">Pour la liste de {{ $proprietaires }}. Ce que tu ajoutes reste <strong>secret</strong> : {{ $proprietaires }} ne le verra pas, les autres invités oui.</p>
        @elseif (auth()->check())
            <p class="section-texte">Ajoute une idée à l'une de tes listes. Tu peux la retirer ensuite depuis la fiche de la liste.</p>
        @else
            <p class="section-texte">Chaque idée a un prix indicatif. Connecte-toi pour l'ajouter à ta liste.</p>
        @endif

        @if (session('statut'))
            <p class="message" role="status">{{ session('statut') }}</p>
        @endif

        <form method="GET" action="{{ $actionFiltres }}" class="filtres">
            <div class="champ">
                <label for="evenement">Pour quel événement ?</label>
                <select id="evenement" name="evenement">
                    <option value="">Tous les événements</option>
                    @foreach ($evenements as $cle => $nom)
                        <option value="{{ $cle }}" @selected($evenement === $cle)>{{ $nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="champ">
                <label for="budget">Quel budget ?</label>
                <select id="budget" name="budget">
                    <option value="">Tous les budgets</option>
                    @foreach ($budgets as $cle => $nom)
                        <option value="{{ $cle }}" @selected($budget === $cle)>{{ $nom }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bouton bouton-encre">Filtrer</button>
        </form>

        <div class="idees">
            @forelse ($idees as $idee)
                <article class="carte idee">
                    <span class="rond teinte-or"><x-icone :nom="$idee->icon" /></span>
                    <span class="pastille pastille-rose">{{ ucfirst($idee->category) }}</span>
                    <h2 class="h3">{{ $idee->title }}</h2>
                    <p>{{ $idee->description }}</p>
                    <p class="legende">Environ {{ number_format((float) $idee->price, 0, ',', ' ') }} €</p>

                    @if ($liste && $invite)
                        <form method="POST" action="{{ route('invite.idees.ajouter', [$liste->share_token, $idee]) }}">
                            @csrf
                            <button type="submit" class="bouton bouton-plein bouton-mini">Ajouter en secret</button>
                        </form>
                    @elseif (auth()->check() && $mesListes->isNotEmpty())
                        <form method="POST" action="{{ route('idees.ajouter', $idee) }}" class="idee-ajout">
                            @csrf
                            <label class="sr-only" for="liste-{{ $idee->id }}">Quelle liste ?</label>
                            <select id="liste-{{ $idee->id }}" name="liste" required>
                                @foreach ($mesListes as $maListe)
                                    <option value="{{ $maListe->id }}">{{ $maListe->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="bouton bouton-plein bouton-mini">Ajouter à ma liste</button>
                        </form>
                    @elseif (auth()->check())
                        <a href="{{ route('listes.create') }}" class="lien">Crée d'abord une liste</a>
                    @elseif (! $liste)
                        <a href="{{ route('login') }}" class="lien">Connecte-toi pour l'ajouter</a>
                    @endif
                </article>
            @empty
                <p class="legende">Aucune idée ne correspond à ces filtres. Essaie un autre budget.</p>
            @endforelse
        </div>
    </section>
</x-layout>
