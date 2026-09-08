<?php
header('Content-Type: application/json; charset=utf-8');

// Mapping des catégories
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Fallback de haute qualité (cas où le scraping échoue ou est bloqué par Cloudflare/CORS/etc.)
$fallback_events = [
    [
        "date" => "Ce Samedi matin",
        "titre" => "Marché Traditionnel Aveyronnais",
        "lieu" => "Saint-Geniez-d'Olt (à 2 km)",
        "description" => "Producteurs locaux : fromages Aubrac, aligot, tripous et charcuteries artisanales.",
        "type" => "fete"
    ],
    [
        "date" => "Tous les mercredis de l'Été",
        "titre" => "Marché Nocturne et Festif",
        "lieu" => "Sainte-Eulalie-d'Olt",
        "description" => "Ambiance conviviale avec producteurs locaux, artisanat et repas partagé sur la grande place.",
        "type" => "fete"
    ],
    [
        "date" => "Chaque Dimanche",
        "titre" => "Brocante & Antiquités",
        "lieu" => "Espalion (à 12 km)",
        "description" => "Découvrez des trésors anciens et objets de collection au bord du Lot.",
        "type" => "culture"
    ],
    [
        "date" => "Mardi & Jeudi d'été",
        "titre" => "Initiation au Paddle",
        "lieu" => "Lac de Castelnau",
        "description" => "Cours pour enfants et adultes sur les eaux calmes du lac de Castelnau. Matériel fourni.",
        "type" => "nature"
    ],
    [
        "date" => "La Nuit tombée, de Mai à Septembre",
        "titre" => "Spectacle Illuminations Inédites",
        "lieu" => "Abbatiale de Conques",
        "description" => "Création magique de lumières et chants géorgiens révélant le tympan du Jugement Dernier.",
        "type" => "culture"
    ],
    [
        "date" => "En ce moment",
        "titre" => "Transhumance & Estive",
        "lieu" => "Plateau de l'Aubrac",
        "description" => "Les vaches Aubrac montent sur les hauts plateaux fleuris. Fête traditionnelle dans les villages traversés.",
        "type" => "nature"
    ],
    [
        "date" => "Tous les week-ends",
        "titre" => "Sortie VTT en Sous-Bois",
        "lieu" => "Autour de Bellevue Aveyron",
        "description" => "Parcours familial le long du Lot ou boucles sportives dans les monts. Idéal pour s'aérer.",
        "type" => "famille"
    ],
    [
        "date" => "Tous les jours",
        "titre" => "Visite d'un Buron d'Aubrac",
        "lieu" => "Burons de l'Aubrac",
        "description" => "Dégustation du véritable Aligot traditionnel dans un ancien abri de bergers restauré.",
        "type" => "restaurant"
    ],
    [
        "date" => "De Juin à Septembre",
        "titre" => "Ateliers Créatifs : Céramique",
        "lieu" => "Sainte-Eulalie-d'Olt (Eulalie d'Art)",
        "description" => "Initiation à la poterie pour petits et grands avec les artisans locaux.",
        "type" => "famille"
    ]
];

// Fonction de filtrage
function filterEvents($events, $cat) {
    if ($cat === 'all') return $events;
    $filtered = [];
    foreach ($events as $ev) {
        // Logique de mapping complexe si besoin, ou on filtre par la catégorie stricte
        if ($ev['type'] === $cat || 
           ($cat === 'fete' && in_array($ev['type'], ['fete', 'restaurant'])) ||
           ($cat === 'famille' && in_array($ev['type'], ['famille', 'nature']))
        ) {
            $filtered[] = $ev;
        }
    }
    return $filtered;
}


