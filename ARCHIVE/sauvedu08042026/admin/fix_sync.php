<?php
// admin/fix_sync.php
session_start();
require_once '../config/db.php';

// Check Auth
if (!isset($_SESSION['admin_id'])) {
    die("Accès refusé. Veuillez vous connecter à l'admin.");
}

try {
    // 1. Clear Availability Table
    $pdo->exec("DELETE FROM calendrier_dispo");

    // 2. Fetch All Reservations
    // We only care about Validated or Unavailable (Blocked)
    $stmt = $pdo->query("SELECT id, date_debut, date_fin FROM reservations WHERE statut IN ('validee', 'indisponible')");
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;
    $stmtInsert = $pdo->prepare("INSERT IGNORE INTO calendrier_dispo (jour, statut, id_reservation) VALUES (?, 'reserve', ?)");

    $duplicates = 0;

    foreach ($reservations as $res) {
        $startDate = new DateTime($res['date_debut']);
        $endDate = new DateTime($res['date_fin']);

        // Loop from Start Date to End Date (Exclusive)
        $period = new DatePeriod(
            $startDate,
            new DateInterval('P1D'),
            $endDate
        );

        foreach ($period as $dt) {
            $stmtInsert->execute([$dt->format('Y-m-d'), $res['id']]);
            if ($stmtInsert->rowCount() > 0) {
                $count++;
            } else {
                $duplicates++;
            }
        }
    }

    echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
    echo "<h1 style='color:green;'>Synchronisation Terminée</h1>";
    echo "<p>Le calendrier client a été mis à jour.</p>";
    echo "<p><strong>$count</strong> jours marqués comme occupés.</p>";
    if ($duplicates > 0) {
        echo "<p style='color:orange;'><strong>$duplicates</strong> jours ignorés (doublons/chevauchements).</p>";
    }
    echo "<br><a href='calendar.php' style='background:#2563eb; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Retour au Calendrier</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>