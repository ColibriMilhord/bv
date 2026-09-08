<?php
// admin/index.php
session_start();
require_once '../config/db.php';

// Auth Check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Stats Calculation
// 1. Pending Reservations
$pending_count = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'attente'")->fetchColumn();

// 2. Confirmed Revenue (Total)
$revenue_total = $pdo->query("SELECT SUM(prix_total) FROM reservations WHERE statut = 'validee'")->fetchColumn();

// 3. Next Check-in
$next_checkin = $pdo->query("SELECT * FROM reservations WHERE statut = 'validee' AND date_debut >= CURDATE() ORDER BY date_debut ASC LIMIT 1")->fetch();

// 4. Occupancy Rate (Next 30 days)
// Simplified calculation
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>administration - Tableau de Bord</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen">

    <!-- Top Bar -->
    <nav class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="bg-blue-600 text-white p-1 rounded mr-2">
                        <span class="material-symbols-outlined block">dashboard</span>
                    </div>
                    <h1 class="text-xl font-bold text-slate-800">Administration du gite de Bellevue</h1>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="../index.php" target="_blank"
                        class="text-slate-500 hover:text-blue-600 text-sm font-medium flex items-center transition">
                        <span class="material-symbols-outlined text-lg mr-1">public</span> Voir le site
                    </a>
                    <a href="logout.php"
                        class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center transition">
                        <span class="material-symbols-outlined text-lg mr-1">logout</span> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <!-- Welcome Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-900">Bienvenue,
                <?php echo htmlspecialchars($_SESSION['admin_username']); ?> 👋
            </h2>
            <p class="text-slate-500">Voici un aperçu de l'activité de votre gîte.</p>
        </div>

        <!-- KPIs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- KPI 1: Reservations En Attente -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-slate-500 text-sm font-medium uppercase tracking-wide">En Attente</h3>
                        <p class="text-4xl font-extrabold text-orange-500 mt-2"><?php echo $pending_count; ?></p>
                    </div>
                    <div class="p-2 bg-orange-50 rounded-lg text-orange-500">
                        <span class="material-symbols-outlined text-2xl">pending_actions</span>
                    </div>
                </div>
                <a href="reservations_pending.php"
                    class="text-orange-600 text-sm font-semibold hover:underline flex items-center">
                    Traiter les demandes <span class="material-symbols-outlined text-sm ml-1">arrow_forward</span>
                </a>
            </div>

            <!-- KPI 2: Chiffre d'Affaires -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-slate-500 text-sm font-medium uppercase tracking-wide">CA Validé (Est.)</h3>
                        <p class="text-4xl font-extrabold text-green-600 mt-2">
                            <?php echo number_format($revenue_total, 0, ',', ' '); ?> €
                        </p>
                    </div>
                    <div class="p-2 bg-green-50 rounded-lg text-green-600">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                </div>
                <div class="text-green-600 text-sm font-semibold flex items-center">
                    <span class="material-symbols-outlined text-sm mr-1">trending_up</span> Revenus confirmés
                </div>
            </div>

            <!-- KPI 3: Prochaine Arrivée -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-slate-500 text-sm font-medium uppercase tracking-wide">Prochaine Arrivée</h3>
                        <?php if ($next_checkin): ?>
                            <p class="text-xl font-bold text-slate-800 mt-2">
                                <?php echo htmlspecialchars($next_checkin['client_nom']); ?>
                            </p>
                            <p class="text-slate-500 text-sm mt-1">
                                Le <?php echo date('d/m/Y', strtotime($next_checkin['date_debut'])); ?>
                            </p>
                        <?php else: ?>
                            <p class="text-lg font-medium text-slate-400 mt-2">Aucune arrivée prévue</p>
                        <?php endif; ?>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <span class="material-symbols-outlined text-2xl">flight_land</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Grid -->
        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
            <span class="material-symbols-outlined mr-2">apps</span>
            Menu d'Administration
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Card 1: Calendrier & Réservations -->
            <a href="calendar.php"
                class="group bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-blue-500 hover:shadow-lg transition flex flex-col items-center text-center">
                <div
                    class="h-14 w-14 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition">
                    <span class="material-symbols-outlined text-3xl">calendar_month</span>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-2">Planning & Réservations</h4>
                <p class="text-sm text-slate-500">Visualiser le calendrier, ajouter ou modifier des réservations.</p>
            </a>

            <!-- Card 2: Tarifs & Saisons -->
            <a href="tarifs.php"
                class="group bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-blue-500 hover:shadow-lg transition flex flex-col items-center text-center">
                <div
                    class="h-14 w-14 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <span class="material-symbols-outlined text-3xl">price_change</span>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-2">Tarifs & Saisons</h4>
                <p class="text-sm text-slate-500">Définir les périodes, les prix à la semaine et les promotions.</p>
            </a>

            <!-- Card 3: Utilisateurs -->
            <a href="users.php"
                class="group bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-blue-500 hover:shadow-lg transition flex flex-col items-center text-center">
                <div
                    class="h-14 w-14 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition">
                    <span class="material-symbols-outlined text-3xl">group</span>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-2">Utilisateurs</h4>
                <p class="text-sm text-slate-500">Gérer les comptes administrateurs et les accès.</p>
            </a>

            <!-- Card 4: Paramètres -->
            <a href="settings.php"
                class="group bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-blue-500 hover:shadow-lg transition flex flex-col items-center text-center">
                <div
                    class="h-14 w-14 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-slate-600 group-hover:text-white transition">
                    <span class="material-symbols-outlined text-3xl">settings</span>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-2">Paramètres Généraux</h4>
                <p class="text-sm text-slate-500">Configuration du gîte, acompte, heures d'arrivée, etc.</p>
            </a>

            <!-- Card 5: Intégration Widget -->
            <a href="integration.php"
                class="group bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-blue-500 hover:shadow-lg transition flex flex-col items-center text-center">
                <div
                    class="h-14 w-14 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-teal-600 group-hover:text-white transition">
                    <span class="material-symbols-outlined text-3xl">code</span>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-2">Intégration Site</h4>
                <p class="text-sm text-slate-500">Obtenir le code pour Wix (calendrier iframe).</p>
            </a>

        </div>

    </div>
</body>

</html>