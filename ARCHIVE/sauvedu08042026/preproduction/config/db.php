<?php
// config/db.php

$host = 'localhost';
$dbname = 'u424962071_rbellevue';
$username = 'u424962071_rbellevue';
$password = 'MOT_DE_PASSE_RETIRE'; // Le mot de passe reste vide en local ou à définir selon l'environnement

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // In production, log this error instead of showing it
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>