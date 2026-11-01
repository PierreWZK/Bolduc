# Jour 19/30 — Bolduc, les listes de cadeaux, sans gâcher la surprise

> Stack : **Laravel**, **Livewire**, **MySQL** · hébergement **Coolify**, sous-domaine `bolduc.projectp.fr`.
> Durée visée : **4 h** pour le MVP, les bonus ensuite.

Ce document est notre contrat : on le suit étape par étape, et toute décision qui s'en écarte est d'abord notée ici. Il s'ajuste au début de la journée.

## 1. L'idée

Noël, anniversaire, mariage, crémaillère, naissance, départ en retraite : **Bolduc** permet de créer une liste de cadeaux, de l'envoyer par un lien et de laisser les proches réserver ce qu'ils offrent. Le principe qui guide tout : **la surprise est sacrée**. Le propriétaire d'une liste ne voit jamais qui a réservé quoi, ni les cadeaux que ses proches lui préparent en dehors de sa liste.

> Maman fait sa liste de Noël. En panne d'idées, elle parcourt **Idées de cadeaux** et ajoute un vase. Moi, j'ai envie de lui offrir autre chose : dans les idées, je trouve de beaux draps et je les ajoute **en secret** à sa liste. Elle ne les voit pas, mais mon frère, oui : il ne les achètera pas en double, et il peut même participer.

## 2. MVP — ce qui doit fonctionner à la fin

1. **Comptes** pour les propriétaires de listes ; les invités n'en ont pas besoin, un prénom suffit (gardé dans un cookie).
2. **Listes par événement** : type (Noël, anniversaire, mariage, crémaillère, naissance, autre), titre, date, compte à rebours, thème visuel selon le type. Partage par **lien secret**.
3. **Cadeaux** : titre, lien, image, prix, quantité, priorité ❤️ « coup de cœur », note (taille, couleur, « ou équivalent »). **Ajout par lien** : on colle l'URL d'une boutique, Bolduc lit le titre, l'image et le prix (balises Open Graph), modifiables avant d'enregistrer.
4. **Réservation sans spoiler** : un invité clique « Je m'en occupe » ; le cadeau passe en « réservé » pour les autres invités, et reste affiché comme libre pour le propriétaire.
5. **Cadeau à plusieurs** : sur un cadeau, chacun annonce sa part ; une barre se remplit jusqu'au prix. Déclaratif, sans paiement en ligne.
6. **Idées de cadeaux** : un catalogue d'inspiration (par événement, budget et centre d'intérêt), utile aux deux côtés :
   - le **propriétaire** y pioche pour remplir sa liste ;
   - l'**invité** y trouve une idée et l'ajoute **en secret** à la liste : invisible pour le propriétaire, visible et réservable par les autres invités.
7. **Secret Santa** : un groupe, des participants, des exclusions (un couple ne se tire pas) ; chacun ne voit que la personne qu'il a tirée, avec sa liste.
8. **Remerciements** : après la date de l'événement, le propriétaire peut découvrir qui a offert quoi, avec une case « merci envoyé ».
9. **Rappels par e-mail** : à J-7, aux invités qui ont laissé leur e-mail (« il reste 5 cadeaux libres ») et à ceux qui ont réservé ; au propriétaire, le récapitulatif des remerciements après l'événement.
10. **Mode soirée** : deux petits jeux pour le moment où l'on ouvre les cadeaux, sur téléphone, autour de la table (section 3) : **Qui m'a gâté ?** et **Devine ton cadeau**.
11. **Jeux du jour** : **Le Juste Prix** et **Paquet mystère** (section 3).
12. En ligne sur Coolify, avec un README à jour et un aperçu de lien (Open Graph).

### Périmètre volontairement limité

- Pas de paiement en ligne : les participations et les achats sont déclaratifs.
- Pas d'affiliation ni de comparateur de prix.
- L'ajout par lien lit seulement les balises publiques de la page ; si une boutique bloque, on remplit à la main.
- Le catalogue d'idées est rédigé et rangé par nous (graines), pas généré ni aspiré.

## 3. Cahier des charges fonctionnel

### Listes et cadeaux

