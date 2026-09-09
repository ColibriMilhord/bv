<?php
/**
 * config/avis.php — Avis clients Google : note, nombre total et derniers avis.
 * ---------------------------------------------------------------------------
 * Corrige deux défauts constatés : les trois avis affichés étaient figés dans
 * le code (l'un d'eux datait de plus de sept ans) et le compteur « 102 avis »
 * était écrit en dur à trois endroits, sans jamais bouger.
 *
 * Source de vérité : l'API Google Places, interrogée au maximum une fois
 * toutes les douze heures et mise en cache sur disque. Deux réglages à
 * renseigner dans config/secrets.php :
 *
 *     'GOOGLE_PLACES_API_KEY' => 'AIza…',
 *     'GOOGLE_PLACE_ID'       => 'ChIJ…',
 *
 * Le Place ID se trouve avec l'outil officiel :
 * https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder
 *
 * Sans ces réglages — ou si Google ne répond pas — le site retombe sur
 * config/avis-secours.php, modifiable à la main. La page reste donc toujours
 * complète, et n'affiche jamais de valeur inventée : la provenance est
 * indiquée sous les avis.
 *
 * Le même jeu de données alimente l'affichage ET le balisage JSON-LD
 * (aggregateRating) : les deux ne peuvent plus diverger.
 */

require_once __DIR__ . '/env.php';

const AVIS_CACHE        = __DIR__ . '/../cache/avis-google.json';
const AVIS_TTL          = 43200; // 12 h
const AVIS_TTL_ECHEC    = 3600;  // 1 h avant de retenter après un échec
const AVIS_NOMBRE       = 3;     // avis affichés
const AVIS_TIMEOUT      = 6;     // secondes
const AVIS_LONGUEUR_MAX = 260;   // caractères affichés par avis

// ═══════════════════════════════════════════════════════════════════════════
// API publique
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Avis à afficher.
 *
 * @return array{
 *   note: float, total: int, avis: array<int,array>, maj: int, source: string
 * }
 */
function avis_donnees(): array
{
    $cache = avis_lire_cache();
    if ($cache !== null) return $cache;

    $frais = avis_interroger_google();
    if ($frais !== null) {
        avis_ecrire_cache($frais);
        return $frais;
    }

    // Échec : on mémorise l'heure pour ne pas relancer un appel à chaque visite.
    $secours = avis_secours();
    avis_ecrire_cache($secours + ['echec' => true]);

    return $secours;
}

/** Libellé de provenance, affiché sous les avis. */
function avis_libelle_source(array $donnees): string
{
    switch ($donnees['source'] ?? '') {
        case 'google': return 'Google — synchronisé le ' . date('d/m/Y à H\hi', $donnees['maj']);
        case 'secours': return 'Sélection publiée par les propriétaires';
        default:        return 'Google';
    }
}

/** « il y a 3 mois », « il y a 2 ans » — à partir d'un horodatage Unix. */
function avis_date_relative(?int $horodatage): string
{
    if (!$horodatage) return '';

    $jours = max(0, (int) floor((time() - $horodatage) / 86400));

    if ($jours < 1)   return "aujourd'hui";
    if ($jours < 14)  return 'il y a ' . $jours . ' jour' . ($jours > 1 ? 's' : '');
    if ($jours < 61)  return 'il y a ' . (int) round($jours / 7) . ' semaines';
    if ($jours < 365) return 'il y a ' . (int) round($jours / 30) . ' mois';

    $annees = $jours / 365;
    if ($annees < 2)  return 'il y a un an';

    return 'il y a ' . (int) floor($annees) . ' ans';
}

// ═══════════════════════════════════════════════════════════════════════════
// Repli éditorial
// ═══════════════════════════════════════════════════════════════════════════

/** Valeurs de repli, modifiables à la main dans config/avis-secours.php. */
function avis_secours(): array
{
    $chemin  = __DIR__ . '/avis-secours.php';
    $donnees = is_file($chemin) ? require $chemin : [];

    return [
        'note'   => (float) ($donnees['note'] ?? 5.0),
        'total'  => (int) ($donnees['total'] ?? 0),
        'avis'   => array_slice($donnees['avis'] ?? [], 0, AVIS_NOMBRE),
        'maj'    => time(),
        'source' => 'secours',
    ];
}

// ═══════════════════════════════════════════════════════════════════════════
// Cache disque
// ═══════════════════════════════════════════════════════════════════════════

function avis_lire_cache(): ?array
{
    if (!is_file(AVIS_CACHE)) return null;

    $donnees = json_decode((string) file_get_contents(AVIS_CACHE), true);
    if (!is_array($donnees) || !isset($donnees['maj'])) return null;

    $age = time() - (int) $donnees['maj'];
    $ttl = !empty($donnees['echec']) ? AVIS_TTL_ECHEC : AVIS_TTL;
    if ($age > $ttl) return null;

    unset($donnees['echec']);
    if (($donnees['source'] ?? '') === 'google') $donnees['source'] = 'google';

    return $donnees;
}

function avis_ecrire_cache(array $donnees): void
{
    $dossier = dirname(AVIS_CACHE);
    if (!is_dir($dossier) && !@mkdir($dossier, 0775, true)) return;
    if (!is_writable($dossier)) return;

    @file_put_contents(AVIS_CACHE, json_encode($donnees, JSON_UNESCAPED_UNICODE));
}

// ═══════════════════════════════════════════════════════════════════════════
// Appel à Google
// ═══════════════════════════════════════════════════════════════════════════

