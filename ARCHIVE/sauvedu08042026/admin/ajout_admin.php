<?php
// admin/ajouter_admin.php
require_once '../config/db.php';

$username = 'Daniel';
$password_en_clair = 'Motdepasse';

// C'est ici que la magie opère : on crypte le mot de passe pour PHP
$password_crypte = password_hash($password_en_clair, PASSWORD_DEFAULT);

try {
    // On supprime l'ancien Daniel s'il existe (pour éviter les doublons ou erreurs)
    $stmt = $pdo->prepare("DELETE FROM admins WHERE username = ?");
    $stmt->execute([$username]);

    // On insère le nouveau Daniel avec le mot de passe crypté
    $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username, $password_crypte]);

    echo "✅ L'admin Daniel a été créé avec succès ! Le mot de passe est crypté correctement.<br>";
    echo "Hash stocké en base : " . $password_crypte;
    echo "<br><br><a href='login.php'>Clique ici pour te connecter</a>";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>