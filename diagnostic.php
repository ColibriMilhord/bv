<?php
/**
 * diagnostic.php — Vérification de l'installation après une mise en ligne.
 *
 * À ouvrir dans le navigateur : https://votre-site/diagnostic.php
 * Il indique ce qui manque ou ce qui bloque, et affiche les dernières erreurs
 * PHP du serveur — c'est là que se trouve la cause exacte d'une erreur 500.
 *
 * Aucun mot de passe n'est affiché : seule la présence des réglages est
 * signalée, jamais leur valeur.
 *
 * ⚠ À SUPPRIMER du serveur une fois le problème réglé.
 */

header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');

$racine = __DIR__;
$lignes = [];

/** Ajoute une ligne au rapport. */
function verdict(&$lignes, $etat, $sujet, $detail = '')
{
    $lignes[] = ['etat' => $etat, 'sujet' => $sujet, 'detail' => $detail];
}

// ── 1. Version de PHP ──────────────────────────────────────────────────────
$version = PHP_VERSION;
verdict(
    $lignes,
    version_compare($version, '7.4', '>=') ? 'ok' : (version_compare($version, '7.1', '>=') ? 'attention' : 'ko'),
    'Version de PHP',
    $version . (version_compare($version, '7.4', '<')
        ? ' — le site demande 7.4 ou plus. hPanel → Avancé → Configuration PHP.'
        : '')
);

// ── 2. Extensions ──────────────────────────────────────────────────────────
foreach ([
    'pdo_mysql' => 'indispensable — base de données',
    'curl'      => 'indispensable — agenda et avis Google',
    'mbstring'  => 'indispensable — textes accentués',
    'json'      => 'indispensable',
    'intl'      => 'utilisé pour les dates des tarifs',
] as $ext => $role) {
    $present = extension_loaded($ext);
    verdict(
        $lignes,
        $present ? 'ok' : ($ext === 'intl' ? 'attention' : 'ko'),
        'Extension ' . $ext,
        $present ? '' : 'absente — ' . $role
    );
}

// ── 3. Fichiers attendus ───────────────────────────────────────────────────
$fichiers = [
    'config/secrets.php'      => true,
    'config/env.php'          => true,
    'config/db.php'           => true,
    'config/mail_config.php'  => true,
    'config/mail_smtp.php'    => true,
    'config/seo.php'          => true,
    'config/medias.php'       => true,
    'config/agenda.php'       => true,
    'config/notifications.php'=> true,
    'config/avis.php'         => true,
    'config/avis-secours.php' => true,
    'config/.htaccess'        => false,
    'robots.txt'              => false,
    'sitemap.xml'             => false,
    'llms.txt'                => false,
];

foreach ($fichiers as $fichier => $obligatoire) {
    $chemin  = $racine . '/' . $fichier;
    $present = is_file($chemin);

    if (!$present) {
        verdict($lignes, $obligatoire ? 'ko' : 'attention', $fichier, 'fichier absent');
        continue;
    }

    // Contrôle de syntaxe sans exécuter le fichier.
    if (substr($fichier, -4) === '.php') {
        $code = @file_get_contents($chemin);
        try {
            token_get_all((string) $code, TOKEN_PARSE);
            verdict($lignes, 'ok', $fichier, 'présent, syntaxe correcte');
        } catch (\ParseError $e) {
            verdict($lignes, 'ko', $fichier, 'ERREUR DE SYNTAXE : ' . $e->getMessage());
        } catch (\Throwable $e) {
            verdict($lignes, 'attention', $fichier, 'contrôle impossible : ' . $e->getMessage());
        }
    } else {
        verdict($lignes, 'ok', $fichier, 'présent');
    }
}

// ── 4. Réglages présents dans secrets.php (jamais les valeurs) ─────────────
$cheminSecrets = $racine . '/config/secrets.php';
if (is_file($cheminSecrets)) {
    $syntaxeOk = true;
    try {
        token_get_all((string) @file_get_contents($cheminSecrets), TOKEN_PARSE);
    } catch (\Throwable $e) {
        $syntaxeOk = false;
    }

    if ($syntaxeOk) {
        $valeurs = @include $cheminSecrets;
        if (!is_array($valeurs)) {
            verdict($lignes, 'ko', 'config/secrets.php', "le fichier doit se terminer par « return [ … ]; »");
        } else {
            foreach ([
                'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
                'SMTP_HOST', 'SMTP_USER', 'SMTP_PASS', 'SMTP_FROM',
            ] as $cle) {
                $rempli = isset($valeurs[$cle]) && $valeurs[$cle] !== '';
                verdict($lignes, $rempli ? 'ok' : 'ko', 'Réglage ' . $cle, $rempli ? 'renseigné' : 'MANQUANT');
            }
            foreach (['GOOGLE_PLACES_API_KEY', 'GOOGLE_PLACE_ID'] as $cle) {
                $rempli = isset($valeurs[$cle]) && $valeurs[$cle] !== '';
                verdict($lignes, 'info', 'Réglage ' . $cle, $rempli ? 'renseigné' : 'vide (avis Google non synchronisés)');
            }
        }
    }
}

