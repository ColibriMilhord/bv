<?php
/**
 * diagnostic-avis.php — Pourquoi les avis Google ne se synchronisent pas.
 *
 * À ouvrir dans le navigateur : https://votre-site/diagnostic-avis.php
 * Interroge réellement Google et affiche sa réponse, y compris son message
 * d'erreur — la seule information vraiment utile ici.
 *
 * ⚠ À SUPPRIMER du serveur une fois le problème réglé.
 */

require_once __DIR__ . '/config/avis.php';

/**
 * Accès réservé.
 * ---------------------------------------------------------------------------
 * Cet outil décrit l'installation : il ne doit pas rester ouvert à tous.
 * Deux façons d'y accéder :
 *   • être connecté à l'espace d'administration ;
 *   • ou ajouter la clé à l'adresse : ?cle=bellevue-avis
 *
 * La clé ci-dessous est volontairement lisible : elle sert de garde-fou quand
 * la base de données est en panne et que la connexion à l'administration est
 * elle-même impossible. Changez-la, ou supprimez ce fichier une fois le
 * problème réglé.
 */
const DIAG_CLE = 'bellevue-avis';

if (session_status() === PHP_SESSION_NONE) @session_start();

if (empty($_SESSION['admin_id']) && (($_GET['cle'] ?? '') !== DIAG_CLE)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow');
    exit("Accès refusé.\n\nConnectez-vous à l'espace d'administration, ou ajoutez ?cle=… à l'adresse.\nLa clé figure en clair au début de ce fichier.");
}

header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');

/** Masque une clé : on doit pouvoir la reconnaître, pas la recopier. */
function masquer(string $valeur): string
{
    $n = strlen($valeur);
    if ($n === 0) return '(vide)';
    if ($n <= 12) return substr($valeur, 0, 3) . str_repeat('•', max(1, $n - 3));
    return substr($valeur, 0, 6) . str_repeat('•', 8) . substr($valeur, -4) . ' (' . $n . ' caractères)';
}

$cle   = (string) secret('GOOGLE_PLACES_API_KEY', '');
$place = (string) secret('GOOGLE_PLACE_ID', '');

// Vider le cache pour forcer un nouvel appel.
$videAsk = isset($_GET['vider']);
if ($videAsk && is_file(AVIS_CACHE)) @unlink(AVIS_CACHE);

$cacheExiste = is_file(AVIS_CACHE);
$cacheAge    = $cacheExiste ? time() - (int) filemtime(AVIS_CACHE) : null;
$cacheData   = $cacheExiste ? json_decode((string) @file_get_contents(AVIS_CACHE), true) : null;

// Appels réels, seulement si les deux réglages sont là.
$traceNouvelle = $traceAncienne = null;
$repNouvelle = $repAncienne = null;
if ($cle !== '' && $place !== '') {
    $repNouvelle = avis_api_nouvelle($cle, $place, $traceNouvelle);
    if ($repNouvelle === null) {
        $repAncienne = avis_api_ancienne($cle, $place, $traceAncienne);
    }
}

/** Traduit les refus les plus fréquents de Google. */
function explication(string $erreur): string
{
    $e = mb_strtolower($erreur);
    if (strpos($e, 'api key not valid') !== false || strpos($e, 'api_key_invalid') !== false) {
        return "La clé est refusée. Vérifiez que vous avez copié l'« Access Key » complète, sans espace ni retour à la ligne.";
    }
    if (strpos($e, 'not authorized to use this api') !== false || strpos($e, 'has not been used') !== false || strpos($e, 'is disabled') !== false) {
        return "L'API n'est pas activée pour ce projet. Console Google Cloud → API et services → Bibliothèque → activer « Places API (New) ».";
    }
    if (strpos($e, 'referer') !== false || strpos($e, 'ip') !== false && strpos($e, 'restriction') !== false) {
        return "La clé est restreinte à d'autres adresses. Dans les paramètres de la clé, section « Restrictions relatives aux applications », choisissez « Aucune » le temps du test, ou saisissez l'adresse IP du serveur.";
    }
    if (strpos($e, 'billing') !== false) {
        return "La facturation n'est pas activée sur le projet Google Cloud. Elle est obligatoire même si l'usage reste dans le crédit gratuit.";
    }
    if (strpos($e, 'not_found') !== false || strpos($e, 'requested entity was not found') !== false || strpos($e, 'invalid_request') !== false) {
        return "Le Place ID n'est pas reconnu. Reprenez-le avec l'outil officiel : developers.google.com/maps/documentation/javascript/examples/places-placeid-finder";
    }
    if (strpos($e, 'over_query_limit') !== false || strpos($e, 'quota') !== false) {
        return "Quota dépassé sur le projet Google Cloud.";
    }
    if (strpos($e, 'timed out') !== false || strpos($e, 'could not resolve') !== false || strpos($e, 'connect') !== false) {
        return "Le serveur n'arrive pas à joindre Google. Les appels sortants sont peut-être bloqués par l'hébergeur.";
    }
    return '';
}

