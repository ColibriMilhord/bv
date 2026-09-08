<?php
/**
 * tools/generer-inventaire-images.php — Régénère docs/seo-ia/inventaire-images.md
 * à partir du registre config/medias.php.
 *
 * Usage (en ligne de commande, depuis la racine du projet) :
 *   php tools/generer-inventaire-images.php
 *
 * À relancer après toute modification du registre des visuels.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Script réservé à la ligne de commande.\n");
}

chdir(__DIR__ . '/..');
require 'config/medias.php';
$r   = media_registry();
$gen = array_filter($r, fn($e) => $e['statut'] === 'generique');

$head = "# Inventaire des visuels — page « Découvrir »\n\n"
. "Généré depuis `config/medias.php`. Ce tableau sert de feuille de route photo.\n\n"
. "## Comment remplacer une image\n\n"
. "Déposer le fichier dans `images/decouvrir/` en le nommant exactement comme le\n"
. "**slug** de la ligne, avec l'extension `.webp`, `.jpg`, `.jpeg` ou `.png` :\n\n"
. "```\nimages/decouvrir/au-moulin-d-alexandre.jpg\n```\n\n"
. "La substitution est immédiate, sans aucune modification de code. Format\n"
. "conseillé : 1200 x 760 px, recadrage paysage, moins de 250 Ko, `.webp` de\n"
. "préférence. Si le sujet de la photo change, mettre à jour le texte alternatif\n"
. "correspondant dans `config/medias.php`.\n\n"
. "## Pourquoi cela compte pour le référencement IA\n\n"
. "Une photo authentique d'un lieu nommé est un signal de première main : elle est\n"
. "indexable dans Google Images, exploitable par les agents multimodaux, et elle\n"
. "distingue le site des dizaines de pages illustrées avec les mêmes photos de\n"
. "banque d'images. Le texte alternatif est, lui, la seule description du visuel\n"
. "que lit un moteur de recherche.\n\n"
. "## Statut des visuels\n\n"
. "- **réel** — le visuel montre effectivement le sujet de la carte\n"
. "- **générique** — photo d'illustration, à remplacer en priorité\n\n";

$rows = "| Slug (nom du fichier à déposer) | Sujet attendu | Statut | Repli actuel |\n|---|---|---|---|\n";
foreach ($r as $slug => $e) {
    $host = parse_url($e['src'], PHP_URL_HOST);
    if ($host === null)                        $src = '**photo du gîte**';
    elseif ($host === 'images.unsplash.com')   $src = 'banque d’images';
    else                                       $src = 'Wikimedia Commons';
    $st   = $e['statut'] === 'generique' ? '**générique**' : 'réel';
    $rows .= sprintf("| `%s` | %s | %s | %s |\n", $slug, $e['alt'], $st, $src);
}

$foot = "\n**" . count($gen) . " visuels sur " . count($r)
      . "** sont encore des photos d’illustration, à remplacer par de vraies photos des lieux.\n\n"
      . "Déjà traité :\n\n"
      . "- La carte « Ping-Pong, Trampoline, Vélos & Piscine », qui parle du gîte, utilise\n"
      . "  désormais une vraie photo de la propriété (piscine et terrasse face à la vallée).\n"
      . "- Le Festival en Vallée d'Olt était illustré par la nef de l'abbatiale de Conques,\n"
      . "  à 70 km : il montre maintenant le village de Sainte-Eulalie-d'Olt.\n"
      . "- Le Trésor de Conques reçoit cette nef, où le trésor est effectivement conservé.\n";

file_put_contents('docs/seo-ia/inventaire-images.md', $head . $rows . $foot);
echo "écrit : " . count($r) . " lignes, " . count($gen) . " génériques\n";
