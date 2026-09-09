<?php
/**
 * config/stats.php — Mesure d'audience interne.
 * ---------------------------------------------------------------------------
 * Enregistre les visites du site public et les restitue dans
 * admin/statistiques.php : volumes, provenance géographique, pages consultées.
 *
 * Choix de conception
 * -------------------
 * • Aucun traceur tiers, aucun cookie : la mesure se fait côté serveur. Elle
 *   ne nécessite donc pas de bandeau de consentement.
 * • L'adresse IP n'est JAMAIS stockée en entier. Seul le préfixe réseau est
 *   conservé (192.168.1.42 devient 192.168.1.0), ce qui suffit à déterminer
 *   le pays sans identifier un foyer. Le visiteur est compté via une empreinte
 *   non réversible, renouvelée chaque jour.
 * • Le pays n'est pas résolu pendant la visite : aucun appel réseau ne vient
 *   ralentir la page. Les préfixes en attente sont résolus par lots quand
 *   l'administrateur ouvre l'écran des statistiques.
 * • Les données sont purgées au-delà de 13 mois.
 *
 * L'enregistrement est volontairement silencieux : une panne de la mesure ne
 * doit jamais empêcher une page de s'afficher.
 */

require_once __DIR__ . '/env.php';

/** Date de début d'une période exprimée en jours, au format SQL. */
function stats_depuis(int $jours): string
{
    return date('Y-m-d', strtotime('-' . max(0, $jours - 1) . ' day'));
}

const STATS_RETENTION_JOURS = 400;  // ~13 mois
const STATS_GEO_PAR_LOT     = 40;   // préfixes résolus par ouverture de l'écran
const STATS_GEO_TIMEOUT     = 4;

/** Pays mis en avant : la France et ses voisins. */
function stats_pays_proches(): array
{
    return [
        'FR' => 'France',
        'BE' => 'Belgique',
        'CH' => 'Suisse',
        'LU' => 'Luxembourg',
        'DE' => 'Allemagne',
        'IT' => 'Italie',
        'ES' => 'Espagne',
        'GB' => 'Royaume-Uni',
        'NL' => 'Pays-Bas',
        'AD' => 'Andorre',
        'MC' => 'Monaco',
    ];
}

/** Nom français d'un pays, à partir de son code ISO. */
function stats_nom_pays(?string $code): string
{
    if (!$code) return 'Origine inconnue';

    $proches = stats_pays_proches();
    if (isset($proches[$code])) return $proches[$code];

    $autres = [
        'US' => 'États-Unis', 'CA' => 'Canada', 'PT' => 'Portugal', 'IE' => 'Irlande',
        'AT' => 'Autriche', 'DK' => 'Danemark', 'SE' => 'Suède', 'NO' => 'Norvège',
        'FI' => 'Finlande', 'PL' => 'Pologne', 'CZ' => 'Tchéquie', 'RO' => 'Roumanie',
        'AU' => 'Australie', 'NZ' => 'Nouvelle-Zélande', 'JP' => 'Japon', 'CN' => 'Chine',
        'BR' => 'Brésil', 'MA' => 'Maroc', 'DZ' => 'Algérie', 'TN' => 'Tunisie',
        'RU' => 'Russie', 'IN' => 'Inde', 'ZA' => 'Afrique du Sud', 'MX' => 'Mexique',
        'AR' => 'Argentine', 'IL' => 'Israël', 'TR' => 'Turquie', 'GR' => 'Grèce',
    ];

    return $autres[$code] ?? $code;
}

// ═══════════════════════════════════════════════════════════════════════════
// Schéma
// ═══════════════════════════════════════════════════════════════════════════