// Tentative de Web Scraping avec cURL sur Tourisme Aveyron
$scraped_events = [];
try {
    // L'URL de l'agenda
    $url = 'https://www.tourisme-aveyron.com/fr/agenda';
    
    // Initialisation cURL (avec un user-agent de navigateur standard pour ne pas être bloqué)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 sec max pour ne pas bloquer le site
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $html) {
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($dom);

        // Recherche des éléments d'agenda (La classe varie, on cherche les div.node-event ou similar)
        // Ce XPath est générique est s'adapte à la structure habituelle
        $nodes = $xpath->query('//div[contains(@class, "views-row")]');
        
        $count = 0;
        foreach ($nodes as $node) {
            if ($count >= 8) break; // on limite à 8 événements
            
            $titreDOM = $xpath->query('.//h3', $node);
            if ($titreDOM->length == 0) $titreDOM = $xpath->query('.//h2', $node);
            
            $dateDOM = $xpath->query('.//span[contains(@class, "date")]', $node);
            if ($dateDOM->length == 0) $dateDOM = $xpath->query('.//div[contains(@class, "field-name-field-date")]', $node);

            $lieuDOM = $xpath->query('.//span[contains(@class, "ville")]', $node);
            if ($lieuDOM->length == 0) $lieuDOM = $xpath->query('.//div[contains(@class, "field-name-field-city")]', $node);
            
            $descDOM = $xpath->query('.//div[contains(@class, "field-type-text-with-summary")]', $node);

            if ($titreDOM->length > 0) {
                // S'il y a un titre, on considère qu'on a un événement valide
                $titre = trim($titreDOM->item(0)->textContent);
                $date = $dateDOM->length > 0 ? trim($dateDOM->item(0)->textContent) : "Prochainement";
                $lieu = $lieuDOM->length > 0 ? trim($lieuDOM->item(0)->textContent) : "Aveyron";
                $desc = $descDOM->length > 0 ? trim($descDOM->item(0)->textContent) : "Retrouvez plus d'informations sur cet événement exceptionnel sur place.";
                
                // Déduction du type basé sur les mots clés du titre/desc
                $type = 'culture';
                $t = strtolower($titre . ' ' . $desc);
                if (strpos($t, 'marché') !== false || strpos($t, 'fête') !== false || strpos($t, 'festival') !== false) {
                    $type = 'fete';
                } elseif (strpos($t, 'randonnée') !== false || strpos($t, 'nature') !== false || strpos($t, 'sport') !== false || strpos($t, 'lac') !== false) {
                    $type = 'nature';
                } elseif (strpos($t, 'enfant') !== false || strpos($t, 'famille') !== false || strpos($t, 'atelier') !== false) {
                    $type = 'famille';
                } elseif (strpos($t, 'repas') !== false || strpos($t, 'dégustation') !== false || strpos($t, 'marché de producteurs') !== false) {
                    $type = 'restaurant';
                }

                $scraped_events[] = [
                    "date" => $date,
                    "titre" => $titre,
                    "lieu" => $lieu,
                    "description" => mb_substr($desc, 0, 150) . '...', // Limite la longueur
                    "type" => $type
                ];
                $count++;
            }
        }
    }
} catch (Exception $e) {
    // Ignorer, le fallback prendra le relais
}

