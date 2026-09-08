<?php
// login.php
session_start();
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Identifiants incorrects.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}

// Fallback user creation structure if admins table is empty
$stmt = $pdo->query("SELECT COUNT(*) FROM admins");
if ($stmt->fetchColumn() == 0) {
    $default_pass = password_hash('admin', PASSWORD_DEFAULT);
    $pdo->query("INSERT INTO admins (username, password_hash) VALUES ('admin', '$default_pass')");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Connexion</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">

    <div class="glass-panel login-card">
        <div class="brand" style="justify-content: center; margin-bottom: 2rem;">
            <div class="brand-dot"></div>
            Management Gîte
        </div>
        
        <h2>Connexion</h2>
        <p style="margin-bottom: 2rem;">Veuillez vous authentifier pour accéder à l'espace de gestion.</p>

        <?php if($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="username">Nom d'utilisateur</label>
                <div style="position: relative;">
                    <i class="fas fa-user" style="position: absolute; left: 16px; top: 14px; color: var(--text-secondary);"></i>
                    <input type="text" id="username" name="username" class="form-control" style="padding-left: 45px;" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <div style="position: relative;">
                    <i class="fas fa-lock" style="position: absolute; left: 16px; top: 14px; color: var(--text-secondary);"></i>
                    <input type="password" id="password" name="password" class="form-control" style="padding-left: 45px;" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>
    </div>

</body>
</html>