/** Crée les tables si besoin. Idempotent, appelé depuis l'administration. */
function stats_migrer(?PDO $pdo): bool
{
    if (!$pdo) return false;

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS visites (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                vue_le      DATETIME     NOT NULL,
                jour        DATE         NOT NULL,
                page        VARCHAR(190) NOT NULL,
                prefixe_ip  VARCHAR(45)  NULL,
                pays        CHAR(2)      NULL,
                visiteur    CHAR(40)     NOT NULL,
                referent    VARCHAR(190) NULL,
                appareil    VARCHAR(12)  NOT NULL DEFAULT 'ordinateur',
                INDEX idx_jour (jour),
                INDEX idx_pays (pays),
                INDEX idx_prefixe (prefixe_ip)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS geo_cache (
                prefixe   VARCHAR(45) PRIMARY KEY,
                pays      CHAR(2)     NULL,
                resolu_le DATETIME    NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        return true;
    } catch (PDOException $e) {
        error_log('[bellevue] stats : création des tables impossible — ' . $e->getMessage());
        return false;
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// Enregistrement d'une visite
// ═══════════════════════════════════════════════════════════════════════════

/** Adresse IP du visiteur, en tenant compte d'un éventuel proxy. */
function stats_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $cle) {
        if (empty($_SERVER[$cle])) continue;
        $valeur = explode(',', (string) $_SERVER[$cle])[0];
        $valeur = trim($valeur);
        if (filter_var($valeur, FILTER_VALIDATE_IP)) return $valeur;
    }
    return '';
}

/**
 * Préfixe réseau : dernier octet mis à zéro en IPv4, 64 premiers bits en IPv6.
 * Suffisant pour le pays, insuffisant pour identifier quiconque.
 */
function stats_prefixe_ip(string $ip): ?string
{
    if ($ip === '') return null;

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $o = explode('.', $ip);
        return $o[0] . '.' . $o[1] . '.' . $o[2] . '.0';
    }

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $blocs = explode(':', $ip);
        return implode(':', array_slice($blocs, 0, 4)) . '::';
    }

    return null;
}

/** Vrai si la requête vient d'un robot : ces visites ne sont pas comptées. */
function stats_est_robot(string $ua): bool
{
    if ($ua === '') return true;

    $motifs = [
        'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'python-requests',
        'headless', 'preview', 'monitor', 'pingdom', 'uptime', 'lighthouse',
        'gptbot', 'claudebot', 'perplexity', 'ccbot', 'facebookexternalhit',
        'semrush', 'ahrefs', 'mj12', 'dotbot', 'petalbot', 'bingpreview',
    ];
    $ua = strtolower($ua);
    foreach ($motifs as $motif) {
        if (strpos($ua, $motif) !== false) return true;
    }
    return false;
}

/** Type d'appareil déduit de l'en-tête User-Agent. */
function stats_appareil(string $ua): string
{
    $ua = strtolower($ua);
    if (strpos($ua, 'ipad') !== false || strpos($ua, 'tablet') !== false) return 'tablette';
    foreach (['mobi', 'iphone', 'ipod', 'android', 'windows phone'] as $motif) {
        if (strpos($ua, $motif) !== false) return 'mobile';
    }
    return 'ordinateur';
}

/**
 * Enregistre la visite courante. À appeler depuis les pages publiques.
 * Ne lève jamais d'exception et n'affiche jamais rien.
 */