// ── 5. Base de données ─────────────────────────────────────────────────────
if (is_file($racine . '/config/env.php') && is_file($cheminSecrets)) {
    try {
        require_once $racine . '/config/env.php';
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            secret('DB_HOST', 'localhost'),
            secret('DB_NAME', '')
        );
        $test = new PDO($dsn, secret('DB_USER', ''), secret('DB_PASS', ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $tables = $test->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        verdict($lignes, 'ok', 'Connexion à la base', count($tables) . ' tables');

        $colonne = $test->query("SHOW COLUMNS FROM gite_settings LIKE 'emails_destinataires'")->fetch();
        verdict(
            $lignes,
            $colonne ? 'ok' : 'attention',
            'Colonne emails_destinataires',
            $colonne ? 'en place' : 'absente — elle se crée à l\'ouverture de « Paramètres du Gîte »'
        );
    } catch (Throwable $e) {
        verdict($lignes, 'ko', 'Connexion à la base', $e->getMessage());
    }
}

// ── 6. Dossier de cache ────────────────────────────────────────────────────
$cache = $racine . '/cache';
if (!is_dir($cache)) {
    verdict($lignes, 'attention', 'Dossier cache/', 'absent — il sera créé automatiquement si les droits le permettent');
} else {
    verdict(
        $lignes,
        is_writable($cache) ? 'ok' : 'attention',
        'Dossier cache/',
        is_writable($cache) ? 'accessible en écriture' : 'lecture seule — agenda et avis ne seront pas mis en cache'
    );
}

// ── 7. Journal d'erreurs PHP ───────────────────────────────────────────────
$journaux = array_filter([
    ini_get('error_log') ?: null,
    $racine . '/error_log',
    $racine . '/php_errorlog',
    dirname($racine) . '/error_log',
]);

$journal = null;
foreach ($journaux as $candidat) {
    if (is_string($candidat) && is_file($candidat) && is_readable($candidat)) {
        $journal = $candidat;
        break;
    }
}

$dernieres = '';
if ($journal) {
    $contenu   = (string) @file_get_contents($journal);
    $toutes    = preg_split("/\r\n|\n/", trim($contenu)) ?: [];
    $dernieres = implode("\n", array_slice($toutes, -25));
}

?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Diagnostic — Bellevue d'Aveyron</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body { font: 15px/1.6 system-ui, -apple-system, Segoe UI, sans-serif; margin: 0; padding: 28px; background: #f6f5f2; color: #26221c; }
    h1 { font-size: 1.35rem; margin: 0 0 4px; }
    p.sous { color: #6a6258; margin: 0 0 22px; }
    table { border-collapse: collapse; width: 100%; max-width: 1000px; background: #fff; border: 1px solid #e2dccd; }
    th, td { text-align: left; padding: 8px 12px; border-bottom: 1px solid #efe9dc; vertical-align: top; }
    th { background: #faf8f4; font-size: .8rem; text-transform: uppercase; letter-spacing: .06em; color: #6a6258; }
    tr:last-child td { border-bottom: 0; }
    .etat { font-weight: 600; white-space: nowrap; }
    .ok { color: #1d7a3d; } .ko { color: #b3261e; } .attention { color: #a8730a; } .info { color: #55606e; }
    .sujet { font-family: ui-monospace, Menlo, Consolas, monospace; font-size: .88rem; }
    pre { background: #1d1b17; color: #ecebe6; padding: 16px; border-radius: 5px; overflow-x: auto; font-size: .78rem; line-height: 1.55; max-width: 1000px; }
    .avert { background: #fff4d6; border: 1px solid #e6c76a; padding: 12px 16px; border-radius: 5px; max-width: 1000px; margin-bottom: 22px; }
</style>
</head>
<body>

<h1>Diagnostic de l'installation</h1>
<p class="sous"><?php echo date('d/m/Y à H:i'); ?> — <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?></p>

<div class="avert">
    <strong>À supprimer du serveur une fois le problème réglé.</strong>
    Ce rapport n'affiche aucun mot de passe, mais il décrit l'installation.
</div>

<table>
    <tr><th>État</th><th>Élément</th><th>Détail</th></tr>
    <?php foreach ($lignes as $l): ?>
    <tr>
        <td class="etat <?php echo $l['etat']; ?>">
            <?php
            echo ['ok' => '✔ OK', 'ko' => '✖ BLOQUANT', 'attention' => '▲ à voir', 'info' => 'i'][$l['etat']] ?? '';
            ?>
        </td>
        <td class="sujet"><?php echo htmlspecialchars($l['sujet']); ?></td>
        <td><?php echo htmlspecialchars($l['detail']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Dernières erreurs PHP</h2>
<?php if ($dernieres !== ''): ?>
    <p class="sous">Journal lu : <code><?php echo htmlspecialchars((string) $journal); ?></code></p>
    <pre><?php echo htmlspecialchars($dernieres); ?></pre>
<?php else: ?>
    <p class="sous">
        Aucun journal lisible depuis ici. Dans hPanel : <strong>Avancé → Journaux d'erreurs PHP</strong>,
        ou cherchez un fichier <code>error_log</code> dans le dossier du site avec le gestionnaire de fichiers.
        La dernière ligne mentionnant « Fatal error » donne le fichier et le numéro de ligne exacts.
    </p>
<?php endif; ?>

</body>
</html>
