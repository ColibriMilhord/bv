<?php
/**
 * config/db.php — Connexion à la base de données.
 *
 * Les identifiants viennent de config/env.php (variables d'environnement ou
 * config/secrets.php) : ce fichier ne contient plus aucun secret.
 *
 * En cas d'échec, $pdo vaut null. Le site public se dégrade alors sans
 * planter (calendrier et tarifs simplement absents) ; l'espace
 * d'administration répond 503, statut qui indique aux moteurs de revenir
 * plus tard au lieu d'indexer une page d'erreur.
 */

require_once __DIR__ . '/env.php';

$pdo = null;

try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        secret('DB_HOST', 'localhost'),
        secret('DB_NAME', '')
    );

    $pdo = new PDO($dsn, secret('DB_USER', ''), secret('DB_PASS', ''), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Le détail part dans le journal du serveur, jamais dans la page.
    error_log('[bellevue] connexion base impossible : ' . $e->getMessage());

    if (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false) {
        http_response_code(503);
        header('Retry-After: 300');
        header('X-Robots-Tag: noindex');
        exit('Service temporairement indisponible. Merci de réessayer dans quelques minutes.');
    }
}
