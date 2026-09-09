<?php
/**
 * config/seo.php — Socle SEO & GEO (Generative Engine Optimization)
 * ---------------------------------------------------------------------------
 * Source unique de vérité pour :
 *   • l'identité du site (NAP : Name / Address / Phone) ;
 *   • les balises <head> (canonical, robots, Open Graph, Twitter, geo) ;
 *   • les données structurées JSON-LD lues par Google, ChatGPT, Perplexity,
 *     Claude, Gemini et les moteurs de réponse.
 *
 * Principe GEO : un moteur génératif ne « classe » pas des pages, il extrait
 * des FAITS et cite la source qui les exprime le plus clairement. Tout fait
 * exposé ici doit donc être (1) vrai, (2) visible sur la page, (3) balisé.
 *
 * Réutilisation (artifacile.fr) : seules les constantes du bloc 1 et les
 * tableaux de faits du bloc 3 sont spécifiques au site. Les fonctions des
 * blocs 2 et 4 sont génériques et se transposent telles quelles.
 * Voir docs/seo-ia/playbook-geo.md
 */

// ═══════════════════════════════════════════════════════════════════════════
// 1. IDENTITÉ DU SITE — à adapter pour chaque projet
// ═══════════════════════════════════════════════════════════════════════════

const SEO_SITE_URL     = 'https://bellevuedaveyron.fr'; // domaine canonique, sans slash final
const SEO_SITE_NAME    = "Bellevue d'Aveyron";
const SEO_LEGAL_NAME   = "Bellevue d'Aveyron — Véronique & Daniel Lacan";
const SEO_LOCALE       = 'fr_FR';
const SEO_LANG         = 'fr';
const SEO_PHONE        = '+33680907107';
const SEO_PHONE_HUMAN  = '06 80 90 71 07';
const SEO_EMAIL        = 'accueil@bellevuedaveyron.com';
const SEO_SIRET        = '414 548 776 00023';
const SEO_LOCALITY     = "Sainte-Eulalie-d'Olt";
const SEO_POSTAL       = '12130';
const SEO_REGION       = 'Occitanie';
const SEO_COUNTRY      = 'FR';
// Position exacte de la villa, communiquée par les propriétaires :
// N 44° 27' 27" / E 2° 57' 29". L'ancienne valeur (44.4844 / 2.8531) tombait
// une douzaine de kilomètres à l'ouest, du côté de Laissac : c'est elle qui
// faisait remonter des événements de Laissac ou de Réquista dans l'onglet
// « autour du gîte ». Ces deux nombres pilotent le balisage géographique, les
// widgets touristiques et le tri de l'agenda : ne les modifier qu'ici.
const SEO_LAT          = 44.4575;
const SEO_LNG          = 2.9581;
// Repli si config/avis.php ne renvoie rien ; la valeur réelle vient de Google.
const SEO_RATING_VALUE = 5.0;
const SEO_RATING_COUNT = 102;
const SEO_DEFAULT_IMG  = 'images/accueil.jpg';

/** Profils officiels — consolident l'entité pour les moteurs et les LLM. */
function seo_same_as(): array {
    return [
        'https://www.instagram.com/gitebellevuedaveyron/',
        'https://www.facebook.com/gitebellevuedaveyron',
        'https://g.page/r/CVWZLGkfDaptEAE',
    ];
}

// ═══════════════════════════════════════════════════════════════════════════
// 2. HELPERS GÉNÉRIQUES
// ═══════════════════════════════════════════════════════════════════════════

/** URL absolue à partir d'un chemin relatif ('' => page d'accueil). */
function seo_url(string $path = ''): string {
    return SEO_SITE_URL . '/' . ltrim($path, '/');
}

/**
 * URL d'une ressource statique, suffixée par sa date de modification.
 *
 * Sans ce marqueur, les navigateurs — et le cache de l'hébergeur — continuent
 * de servir l'ancienne feuille de style après une mise en ligne : le HTML est
 * à jour, la mise en page non. Le suffixe change dès que le fichier change,
 * ce qui force le rechargement sans jamais casser la mise en cache habituelle.
 */
function seo_asset(string $chemin): string
{
    $absolu = __DIR__ . '/../' . ltrim($chemin, '/');
    $version = is_file($absolu) ? (int) filemtime($absolu) : time();

    return $chemin . '?v=' . $version;
}

