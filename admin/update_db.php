<?php
// admin/update_db.php

// ── Garde d'accès ──────────────────────────────────────────────────────────
// Script de maintenance : il modifie la base. Réservé à un administrateur
// connecté, et jamais indexable.
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    header('X-Robots-Tag: noindex, nofollow');
    exit("Accès refusé. Connectez-vous à l'espace d'administration.");
}

// Connexion : identifiants lus depuis l'environnement ou config/secrets.php.
require_once __DIR__ . '/../config/db.php';

if (!$pdo) {
    exit("Connexion à la base impossible. Vérifiez config/secrets.php.\n");
}

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM reservations LIKE 'notes'");
    if ($stmt->fetch()) {
        echo "Column 'notes' already exists.\n";
    } else {
        $pdo->exec("ALTER TABLE reservations ADD COLUMN notes TEXT DEFAULT NULL AFTER statut");
        echo "Column 'notes' added successfully.\n";
    }
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage() . "\n";
}
?>
