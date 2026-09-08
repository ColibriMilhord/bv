<?php
// includes/header.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gîte Management Pro</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar glass">
        <div class="brand">
            <div class="brand-dot"></div>
            GîteAdmin
        </div>
        
        <ul class="nav-links">
            <li>
                <a href="index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Tableau de bord
                </a>
            </li>
            <li>
                <a href="calendrier.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'calendrier.php' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i> Calendrier unique
                </a>
            </li>
            <li>
                <a href="reservations.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : '' ?>">
                    <i class="fas fa-book"></i> Réservations
                </a>
            </li>
            <li>
                <a href="clients.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Clients
                </a>
            </li>
            <li>
                <a href="parametres.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'parametres.php' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Paramètres
                </a>
            </li>
        </ul>

        <div class="user-profile">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--accent-primary); display: flex; align-items: center; justify-content: center; font-weight: bold;">
                <?= strtoupper(substr($_SESSION['admin_username'], 0, 1)) ?>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['admin_username']) ?></div>
                <div style="font-size: 0.8rem; color: var(--text-secondary);">Administrateur</div>
            </div>
            <a href="logout.php" style="color: var(--text-secondary); text-decoration: none;" title="Déconnexion">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </nav>

    <!-- Main Wrapper -->
    <main class="main-content">