/** Échappement HTML court. */
function seo_e(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Rend l'intégralité des balises <head> orientées référencement.
 *
 * @param array{
 *   title:string, description:string, path?:string, image?:string,
 *   type?:string, noindex?:bool, modified?:string
 * } $page
 */
function seo_head(array $page): void {
    $title  = $page['title'];
    $desc   = $page['description'];
    $canon  = seo_url($page['path'] ?? '');
    $image  = seo_url($page['image'] ?? SEO_DEFAULT_IMG);
    $type   = $page['type'] ?? 'website';
    $robots = !empty($page['noindex'])
        ? 'noindex, nofollow'
        // max-snippet:-1 autorise les extraits longs : indispensable pour être
        // repris dans un AI Overview ou une réponse Perplexity.
        : 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';

    $out  = '    <title>' . seo_e($title) . "</title>\n";
    $out .= '    <meta name="description" content="' . seo_e($desc) . "\">\n";
    $out .= '    <meta name="robots" content="' . $robots . "\">\n";
    $out .= '    <link rel="canonical" href="' . seo_e($canon) . "\">\n";
    $out .= '    <meta name="author" content="' . seo_e(SEO_LEGAL_NAME) . "\">\n";
    $out .= '    <meta name="publisher" content="' . seo_e(SEO_SITE_NAME) . "\">\n";

    // Open Graph — utilisé par les réseaux sociaux ET par plusieurs crawlers IA
    // comme description de repli quand la page est trop lourde à parser.
    $out .= '    <meta property="og:type" content="' . seo_e($type) . "\">\n";
    $out .= '    <meta property="og:site_name" content="' . seo_e(SEO_SITE_NAME) . "\">\n";
    $out .= '    <meta property="og:locale" content="' . SEO_LOCALE . "\">\n";
    $out .= '    <meta property="og:title" content="' . seo_e($title) . "\">\n";
    $out .= '    <meta property="og:description" content="' . seo_e($desc) . "\">\n";
    $out .= '    <meta property="og:url" content="' . seo_e($canon) . "\">\n";
    $out .= '    <meta property="og:image" content="' . seo_e($image) . "\">\n";
    $out .= '    <meta property="og:image:alt" content="' . seo_e($title) . "\">\n";
    $out .= '    <meta name="twitter:card" content="summary_large_image">' . "\n";
    $out .= '    <meta name="twitter:title" content="' . seo_e($title) . "\">\n";
    $out .= '    <meta name="twitter:description" content="' . seo_e($desc) . "\">\n";
    $out .= '    <meta name="twitter:image" content="' . seo_e($image) . "\">\n";

    // Ancrage géographique explicite : les moteurs de réponse s'en servent pour
    // les requêtes « près de », « dans l'Aveyron », « autour de Millau »…
    $out .= '    <meta name="geo.region" content="FR-12">' . "\n";
    $out .= '    <meta name="geo.placename" content="' . seo_e(SEO_LOCALITY) . "\">\n";
    $out .= '    <meta name="geo.position" content="' . SEO_LAT . ';' . SEO_LNG . "\">\n";
    $out .= '    <meta name="ICBM" content="' . SEO_LAT . ', ' . SEO_LNG . "\">\n";

    if (!empty($page['modified'])) {
        $out .= '    <meta property="article:modified_time" content="' . seo_e($page['modified']) . "\">\n";
    }

    // Résumé texte destiné aux agents conversationnels (convention llms.txt).
    $out .= '    <link rel="alternate" type="text/plain" title="llms.txt" href="' . seo_url('llms.txt') . "\">\n";
    $out .= '    <link rel="sitemap" type="application/xml" href="' . seo_url('sitemap.xml') . "\">\n";

    echo $out;
}

/** Sérialise un graphe JSON-LD dans un <script> unique. */
function seo_jsonld(array $nodes): void {
    $graph = ['@context' => 'https://schema.org', '@graph' => array_values($nodes)];
    $json  = json_encode(
        $graph,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
    // Sécurité : neutralise une éventuelle fermeture de balise dans les données.
    $json = str_replace('</', '<\/', $json);
    echo "<script type=\"application/ld+json\">\n" . $json . "\n</script>\n";
}

// ═══════════════════════════════════════════════════════════════════════════
// 3. FAITS DU SITE — chaque entrée est aussi affichée en clair sur la page
// ═══════════════════════════════════════════════════════════════════════════

/** Équipements (LocationFeatureSpecification). */
function seo_amenities(): array {
    return [
        ['Piscine privée chauffée 4x8 m (volet roulant sécurisé, chauffée à partir de mai)', true],
        ['Vue panoramique 360° sur la vallée du Lot et les monts d\'Aubrac', true],
        ['Borne de recharge électrique 18 kVA toutes marques', true],
        ['Fibre optique 1 Gbit/s et Wi-Fi dans toute la villa', true],
        ['Parc privé clos de 5 000 m²', true],
        ['Accès PMR : rez-de-chaussée de plain-pied, chambre et salle de bain adaptées', true],
        ['Baby-foot Bonzini professionnel', true],
        ['6 vélos adultes et 5 vélos enfants à disposition', true],
        ['Terrasses multiples orientées plein sud', true],
        ['Parking privé sécurisé', true],
    ];
}

/**
 * Chiffres clés du gîte, affichés en bandeau sous le bandeau d'accueil.
 * Format : [valeur, libellé, précision]. Une information par colonne : c'est
 * ce qu'un visiteur cherche en premier, et ce qu'un moteur de réponse retient.
 */
function seo_chiffres_cles(): array
{
    // « nombre » est la part que le compteur fait défiler à l'arrivée dans
    // l'écran ; « suffixe » la suit sans bouger. Sans JavaScript, la valeur
    // complète s'affiche telle quelle : l'animation est un agrément, jamais
    // une condition de lecture.
    return [
        // L'espace qui ouvre un suffixe est insécable : à l'intérieur d'un bloc
        // en ligne, une espace ordinaire en tête serait supprimée à l'affichage
        // et donnerait « 200m² ».
        ['nombre' => 5,   'suffixe' => '★',            'libelle' => 'Meublé de tourisme', 'precision' => 'Classement 5 étoiles'],
        ['nombre' => 10,  'suffixe' => '',             'libelle' => 'Personnes',          'precision' => '5 chambres'],
        ['nombre' => 200, 'suffixe' => "\u{00A0}m²",    'libelle' => 'Habitables',         'precision' => 'Parc privé de 5 000 m²'],
        ['nombre' => 4,   'suffixe' => "\u{00A0}× 8 m", 'libelle' => 'Piscine chauffée',   'precision' => 'Volet sécurisé, dès mai'],
        ['nombre' => 0,   'suffixe' => '',             'libelle' => 'Marche pour entrer', 'precision' => 'Accès PMR de plain-pied'],
        ['nombre' => 1,   'suffixe' => "\u{00A0}Gb/s",  'libelle' => 'Fibre optique',      'precision' => 'Borne de recharge 18 kVA'],
    ];
}

/**
 * Adresse de retour vers le site, depuis une page annexe (mentions légales,
 * politique de confidentialité).
 * ---------------------------------------------------------------------------
 * Le visiteur qui ouvre les mentions légales depuis le bas de la page d'accueil
 * s'attend à revenir là où il était, et non tout en haut. La page d'origine et
 * la section sont donc passées en paramètres, et relues ici.
 *
 * Les deux valeurs viennent de l'adresse : elles sont vérifiées contre une
 * liste fermée de pages et un format d'ancre strict. Une valeur inattendue est
 * ignorée au profit du défaut — une page annexe ne renverra jamais ailleurs
 * que sur ce site.
 */
function seo_retour_site(string $defaut = 'index.php'): string
{
    $pages = ['index.php', 'decouvrir.php'];

    $page = (string) ($_GET['retour'] ?? '');
    if (!in_array($page, $pages, true)) return $defaut;

    $ancre = (string) ($_GET['section'] ?? '');
    if ($ancre !== '' && preg_match('~^[A-Za-z][A-Za-z0-9_-]{0,39}$~', $ancre)) {
        return $page . '#' . $ancre;
    }

    return $page;
}

/** Valeur complète d'un chiffre clé : « 200 m² », « 4 × 8 m »… */
function seo_chiffre_texte(array $chiffre): string
{
    return number_format((int) $chiffre['nombre'], 0, ',', ' ') . $chiffre['suffixe'];
}

/**
 * Questions fréquentes — affichées dans la section #faq d'index.php et
 * balisées en FAQPage. Format « une question = une réponse autoportante »,
 * qui est l'unité de citation des moteurs génératifs.
 */
function seo_faq(): array {
    return [
        [
            'Combien de personnes peut accueillir le gîte Bellevue d\'Aveyron ?',
            'La villa accueille jusqu\'à 10 personnes dans 5 chambres, sur 200 m² habitables, avec un parc privé de 5 000 m². Elle convient aux séjours en famille comme aux réunions entre amis.',
        ],
        [
            'Où se situe exactement Bellevue d\'Aveyron ?',
            'La villa se trouve à Sainte-Eulalie-d\'Olt (12130), dans l\'Aveyron en Occitanie, village classé parmi les Plus Beaux Villages de France, sur les hauteurs de la vallée du Lot. Saint-Geniez-d\'Olt est à 3 km, le plateau de l\'Aubrac à 25 km, Rodez et son musée Soulages à 50 km.',
        ],
        [
            'La piscine est-elle chauffée et sécurisée ?',
            'Oui. Le bassin privé de 4x8 mètres est chauffé à partir du mois de mai et sécurisé par un volet roulant homologué.',
        ],
        [
            'Quelle est la durée minimale de séjour ?',
            'Le séjour minimum est de 3 nuits, sur certaines périodes de l\'année uniquement. La location se fait principalement à la semaine, du samedi au samedi, selon le calendrier de disponibilités.',
        ],
        [
            'Quels sont les tarifs de location à la semaine ?',
            'Les tarifs varient selon la saison et sont affichés en temps réel dans la section Tarifs du site. Le ménage de fin de séjour est en option à 220 €, et un acompte de 30 % est demandé à la validation de la réservation.',
        ],
        [
            'Le gîte est-il accessible aux personnes à mobilité réduite ?',
            'Oui. Tout le rez-de-chaussée est accessible de plain-pied, sans une seule marche : du parking aux espaces de vie, avec une chambre et une salle de bain adaptées. Une piste aménagée relie le parking à la maison. La villa compte un étage, desservi par un escalier, mais un séjour complet se vit au rez-de-chaussée sans jamais l\'emprunter.',
        ],
        [
            'Peut-on recharger un véhicule électrique sur place ?',
            'Oui, une borne de recharge rapide de 18 kVA compatible toutes marques (Tesla, Renault et autres) est incluse dans la location, sur le parking privé de la propriété.',
        ],
        [
            'Que visiter en Aveyron depuis le gîte ?',
            'Les grands sites de l\'Aveyron sont tous accessibles à la journée : Conques et son abbatiale romane classée à l\'UNESCO à 70 km, le musée Soulages de Rodez à 50 km, le viaduc de Millau à 75 km, le plateau de l\'Aubrac à 25 km et le canyon de Bozouls à 35 km. Trois villages classés Plus Beaux Villages de France se suivent dans la vallée du Lot, à commencer par Sainte-Eulalie-d\'Olt où se trouve la villa.',
        ],
        [
            'Qu\'est-ce qui fait de Bellevue d\'Aveyron un gîte de luxe 5 étoiles ?',
            'Le classement 5 étoiles en meublé de tourisme récompense la surface, l\'équipement et le niveau de finition. Ici : 200 m² habitables, 5 chambres, piscine privée chauffée, parc clos de 5 000 m², fibre optique 1 Gbit/s, borne de recharge pour véhicule électrique, rez-de-chaussée de plain-pied accessible PMR, et une vue panoramique sur la vallée du Lot. La maison est louée en exclusivité, sans voisinage ni partage d\'équipement.',
        ],
        [
            'Peut-on louer ce gîte avec piscine pour un groupe de 10 personnes ?',
            'Oui, la villa se loue entière pour un seul groupe, jusqu\'à 10 personnes réparties dans 5 chambres. Le format convient aux réunions de famille, aux anniversaires et aux séjours entre amis. La piscine, les terrasses et le parc sont privatifs pendant toute la durée du séjour.',
        ],
        [
            'Comment réserver un séjour à Bellevue d\'Aveyron ?',
            'La réservation se fait directement auprès des propriétaires : sélection des dates dans le calendrier en ligne du site, puis envoi de la demande. Réponse par e-mail ou par téléphone au ' . SEO_PHONE_HUMAN . ', sans intermédiaire ni commission de plateforme.',
        ],
    ];
}

// ═══════════════════════════════════════════════════════════════════════════
// 4. NŒUDS JSON-LD
// ═══════════════════════════════════════════════════════════════════════════

/** Le site lui-même (WebSite) — rattache toutes les pages à une entité. */
function seo_node_website(): array {
    return [
        '@type'      => 'WebSite',
        '@id'        => seo_url('#website'),
        'url'        => seo_url(),
        'name'       => SEO_SITE_NAME,
        'inLanguage' => 'fr-FR',
        'publisher'  => ['@id' => seo_url('#gite')],
    ];
}

/** La page courante (WebPage). */
function seo_node_webpage(string $path, string $name, string $description): array {
    return [
        '@type'       => 'WebPage',
        '@id'         => seo_url($path) . '#webpage',
        'url'         => seo_url($path),
        'name'        => $name,
        'description' => $description,
        'inLanguage'  => 'fr-FR',
        'isPartOf'    => ['@id' => seo_url('#website')],
        'about'       => ['@id' => seo_url('#gite')],
        'dateModified'=> date('c'),
    ];
}

/** Fil d'Ariane : $items = [['Accueil','/'], ['Découvrir','decouvrir.php']]. */
function seo_node_breadcrumb(array $items): array {
    $list = [];
    foreach ($items as $i => [$name, $path]) {
        $list[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $name,
            'item'     => seo_url($path),
        ];
    }
    return [
        '@type'           => 'BreadcrumbList',
        '@id'             => seo_url(end($items)[1]) . '#breadcrumb',
        'itemListElement' => $list,
    ];
}

/** FAQPage à partir de seo_faq(). */
function seo_node_faq(array $qa): array {
    $entities = [];
    foreach ($qa as [$question, $answer]) {
        $entities[] = [
            '@type'          => 'Question',
            'name'           => $question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer],
        ];
    }
    return [
        '@type'      => 'FAQPage',
        '@id'        => seo_url('#faq'),
        'inLanguage' => 'fr-FR',
        'mainEntity' => $entities,
    ];
}

/**
 * L'hébergement lui-même : nœud central cité par les moteurs de réponse.
 *
 * @param array      $tarifs Lignes de tarifs_saison (nom_saison, date_debut,
 *                           date_fin, prix_semaine) pour générer les offres.
 * @param array|null $avis   Données de config/avis.php. La note balisée est
 *                           alors exactement celle affichée sur la page —
 *                           condition de validité du balisage.
 */
function seo_node_lodging(array $tarifs = [], ?array $avis = null): array {
    // Note et nombre d'avis : exactement ceux affichés sur la page.
    $note   = (float) ($avis['note'] ?? SEO_RATING_VALUE);
    $nbAvis = (int) ($avis['total'] ?? SEO_RATING_COUNT);

    $amenities = [];
    foreach (seo_amenities() as [$label, $value]) {
        $amenities[] = [
            '@type' => 'LocationFeatureSpecification',
            'name'  => $label,
            'value' => $value,
        ];
    }

    $offers = [];
    foreach ($tarifs as $t) {
        if (!isset($t['prix_semaine'])) continue;
        // La saison figure dans le nom de l'offre : c'est ce qui permet à un
        // moteur de réponse de dire « en haute saison, la semaine est à … ».
        $saison = '';
        if (function_exists('tarifs_categorie') && function_exists('tarifs_saisons')) {
            $tous   = array_map(function ($x) { return (float) ($x['prix_semaine'] ?? 0); }, $tarifs);
            $cat    = tarifs_categorie($t, min($tous), max($tous));
            $saison = tarifs_saisons()[$cat]['nom'] ?? '';
        }

        $offers[] = array_filter([
            '@type'            => 'Offer',
            'name'             => 'Location à la semaine — ' . ($t['nom_saison'] ?? 'Saison')
                                . ($saison !== '' ? ' (' . $saison . ')' : ''),
            'availability'     => 'https://schema.org/InStock',
            'priceCurrency'    => 'EUR',
            'price'            => (string) (int) $t['prix_semaine'],
            'validFrom'        => $t['date_debut'] ?? null,
            'validThrough'     => $t['date_fin'] ?? null,
            'url'              => seo_url('#tarifs'),
            'priceSpecification' => [
                '@type'         => 'UnitPriceSpecification',
                'price'         => (string) (int) $t['prix_semaine'],
                'priceCurrency' => 'EUR',
                'unitCode'      => 'WEE', // semaine (UN/CEFACT)
                'referenceQuantity' => [
                    '@type' => 'QuantitativeValue',
                    'value' => 1,
                    'unitCode' => 'WEE',
                ],
            ],
        ], function ($v) { return $v !== null; });
    }

    return array_filter([
        '@type' => ['VacationRental', 'LodgingBusiness'],
        '@id'   => seo_url('#gite'),
        'name'  => SEO_SITE_NAME,
        'alternateName' => "Gîte Bellevue d'Aveyron",
        'legalName'     => SEO_LEGAL_NAME,
        'url'           => seo_url(),
        'description'   => "Villa de luxe 5 étoiles avec piscine chauffée et vue panoramique sur la vallée du Lot, à Sainte-Eulalie-d'Olt en Aveyron. 200 m², 5 chambres, jusqu'à 10 personnes, parc privé de 5 000 m², rez-de-chaussée de plain-pied accessible PMR. Location à la semaine en direct auprès des propriétaires.",
        'image'         => [seo_url(SEO_DEFAULT_IMG)],
        'telephone'     => SEO_PHONE,
        'email'         => SEO_EMAIL,
        'currenciesAccepted' => 'EUR',
        'address' => [
            '@type'           => 'PostalAddress',
            'addressLocality' => SEO_LOCALITY,
            'postalCode'      => SEO_POSTAL,
            'addressRegion'   => SEO_REGION,
            'addressCountry'  => SEO_COUNTRY,
        ],
        'geo' => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => SEO_LAT,
            'longitude' => SEO_LNG,
        ],
        'hasMap' => 'https://www.google.com/maps/search/?api=1&query=' . SEO_LAT . ',' . SEO_LNG,
        'starRating' => ['@type' => 'Rating', 'ratingValue' => 5, 'bestRating' => 5],
        'aggregateRating' => ($note > 0 && $nbAvis > 0) ? [
            '@type'       => 'AggregateRating',
            'ratingValue' => $note,
            'reviewCount' => $nbAvis,
            'bestRating'  => 5,
            'worstRating' => 1,
        ] : null,
        'numberOfRooms'    => 5,
        'numberOfBedrooms' => 5,
        'occupancy' => [
            '@type'    => 'QuantitativeValue',
            'maxValue' => 10,
            'unitText' => 'personnes',
        ],
        'floorSize' => [
            '@type'    => 'QuantitativeValue',
            'value'    => 200,
            'unitCode' => 'MTK', // mètre carré
        ],
        'amenityFeature' => $amenities,
        'makesOffer'     => $offers ?: null,
        'additionalProperty' => [
            ['@type' => 'PropertyValue', 'name' => 'Durée minimale de séjour', 'value' => '3 nuits'],
            ['@type' => 'PropertyValue', 'name' => 'Frais de ménage optionnels', 'value' => '220 EUR'],
            ['@type' => 'PropertyValue', 'name' => 'Acompte à la réservation', 'value' => '30 %'],
            ['@type' => 'PropertyValue', 'name' => 'Surface du parc', 'value' => '5000 m²'],
            ['@type' => 'PropertyValue', 'name' => 'SIRET', 'value' => SEO_SIRET],
        ],
        'sameAs'    => seo_same_as(),
        'isPartOf'  => ['@id' => seo_url('#website')],
        'areaServed'=> [
            '@type' => 'AdministrativeArea',
            'name'  => 'Aveyron, Occitanie, France',
        ],
    ], function ($v) { return $v !== null; });
}

/** Lieux touristiques cités sur la page « Découvrir » (ItemList). */
function seo_node_itemlist(string $id, string $name, array $places): array {
    $items = [];
    foreach ($places as $i => [$label, $desc]) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'item'     => [
                '@type'       => 'TouristAttraction',
                'name'        => $label,
                'description' => $desc,
            ],
        ];
    }
    return [
        '@type'           => 'ItemList',
        '@id'             => seo_url($id),
        'name'            => $name,
        'itemListElement' => $items,
    ];
}
