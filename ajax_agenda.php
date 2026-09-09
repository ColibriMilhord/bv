<?php
/**
 * ajax_agenda.php — Point d'entrée JSON de l'agenda de proximité.
 *
 * Toute la logique (récupération du flux Datatourisme, cache, calcul de
 * distance, filtrage et tri) vit dans config/agenda.php : ce fichier n'est
 * plus qu'une façade HTTP. Il remplace l'ancienne version, qui scrapait le
 * HTML de tourisme-aveyron.com, dupliquait le parsing JSON-LD et renvoyait
 * les 15 premiers éléments du flux sans aucun critère géographique.
 *
 * Paramètres :
 *   ?category = all|fete|culture|nature|famille|restaurant   (défaut : all)
 *   ?rayon    = rayon de recherche en km, 1 à 120            (défaut : 35)
 *   ?limit    = nombre maximum d'événements, 1 à 50          (défaut : 12)
 */

require_once __DIR__ . '/config/agenda.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=1800');
header('X-Robots-Tag: noindex');

$category = isset($_GET['category']) ? (string) $_GET['category'] : 'all';
$rayon    = min(120, max(1, (int) ($_GET['rayon'] ?? AGENDA_DEFAULT_KM)));
$limit    = min(50,  max(1, (int) ($_GET['limit'] ?? 12)));

$events = agenda_events($rayon, $limit);

// Regroupements d'affichage conservés depuis la version précédente.
$groupes = [
    'fete'    => ['fete', 'restaurant'],
    'famille' => ['famille', 'nature'],
];

if ($category !== 'all') {
    $acceptes = $groupes[$category] ?? [$category];
    $events   = array_values(array_filter(
        $events,
        function ($ev) use ($acceptes) { return in_array($ev['type'], $acceptes, true); }
    ));
}

echo json_encode([
    'reference' => [
        'commune'   => SEO_LOCALITY,
        'latitude'  => SEO_LAT,
        'longitude' => SEO_LNG,
        'rayon_km'  => $rayon,
    ],
    'count'  => count($events),
    'events' => $events,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
