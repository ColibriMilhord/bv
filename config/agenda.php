<?php
/**
 * config/agenda.php — Service « agenda de proximité ».
 * ---------------------------------------------------------------------------
 * Corrige le défaut constaté sur la page Découvrir : le widget HIT Aveyron
 * remontait des événements de Réquista, Aubin ou Laissac, à 40-80 km du gîte,
 * sous un intitulé « autour du gîte ».
 *
 * Deux causes, deux réponses :
 *   1. Le point de référence transmis au widget était erroné (voir SEO_LAT /
 *      SEO_LNG dans config/seo.php). Les URL des widgets sont désormais
 *      construites à partir de cette source unique.
 *   2. Aucun filtrage de distance n'était appliqué côté site. Ce service
 *      calcule une distance réelle (formule de haversine) depuis le gîte,
 *      écarte tout ce qui dépasse le rayon demandé et trie du plus proche au
 *      plus lointain.
 *
 * Le socle éditorial (agenda_curated_events) est vérifié et toujours
 * disponible : même flux Datatourisme éteint, la page reste pertinente.
 *
 * Bénéfice SEO/IA : l'agenda est rendu côté serveur, en HTML, avec des
 * distances explicites — là où un iframe reste totalement opaque aux moteurs
 * de recherche comme aux robots des moteurs de réponse.
 */

require_once __DIR__ . '/seo.php';

const AGENDA_CACHE_FILE   = __DIR__ . '/../cache/agenda-datatourisme.json';
const AGENDA_CACHE_TTL    = 21600; // 6 h
const AGENDA_FAIL_TTL     = 1800;  // 30 min avant de retenter après un échec
const AGENDA_DEFAULT_KM   = 35;    // rayon « autour du gîte »
const AGENDA_HTTP_TIMEOUT = 6;

// ═══════════════════════════════════════════════════════════════════════════
// Distances
// ═══════════════════════════════════════════════════════════════════════════

/** Distance à vol d'oiseau entre le gîte et un point, en km (haversine). */
function agenda_distance_km(float $lat, float $lng): float
{
    $r = 6371.0;
    $dLat = deg2rad($lat - SEO_LAT);
    $dLng = deg2rad($lng - SEO_LNG);
    $a = sin($dLat / 2) ** 2
       + cos(deg2rad(SEO_LAT)) * cos(deg2rad($lat)) * sin($dLng / 2) ** 2;

    return round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
}

/** Libellé lisible d'une distance. */
function agenda_distance_label(?float $km): string
{
    if ($km === null)  return 'Aux alentours';
    if ($km < 1.0)     return 'Dans le village';
    return 'À ' . ($km < 10 ? number_format($km, 1, ',', ' ') : round($km)) . ' km';
}

// ═══════════════════════════════════════════════════════════════════════════
// Socle éditorial vérifié — rendez-vous récurrents à moins de 30 km
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Rendez-vous locaux permanents ou saisonniers, vérifiés par les propriétaires.
 * Sert de socle affiché et de repli si le flux Datatourisme est indisponible.
 *
 * 'lat'/'lng' localisent l'événement pour le balisage schema.org ;
 * 'distance_km' est la distance routière déclarée, celle qui est affichée, afin
 * de rester cohérente avec le tableau des distances de la page. Sans
 * 'distance_km', la distance à vol d'oiseau est calculée automatiquement.
 */
