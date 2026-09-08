<?php
// admin/update_db.php

// Do NOT include config/db.php because it auto-connects and dies on failure
// require_once __DIR__ . '/../config/db.php';

$credentials = [
    ['localhost', 'u424962071_rbellevue', 'root', ''],        // Local Standard
    ['localhost', 'u424962071_rbellevue', 'root', 'root'],    // MAMP/Other
    ['localhost', 'u424962071_rbellevue', 'u424962071_rbellevue', 'qY+H9iazQj:2'], // Prod/Config
];

$pdo = null;

foreach ($credentials as $cred) {
    try {
        $pdo = new PDO("mysql:host=$cred[0];dbname=$cred[1];charset=utf8mb4", $cred[2], $cred[3]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected successfully using user: " . $cred[2] . "\n";
        break;
    } catch (PDOException $e) {
        echo "Failed with user " . $cred[2] . ": " . $e->getMessage() . "\n";
        continue;
    }
}

if (!$pdo) {
    die("Could not connect to database with any credentials.\n");
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
