@php
    $evenements = [
        'noel' => ['Noël', 'Noël chez les Martin', '24 décembre', [
            ['Coffret de thés', 32, 'tasse', 'menthe', 'Hugo'],
            ['Puzzle 1 000 pièces', 19, 'puzzle', 'or', 'disponible'],
            ['Pull en laine', 65, 'plaid', 'rose', 'disponible'],
        ]],
        'anniversaire' => ['Anniversaire', 'Les 30 ans de Hugo', '14 mars', [
            ['Vinyle de jazz', 28, 'musique', 'rose', 'Léa'],
            ['Séance d\'escalade', 45, 'montagne', 'menthe', 'disponible'],
            ['Casque audio', 129, 'casque', 'or', 'plusieurs', [2, 4]],
        ]],
        'mariage' => ['Mariage', 'Léa & Sam se marient', '21 juin', [
            ['Nuit en cabane', 180, 'maison', 'menthe', 'plusieurs', [5, 8]],
            ['Cocotte en fonte', 119, 'tasse', 'rose', 'Camille'],
            ['Service de verres', 64, 'verre', 'or', 'disponible'],
        ]],
        'cremaillere' => ['Crémaillère', 'Inès pend la crémaillère', '8 février', [
            ['Monstera', 35, 'plante', 'menthe', 'disponible'],
            ['6 verres à vin', 42, 'verre', 'rose', 'Tom'],
            ['Lampe de chevet', 49, 'lampe', 'or', 'disponible'],
        ]],
        'naissance' => ['Naissance', 'Bienvenue Jules', 'avril', [
            ['Tapis d\'éveil', 59, 'bebe', 'menthe', 'Mamie'],
            ['Gigoteuse', 39, 'plaid', 'rose', 'disponible'],
            ['Livres en tissu', 18, 'carnet', 'or', 'disponible'],
        ]],
    ];

    $questions = [
        ['Mes proches doivent-ils créer un compte ?', 'Non. Ils ouvrent le lien, donnent leur prénom et réservent. Seul toi as un compte, pour gérer tes listes.'],
        ['Comment je fais pour ne rien voir ?', 'Sur ta liste, chaque cadeau reste affiché comme libre, même quand il est réservé. Tes proches, eux, voient ce qui est pris pour ne pas l\'offrir en double.'],
        ['Je peux ajouter un cadeau de n\'importe quelle boutique ?', 'Oui. Colle le lien : Bolduc lit le titre, l\'image et le prix. Si une boutique ne se laisse pas lire, tu remplis à la main en dix secondes.'],
        ['Un cadeau est trop cher pour une seule personne ?', 'Passe-le en « À plusieurs ». Chacun annonce sa part et la barre se remplit jusqu\'au prix.'],
        ['Mes proches peuvent-ils m\'offrir autre chose ?', 'Oui. Dans les idées de cadeaux, ils ajoutent un cadeau en secret à ta liste. Tu ne le vois pas ; les autres invités, si, pour ne pas faire de doublon.'],
        ['Et après la fête, je découvre qui m\'a offert quoi ?', 'Seulement si tu le veux, une fois la date passée. Tu as alors un carnet de remerciements avec une case « merci envoyé ».'],
    ];
