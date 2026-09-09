<?php
/**
 * debug-500.php — Affiche la cause exacte d'une erreur 500.
 *
 * À téléverser à la racine du site, puis à ouvrir dans le navigateur :
 *     https://votre-site/debug-500.php
 *
 * Le serveur masque normalement les erreurs PHP, ce qui donne une page 500
 * muette. Ce fichier force leur affichage, puis charge les fichiers du site
 * un par un : la dernière étape affichée avant l'arrêt désigne le fichier
 * fautif, avec son numéro de ligne.
 *
 * ⚠ À SUPPRIMER du serveur une fois le problème réglé.
 */

/**
 * Accès réservé.
 * ---------------------------------------------------------------------------
 * Cet outil décrit l'installation : il ne doit pas rester ouvert à tous.
 * Deux façons d'y accéder :
 *   • être connecté à l'espace d'administration ;
 *   • ou ajouter la clé à l'adresse : ?cle=bellevue-debug
 *
 * La clé ci-dessous est volontairement lisible : elle sert de garde-fou quand
 * la base de données est en panne et que la connexion à l'administration est
 * elle-même impossible. Changez-la, ou supprimez ce fichier une fois le
 * problème réglé.
 */
const DIAG_CLE = 'bellevue-debug';

if (session_status() === PHP_SESSION_NONE) @session_start();

if (empty($_SESSION['admin_id']) && (($_GET['cle'] ?? '') !== DIAG_CLE)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow');
    exit("Accès refusé.\n\nConnectez-vous à l'espace d'administration, ou ajoutez ?cle=… à l'adresse.\nLa clé figure en clair au début de ce fichier.");
}

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$etape = 'démarrage';

// Un arrêt brutal (erreur fatale) passe quand même par ici : c'est ce qui
// permet de nommer l'étape en cours au moment du plantage.
register_shutdown_function(function () use (&$etape) {
    $erreur = error_get_last();
    $fatales = [E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR, E_USER_ERROR];

    if ($erreur && in_array($erreur['type'], $fatales, true)) {
        echo '<div style="background:#fdecea;border:2px solid #b3261e;padding:16px 20px;'
           . 'margin:18px 0;border-radius:6px;font-family:system-ui,sans-serif">'
           . '<h2 style="margin:0 0 10px;color:#b3261e">Erreur fatale à l\'étape : '
           . htmlspecialchars($etape) . '</h2>'
           . '<p style="margin:0 0 6px"><strong>Message :</strong> '
           . htmlspecialchars($erreur['message']) . '</p>'
           . '<p style="margin:0"><strong>Fichier :</strong> '
           . htmlspecialchars($erreur['file']) . ' <strong>ligne</strong> '
           . (int) $erreur['line'] . '</p></div>';
    } else {
        echo '<p style="font-family:system-ui,sans-serif;color:#1d7a3d;font-weight:600">'
           . 'Aucune erreur fatale détectée jusqu\'au bout du test.</p>';
    }
});

header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');

echo '<!doctype html><meta charset="utf-8"><title>Debug 500</title>';
echo '<style>body{font:15px/1.6 system-ui,sans-serif;margin:0;padding:26px;background:#f6f5f2}'
   . 'li{margin:3px 0}code{background:#efe9dc;padding:1px 6px;border-radius:3px}'
   . '.ok{color:#1d7a3d}.wa{color:#a8730a}</style>';
echo '<h1 style="font-size:1.3rem;margin:0 0 4px">Recherche de la cause du 500</h1>';
echo '<p style="color:#6a6258;margin:0 0 18px">PHP ' . PHP_VERSION
   . ' — à supprimer du serveur une fois le problème réglé.</p><ul>';