function stats_enregistrer(?PDO $pdo, string $page): void
{
    if (!$pdo) return;

    try {
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (stats_est_robot($ua)) return;

        // On ne compte ni les requêtes POST ni l'espace d'administration.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return;

        $ip      = stats_ip();
        $prefixe = stats_prefixe_ip($ip);

        // Empreinte non réversible, renouvelée chaque jour : elle permet de
        // compter les visiteurs distincts sans jamais pouvoir les retrouver.
        $visiteur = substr(hash('sha256', $ip . '|' . $ua . '|' . date('Y-m-d') . '|bellevue'), 0, 40);

        $referent = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        if ($referent !== '') {
            $hote = parse_url($referent, PHP_URL_HOST);
            $referent = $hote && strpos($hote, 'bellevuedaveyron') === false ? $hote : '';
        }

        $stmt = $pdo->prepare(
            "INSERT INTO visites (vue_le, jour, page, prefixe_ip, pays, visiteur, referent, appareil)
             VALUES (?, ?, ?, ?, NULL, ?, ?, ?)"
        );
        $stmt->execute([
            date('Y-m-d H:i:s'),
            date('Y-m-d'),
            substr($page, 0, 190),
            $prefixe,
            $visiteur,
            substr($referent, 0, 190) ?: null,
            stats_appareil($ua),
        ]);
    } catch (Throwable $e) {
        // Silencieux par construction : la mesure ne doit rien casser.
        error_log('[bellevue] stats : enregistrement impossible — ' . $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// Résolution géographique — déclenchée depuis l'administration
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Résout les préfixes encore sans pays, par petits lots.
 * L'IP anonymisée reste routable : le pays est déterminé correctement.
 *
 * @return array{resolus:int, restants:int, erreur:string}
 */
function stats_resoudre_pays(?PDO $pdo): array
{
    $bilan = ['resolus' => 0, 'restants' => 0, 'erreur' => ''];
    if (!$pdo) return $bilan;

    try {
        // 1. Report des pays déjà connus du cache, préfixe par préfixe.
        $connus = $pdo->query(
            "SELECT prefixe, pays FROM geo_cache WHERE pays IS NOT NULL"
        )->fetchAll();
        $maj = $pdo->prepare("UPDATE visites SET pays = ? WHERE prefixe_ip = ? AND pays IS NULL");
        foreach ($connus as $c) {
            $maj->execute([$c['pays'], $c['prefixe']]);
        }

        // 2. Préfixes encore inconnus.
        $stmt = $pdo->prepare(
            "SELECT DISTINCT v.prefixe_ip
               FROM visites v
          LEFT JOIN geo_cache g ON g.prefixe = v.prefixe_ip
              WHERE v.pays IS NULL AND v.prefixe_ip IS NOT NULL AND g.prefixe IS NULL
              LIMIT " . (int) STATS_GEO_PAR_LOT
        );
        $stmt->execute();
        $prefixes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($prefixes as $prefixe) {
            $pays = stats_interroger_geo((string) $prefixe, $bilan['erreur']);

            // Même sans réponse, on mémorise la tentative pour ne pas boucler.
            $pdo->prepare("REPLACE INTO geo_cache (prefixe, pays, resolu_le) VALUES (?, ?, ?)")
                ->execute([$prefixe, $pays, date('Y-m-d H:i:s')]);

            if ($pays !== null) {
                $pdo->prepare("UPDATE visites SET pays = ? WHERE prefixe_ip = ? AND pays IS NULL")
                    ->execute([$pays, $prefixe]);
                $bilan['resolus']++;
            }
        }

        $bilan['restants'] = (int) $pdo->query(
            "SELECT COUNT(DISTINCT v.prefixe_ip)
               FROM visites v
          LEFT JOIN geo_cache g ON g.prefixe = v.prefixe_ip
              WHERE v.pays IS NULL AND v.prefixe_ip IS NOT NULL AND g.prefixe IS NULL"
        )->fetchColumn();
    } catch (Throwable $e) {
        $bilan['erreur'] = $e->getMessage();
        error_log('[bellevue] stats : résolution géographique — ' . $e->getMessage());
    }

    return $bilan;
}

/** Interroge le service de géolocalisation pour un préfixe. */
function stats_interroger_geo(string $prefixe, string &$erreur): ?string
{
    if (!function_exists('curl_init')) {
        $erreur = "L'extension cURL n'est pas activée sur ce serveur.";
        return null;
    }

    // ipwho.is : gratuit, en HTTPS, sans inscription ni clé.
    $ch = curl_init('https://ipwho.is/' . rawurlencode($prefixe) . '?fields=success,country_code');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => STATS_GEO_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_USERAGENT      => 'BellevueAveyron-Stats/1.0',
    ]);
    $corps = curl_exec($ch);
    $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $souci = curl_error($ch);
    curl_close($ch);

    if ($code !== 200 || !is_string($corps)) {
        $erreur = $souci !== '' ? $souci : 'réponse HTTP ' . $code;
        return null;
    }

    $json = json_decode($corps, true);
    if (!is_array($json) || empty($json['success'])) {
        $erreur = 'service de géolocalisation indisponible';
        return null;
    }

    $pays = $json['country_code'] ?? null;
    return is_string($pays) && strlen($pays) === 2 ? strtoupper($pays) : null;
}

// ═══════════════════════════════════════════════════════════════════════════
// Restitution
// ═══════════════════════════════════════════════════════════════════════════

/** Chiffres de synthèse sur une période donnée, en jours. */
function stats_synthese(?PDO $pdo, int $jours): array
{
    $vide = ['pages_vues' => 0, 'visiteurs' => 0, 'pays' => 0];
    if (!$pdo) return $vide;

    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS pages_vues,
                    COUNT(DISTINCT visiteur) AS visiteurs,
                    COUNT(DISTINCT pays) AS pays
               FROM visites
              WHERE jour >= ?"
        );
        $stmt->execute([stats_depuis($jours)]);
        $ligne = $stmt->fetch();

        return $ligne ? array_map('intval', $ligne) : $vide;
    } catch (Throwable $e) {
        return $vide;
    }
}

