<?php
// admin/index.php
session_start();
require_once '../config/db.php';
require_once '../config/notifications.php';
require_once '../config/demandes.php';

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
// Date calculée en PHP plutôt que par CURDATE() : la requête reste ainsi
// vérifiable hors MySQL, comme le reste des modules du site.
$stmt_arrivee = $pdo->prepare("SELECT * FROM reservations WHERE statut = 'validee' AND date_debut >= ? ORDER BY date_debut ASC LIMIT 1");
$stmt_arrivee->execute([date('Y-m-d')]);
$next_checkin = $stmt_arrivee->fetch();

// 4. Occupancy Rate (Next 30 days)
// Simplified calculation

// 5. La dernière notification est-elle partie ?
$dernier_envoi = notifications_dernier();

// 6. Le témoin ci-dessus vit dans un fichier de cache : un déploiement peut
//    l'effacer. La base, elle, garde la trace. Une demande enregistrée depuis
//    plus d'un quart d'heure et toujours pas notifiée signale une panne
//    d'envoi, même si plus personne ne se souvient de la dernière tentative.
$sans_notification = 0;
if (demandes_migrer($pdo)) {
    try {
        $stmt_muettes = $pdo->prepare(
            "SELECT COUNT(*) FROM reservations WHERE notifie = 0 AND created_at <= ?"
        );
        $stmt_muettes->execute([date('Y-m-d H:i:s', time() - 900)]);
        $sans_notification = (int) $stmt_muettes->fetchColumn();
    } catch (Throwable $e) {
        $sans_notification = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>administration - Tableau de Bord</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="assets/tailwind.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/tailwind.css'); ?>">
    <link rel="stylesheet" href="assets/icones.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/icones.css'); ?>">
    <link rel="stylesheet" href="assets/inter/inter.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/inter/inter.css'); ?>">
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
                <div class="flex items-center min-w-0">
                    <div class="bg-blue-600 text-white p-1 rounded mr-2 shrink-0">
                        <span class="material-symbols-outlined block">dashboard</span>
                    </div>
                    <h1 class="text-base sm:text-xl font-bold text-slate-800 truncate">
                        <span class="sm:hidden">Administration</span>
                        <span class="hidden sm:inline">Administration du gîte de Bellevue</span>
                    </h1>
                </div>
                <div class="flex items-center space-x-4 sm:space-x-6 shrink-0 ml-3">
                    <a href="../index.php" target="_blank" title="Voir le site"
                        class="text-slate-500 hover:text-blue-600 text-sm font-medium flex items-center transition">
                        <span class="material-symbols-outlined text-lg sm:mr-1">public</span>
                        <span class="hidden sm:inline">Voir le site</span>
                    </a>
                    <a href="logout.php" title="Déconnexion"
                        class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center transition">
                        <span class="material-symbols-outlined text-lg sm:mr-1">logout</span>
                        <span class="hidden sm:inline">Déconnexion</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:py-10 px-4 sm:px-6 lg:px-8">

        <!-- Welcome Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-900">Bienvenue,
                <?php echo htmlspecialchars($_SESSION['admin_username']); ?> 👋
            </h2>
            <p class="text-slate-500">Voici un aperçu de l'activité de votre gîte.</p>
        </div>

        <?php if ($dernier_envoi && !$dernier_envoi['ok']): ?>
            <!-- Une panne d'envoi est silencieuse : elle doit se voir ici, sinon
                 les demandes s'accumulent sans que personne ne le sache. -->
            <div class="mb-8 rounded-lg border border-red-200 bg-red-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-red-800">
                    La dernière demande n'a pas pu vous être envoyée par courriel
                </h3>
                <p class="mt-1 text-sm text-red-700">
                    Réponse du serveur de messagerie :
                    <span class="font-mono text-xs"><?php echo htmlspecialchars($dernier_envoi['detail'] ?: 'non précisée'); ?></span>
                </p>
                <p class="mt-2 text-sm text-red-700">
                    <strong>Les demandes restent enregistrées</strong> — elles ne sont pas perdues.
                    Consultez-les ci-dessous tant que l'envoi ne fonctionne pas, et vérifiez
                    la chaîne dans <a href="settings.php" class="underline font-medium">Paramètres du Gîte</a>.
                </p>
                <?php if ($dernier_envoi['quand']): ?>
                    <p class="mt-1 text-xs text-red-600">
                        Constaté le <?php echo htmlspecialchars(date('d/m/Y à H\\hi', strtotime($dernier_envoi['quand']))); ?>.
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($sans_notification > 0): ?>
            <div class="mb-8 rounded-lg border border-amber-200 bg-amber-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-amber-900">
                    <?php echo $sans_notification; ?>
                    demande<?php echo $sans_notification > 1 ? 's' : ''; ?>
                    enregistrée<?php echo $sans_notification > 1 ? 's' : ''; ?>
                    sans courriel de notification
                </h3>
                <p class="mt-1 text-sm text-amber-800">
                    Le client a bien envoyé sa demande, mais l'avis ne vous est pas parvenu.
                    Rien n'est perdu :
                    <a href="demandes.php?filtre=non-notifiees" class="underline font-medium">consultez-les ici</a>
                    et rappelez directement.
                </p>
            </div>
        <?php endif; ?>

        <!-- KPIs Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-10 sm:mb-12">
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

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

            <!-- Card 1: Calendrier & Réservations -->
            <a href="demandes.php"
                class="group bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-blue-500 hover:shadow-lg transition flex flex-col items-center text-center">
                <div
                    class="h-14 w-14 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-orange-600 group-hover:text-white transition">
                    <span class="material-symbols-outlined text-3xl">inbox</span>
                </div>
                <h4 class="font-bold text-slate-800 mb-1">Demandes reçues</h4>
                <p class="text-xs text-slate-500">Toutes les demandes du formulaire, même si le courriel n'est pas parti.</p>
            </a>

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

            <!-- Card : Audience -->
            <a href="statistiques.php"
                class="group bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-blue-500 hover:shadow-lg transition flex flex-col items-center text-center">
                <div
                    class="h-14 w-14 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <span class="material-symbols-outlined text-3xl">public</span>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-2">Audience du site</h4>
                <p class="text-sm text-slate-500">Qui visite le site, d'où, sur quelles pages — carte du monde et détail par pays.</p>
            </a>

            <!-- Card : Annonces -->
            <a href="annonces.php"
                class="group bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-blue-500 hover:shadow-lg transition flex flex-col items-center text-center">
                <div
                    class="h-14 w-14 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-amber-600 group-hover:text-white transition">
                    <span class="material-symbols-outlined text-3xl">campaign</span>
                </div>
                <h4 class="text-lg font-bold text-slate-900 mb-2">Annonces du site</h4>
                <p class="text-sm text-slate-500">Publier une dernière disponibilité, une promotion, une information ponctuelle.</p>
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