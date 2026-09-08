<?php
/**
 * config/medias.php — Registre des visuels éditoriaux de la page « Découvrir ».
 * ---------------------------------------------------------------------------
 * Objectif : un seul endroit pour piloter les images des cartes, et une
 * substitution sans toucher au HTML.
 *
 * Ordre de priorité pour chaque carte :
 *   1. images/decouvrir/<slug>.webp | .jpg | .jpeg | .png  → utilisé s'il existe ;
 *   2. sinon l'URL de repli déclarée ci-dessous.
 *
 * Pour remplacer une photo d'illustration par une vraie photo du lieu, il
 * suffit donc de déposer le fichier au bon nom dans images/decouvrir/ :
 * aucune modification de code n'est nécessaire.
 *
 * Le champ 'statut' documente la pertinence du visuel actuel :
 *   'reel'      → l'image montre bien le sujet de la carte ;
 *   'generique' → photo d'illustration, à remplacer en priorité.
 * L'inventaire complet est tenu dans docs/seo-ia/inventaire-images.md
 *
 * Enjeu SEO/IA : ces visuels étaient des background-image CSS, invisibles pour
 * Google Images comme pour les agents multimodaux. Ils sont désormais des
 * balises <img> porteuses d'un texte alternatif descriptif.
 */

const MEDIA_LOCAL_DIR  = 'images/decouvrir';
const MEDIA_CREDITS    = 'images/decouvrir/credits.json';
const MEDIA_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png'];