function agenda_curated_events(): array
{
    return [
        [
            'titre'       => "Marché nocturne et festif de Sainte-Eulalie-d'Olt",
            'quand'       => "Tous les mercredis de l'été",
            'commune'     => "Sainte-Eulalie-d'Olt",
            'lat' => SEO_LAT, 'lng' => SEO_LNG, 'distance_km' => 0.0,
            'description' => "Producteurs locaux, artisanat et repas partagé sur la grande place du village, à quelques minutes à pied du gîte.",
            'type'        => 'fete',
        ],
        [
            'titre'       => "Marché traditionnel de Saint-Geniez-d'Olt",
            'quand'       => 'Le jeudi matin, toute l\'année',
            'commune'     => "Saint-Geniez-d'Olt-et-d'Aubrac",
            'lat' => 44.4661, 'lng' => 3.0089, 'distance_km' => 3.0,
            'description' => "Fromages de l'Aubrac, aligot, tripous et charcuteries de producteurs, sous les halles et dans la vieille ville.",
            'type'        => 'fete',
        ],
        [
            'titre'       => "Eulalie d'Art — parcours d'artistes et ateliers",
            'quand'       => 'De juin à septembre',
            'commune'     => "Sainte-Eulalie-d'Olt",
            'lat' => SEO_LAT, 'lng' => SEO_LNG, 'distance_km' => 0.0,
            'description' => "Expositions dans les ruelles médiévales et initiation à la céramique avec les artisans du village.",
            'type'        => 'culture',
        ],
        [
            'titre'       => "Festival en Vallée d'Olt",
            'quand'       => "L'été, en soirée",
            'commune'     => "Sainte-Eulalie-d'Olt",
            'lat' => SEO_LAT, 'lng' => SEO_LNG, 'distance_km' => 0.0,
            'description' => "Concerts et spectacles dans le cadre du village classé parmi les Plus Beaux Villages de France.",
            'type'        => 'culture',
        ],
        [
            'titre'       => 'Baignade, canoë et paddle sur le Lot',
            'quand'       => 'De mai à septembre',
            'commune'     => "Saint-Geniez-d'Olt-et-d'Aubrac",
            'lat' => 44.4661, 'lng' => 3.0089, 'distance_km' => 3.0,
            'description' => "Base nautique du lac de Castelnau et descentes de la rivière avec O'Paddle d'Olt et Avenga.",
            'type'        => 'nature',
        ],
        [
            'titre'       => 'Musée Marcel Boudou et expositions estivales',
            'quand'       => "L'été",
            'commune'     => "Sainte-Eulalie-d'Olt",
            'lat' => SEO_LAT, 'lng' => SEO_LNG, 'distance_km' => 0.0,
            'description' => "Collections et expositions temporaires au cœur du village médiéval.",
            'type'        => 'culture',
        ],
        [
            'titre'       => 'Marchés et brocante d\'Espalion',
            'quand'       => 'Le mardi et le vendredi',
            'commune'     => 'Espalion',
            'lat' => 44.5211, 'lng' => 2.7644, 'distance_km' => 28.0,
            'description' => "Marché au bord du Lot, au pied du Pont Vieux classé au patrimoine mondial de l'UNESCO.",
            'type'        => 'fete',
        ],
        [
            'titre'       => "Aligot et burons du plateau de l'Aubrac",
            'quand'       => 'De mai à octobre',
            'commune'     => 'Aubrac',
            'lat' => 44.6272, 'lng' => 2.8817, 'distance_km' => 25.0,
            'description' => "Anciennes cabanes de bergers transformées en tables d'altitude : aligot servi face aux estives.",
            'type'        => 'restaurant',
        ],
    ];
}

// ═══════════════════════════════════════════════════════════════════════════
// Flux Datatourisme (enrichissement optionnel)
// ═══════════════════════════════════════════════════════════════════════════

/** Déballe une valeur JSON-LD ({'@value':…}, tableaux, chaînes). */
function agenda_dt_value($node, ?string $lang = 'fr'): string
{
    if ($node === null) return '';
    if (is_string($node) || is_numeric($node)) return (string) $node;

    if (is_array($node)) {
        if (isset($node['@value'])) return (string) $node['@value'];
        foreach ($node as $item) {
            if (is_array($item) && ($item['@language'] ?? null) === $lang && isset($item['@value'])) {
                return (string) $item['@value'];
            }
        }
        foreach ($node as $item) {
            $v = agenda_dt_value($item, $lang);
            if ($v !== '') return $v;
        }
    }
    return '';
}

/** Premier élément utile d'une propriété JSON-LD (objet ou tableau d'objets). */
function agenda_dt_first($node): ?array
{
    if (!is_array($node)) return null;
    if (isset($node[0]) && is_array($node[0])) return $node[0];
    return $node;
}

