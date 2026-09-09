<?php
/**
 * config/tarifs.php — Règles de saison et présentation des tarifs.
 * ---------------------------------------------------------------------------
 * L'affichage précédent alignait les périodes dans l'ordre des prix croissants,
 * sans les nommer autrement que par le libellé saisi, et mettait en évidence
 * les lignes au-dessus de 2 000 € par un simple seuil codé en dur. Un visiteur
 * ne pouvait ni situer une date dans une saison, ni comparer deux périodes.
 *
 * Trois saisons sont désormais distinguées explicitement : haute, moyenne et
 * basse. La catégorie est une donnée de la table `tarifs_saison`, choisie dans
 * l'administration. Si elle n'est pas renseignée — anciennes lignes, import —
 * elle est déduite du prix, du plus élevé au plus bas, de façon à ce que la
 * page reste juste sans intervention.
 *
 * Toutes les valeurs affichées côté public sortent d'ici : prix à la semaine,
 * équivalent par nuit, période lisible. Aucun calcul n'est refait dans le HTML.
 */

/** Les trois saisons, de la plus chère à la plus douce. */
function tarifs_saisons(): array
{
    return [
        'haute' => [
            'nom'      => 'Haute saison',
            'resume'   => "Juillet, août et les grandes vacances scolaires",
            'conseil'  => "Les semaines partent souvent plusieurs mois à l'avance.",
        ],
        'moyenne' => [
            'nom'      => 'Moyenne saison',
            'resume'   => "Printemps, début d'automne et vacances scolaires",
            'conseil'  => "La piscine est chauffée dès le mois de mai.",
        ],
        'basse' => [
            'nom'      => 'Basse saison',
            'resume'   => "Le reste de l'année",
            'conseil'  => "La période la plus calme, idéale pour un séjour au coin du feu.",
        ],
    ];
}

/** Ajoute la colonne `categorie` si elle manque. Idempotent. */
function tarifs_migrer(?PDO $pdo): bool
{
    if (!$pdo) return false;

    try {
        $colonne = $pdo->query("SHOW COLUMNS FROM tarifs_saison LIKE 'categorie'")->fetch();
        if (!$colonne) {
            $pdo->exec("ALTER TABLE tarifs_saison ADD COLUMN categorie VARCHAR(10) DEFAULT NULL");
        }
        return true;
    } catch (PDOException $e) {
        error_log('[bellevue] tarifs : migration impossible — ' . $e->getMessage());
        return false;
    }
}

/**
 * Catégorie d'une ligne de tarif.
 *
 * Règle de repli quand la catégorie n'est pas renseignée : on situe le prix
 * dans l'écart entre le tarif le plus bas et le plus haut de la grille.
 * Au-dessus des deux tiers, haute saison ; en dessous du tiers, basse saison ;
 * entre les deux, moyenne saison. Une grille à un seul tarif est classée en
 * moyenne saison, faute de point de comparaison.
 */
function tarifs_categorie(array $ligne, float $min, float $max): string
{
    $declaree = strtolower(trim((string) ($ligne['categorie'] ?? '')));
    if (isset(tarifs_saisons()[$declaree])) return $declaree;

    $prix = (float) ($ligne['prix_semaine'] ?? 0);
    if ($max <= $min) return 'moyenne';

    $position = ($prix - $min) / ($max - $min);
    if ($position >= 0.66) return 'haute';
    if ($position <= 0.33) return 'basse';

    return 'moyenne';
}