/** Visites par jour, pour la courbe. */
function stats_par_jour(?PDO $pdo, int $jours = 30): array
{
    if (!$pdo) return [];

    try {
        $stmt = $pdo->prepare(
            "SELECT jour, COUNT(*) AS vues, COUNT(DISTINCT visiteur) AS visiteurs
               FROM visites
              WHERE jour >= ?
           GROUP BY jour ORDER BY jour"
        );
        $stmt->execute([stats_depuis($jours)]);
        $lignes = [];
        foreach ($stmt->fetchAll() as $l) {
            $lignes[$l['jour']] = ['vues' => (int) $l['vues'], 'visiteurs' => (int) $l['visiteurs']];
        }

        // Les jours sans visite doivent apparaître : une courbe trouée ment.
        $resultat = [];
        for ($i = $jours - 1; $i >= 0; $i--) {
            $j = date('Y-m-d', strtotime("-$i day"));
            $resultat[$j] = $lignes[$j] ?? ['vues' => 0, 'visiteurs' => 0];
        }
        return $resultat;
    } catch (Throwable $e) {
        return [];
    }
}

/** Répartition par pays. */
function stats_par_pays(?PDO $pdo, int $jours = 365): array
{
    if (!$pdo) return [];

    try {
        $stmt = $pdo->prepare(
            "SELECT pays, COUNT(*) AS vues, COUNT(DISTINCT visiteur) AS visiteurs
               FROM visites
              WHERE jour >= ?
           GROUP BY pays ORDER BY vues DESC"
        );
        $stmt->execute([stats_depuis($jours)]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/** Classement générique sur une colonne (page, referent, appareil). */
function stats_classement(?PDO $pdo, string $colonne, int $jours = 30, int $limite = 8): array
{
    if (!$pdo) return [];
    if (!in_array($colonne, ['page', 'referent', 'appareil'], true)) return [];

    try {
        $stmt = $pdo->prepare(
            "SELECT $colonne AS cle, COUNT(*) AS vues
               FROM visites
              WHERE jour >= ? AND $colonne IS NOT NULL
           GROUP BY $colonne ORDER BY vues DESC LIMIT " . (int) $limite
        );
        $stmt->execute([stats_depuis($jours)]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/** Supprime les visites au-delà de la durée de conservation. */
function stats_purger(?PDO $pdo): int
{
    if (!$pdo) return 0;

    try {
        $stmt = $pdo->prepare("DELETE FROM visites WHERE jour < ?");
        $stmt->execute([stats_depuis(STATS_RETENTION_JOURS)]);
        return $stmt->rowCount();
    } catch (Throwable $e) {
        return 0;
    }
}
