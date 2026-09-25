<?php
// admin/reservations_pending.php
session_start();
require_once '../config/db.php';

// Auth Check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle Actions (Validate, Refuse, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id']);

    if ($action === 'validate') {
        // Validate
        $pdo->prepare("UPDATE reservations SET statut = 'validee' WHERE id = ?")->execute([$id]);

        // Update Calendar Availability
        $res = $pdo->query("SELECT * FROM reservations WHERE id = $id")->fetch();
        updateCalendar($pdo, $res['id'], $res['date_debut'], $res['date_fin'], 'validee');

    } elseif ($action === 'refuse') {
        // Refuse (Status update only, no calendar block usually needed, but user wanted red legend, so let's allow blocking if they want? 
        // User request "Refusée" in legend implies they might want to see it. 
        // For now, let's set status to refused.
        $pdo->prepare("UPDATE reservations SET statut = 'refusee' WHERE id = ?")->execute([$id]);

        // Ideally we remove it from calendar_dispo if it was there? Or add it as red?
        // Let's add it as 'refusee' (mapped to red in calendar.php) so they see it.
        $res = $pdo->query("SELECT * FROM reservations WHERE id = $id")->fetch();
        // Custom logic: Do we block dates for a refused reservation? Usually NO because we want to resell.
        // BUT user asked for "Red = Refused" in legend.
        // If I put it in calendar_dispo, it will show up.
        // Let's NOT block the dates availability-wise (client side), but maybe show in admin?
        // Actually, if I add to calendrier_dispo with 'statut'='refusee', get-dispo.php might return it.
        // Let's check get-dispo.php. It returns all. `site.php` checks `if != 'libre'`. 
        // So 'refusee' is not 'libre', so it would show as booked.
        // That is seemingly WRONG for a refused request (dates should be free).
        // User probably just wants to SEE it in admin list as refused, NOT block the dates.
        // So I will REMOVE from calendar_dispo.
        $pdo->prepare("DELETE FROM calendrier_dispo WHERE id_reservation = ?")->execute([$id]);

    } elseif ($action === 'delete') {
        // Delete
        $pdo->prepare("DELETE FROM reservations WHERE id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM calendrier_dispo WHERE id_reservation = ?")->execute([$id]);
    }
}

// Helper to sync calendar
function updateCalendar($pdo, $resId, $start, $end, $status)
{
    // Exact logic from api/events.php
    $pdo->prepare("DELETE FROM calendrier_dispo WHERE id_reservation = ?")->execute([$resId]);
    if ($status === 'validee' || $status === 'indisponible') {
        try {
            $period = new DatePeriod(new DateTime($start), new DateInterval('P1D'), new DateTime($end));
            $stmt = $pdo->prepare("INSERT IGNORE INTO calendrier_dispo (jour, statut, id_reservation) VALUES (?, 'reserve', ?)");
            foreach ($period as $dt)
                $stmt->execute([$dt->format('Y-m-d'), $resId]);
        } catch (Exception $e) {
        }
    }
}