/** Récupère le flux, avec cache disque et repli silencieux. */
function agenda_fetch_raw(): ?array
{
    $cacheDir = dirname(AGENDA_CACHE_FILE);
    if (is_file(AGENDA_CACHE_FILE)) {
        $age  = time() - (int) filemtime(AGENDA_CACHE_FILE);
        $data = json_decode((string) file_get_contents(AGENDA_CACHE_FILE), true);
        $ttl  = ($data === null || $data === []) ? AGENDA_FAIL_TTL : AGENDA_CACHE_TTL;
        if ($age < $ttl) return is_array($data) ? $data : null;
    }

    if (!defined('DATATOURISME_API_URL')) {
        $conf = __DIR__ . '/../config_datatourisme.php';
        if (!is_file($conf)) return null;
        require_once $conf;
    }

    $ch = curl_init(DATATOURISME_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => AGENDA_HTTP_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_ENCODING       => '',
        CURLOPT_USERAGENT      => 'BellevueAveyron-App/2.0 (+https://bellevuedaveyron.fr/)',
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $items = [];
    if ($code === 200 && is_string($body) && $body !== '') {
        $json  = json_decode($body, true);
        $items = $json['@graph'] ?? (is_array($json) ? $json : []);
    }

    // Cache écrit dans tous les cas : un tableau vide fait office de cache négatif.
    if (is_dir($cacheDir) ? is_writable($cacheDir) : @mkdir($cacheDir, 0775, true)) {
        @file_put_contents(AGENDA_CACHE_FILE, json_encode($items, JSON_UNESCAPED_UNICODE));
    }

    return $items ?: null;
}

/** Événements du flux situés dans le rayon demandé, triés par distance. */
function agenda_remote_events(int $radiusKm, int $limit): array
{
    $items = agenda_fetch_raw();
    if (!$items) return [];

    $events = [];
    foreach ($items as $item) {
        if (!is_array($item)) continue;

        $titre = agenda_dt_value($item['rdfs:label'] ?? null);
        if ($titre === '') continue;

        // Géolocalisation : sans coordonnées, impossible de garantir la proximité.
        $place = agenda_dt_first($item['isLocatedAt'] ?? null);
        $geo   = $place ? agenda_dt_first($place['schema:geo'] ?? null) : null;
        $lat   = $geo ? (float) agenda_dt_value($geo['schema:latitude']  ?? null) : 0.0;
        $lng   = $geo ? (float) agenda_dt_value($geo['schema:longitude'] ?? null) : 0.0;
        if ($lat === 0.0 || $lng === 0.0) continue;

        $km = agenda_distance_km($lat, $lng);
        if ($km > $radiusKm) continue;

        $adresse = $place ? agenda_dt_first($place['schema:address'] ?? null) : null;
        $commune = $adresse ? agenda_dt_value($adresse['schema:addressLocality'] ?? null) : '';

        $desc = '';
        $hd   = agenda_dt_first($item['hasDescription'] ?? null);
        if ($hd) {
            $desc = agenda_dt_value($hd['shortDescription'] ?? null)
                 ?: agenda_dt_value($hd['dc:description'] ?? null);
        }

        // Dates : on écarte ce qui est déjà passé.
        $debut = $fin = null;
        foreach (['takesPlaceAt', 'isSpecialOpening'] as $prop) {
            $slot = agenda_dt_first($item[$prop] ?? null);
            if (!$slot) continue;
            $debut = agenda_dt_value($slot['startDate'] ?? $slot['schema:startDate'] ?? null) ?: null;
            $fin   = agenda_dt_value($slot['endDate']   ?? $slot['schema:endDate']   ?? null) ?: null;
            break;
        }
        $aujourdhui = date('Y-m-d');
        if (($fin ?: $debut) !== null && ($fin ?: $debut) < $aujourdhui) continue;

        $events[] = [
            'titre'       => $titre,
            'quand'       => $debut ? agenda_format_periode($debut, $fin) : 'Prochainement',
            'commune'     => $commune ?: 'Aveyron',
            'lat'         => $lat,
            'lng'         => $lng,
            'distance_km' => $km,
            'description' => mb_substr($desc, 0, 170) . (mb_strlen($desc) > 170 ? '…' : ''),
            'debut'       => $debut,
            'fin'         => $fin,
            'type'        => agenda_guess_type($titre . ' ' . $desc),
            'source'      => 'datatourisme',
        ];
    }

    usort($events, function ($a, $b) { return $a['distance_km'] <=> $b['distance_km']; });

    return array_slice($events, 0, $limit);
}

/** Période lisible en français. */
function agenda_format_periode(string $debut, ?string $fin): string
{
    $d = date_create($debut);
    if (!$d) return 'Prochainement';
    $f = $fin ? date_create($fin) : null;
    if (!$f || $f->format('Y-m-d') === $d->format('Y-m-d')) {
        return 'Le ' . $d->format('d/m/Y');
    }
    return 'Du ' . $d->format('d/m') . ' au ' . $f->format('d/m/Y');
}

/** Catégorie déduite du texte, pour le filtrage par onglet. */
function agenda_guess_type(string $texte): string
{
    $t = mb_strtolower($texte);
    $regles = [
        'restaurant' => ['restaurant', 'dégustation', 'gastronomie', 'aligot', 'producteurs'],
        'nature'     => ['randonnée', 'balade', 'sentier', 'nature', 'kayak', 'paddle', 'vélo'],
        'famille'    => ['famille', 'enfant', 'atelier', 'jeu'],
        'fete'       => ['fête', 'marché', 'festival', 'concert', 'bal', 'feu d'],
    ];
    foreach ($regles as $type => $mots) {
        foreach ($mots as $mot) {
            if (mb_strpos($t, $mot) !== false) return $type;
        }
    }
    return 'culture';
}

// ═══════════════════════════════════════════════════════════════════════════
// API publique
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Agenda de proximité : socle éditorial + flux officiel filtré, dédoublonné
 * et trié du plus proche au plus lointain.
 */
function agenda_events(int $radiusKm = AGENDA_DEFAULT_KM, int $limit = 12): array
{
    $curated = [];
    foreach (agenda_curated_events() as $ev) {
        $ev['distance_km'] = $ev['distance_km'] ?? agenda_distance_km($ev['lat'], $ev['lng']);
        $ev['source']      = 'local';
        if ($ev['distance_km'] <= $radiusKm) $curated[] = $ev;
    }

    $events = array_merge($curated, agenda_remote_events($radiusKm, $limit));

    // Dédoublonnage sur un titre normalisé
    $vus = [];
    $events = array_filter($events, function ($ev) use (&$vus) {
        $cle = preg_replace('/[^a-z0-9]/', '', mb_strtolower($ev['titre']));
        if (isset($vus[$cle])) return false;
        $vus[$cle] = true;
        return true;
    });

    usort($events, function ($a, $b) { return $a['distance_km'] <=> $b['distance_km']; });

    return array_slice(array_values($events), 0, $limit);
}

/** Nœud JSON-LD Event pour un événement de l'agenda. */
function agenda_node_event(array $ev): array
{
    return array_filter([
        '@type'       => 'Event',
        'name'        => $ev['titre'],
        'description' => $ev['description'] ?: null,
        'startDate'   => $ev['debut'] ?? null,
        'endDate'     => $ev['fin'] ?? null,
        'eventSchedule' => isset($ev['quand']) && !isset($ev['debut'])
            ? ['@type' => 'Schedule', 'description' => $ev['quand']]
            : null,
        'location' => [
            '@type'   => 'Place',
            'name'    => $ev['commune'],
            'address' => [
                '@type'           => 'PostalAddress',
                'addressLocality' => $ev['commune'],
                'addressRegion'   => SEO_REGION,
                'addressCountry'  => SEO_COUNTRY,
            ],
            'geo' => [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $ev['lat'],
                'longitude' => $ev['lng'],
            ],
        ],
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
    ], function ($v) { return $v !== null; });
}
