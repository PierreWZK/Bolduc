<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Lit titre, image et prix d'une page de boutique, à partir de ses balises publiques.
 * Appelé par CadeauController::store ; l'URL vient de l'utilisateur, donc chaque saut est vérifié.
 */
class LectureDeLien
{
    private const SAUTS_MAX = 3;

    /** @return array{title: ?string, image: ?string, price: ?float} */
    public function lire(string $url): array
    {
        $vide = ['title' => null, 'image' => null, 'price' => null];

        try {
            // Pas de suivi automatique des redirections : chaque adresse est revérifiée.
            // shortcut: page entière lue, sans limite de taille ; à borner si une boutique est très lourde.
            for ($saut = 0; $saut <= self::SAUTS_MAX; $saut++) {
                if (! $this->adresseSure($url)) {
                    return $vide;
                }

                $reponse = Http::timeout(5)
                    ->withUserAgent('Bolduc/1.0 (lecture de lien)')
                    ->withOptions(['allow_redirects' => false])
                    ->get($url);

                if ($reponse->status() < 300 || $reponse->status() >= 400) {
                    break;
                }

                $url = $this->adresseAbsolue($url, (string) $reponse->header('Location'));
            }
        } catch (\Throwable) {
            return $vide;
        }

        if (! $reponse->successful()) {
            return $vide;
        }

        return $this->extraire($reponse->body());
    }

    private function extraire(string $html): array
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument;
        $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($doc);

        $titre = $this->balise($xpath, ['og:title', 'twitter:title']) ?? $this->texteTitre($xpath);
        $image = $this->balise($xpath, ['og:image', 'twitter:image']);
        $prix = $this->balise($xpath, ['product:price:amount', 'og:price:amount']);

        return [
            'title' => $titre ? mb_substr(trim($titre), 0, 120) : null,
            'image' => $image && preg_match('#^https?://#i', $image) ? $image : null,
            'price' => $prix ? round((float) str_replace(',', '.', $prix), 2) : null,
        ];
    }

    private function balise(\DOMXPath $xpath, array $cles): ?string
    {
        foreach ($cles as $cle) {
            $noeud = $xpath->query("//meta[@property='$cle' or @name='$cle']/@content")->item(0);
            if ($noeud && trim($noeud->nodeValue) !== '') {
                return trim($noeud->nodeValue);
            }
        }

        return null;
    }

    private function texteTitre(\DOMXPath $xpath): ?string
    {
        return $xpath->query('//title')->item(0)?->textContent ?: null;
    }

    // Une adresse est refusée si elle pointe vers le réseau local, un loopback ou une plage réservée.
    private function adresseSure(string $url): bool
    {
        $hote = parse_url($url, PHP_URL_HOST);
        $schema = parse_url($url, PHP_URL_SCHEME);

        if (! $hote || ! in_array($schema, ['http', 'https'], true)) {
            return false;
        }

        $ip = $this->ipDe($hote);

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    // Isolé pour que les tests puissent fournir une adresse sans passer par le DNS.
    protected function ipDe(string $hote): string
    {
        return gethostbyname(trim($hote, '[]'));
    }

    private function adresseAbsolue(string $base, string $cible): string
    {
        if (preg_match('#^https?://#i', $cible)) {
            return $cible;
        }

        $schema = parse_url($base, PHP_URL_SCHEME);
        $hote = parse_url($base, PHP_URL_HOST);

        return str_starts_with($cible, '/')
            ? "$schema://$hote$cible"
            : rtrim($base, '/').'/'.$cible;
    }
}