- Une liste peut avoir **deux propriétaires** (mariage, parents d'un bébé).
- Filtres invités : budget (< 20 €, 20–50 €, > 50 €), coup de cœur, encore libre.
- Un cadeau en quantité (6 verres) se réserve à l'unité.
- Annuler sa réservation reste possible tant que l'événement n'est pas passé.

### La surprise

- Côté propriétaire : ni les réservations, ni les participations, ni les cadeaux secrets ne sont visibles avant la date de l'événement, et seulement s'il choisit de les découvrir.
- Un propriétaire qui ouvre le lien invité de sa propre liste voit la vue propriétaire, pas la vue invité.
- Un cadeau que le propriétaire supprime alors qu'il est réservé : l'invité concerné est prévenu.

### Idées de cadeaux

- Fiches : titre, image, fourchette de prix, événements et centres d'intérêt (maison, cuisine, jardin, lecture, jeux, sport, bébé…).
- Depuis une fiche : « Ajouter à ma liste » ou « Ajouter en secret à la liste de… ».

### Secret Santa

- L'organisateur crée le groupe, invite par lien, saisit les exclusions et lance le tirage ; il ne voit pas le résultat.
- Le tirage refuse les cas impossibles (exclusions qui bloquent tout le monde) avec un message clair.

### Mode soirée (le jour où l'on ouvre les cadeaux)

Ce ne sont pas des jeux du jour : ils se lancent pendant l'événement, pour un Secret Santa ou une liste. Celui qui offre écrit ses indices à l'avance (au tirage ou à la réservation) ; sans indices, le jeu est simplement sauté pour ce cadeau.

1. **Qui m'a gâté ?** Chaque destinataire devine qui lui a offert son cadeau. Les indices se dévoilent un par un :
   1. un **emoji** choisi par celui qui offre ;
   2. son **surnom** ;
   3. une **phrase libre** (un souvenir commun, une habitude).

   Trouvé au 1er indice = 3 points, au 2e = 2, au 3e = 1. À la fin, la chaîne complète « qui a offert à qui » s'affiche pour tout le monde.
2. **Devine ton cadeau.** Au moment de donner son cadeau, on touche le nom de la personne qui le reçoit ; son écran affiche le 1er indice sur le cadeau, et elle doit deviner ce qu'il y a dans le paquet avant de l'ouvrir. Trois indices, même barème. Celui qui offre valide la réponse (« trouvé » / « indice suivant »), pas de saisie exacte à vérifier.

Un **tableau de la soirée** additionne les points des deux jeux. Pas de temps réel : chaque écran se met à jour toutes les quelques secondes (`wire:poll`).

### Jeux du jour

Deux jeux sur la page `/jeux`, le même tirage pour tout le monde (d'après la date), une tentative par jour et par jeu, une série 🔥 et un résultat à partager. Ils piochent dans une liste d'objets dédiée (prix exact et image), distincte du catalogue d'idées.

1. **Le Juste Prix** : une photo et un nom d'objet ; on devine son prix en 6 essais, avec « c'est plus » ou « c'est moins » à chaque fois. Gagné à 5 % près.
   > 🏷️ Le Juste Prix #12 · 4/6 ⬆️⬇️⬆️✅ · bolduc.projectp.fr
2. **Paquet mystère** : un objet emballé sous 5 couches de papier ; chaque mauvaise réponse en arrache une et laisse voir un peu plus de l'objet. La réponse se choisit dans une liste avec autocomplétion (pas de faute de frappe à gérer).
   > 📦 Paquet mystère #12 · trouvé avec 3 couches restantes 🟥🟥🎁 · bolduc.projectp.fr

## 4. Cahier des charges technique

- Composants Livewire pour la liste, la réservation et la barre de participation (sans rechargement).
- Tables `users`, `lists`, `list_owners`, `gifts` (avec `is_secret` et `added_by`), `reservations`, `contributions`, `guests`, `ideas`, `santa_groups`, `santa_members`, `santa_exclusions`, `clues` (indices du mode soirée), `party_guesses`, `daily_items`, `daily_plays`.
- Liens secrets : jetons aléatoires longs, jamais d'identifiant devinable dans l'URL.
- Lecture des liens : requête serveur avec délai court, taille limitée, refus des adresses internes (protection SSRF).
- Rappels : commande planifiée (`schedule:run`) qui envoie les e-mails du jour.
- Tests : la surprise (le propriétaire ne voit rien), le tirage du Secret Santa (personne ne se tire, exclusions respectées), la somme des participations, le tirage déterministe des jeux du jour (même date = même objet), le barème du mode soirée.
- Mise en ligne sur Coolify à chaque push sur la branche principale.

## 5. Critères de réussite

- [ ] Le propriétaire ne voit ni réservation, ni participation, ni cadeau secret, sauf s'il choisit de les découvrir après la date.
- [ ] Deux invités ne peuvent pas réserver le même cadeau.
- [ ] Un lien de boutique remplit titre, image et prix (ou laisse la main sans planter).
- [ ] Le Secret Santa respecte les exclusions et personne ne se tire lui-même.
- [ ] Les rappels partent à J-7 (testé avec une date simulée).
- [ ] En mode soirée, un indice n'apparaît qu'après le précédent, et le destinataire ne voit jamais les indices avant le lancement.
- [ ] Les deux jeux du jour donnent le même objet à tout le monde le même jour, et une seule partie par jour.

## 6. Bonus facultatifs

- Carnet d'anniversaires des proches, avec rappel et lien vers leur liste.
- Listes des enfants gérées depuis le compte d'un parent.
- QR code imprimable pour un faire-part.
- Mode « Pas de cadeau, merci » (cagnotte voyage, don à une association).
- Liste suivante pré-remplie avec les cadeaux non offerts, ou dupliquée de l'an dernier.
- Questions anonymes au propriétaire (« Quelle taille ? »).
- Bookmarklet « Ajouter à ma liste » depuis n'importe quelle boutique.
- Export PDF de la liste.

## 7. Définition du « terminé »

Le MVP fonctionne, les critères de la section 5 sont cochés, le site est en ligne sur `bolduc.projectp.fr`, le README a son lien et sa capture, et la carte du projet passe à « terminé » sur la vitrine.

## 8. À me demander avant de coder

À poser **avant d'écrire le code**, et attendre ma réponse :

1. ~~**Le jeu quotidien.**~~ Choisi le 31 octobre : **Le Juste Prix** et **Paquet mystère** (section 3).
2. **Le look.** Me demander une référence visuelle sur laquelle se baser (un site, une marque, une ambiance, une capture). Le site doit être original et agréable à regarder, **pas « basique »** : une vraie identité (typographie, palette, mise en page, détails, animations), pas un gabarit générique. Si je n'ai pas d'idée, me proposer deux directions et attendre mon choix.

## 9. Aperçu du lien (à faire sans me le redemander)

- Image Open Graph **1200 × 630** stylisée aux couleurs du projet : nom, accroche, technos et capture, générée avec `scripts/og/build-og.py` (ajouter le projet à la liste).
- Sur chaque page publique : `og:title`, `og:description`, `og:url`, `og:image` (adresse absolue), `og:image:width`, `og:image:height`, `og:image:alt` et `twitter:card` = `summary_large_image`.
- Une liste partagée a son propre aperçu (titre de la liste et type d'événement), sans rien révéler des réservations.
- Vérifier en ligne que l'image répond en 200 et que les balises sont servies.

## 10. Indexation (à faire sans me le redemander)

- `robots.txt` : pages privées et routes techniques (listes, Secret Santa, admin) en `Disallow`, lien vers le sitemap.
- `sitemap.xml` : les pages publiques uniquement (accueil, idées de cadeaux).
- Balise `canonical` sur chaque page publique ; `noindex` sur les listes et les pages privées.
- Données structurées JSON-LD sur l'accueil : `WebApplication`, avec `isPartOf` ProjectP (https://projectp.fr/).
- Lien « 30 jours, 30 projets » vers https://projectp.fr/ dans le pied de page.
- Vérifier en ligne, puis me rappeler de soumettre `https://bolduc.projectp.fr/sitemap.xml` dans Google Search Console.

## 11. Vitrine 30days30projects (avec ma validation)

Une fois le projet en ligne, **me demander mon accord**, puis seulement :

- `projects.js` : l'entrée du jour 19 passe à `status: 'done'`, avec `folder: 'day-19-gift-list'`, `name: 'Bolduc'`, `url`, `repo`, `image` et `description`.
- `preview-days-images/day19_bolduc.png` : la capture de l'accueil, en 1600 × 900.
- `README.md` : la ligne du jour 19 dans le tableau des 30 projets.
- Commit et push du dépôt `30days30projects`.