/** Charge un fichier et signale le résultat. */
function etape_chargement(&$etape, $chemin, $obligatoire = true)
{
    $etape = $chemin;
    $complet = __DIR__ . '/' . $chemin;

    if (!is_file($complet)) {
        printf(
            '<li class="%s">%s <code>%s</code></li>',
            $obligatoire ? 'wa' : 'wa',
            $obligatoire ? '✖ ABSENT —' : '▲ absent (facultatif) —',
            htmlspecialchars($chemin)
        );
        return false;
    }

    require_once $complet;
    echo '<li class="ok">✔ chargé : <code>' . htmlspecialchars($chemin) . '</code></li>';
    flush();
    return true;
}

// ── Les fichiers de configuration, un par un ───────────────────────────────
etape_chargement($etape, 'config/env.php');
etape_chargement($etape, 'config/secrets.php', false);
etape_chargement($etape, 'config/mail_config.php');
etape_chargement($etape, 'config/mail_smtp.php');
etape_chargement($etape, 'config/seo.php');
etape_chargement($etape, 'config/medias.php');
etape_chargement($etape, 'config/notifications.php');
etape_chargement($etape, 'config/avis-secours.php', false);
etape_chargement($etape, 'config/avis.php');
etape_chargement($etape, 'config/agenda.php');

// ── Connexion à la base ────────────────────────────────────────────────────
$etape = 'connexion à la base de données';
echo '</ul><h2 style="font-size:1.05rem">Base de données</h2><ul>';
try {
    require_once __DIR__ . '/config/db.php';
    echo isset($pdo) && $pdo
        ? '<li class="ok">✔ connexion établie</li>'
        : '<li class="wa">▲ $pdo vaut null — vérifier config/secrets.php</li>';
} catch (Throwable $e) {
    echo '<li class="wa">▲ ' . htmlspecialchars($e->getMessage()) . '</li>';
}

// ── Les fonctions utilisées par la page d'accueil ──────────────────────────
echo '</ul><h2 style="font-size:1.05rem">Fonctions de la page</h2><ul>';

$etape = 'avis_donnees()';
try {
    $avis = avis_donnees();
    echo '<li class="ok">✔ avis : ' . (int) $avis['total'] . ' au total, '
       . count($avis['avis']) . ' affichés, source « ' . htmlspecialchars($avis['source']) . ' »</li>';
} catch (Throwable $e) {
    echo '<li class="wa">▲ avis_donnees() : ' . htmlspecialchars($e->getMessage()) . '</li>';
}

$etape = 'seo_head()';
try {
    ob_start();
    seo_head(['title' => 'test', 'description' => 'test', 'path' => '']);
    ob_end_clean();
    echo '<li class="ok">✔ balises &lt;head&gt;</li>';
} catch (Throwable $e) {
    echo '<li class="wa">▲ seo_head() : ' . htmlspecialchars($e->getMessage()) . '</li>';
}

$etape = 'IntlDateFormatter (extension intl)';
if (class_exists('IntlDateFormatter')) {
    echo '<li class="ok">✔ extension intl présente</li>';
} else {
    echo '<li class="wa">✖ extension <strong>intl</strong> ABSENTE — la page d\'accueil '
       . 'plante au moment d\'afficher les tarifs. hPanel → Avancé → Configuration PHP, '
       . 'activer l\'extension intl.</li>';
}

// ── Enfin, la page d'accueil complète ──────────────────────────────────────
echo '</ul><h2 style="font-size:1.05rem">Page d\'accueil</h2><ul>';
$etape = 'index.php';
echo '<li>chargement de <code>index.php</code>…</li></ul>';
flush();

ob_start();
require __DIR__ . '/index.php';
$html = ob_get_clean();

echo '<p class="ok" style="font-weight:600">✔ index.php s\'est exécuté sans erreur fatale ('
   . number_format(strlen($html), 0, ',', ' ') . ' octets produits).</p>';
echo '<p>Si la page publique renvoie malgré tout une erreur 500, la cause est côté '
   . 'serveur et non côté PHP : vérifiez les fichiers <code>.htaccess</code>.</p>';