?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Diagnostic des avis Google</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body { font: 15px/1.6 system-ui, -apple-system, Segoe UI, sans-serif; margin: 0; padding: 28px; background: #f6f5f2; color: #26221c; }
    h1 { font-size: 1.35rem; margin: 0 0 4px; }
    h2 { font-size: 1.05rem; margin: 28px 0 8px; }
    p.sous { color: #6a6258; margin: 0 0 20px; }
    table { border-collapse: collapse; width: 100%; max-width: 1000px; background: #fff; border: 1px solid #e2dccd; margin-bottom: 8px; }
    th, td { text-align: left; padding: 8px 12px; border-bottom: 1px solid #efe9dc; vertical-align: top; }
    th { width: 230px; background: #faf8f4; font-weight: 600; }
    tr:last-child td, tr:last-child th { border-bottom: 0; }
    .ok { color: #1d7a3d; font-weight: 600; } .ko { color: #b3261e; font-weight: 600; } .wa { color: #a8730a; font-weight: 600; }
    pre { background: #1d1b17; color: #ecebe6; padding: 14px; border-radius: 5px; overflow-x: auto; font-size: .76rem; line-height: 1.5; max-width: 1000px; white-space: pre-wrap; word-break: break-word; }
    .avert { background: #fff4d6; border: 1px solid #e6c76a; padding: 12px 16px; border-radius: 5px; max-width: 1000px; margin-bottom: 20px; }
    .fix { background: #eef6ff; border: 1px solid #a8c7e8; padding: 12px 16px; border-radius: 5px; max-width: 1000px; margin: 10px 0; }
    a.btn { display: inline-block; background: #26221c; color: #fff; text-decoration: none; padding: 7px 15px; border-radius: 4px; font-size: .88rem; }
    code { background: #efe9dc; padding: 1px 6px; border-radius: 3px; font-size: .9em; }
</style>
</head>
<body>

<h1>Diagnostic des avis Google</h1>
<p class="sous"><?php echo date('d/m/Y à H:i'); ?></p>

<div class="avert"><strong>À supprimer du serveur une fois le problème réglé.</strong>
La clé n'est jamais affichée en entier.</div>

<h2>1. Réglages lus dans config/secrets.php</h2>
<table>
    <tr>
        <th>GOOGLE_PLACES_API_KEY</th>
        <td class="<?php echo $cle !== '' ? 'ok' : 'ko'; ?>">
            <?php echo $cle !== '' ? htmlspecialchars(masquer($cle)) : '✖ ABSENTE'; ?>
        </td>
    </tr>
    <tr>
        <th>GOOGLE_PLACE_ID</th>
        <td class="<?php echo $place !== '' ? 'ok' : 'ko'; ?>">
            <?php echo $place !== '' ? htmlspecialchars($place) : '✖ ABSENT'; ?>
        </td>
    </tr>
</table>

<?php if ($cle === '' || $place === ''): ?>
<div class="fix">
    <strong>C'est la cause.</strong> Les deux valeurs doivent figurer
    <em>à l'intérieur</em> du <code>return [ … ];</code> de
    <code>config/secrets.php</code>, comme les identifiants de la base :
    <pre>    'GOOGLE_PLACES_API_KEY' => 'AIza…',
    'GOOGLE_PLACE_ID'       => 'ChIJ…',</pre>
    Écrites avant ou après le <code>return</code>, elles ne sont jamais lues.
</div>
<?php endif; ?>

<h2>2. Cache</h2>
<table>
    <tr><th>Fichier</th><td><code><?php echo htmlspecialchars(AVIS_CACHE); ?></code></td></tr>
    <tr><th>Dossier accessible en écriture</th>
        <td class="<?php echo is_writable(dirname(AVIS_CACHE)) ? 'ok' : 'wa'; ?>">
            <?php echo is_writable(dirname(AVIS_CACHE)) ? 'oui' : 'NON — créez le dossier cache/ en 755'; ?>
        </td></tr>
    <tr><th>État</th>
        <td>
            <?php if (!$cacheExiste): ?>vide<?php else: ?>
                écrit il y a <?php echo (int) round($cacheAge / 60); ?> min,
                source « <?php echo htmlspecialchars($cacheData['source'] ?? '?'); ?> »
                <?php if (!empty($cacheData['echec'])): ?>
                    — <span class="wa">un échec est mémorisé pendant 1 h pour ne pas rappeler Google à chaque visite</span>
                <?php endif; ?>
            <?php endif; ?>
        </td></tr>
</table>
<p><a class="btn" href="?vider=1">Vider le cache et retester</a></p>

<?php if ($cle !== '' && $place !== ''): ?>

<h2>3. Appel réel à Google</h2>

<?php foreach ([['Places API (New)', $repNouvelle, $traceNouvelle], ['Ancienne Places API', $repAncienne, $traceAncienne]] as [$nom, $rep, $trace]): ?>
    <?php if ($trace === null) continue; ?>
    <h3 style="font-size:.95rem;margin:18px 0 6px"><?php echo $nom; ?></h3>
    <table>
        <tr><th>Code HTTP</th>
            <td class="<?php echo (int) $trace['code'] === 200 ? 'ok' : 'ko'; ?>"><?php echo (int) $trace['code']; ?></td></tr>
        <tr><th>Résultat</th>
            <td class="<?php echo $rep !== null ? 'ok' : 'ko'; ?>">
                <?php echo $rep !== null
                    ? '✔ ' . (int) $rep['total'] . ' avis au total, note ' . $rep['note'] . ', ' . count($rep['avis']) . ' avis reçus'
                    : '✖ ' . htmlspecialchars($trace['erreur'] ?: 'aucune donnée'); ?>
            </td></tr>
    </table>
    <?php $aide = explication((string) $trace['erreur']); ?>
    <?php if ($rep === null && $aide !== ''): ?>
        <div class="fix"><strong>Que faire :</strong> <?php echo htmlspecialchars($aide); ?></div>
    <?php endif; ?>
    <?php if ($rep === null && $trace['corps'] !== ''): ?>
        <p class="sous" style="margin:8px 0 4px">Réponse brute de Google :</p>
        <pre><?php echo htmlspecialchars($trace['corps']); ?></pre>
    <?php endif; ?>
<?php endforeach; ?>

<?php if ($repNouvelle !== null || $repAncienne !== null): ?>
    <div class="fix">
        <strong>Google répond correctement.</strong> Rechargez la page d'accueil :
        le bloc d'avis doit maintenant afficher « Google — synchronisé le … »
        avec le badge <code>GOOGLE</code>. Si ce n'est pas le cas, le dossier
        <code>cache/</code> n'est probablement pas accessible en écriture.
    </div>
<?php endif; ?>

<?php endif; ?>

<h2>4. Ce que la page affiche en ce moment</h2>
<?php $actuel = avis_donnees(); ?>
<table>
    <tr><th>Source</th>
        <td class="<?php echo ($actuel['source'] ?? '') === 'google' ? 'ok' : 'wa'; ?>">
            <?php echo htmlspecialchars($actuel['source'] ?? '?'); ?>
        </td></tr>
    <tr><th>Note / compteur</th><td><?php echo htmlspecialchars((string) $actuel['note']); ?> — <?php echo (int) $actuel['total']; ?> avis</td></tr>
    <tr><th>Avis affichés</th><td><?php echo count($actuel['avis']); ?></td></tr>
</table>

</body>
</html>
