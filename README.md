# Bolduc · Les listes de cadeaux, sans gâcher la surprise

> Jour 19 du défi **30 jours, 30 projets** · Laravel · Livewire · MySQL · ⏳ **À venir**

Noël, anniversaire, mariage, crémaillère, naissance : on crée sa liste, on l'envoie par un lien, et les proches réservent ce qu'ils offrent. **Bolduc** garde la surprise : le propriétaire de la liste ne voit jamais qui a réservé quoi, ni les cadeaux « hors liste » que ses proches lui préparent.

📄 Les règles détaillées sont dans le [cahier des charges](CAHIER_DES_CHARGES.md).

## Au programme

- Listes par événement (thème, date, compte à rebours), partagées par lien secret ; pas de compte pour les invités.
- Ajout d'un cadeau en collant un lien de boutique : titre, image et prix lus automatiquement.
- Réservation sans spoiler, et **cadeau à plusieurs** avec une barre de participation.
- **Idées de cadeaux** : un catalogue d'inspiration pour remplir sa liste, ou pour offrir autre chose en secret.
- **Secret Santa** : tirage au sort avec exclusions.
- **Remerciements** après l'événement et **rappels par e-mail** avant.
- **Mode soirée** : *Qui m'a gâté ?* et *Devine ton cadeau*, deux jeux à indices pour le moment d'ouvrir les paquets.
- **Jeux du jour** : *Le Juste Prix* et *Paquet mystère*.

## Lancer en local

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm install && npm run dev   # dans un second terminal
php artisan serve            # http://127.0.0.1:8000
```

## Mise en ligne

Prévue sur Coolify : **https://bolduc.projectp.fr**.

## Stack

Laravel · Livewire · MySQL.

## Avant de déclarer le projet terminé

> Rappel pour Claude : à faire sur chaque projet, sans que je le redemande (détail dans les sections 9 et 10 du cahier des charges).

- **Indexation** : `robots.txt`, `sitemap.xml`, balise canonical, données structurées JSON-LD (`WebApplication` rattachée à ProjectP) et lien « 30 jours, 30 projets » vers https://projectp.fr/ dans le pied de page. Vérifier en ligne, puis me rappeler de soumettre `https://bolduc.projectp.fr/sitemap.xml` dans Google Search Console.
- **Aperçu du lien** : image Open Graph stylisée 1200 × 630 aux couleurs du projet et balises `og:*` / `twitter:card`, vérifiées en ligne.
- **Vitrine** : la mise à jour de `30days30projects` (README et `projects.js`) se fait **après ma validation**.
