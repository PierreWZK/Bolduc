"""Une photo libre par idée, depuis Wikimedia Commons, avec son auteur et sa licence.

Lit database/seeders/data/idees.json, écrit les images dans public/images/idees/
et complète les champs « image » et « credit » du même fichier.
Ne prend que CC0, domaine public et CC BY / CC BY-SA (pas de NC ni de ND).
Relancer le script ne retélécharge pas ce qui existe déjà (--force pour tout refaire).
"""

import html
import json
import re
import sys
import time
import unicodedata
import urllib.parse
import urllib.request
from pathlib import Path

RACINE = Path(__file__).resolve().parents[2]
DONNEES = RACINE / 'database/seeders/data/idees.json'
IMAGES = RACINE / 'public/images/idees'
API = 'https://commons.wikimedia.org/w/api.php'
UA = 'Bolduc/1.0 (projet personnel de Pierre ; https://bolduc.projectp.fr)'
FORCE = '--force' in sys.argv


def slug(texte):
    ascii_ = unicodedata.normalize('NFKD', texte).encode('ascii', 'ignore').decode()
    return re.sub(r'[^a-z0-9]+', '-', ascii_.lower()).strip('-')


def licence_ok(nom):
    nom = (nom or '').lower()
    return nom.startswith('cc0') or nom.startswith('public domain') or nom.startswith('cc by') and 'nc' not in nom and 'nd' not in nom


def chercher(requete):
    parametres = {
        'action': 'query', 'format': 'json', 'generator': 'search',
        'gsrnamespace': 6, 'gsrlimit': 12, 'gsrsearch': f'{requete} filetype:bitmap',
        'prop': 'imageinfo', 'iiprop': 'url|extmetadata|size|mime', 'iiurlwidth': 800,
    }
    requete_http = urllib.request.Request(f'{API}?{urllib.parse.urlencode(parametres)}', headers={'User-Agent': UA})
    with urllib.request.urlopen(requete_http, timeout=20) as reponse:
        pages = json.load(reponse).get('query', {}).get('pages', {}).values()

    for page in sorted(pages, key=lambda p: p.get('index', 0)):
        info = (page.get('imageinfo') or [{}])[0]
        meta = info.get('extmetadata', {})
        licence = meta.get('LicenseShortName', {}).get('value', '')
        if info.get('mime') not in ('image/jpeg', 'image/png') or info.get('width', 0) < 500:
            continue
        if not licence_ok(licence) or 'thumburl' not in info:
            continue
        auteur = html.unescape(re.sub(r'<[^>]+>', '', meta.get('Artist', {}).get('value', 'auteur inconnu'))).strip()
        return info['thumburl'], f'{auteur[:150]} · {licence} · Wikimedia Commons'

    return None, None


def telecharger(url, destination):
    requete_http = urllib.request.Request(url, headers={'User-Agent': UA})
    with urllib.request.urlopen(requete_http, timeout=30) as reponse:
        destination.write_bytes(reponse.read())


def main():
    idees = json.loads(DONNEES.read_text(encoding='utf-8'))
    IMAGES.mkdir(parents=True, exist_ok=True)
    manquantes = []

    for idee in idees:
        if idee.get('image') and (RACINE / 'public' / idee['image']).exists() and not FORCE:
            continue

        url, credit = chercher(idee['requete'])
        if not url:
            idee['image'], idee['credit'] = None, None
            manquantes.append(idee['title'])
            continue

        extension = '.png' if url.lower().endswith('.png') else '.jpg'
        fichier = IMAGES / f"{slug(idee['title'])}{extension}"
        telecharger(url, fichier)
        idee['image'] = f"images/idees/{fichier.name}"
        idee['credit'] = credit
        print(f"ok  {idee['title']}")
        time.sleep(1)

    DONNEES.write_text(json.dumps(idees, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    print(f"{sum(1 for i in idees if i.get('image'))}/{len(idees)} avec photo")
    if manquantes:
        print('Sans photo libre trouvée :', ', '.join(manquantes))


if __name__ == '__main__':
    main()