/** Interroge l'API Places (nouvelle version, puis l'ancienne en secours). */
function avis_interroger_google(): ?array
{
    $cle   = (string) secret('GOOGLE_PLACES_API_KEY', '');
    $place = (string) secret('GOOGLE_PLACE_ID', '');
    if ($cle === '' || $place === '') return null;

    $donnees = avis_api_nouvelle($cle, $place) ?? avis_api_ancienne($cle, $place);
    if ($donnees === null) return null;

    // Du plus récent au plus ancien : c'est la demande, et c'est ce qui
    // rassure un visiteur. Google ne garantit aucun ordre dans sa réponse.
    usort($donnees['avis'], fn($a, $b) => ($b['horodatage'] ?? 0) <=> ($a['horodatage'] ?? 0));
    $donnees['avis'] = array_slice($donnees['avis'], 0, AVIS_NOMBRE);

    return $donnees;
}

/** Places API (New) — places.googleapis.com. */
function avis_api_nouvelle(string $cle, string $place): ?array
{
    $url = 'https://places.googleapis.com/v1/places/' . rawurlencode($place)
         . '?languageCode=fr&regionCode=FR';

    $reponse = avis_http($url, [
        'X-Goog-Api-Key: ' . $cle,
        'X-Goog-FieldMask: rating,userRatingCount,reviews',
    ]);
    if (!is_array($reponse) || !isset($reponse['userRatingCount'])) return null;

    $avis = [];
    foreach ($reponse['reviews'] ?? [] as $r) {
        $auteur = $r['authorAttribution'] ?? [];
        $texte  = $r['text']['text'] ?? ($r['originalText']['text'] ?? '');
        if ($texte === '') continue;

        $avis[] = avis_normaliser(
            $auteur['displayName'] ?? '',
            $texte,
            (int) ($r['rating'] ?? 5),
            isset($r['publishTime']) ? (int) strtotime($r['publishTime']) : null,
            $r['relativePublishTimeDescription'] ?? ''
        );
    }

    return [
        'note'   => round((float) ($reponse['rating'] ?? 5), 1),
        'total'  => (int) $reponse['userRatingCount'],
        'avis'   => $avis,
        'maj'    => time(),
        'source' => 'google',
    ];
}

/** Place Details, ancienne API — utile si la clé n'est pas migrée. */
function avis_api_ancienne(string $cle, string $place): ?array
{
    $url = 'https://maps.googleapis.com/maps/api/place/details/json?'
         . http_build_query([
             'place_id' => $place,
             'fields'   => 'rating,user_ratings_total,reviews',
             'language' => 'fr',
             'key'      => $cle,
         ]);

    $reponse = avis_http($url);
    if (!is_array($reponse) || ($reponse['status'] ?? '') !== 'OK') return null;

    $resultat = $reponse['result'] ?? [];
    $avis     = [];
    foreach ($resultat['reviews'] ?? [] as $r) {
        $texte = $r['text'] ?? '';
        if ($texte === '') continue;

        $avis[] = avis_normaliser(
            $r['author_name'] ?? '',
            $texte,
            (int) ($r['rating'] ?? 5),
            isset($r['time']) ? (int) $r['time'] : null,
            $r['relative_time_description'] ?? ''
        );
    }

    return [
        'note'   => round((float) ($resultat['rating'] ?? 5), 1),
        'total'  => (int) ($resultat['user_ratings_total'] ?? 0),
        'avis'   => $avis,
        'maj'    => time(),
        'source' => 'google',
    ];
}

/** Met un avis au format attendu par la page. */
function avis_normaliser(string $auteur, string $texte, int $note, ?int $horodatage, string $dateGoogle): array
{
    $auteur = trim($auteur) ?: 'Client Google';

    // « Jean-Pierre Martin » → « Jean-Pierre M. » : lisible sans exposer
    // le nom complet d'une personne sur une page publique.
    $morceaux = preg_split('/\s+/u', $auteur) ?: [$auteur];
    if (count($morceaux) > 1) {
        $auteur = $morceaux[0] . ' ' . mb_strtoupper(mb_substr(end($morceaux), 0, 1)) . '.';
    }

    $texte = trim(preg_replace('/\s+/u', ' ', $texte));
    if (mb_strlen($texte) > AVIS_LONGUEUR_MAX) {
        $texte = mb_substr($texte, 0, AVIS_LONGUEUR_MAX);
        $coupe = mb_strrpos($texte, ' ');
        if ($coupe !== false) $texte = mb_substr($texte, 0, $coupe);
        $texte .= '…';
    }

    return [
        'auteur'     => $auteur,
        'initiale'   => mb_strtoupper(mb_substr($auteur, 0, 1)),
        'texte'      => $texte,
        'note'       => max(1, min(5, $note)),
        'horodatage' => $horodatage,
        'date'       => avis_date_relative($horodatage) ?: $dateGoogle,
    ];
}

/** Requête HTTP JSON, silencieuse en cas d'échec. */
function avis_http(string $url, array $entetes = []): ?array
{
    if (!function_exists('curl_init')) return null;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => AVIS_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_HTTPHEADER     => array_merge(['Accept: application/json'], $entetes),
        CURLOPT_USERAGENT      => 'BellevueAveyron-App/2.0 (+https://bellevuedaveyron.fr/)',
    ]);
    $corps = curl_exec($ch);
    $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200 || !is_string($corps) || $corps === '') {
        error_log('[bellevue] avis Google : réponse HTTP ' . $code);
        return null;
    }

    $json = json_decode($corps, true);
    return is_array($json) ? $json : null;
}