// === APPEL DATATOURISME — JSON-LD Compacté ===
require_once dirname(__FILE__) . '/config_datatourisme.php';
$datatourisme_events = [];
try {
    ini_set('memory_limit', '256M');
    $ch_dt = curl_init(DATATOURISME_API_URL);
    curl_setopt($ch_dt, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_dt, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch_dt, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch_dt, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch_dt, CURLOPT_USERAGENT, 'BellevueAveyron-App/1.0');
    curl_setopt($ch_dt, CURLOPT_ENCODING, ''); // Décodage gzip/deflate automatique
    $dt_json = curl_exec($ch_dt);
    $dt_code = curl_getinfo($ch_dt, CURLINFO_HTTP_CODE);
    curl_close($ch_dt);

    if ($dt_code == 200 && $dt_json) {
        $dt_data = json_decode($dt_json, true);
        unset($dt_json); // Libérer la mémoire immédiatement
        
        // Le JSON-LD compacté utilise '@graph' comme conteneur principal
        $items = isset($dt_data['@graph']) ? $dt_data['@graph'] : (is_array($dt_data) ? $dt_data : []);
        unset($dt_data); // Libérer la mémoire

        $dt_count = 0;
        foreach ($items as $item) {
            if ($dt_count >= 15) break;

            // 1. Titre — doit exister (sinon POI sans nom = pas valide)
            if (!isset($item['rdfs:label'])) continue;
            $label = $item['rdfs:label'];
            if (is_array($label)) {
                // Format [{'@value': '...', '@language': 'fr'}, ...]
                $titre = '';
                if (isset($label['@value'])) {
                    $titre = $label['@value'];
                } else {
                    foreach ((array)$label as $l) {
                        if (is_array($l) && isset($l['@language']) && $l['@language'] === 'fr') {
                            $titre = $l['@value']; break;
                        }
                    }
                    if (!$titre && is_array($label[0])) $titre = $label[0]['@value'] ?? '';
                }
            } else {
                $titre = (string)$label;
            }
            if (!$titre) continue;

            // 2. Image (hasMainRepresentation → ebucore:hasRelatedResource → ebucore:locate)
            $image = '';
            if (isset($item['hasMainRepresentation'])) {
                $rep = is_array($item['hasMainRepresentation']) ? $item['hasMainRepresentation'][0] : $item['hasMainRepresentation'];
                if (isset($rep['ebucore:hasRelatedResource'])) {
                    $res = is_array($rep['ebucore:hasRelatedResource']) ? $rep['ebucore:hasRelatedResource'][0] : $rep['ebucore:hasRelatedResource'];
                    if (isset($res['ebucore:locate'])) {
                        $image = is_array($res['ebucore:locate']) ? ($res['ebucore:locate']['@value'] ?? $res['ebucore:locate'][0] ?? '') : $res['ebucore:locate'];
                    }
                }
            }

            // 3. Lieu (isLocatedAt → schema:address → schema:addressLocality)
            $lieu = 'Aveyron';
            if (isset($item['isLocatedAt'])) {
                $loc = is_array($item['isLocatedAt']) ? $item['isLocatedAt'][0] : $item['isLocatedAt'];
                if (isset($loc['schema:address'])) {
                    $adr = is_array($loc['schema:address']) ? $loc['schema:address'][0] : $loc['schema:address'];
                    if (isset($adr['schema:addressLocality'])) {
                        $loc_val = $adr['schema:addressLocality'];
                        $lieu = is_array($loc_val) ? ($loc_val['@value'] ?? $loc_val[0] ?? 'Aveyron') : (string)$loc_val;
                    }
                }
            }

            // 4. Description (hasDescription → shortDescription ou dc:description)
            $desc = '';
            if (isset($item['hasDescription'])) {
                $hd = is_array($item['hasDescription']) ? $item['hasDescription'][0] : $item['hasDescription'];
                // Chercher shortDescription en français
                if (isset($hd['shortDescription'])) {
                    $sd = $hd['shortDescription'];
                    if (is_array($sd)) {
                        // tableau de {'@value':..., '@language':...}
                        if (isset($sd['@value'])) $desc = $sd['@value'];
                        else {
                            foreach ((array)$sd as $d) {
                                if (is_array($d) && isset($d['@language']) && $d['@language'] === 'fr') { $desc = $d['@value']; break; }
                            }
                            if (!$desc && isset($sd[0])) $desc = is_array($sd[0]) ? ($sd[0]['@value'] ?? '') : (string)$sd[0];
                        }
                    } else $desc = (string)$sd;
                } elseif (isset($hd['dc:description'])) {
                    $dc = $hd['dc:description'];
                    $desc = is_array($dc) ? ($dc['@value'] ?? $dc[0]['@value'] ?? '') : (string)$dc;
                }
            }
            if (!$desc) $desc = 'Découvrez une expérience authentique en Aveyron.';

            // 5. Date (takesPlaceAt ou isSpecialOpening)
            $date = "Prochainement";
            foreach (['takesPlaceAt', 'isSpecialOpening'] as $dateProp) {
                if (isset($item[$dateProp])) {
                    $tp = is_array($item[$dateProp]) ? $item[$dateProp][0] : $item[$dateProp];
                    $startKey = isset($tp['startDate']) ? 'startDate' : (isset($tp['schema:startDate']) ? 'schema:startDate' : null);
                    if ($startKey && isset($tp[$startKey])) {
                        try {
                            $d = new DateTime($tp[$startKey]);
                            $date = $d->format('d/m/Y');
                        } catch(Exception $e) {}
                        break;
                    }
                }
            }

            // 6. Type par mots-clés
            $type = 'culture';
            $t = strtolower($titre . ' ' . $desc);
            if (strpos($t, 'restaurant') !== false || strpos($t, 'dégustation') !== false || strpos($t, 'gastronomie') !== false || strpos($t, 'marché de producteurs') !== false) {
                $type = 'restaurant';
            } elseif (strpos($t, 'fête') !== false || strpos($t, 'marché') !== false || strpos($t, 'festival') !== false || strpos($t, 'concert') !== false) {
                $type = 'fete';
            } elseif (strpos($t, 'randonnée') !== false || strpos($t, 'balade') !== false || strpos($t, 'sentier') !== false || strpos($t, 'nature') !== false) {
                $type = 'nature';
            } elseif (strpos($t, 'famille') !== false || strpos($t, 'enfant') !== false || strpos($t, 'atelier') !== false) {
                $type = 'famille';
            }

            $datatourisme_events[] = [
                'date'        => $date,
                'titre'       => $titre,
                'lieu'        => $lieu,
                'description' => mb_substr($desc, 0, 150) . (mb_strlen($desc) > 150 ? '...' : ''),
                'image'       => $image,
                'type'        => $type,
            ];
            $dt_count++;
        }
    } else {
        // En cas d'erreur API
        $errDesc = "Erreur (Code $dt_code) : L'API Datatourisme n'a pas répondu correctement.";
        if ($dt_json && strpos($dt_json, 'inactivit') !== false) {
            $errDesc = "Datatourisme a désactivé votre flux suite à une inactivité. Reconnectez-vous à votre espace Datatourisme pour réactiver le flux.";
        }
        $datatourisme_events[] = [
            'date'        => 'Action Requise',
            'titre'       => '⚠️ Flux Datatourisme Inactif',
            'lieu'        => 'Configuration',
            'description' => $errDesc,
            'image'       => '',
            'type'        => 'culture',
        ];
    }
} catch (Exception $e) {
    // Ignorer si échec total
}

// Choix de la source principale : si le scraping a rapporté moins de 3 événements, on utilise le fallback
$base_events = (count($scraped_events) > 2) ? $scraped_events : $fallback_events;

// Enrichissement avec les données Datatourisme
$final_events = array_merge($base_events, $datatourisme_events);

// On applique le filtre sur la source finale !
$response = filterEvents($final_events, $category);

// Résultat final en JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);