// Fetch Pending
$pending = $pdo->query("SELECT * FROM reservations WHERE statut = 'attente' ORDER BY created_at ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes en Attente</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="assets/tailwind.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/tailwind.css'); ?>">
    <link rel="stylesheet" href="assets/icones.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/icones.css'); ?>">
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center text-gray-500 hover:text-gray-900 mr-4">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900">Demandes en Attente</h1>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto py-10 px-4">

        <?php if (empty($pending)): ?>
            <div class="bg-white p-10 rounded-lg shadow text-center">
                <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">check_circle</span>
                <p class="text-xl text-gray-500">Aucune demande en attente.</p>
                <a href="index.php" class="text-blue-600 font-medium mt-4 inline-block hover:underline">Retour au tableau de
                    bord</a>
            </div>
        <?php else: ?>

            <div class="grid grid-cols-1 gap-6">
                <?php foreach ($pending as $res): ?>
                    <div class="bg-white rounded-lg shadow border-l-4 border-orange-400 overflow-hidden">
                        <div class="p-6 md:flex justify-between items-center">

                            <!-- Info -->
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                    <?php echo htmlspecialchars($res['client_nom']); ?>
                                </h3>
                                <div class="mt-2 text-sm text-gray-600 space-y-1">
                                    <?php
                                    // Une demande d'information n'a ni dates ni montant : l'écrire
                                    // plutôt que d'afficher « du 01/01/1970 » et « 0,00 € ».
                                    $avec_dates = !empty($res['date_debut']) && !empty($res['date_fin']);
                                    ?>
                                    <p class="flex items-start">
                                        <span class="material-symbols-outlined text-gray-400 mr-2 text-lg">calendar_month</span>
                                        <?php if ($avec_dates): ?>
                                            <span>Du
                                                <strong><?php echo date('d/m/Y', strtotime($res['date_debut'])); ?></strong>
                                                au
                                                <strong><?php echo date('d/m/Y', strtotime($res['date_fin'])); ?></strong>
                                                <span class="ml-1 bg-gray-100 px-2 py-0.5 rounded text-xs whitespace-nowrap">
                                                    <?php
                                                    $d1 = new DateTime($res['date_debut']);
                                                    $d2 = new DateTime($res['date_fin']);
                                                    echo $d1->diff($d2)->days . ' nuits';
                                                    ?>
                                                </span>
                                            </span>
                                        <?php else: ?>
                                            <span class="italic text-gray-500">Demande d'information — aucune date précisée.</span>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ((float) $res['prix_total'] > 0): ?>
                                        <p class="flex items-start">
                                            <span class="material-symbols-outlined text-gray-400 mr-2 text-lg">euro</span>
                                            <span>Total&nbsp;: <strong><?php echo number_format((float) $res['prix_total'], 2, ',', ' '); ?> €</strong>
                                                <?php if ($res['option_menage']): ?>
                                                    <span class="text-xs text-gray-500 ml-1">(ménage inclus)</span>
                                                <?php endif; ?>
                                            </span>
                                        </p>
                                    <?php endif; ?>
                                    <p class="flex items-center">
                                        <span class="material-symbols-outlined text-gray-400 mr-2 text-lg">mail</span>
                                        <a href="mailto:<?php echo htmlspecialchars($res['client_email']); ?>"
                                            class="hover:text-blue-600">
                                            <?php echo htmlspecialchars($res['client_email']); ?>
                                        </a>
                                    </p>
                                    <?php if ($res['client_tel']): ?>
                                        <p class="flex items-center">
                                            <span class="material-symbols-outlined text-gray-400 mr-2 text-lg">call</span>
                                            <?php echo htmlspecialchars($res['client_tel']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-6 md:mt-0 flex flex-col sm:flex-row gap-3">

                                <form method="POST"
                                    onsubmit="return confirm('Valider cette réservation ? Cela bloquera les dates.');">
                                    <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                    <input type="hidden" name="action" value="validate">
                                    <button type="submit"
                                        class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center justify-center font-medium shadow-sm transition">
                                        <span class="material-symbols-outlined mr-2">check</span> Valider
                                    </button>
                                </form>

                                <form method="POST" onsubmit="return confirm('Refuser cette demande ?');">
                                    <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                    <input type="hidden" name="action" value="refuse">
                                    <button type="submit"
                                        class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded flex items-center justify-center font-medium shadow-sm transition">
                                        <span class="material-symbols-outlined mr-2">close</span> Refuser
                                    </button>
                                </form>

                                <form method="POST" onsubmit="return confirm('Supprimer définitivement ?');">
                                    <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit"
                                        class="w-full sm:w-auto bg-red-100 hover:bg-red-200 text-red-600 px-4 py-2 rounded flex items-center justify-center font-medium shadow-sm transition">
                                        <span class="material-symbols-outlined mr-2">delete</span> Supprimer
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</body>

</html>