/** Période lisible : « du 4 juillet au 29 août 2026 ». */
function tarifs_periode(?string $debut, ?string $fin): string
{
    if (!$debut || !$fin) return '';

    $d = date_create($debut);
    $f = date_create($fin);
    if (!$d || !$f) return '';

    $mois = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
             'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    $moisD = $mois[(int) $d->format('n')];
    $moisF = $mois[(int) $f->format('n')];

    // « 1er » et non « 1 » : le quantième s'ordonne en français.
    $quantieme = function (DateTimeInterface $date) {
        $jour = (int) $date->format('j');
        return $jour === 1 ? '1er' : (string) $jour;
    };

    $jourD = $quantieme($d);
    $jourF = $quantieme($f);

    // Même mois : « du 4 au 29 août 2026 ».
    if ($d->format('Y-n') === $f->format('Y-n')) {
        return 'Du ' . $jourD . ' au ' . $jourF . ' ' . $moisF . ' ' . $f->format('Y');
    }

    // Même année : l'année n'apparaît qu'une fois.
    if ($d->format('Y') === $f->format('Y')) {
        return 'Du ' . $jourD . ' ' . $moisD . ' au ' . $jourF . ' ' . $moisF . ' ' . $f->format('Y');
    }

    return 'Du ' . $jourD . ' ' . $moisD . ' ' . $d->format('Y')
         . ' au ' . $jourF . ' ' . $moisF . ' ' . $f->format('Y');
}

/**
 * Grille prête à afficher, regroupée par saison.
 *
 * @return array<string, array{nom:string, resume:string, conseil:string, lignes:array}>
 *         Seules les saisons effectivement présentes sont renvoyées.
 */
function tarifs_grille(array $lignes): array
{
    if (!$lignes) return [];

    $prix = array_map(function ($l) { return (float) ($l['prix_semaine'] ?? 0); }, $lignes);
    $min  = min($prix);
    $max  = max($prix);

    $grille = [];
    foreach (tarifs_saisons() as $cle => $saison) {
        $grille[$cle] = $saison + ['lignes' => []];
    }

    foreach ($lignes as $l) {
        $cle = tarifs_categorie($l, $min, $max);

        $semaine = (float) ($l['prix_semaine'] ?? 0);
        $grille[$cle]['lignes'][] = [
            'nom'       => (string) ($l['nom_saison'] ?? ''),
            'periode'   => tarifs_periode($l['date_debut'] ?? null, $l['date_fin'] ?? null),
            'debut'     => $l['date_debut'] ?? null,
            'fin'       => $l['date_fin'] ?? null,
            'semaine'   => $semaine,
        ];
    }

    // Chaque saison est présentée dans l'ordre du calendrier.
    foreach ($grille as $cle => $saison) {
        if (!$saison['lignes']) {
            unset($grille[$cle]);
            continue;
        }
        usort($grille[$cle]['lignes'], function ($a, $b) {
            return strcmp((string) $a['debut'], (string) $b['debut']);
        });
    }

    return $grille;
}

/** Prix hebdomadaire le plus bas de la grille, pour l'accroche « à partir de ». */
function tarifs_a_partir_de(array $lignes): int
{
    $prix = array_filter(array_map(function ($l) { return (float) ($l['prix_semaine'] ?? 0); }, $lignes));

    return $prix ? (int) round(min($prix)) : 0;
}

/**
 * Conditions de location, affichées sous la grille.
 * Elles répondent aux questions posées avant même le premier contact.
 */
function tarifs_conditions(array $settings = []): array
{
    $menage  = (int) ($settings['frais_menage'] ?? 220);
    $acompte = (int) ($settings['acompte_pourcentage'] ?? 30);
    $arrivee = (string) ($settings['check_in'] ?? '16:00');
    $depart  = (string) ($settings['check_out'] ?? '10:00');

    $heure = function ($h) { return str_replace(':', 'h', substr($h, 0, 5)); };

    return [
        ['Compris dans le prix',
         "La villa entière pour votre seul groupe, la piscine chauffée, le parc de 5 000 m², "
         . "la fibre optique, la borne de recharge électrique et les vélos."],
        ['Location à la semaine',
         "Du samedi au samedi. Un séjour de 3 nuits minimum est possible sur certaines périodes : "
         . "écrivez-nous, nous étudions chaque demande."],
        ['Ménage de fin de séjour',
         "En option, " . $menage . " € — ou à votre charge si vous préférez rendre la maison en état."],
        ['Réservation',
         "Un acompte de " . $acompte . " % confirme la réservation, le solde est réglé avant l'arrivée. "
         . "Aucune commission de plateforme : vous réservez en direct."],
        ['Arrivée et départ',
         "Arrivée à partir de " . $heure($arrivee) . ", départ avant " . $heure($depart) . "."],
    ];
}