@endphp
<x-layout>
    <x-slot:head>
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebApplication',
                'name' => 'Bolduc',
                'url' => url('/'),
                'description' => 'La liste de cadeaux qui garde la surprise : Noël, anniversaire, mariage, crémaillère, naissance.',
                'applicationCategory' => 'LifestyleApplication',
                'inLanguage' => 'fr',
                'isPartOf' => ['@type' => 'WebSite', 'name' => 'ProjectP', 'url' => 'https://projectp.fr/'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    </x-slot:head>

    {{-- Hero, repris de la maquette --}}
    <section class="hero conteneur">
        <h1>La liste de cadeaux <br>qui garde <em>la surprise.</em></h1>
        <p class="hero-texte">Crée ta liste, envoie le lien. Tes proches réservent ce qu'ils offrent, et toi tu ne vois ni qui, ni quoi.</p>
        <a href="{{ route('inscription') }}" class="bouton bouton-plein bouton-grand">Créer ma liste</a>
        <p class="hero-atouts">Sans compte pour tes invités · Lien secret · Titre, image et prix lus tout seuls</p>
    </section>

    {{-- Bento : les deux vues de la même liste, et les idées --}}
    <section class="bento conteneur" aria-label="Aperçu d'une liste">
        <article class="carte carte-vue">
            <span class="pastille pastille-encre">Ce que tu vois</span>
            <h2 class="h3">Noël chez Camille</h2>
            <ul class="cadeaux">
                <x-cadeau nom="Plaid en laine" prix="59" icone="plaid" teinte="rose" statut="surprise" />
                <x-cadeau nom="Enceinte nomade" prix="89" icone="enceinte" teinte="menthe" statut="surprise" />
                <x-cadeau nom="Carnet de recettes" prix="24" icone="carnet" teinte="or" statut="surprise" />
            </ul>
            <p class="legende">Tu ne vois pas qui a réservé quoi : la surprise est gardée.</p>
        </article>

        <article class="carte carte-vue">
            <span class="pastille pastille-rose">Ce que voient tes proches</span>
            <h2 class="h3">Noël chez Camille</h2>
            <ul class="cadeaux">
                <x-cadeau nom="Plaid en laine" prix="59" icone="plaid" teinte="rose" statut="Inès" />
                <x-cadeau nom="Enceinte nomade" prix="89" icone="enceinte" teinte="menthe" statut="plusieurs" :parts="[3, 5]" />
                <x-cadeau nom="Carnet de recettes" prix="24" icone="carnet" teinte="or" statut="disponible" />
            </ul>
            <p class="legende">Pas de compte : ils ouvrent le lien et réservent.</p>
        </article>

        <div class="bento-idees">
            <article class="carte carte-encre">
                <p class="surtitre surtitre-or">Idées de cadeaux</p>
                <h2 class="h3-grand">Un trou d'inspiration ? <em>On a des idées.</em></h2>
                <p>Pour remplir ta liste, ou pour offrir autre chose en secret.</p>
                <a href="{{ route('idees') }}" class="bouton bouton-papier">Parcourir les idées</a>
            </article>
            <a href="{{ route('idees') }}" class="carte tuile">
                <span class="rond teinte-or"><x-icone nom="etiquette" /></span>
                <strong>Moins de 30 €</strong>
            </a>
            <a href="{{ route('idees') }}" class="carte tuile">
                <span class="rond teinte-menthe"><x-icone nom="groupe" /></span>
                <strong>À plusieurs</strong>
            </a>
        </div>

        <a href="{{ route('idees') }}" class="categorie fond-rose">
            <span class="rond rond-blanc texte-bolduc"><x-icone nom="maison" /></span>
            <strong>Pour la maison</strong>
            <span>Plaids, bougies, vaisselle</span>
        </a>
        <a href="{{ route('idees') }}" class="categorie fond-menthe">
            <span class="rond rond-blanc texte-menthe"><x-icone nom="panier" /></span>
            <strong>Gourmandises</strong>
            <span>Thés, chocolats, coffrets</span>
        </a>
        <a href="{{ route('idees') }}" class="categorie fond-or">
            <span class="rond rond-blanc texte-or"><x-icone nom="coeur" /></span>
            <strong>Fait main</strong>
            <span>Créations, ateliers, sur mesure</span>
        </a>
    </section>

    {{-- Comment ça marche --}}
    <section class="section conteneur" id="comment">
        <p class="surtitre">Comment ça marche</p>
        <h2>Prête en <em>trois</em> étapes.</h2>
        <ol class="etapes">
            <li class="carte">
                <span class="etape-num">01</span>
                <span class="rond teinte-rose"><x-icone nom="liste" /></span>
                <h3>Crée ta liste</h3>
                <p>Choisis l'événement et la date. Le compte à rebours démarre.</p>
            </li>
            <li class="carte">
                <span class="etape-num">02</span>
                <span class="rond teinte-or"><x-icone nom="lien" /></span>
                <h3>Colle tes liens</h3>
                <p>Un lien de boutique suffit : titre, image et prix arrivent tout seuls. Marque tes coups de cœur.</p>
                <div class="champ-demo" aria-hidden="true">
                    <span>boutique.fr/plaid-en-laine</span>
                    <span class="bouton bouton-encre bouton-mini">Ajouter</span>
                </div>
            </li>
            <li class="carte">
                <span class="etape-num">03</span>
                <span class="rond teinte-menthe"><x-icone nom="envoyer" /></span>
                <h3>Envoie le lien secret</h3>
                <p>Par message, par mail, sur le faire-part. Tes proches réservent, toi tu ne vois rien.</p>
            </li>
        </ol>
    </section>

    {{-- Pour chaque événement --}}
    <section class="section conteneur" id="evenements" x-data="{ ev: 'noel' }">
        <p class="surtitre">Pour chaque événement</p>
        <h2>Une liste pour <em>chaque</em> fête.</h2>
        <div class="evenements">
            <div>
                <p class="section-texte">Noël, anniversaire, mariage, crémaillère, naissance : chaque liste a sa date, son compte à rebours et ses couleurs. Pour un mariage ou une naissance, vous gérez la liste à deux.</p>
                <div class="choix" role="group" aria-label="Choisir un événement">
                    @foreach ($evenements as $cle => [$nom])
                        <button type="button" class="pastille-choix" :aria-pressed="ev === '{{ $cle }}'" @click="ev = '{{ $cle }}'" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $nom }}</button>
                    @endforeach
                </div>
            </div>
            @foreach ($evenements as $cle => [$nom, $titre, $date, $cadeaux])
                <article class="carte carte-vue exemple" x-show="ev === '{{ $cle }}'" @unless ($loop->first) style="display: none" @endunless>
                    <span class="pastille pastille-rose">Ce que voient tes proches</span>
                    <h3 class="h3">{{ $titre }}</h3>
                    <p class="exemple-date">{{ $nom }} · {{ $date }}</p>
                    <ul class="cadeaux">
                        @foreach ($cadeaux as $c)
                            <x-cadeau :nom="$c[0]" :prix="$c[1]" :icone="$c[2]" :teinte="$c[3]" :statut="$c[4]" :parts="$c[5] ?? null" />
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Secret Santa et mode soirée --}}
    <section class="bande-encre" id="secret-santa">
        <div class="conteneur section">
            <p class="surtitre surtitre-or">Secret Santa</p>
            <h2>Le tirage au sort, <em>sans chapeau.</em></h2>
            <div class="santa">
                <div>
                    <p class="section-texte">Invite le groupe par un lien, indique qui ne doit pas se tirer (les couples, par exemple), et lance le tirage. Chacun ne découvre que la personne qu'il a tirée, avec sa liste. Même toi, l'organisateur, tu ne sais rien.</p>
                    <a href="{{ route('secret-santa') }}" class="bouton bouton-papier">Organiser un Secret Santa</a>
                </div>
                <div class="tirage carte" aria-hidden="true">
                    <p class="legende">Tu as tiré…</p>
                    <p class="tirage-nom">Camille</p>
                    <p class="legende">Sa liste : 4 idées, dont 1 coup de cœur ❤️</p>
                    <ul class="tirage-exclusions">
                        <li><x-icone nom="melange" /> 8 participants</li>
                        <li>Léa et Sam ne se tirent pas</li>
                    </ul>
                </div>
            </div>

            <h3 class="h2-petit">Et le jour J, <em>on joue.</em></h3>
            <p class="section-texte">Le moment d'ouvrir les paquets devient un jeu, sur le téléphone de chacun. Celui qui offre prépare trois indices ; trouvé au premier, 3 points, au deuxième, 2, au troisième, 1.</p>
            <div class="jeux-soiree">
                <article class="carte carte-sombre">
                    <h4>Qui m'a gâté ?</h4>
                    <p>Devine qui t'a offert ton cadeau. À la fin, tout le monde découvre qui a offert à qui.</p>
                    <ol class="indices">
                        <li><span>Indice 1</span> 🦊</li>
                        <li><span>Indice 2</span> « Le Renard »</li>
                        <li class="indice-cache"><span>Indice 3</span> Une phrase, un souvenir commun…</li>
                    </ol>
                </article>
                <article class="carte carte-sombre">
                    <h4>Devine ton cadeau</h4>
                    <p>Celui qui offre touche ton nom : à toi de deviner ce qu'il y a dans le paquet, avant de l'ouvrir.</p>
                    <ol class="indices">
                        <li><span>Indice 1</span> Ça se range dans la chambre</li>
                        <li><span>Indice 2</span> C'est tout doux</li>
                        <li class="indice-cache"><span>Indice 3</span> Ça va par deux…</li>
                    </ol>
                </article>
            </div>
        </div>
    </section>

    {{-- Jeux du jour --}}
    <section class="section conteneur" id="jeux">
        <p class="surtitre">Jeux du jour</p>
        <h2>Un défi par jour, <em>le même</em> pour tous.</h2>
        <p class="section-texte">Deux petits jeux qui changent chaque jour à minuit. Une partie par jour, une série 🔥 à tenir, et un résultat à partager.</p>
        <div class="jeux-jour">
            <article class="carte">
                <span class="pastille pastille-or">Le Juste Prix</span>
                <h3>Combien coûte la bouilloire ?</h3>
                <p>Six essais pour trouver le prix, à 5 % près. À chaque fois : c'est plus, ou c'est moins.</p>
                <ul class="essais" aria-label="Exemple de partie">
                    <li>30 € <span class="texte-bolduc"><x-icone nom="haut" /> plus</span></li>
                    <li>70 € <span class="texte-bolduc"><x-icone nom="bas" /> moins</span></li>
                    <li>49 € <span class="pastille pastille-menthe">Trouvé</span></li>
                </ul>
            </article>
            <article class="carte">
                <span class="pastille pastille-rose">Paquet mystère</span>
                <h3>Qu'y a-t-il sous le papier ?</h3>
                <p>Cinq couches de papier cachent un objet. Chaque mauvaise réponse en arrache une.</p>
                <div class="paquet" aria-hidden="true">
                    <span class="couche"></span><span class="couche"></span><span class="couche"></span><span class="couche couche-arrachee"></span><span class="couche couche-arrachee"></span>
                    <x-icone nom="cadeau" />
                </div>
            </article>
        </div>
        <a href="{{ route('jeux') }}" class="bouton bouton-contour">Jouer aujourd'hui</a>
    </section>

    {{-- Questions fréquentes --}}
    <section class="section conteneur faq" id="questions">
        <p class="surtitre">Questions</p>
        <h2>Tout ce qu'on <em>nous demande.</em></h2>
        <div class="questions">
            @foreach ($questions as [$q, $r])
                <details class="carte">
                    <summary>{{ $q }} <x-icone nom="chevron" /></summary>
                    <p>{{ $r }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <section class="fin conteneur">
        <h2>Prête en quelques minutes.</h2>
        <a href="{{ route('inscription') }}" class="bouton bouton-plein bouton-grand">Créer ma liste</a>
    </section>
</x-layout>