/** Registre : slug => alt, url de repli, statut du visuel. */
function media_registry(): array
{
    return [
    'au-moulin-d-alexandre' => [
        'alt'    => "Table de restaurant en terrasse — Au Moulin d'Alexandre, Sainte-Eulalie-d'Olt",
        'src'    => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'maison-de-severac' => [
        'alt'    => "Assiette gastronomique — Maison de Sévérac, Sévérac d'Aveyron",
        'src'    => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'les-burons-de-l-aubrac' => [
        'alt'    => "Aligot de l'Aubrac, servi dans les burons du plateau",
        'src'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Aligot.jpg/640px-Aligot.jpg',
        'statut' => 'reel',
    ],
    'restaurant-bras-laguiole' => [
        'alt'    => "Cuisine gastronomique — restaurant Bras, Laguiole",
        'src'    => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'festival-en-vallee-d-olt' => [
        'alt'    => "Le village de Sainte-Eulalie-d'Olt vu du château, cadre du Festival en Vallée d'Olt",
        'src'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/Sainte-Eulalie-d%27Olt_-_Le_village_vu_du_ch%C3%A2teau.JPG/640px-Sainte-Eulalie-d%27Olt_-_Le_village_vu_du_ch%C3%A2teau.JPG',
        'statut' => 'reel',
    ],
    'eulalie-d-art' => [
        'alt'    => "Atelier d'artiste — parcours Eulalie d'Art à Sainte-Eulalie-d'Olt",
        'src'    => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'musee-marcel-boudou-et-expos-estivales' => [
        'alt'    => "Le village de Sainte-Eulalie-d'Olt vu du château, où se tient le musée Marcel Boudou",
        'src'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/Sainte-Eulalie-d%27Olt_-_Le_village_vu_du_ch%C3%A2teau.JPG/640px-Sainte-Eulalie-d%27Olt_-_Le_village_vu_du_ch%C3%A2teau.JPG',
        'statut' => 'reel',
    ],
    'maison-de-severac-art-et-gastronomie' => [
        'alt'    => "Salle de restaurant contemporaine — Maison de Sévérac",
        'src'    => 'https://images.unsplash.com/photo-1507838153414-b4b713384a76?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'avenga-canoe-kayak-sur-le-lot' => [
        'alt'    => "Descente en canoë-kayak sur la rivière Lot avec Avenga",
        'src'    => 'https://images.unsplash.com/photo-1472745942893-4b9f730c7668?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'o-paddle-d-olt' => [
        'alt'    => "Stand-up paddle sur le Lot avec O'Paddle d'Olt",
        'src'    => 'https://images.unsplash.com/photo-1517400508447-f8dd518b86db?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'l-aubrac-grands-espaces-et-faune' => [
        'alt'    => "Le plateau de l'Aubrac, grands espaces à 25 km du gîte",
        'src'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/56/Plateau_de_l%27Aubrac.JPG/640px-Plateau_de_l%27Aubrac.JPG',
        'statut' => 'reel',
    ],
    'marche-estival-de-sainte-eulalie-d-olt' => [
        'alt'    => "Étal de producteurs — marché estival de Sainte-Eulalie-d'Olt",
        'src'    => 'https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'de-faire-et-de-savoir' => [
        'alt'    => "Atelier d'artisanat local — De Faire et de Savoir",
        'src'    => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'eulalie-d-art-ateliers-et-creations' => [
        'alt'    => "Atelier de céramique — Eulalie d'Art, Sainte-Eulalie-d'Olt",
        'src'    => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'marches-de-saint-geniez-d-olt' => [
        'alt'    => "Marché hebdomadaire de Saint-Geniez-d'Olt, à 2 km du gîte",
        'src'    => 'https://images.unsplash.com/photo-1590779033100-9f60a05a013d?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    // Carte consacrée au gîte lui-même : une vraie photo de la propriété.
    'ping-pong-trampoline-velos-et-piscine' => [
        'alt'    => "Piscine chauffée et terrasse du gîte Bellevue d'Aveyron, face à la vallée du Lot",
        'src'    => 'images/PMR/piscine_accessible.JPG',
        'statut' => 'reel',
    ],
    'jardin-des-betes' => [
        'alt'    => "Ferme pédagogique — Jardin des Bêtes, animaux de la ferme",
        'src'    => 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'o-paddle-d-olt-canoe-kayak-et-sup' => [
        'alt'    => "Canoë, kayak et paddle sur le Lot avec O'Paddle d'Olt",
        'src'    => 'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'maison-de-la-chouette-sainte-eulalie-d-o' => [
        'alt'    => "Chouette — Maison de la Chouette à Sainte-Eulalie-d'Olt",
        'src'    => 'https://images.unsplash.com/photo-1474511320723-9a56873867b5?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'air-globe-fun-e-bike' => [
        'alt'    => "Balade en vélo électrique tout-terrain avec Air Globe",
        'src'    => 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=600&auto=format&fit=crop',
        'statut' => 'generique',
    ],
    'bozouls-et-son-canyon-emblematique' => [
        'alt'    => "Le canyon de Bozouls et son méandre du Dourdou, Aveyron",
        'src'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Bozouls_canyon.jpg/640px-Bozouls_canyon.jpg',
        'statut' => 'reel',
    ],
    'les-secrets-du-tresor-de-conques' => [
        'alt'    => "Nef de l'abbatiale Sainte-Foy de Conques, qui abrite le Trésor",
        'src'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/Abbatiale_Sainte-Foy_de_Conques_%28Aveyron%2C_France%29_-_int%C3%A9rieur%2C_nef.jpg/640px-Abbatiale_Sainte-Foy_de_Conques_%28Aveyron%2C_France%29_-_int%C3%A9rieur%2C_nef.jpg',
        'statut' => 'reel',
    ],
    'balades-au-bord-du-lot-et-village' => [
        'alt'      => "Le village médiéval de Sainte-Eulalie-d'Olt et les bords du Lot",
        'src'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/Sainte-Eulalie-d%27Olt_-_Le_village_vu_du_ch%C3%A2teau.JPG/640px-Sainte-Eulalie-d%27Olt_-_Le_village_vu_du_ch%C3%A2teau.JPG',
        'position' => 'center 30%',
        'statut'   => 'reel',
    ],

    // ── Cartes thématiques en tête de page (« Choisissez votre Aveyron ») ──
    'theme-nature-activites' => [
        'alt'      => "Le plateau de l'Aubrac, terrain de randonnée à 25 km du gîte",
        'src'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/56/Plateau_de_l%27Aubrac.JPG/1280px-Plateau_de_l%27Aubrac.JPG',
        'position' => 'center 40%',
        'statut'   => 'reel',
    ],
    'theme-agenda-evenements' => [
        'alt'      => "Sainte-Eulalie-d'Olt vu du château, village des marchés et fêtes de l'été",
        'src'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/Sainte-Eulalie-d%27Olt_-_Le_village_vu_du_ch%C3%A2teau.JPG/1280px-Sainte-Eulalie-d%27Olt_-_Le_village_vu_du_ch%C3%A2teau.JPG',
        'position' => 'center 60%',
        'statut'   => 'reel',
    ],
    'theme-conques-patrimoine' => [
        'alt'      => "Le village de Conques et son abbatiale Sainte-Foy, à 55 km du gîte",
        'src'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Village_de_Conques_%28Aveyron%29.JPG/1280px-Village_de_Conques_%28Aveyron%29.JPG',
        'statut'   => 'reel',
    ],
    'theme-tables-gastronomie' => [
        'alt'      => "Aligot de l'Aubrac, spécialité des tables aveyronnaises",
        'src'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a9/Aligot.jpg/1280px-Aligot.jpg',
        'statut'   => 'reel',
    ],
    ];
}

/** Chemin d'une photo locale déposée pour ce slug, ou null. */
function media_local_path(string $slug): ?string
{
    static $cache = [];
    if (array_key_exists($slug, $cache)) return $cache[$slug];

    $cache[$slug] = null;
    foreach (MEDIA_EXTENSIONS as $ext) {
        $rel = MEDIA_LOCAL_DIR . '/' . $slug . '.' . $ext;
        if (is_file(__DIR__ . '/../' . $rel)) { $cache[$slug] = $rel; break; }
    }
    return $cache[$slug];
}

/** URL + texte alternatif retenus pour un slug. */
function media_resolve(string $slug): ?array
{
    $registry = media_registry();
    if (!isset($registry[$slug])) return null;

    $entry = $registry[$slug];
    $local = media_local_path($slug);

    return [
        'src'      => $local ?? $entry['src'],
        'alt'      => $entry['alt'],
        'position' => $entry['position'] ?? null,
        'local'    => $local !== null,
    ];
}

/**
 * Rend l'image d'une carte. Sans entrée au registre, rien n'est cassé :
 * un bloc vide conserve la mise en page.
 */
function media_card(string $slug, string $class = 'coup-img'): void
{
    $media = media_resolve($slug);
    if ($media === null) {
        echo '<div class="' . htmlspecialchars($class, ENT_QUOTES) . '"></div>';
        return;
    }

    $style = $media['position']
        ? 'object-position:' . htmlspecialchars($media['position'], ENT_QUOTES) . ';'
        : '';

    // Si l'image distante ne répond pas, on masque la balise : le fond doré du
    // conteneur prend le relais, plutôt qu'une icône d'image cassée.
    printf(
        '<img class="%s" src="%s" alt="%s" style="%s" loading="lazy" decoding="async"'
        . ' width="600" height="380" onerror="this.style.display=&quot;none&quot;">',
        htmlspecialchars($class, ENT_QUOTES),
        htmlspecialchars($media['src'], ENT_QUOTES),
        htmlspecialchars($media['alt'], ENT_QUOTES),
        $style
    );
}

/**
 * Crédits photo déposés par tools/unsplash_photos.py.
 *
 * L'API Unsplash impose de créditer le photographe et Unsplash, avec des liens
 * porteurs des paramètres UTM. Le fichier credits.json est produit et tenu à
 * jour par le script ; sans lui, rien n'est affiché.
 */
function media_credits(): array
{
    $chemin = __DIR__ . '/../' . MEDIA_CREDITS;
    if (!is_file($chemin)) return [];

    $donnees = json_decode((string) file_get_contents($chemin), true);
    return is_array($donnees) ? $donnees : [];
}

/** Ligne de crédits photo, à placer en pied de page. */
function media_credits_html(): void
{
    $credits = media_credits();
    if (!$credits) return;

    // Un photographe peut signer plusieurs visuels : on ne le cite qu'une fois.
    $auteurs = [];
    foreach ($credits as $c) {
        if (!empty($c['auteur'])) $auteurs[$c['auteur']] = $c['auteur_url'] ?? 'https://unsplash.com';
    }
    if (!$auteurs) return;

    ksort($auteurs);
    $liens = [];
    foreach ($auteurs as $nom => $url) {
        $liens[] = '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" rel="noopener nofollow" target="_blank">'
                 . htmlspecialchars($nom, ENT_QUOTES) . '</a>';
    }

    echo '<p class="credits-photos">Crédits photos d\'illustration : '
       . implode(', ', $liens)
       . ' — <a href="https://unsplash.com/?utm_source=bellevue_daveyron&amp;utm_medium=referral"'
       . ' rel="noopener nofollow" target="_blank">Unsplash</a>.</p>';
}
