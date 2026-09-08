<?php
require_once '../config/db.php';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['id'] ?? 0;
    
    if ($_POST['action'] === 'update_status') {
        $statut = $_POST['statut'];
        
        $stmt = $pdo->prepare("UPDATE reservations SET statut = ? WHERE id = ?");
        $stmt->execute([$statut, $id]);
        
        // Also update calendar if 'validee'
        if ($statut === 'validee') {
            // Fetch reservation details to mark days as reserve
            $st = $pdo->prepare("SELECT date_debut, date_fin FROM reservations WHERE id=?");
            $st->execute([$id]);
            $r = $st->fetch();
            if ($r) {
                // simple loop to insert
                $start = new DateTime($r['date_debut']);
                $end = new DateTime($r['date_fin']);
                // usually we don't reserve the checkout day entirely in calendar, but per mathieuweb logic
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start, $interval, $end);
                
                foreach ($period as $dt) {
                    $d = $dt->format('Y-m-d');
                    $pdo->prepare("INSERT IGNORE INTO calendrier_dispo (jour, statut, id_reservation) VALUES (?, 'reserve', ?) ON DUPLICATE KEY UPDATE statut='reserve', id_reservation=?")->execute([$d, $id, $id]);
                }
            }
        } elseif ($statut === 'refusee') {
            // Free the days
            $pdo->prepare("DELETE FROM calendrier_dispo WHERE id_reservation = ?")->execute([$id]);
        }
        
        header("Location: reservations.php?msg=updated");
        exit;
    }
}

require_once 'includes/header.php';

// Fetch all reservations
try {
    $stmt = $pdo->query("SELECT * FROM reservations ORDER BY created_at DESC");
    $reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching reservations");
}
?>

<div class="flex justify-between items-center mb-4">
    <h1>Gestion des Réservations</h1>
    <!-- Add new reservation button could trigger a modal -->
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> Statut mis à jour avec succès.
    </div>
<?php endif; ?>

<div class="glass-panel table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Dates</th>
                <th>Prix</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reservations as $res): ?>
            <tr>
                <td>#<?= $res['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($res['client_nom']) ?></strong><br>
                    <small><?= htmlspecialchars($res['client_email']) ?></small>
                </td>
                <td>
                    Du <?= date('d/m/Y', strtotime($res['date_debut'])) ?><br>
                    Au <?= date('d/m/Y', strtotime($res['date_fin'])) ?>
                </td>
                <td><?= number_format($res['prix_total'], 2) ?> €</td>
                <td>
                    <span class="badge <?= htmlspecialchars($res['statut']) ?>">
                        <?= ucfirst($res['statut']) ?>
                    </span>
                </td>
                <td>
                    <form method="POST" style="display:inline-block;" onsubmit="return confirm('Accepter cette réservation?');">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" value="<?= $res['id'] ?>">
                        <input type="hidden" name="statut" value="validee">
                        <button type="submit" class="btn btn-success" style="padding: 6px 10px; font-size: 0.8rem;" <?= $res['statut'] == 'validee' ? 'disabled' : '' ?> title="Valider">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                    <form method="POST" style="display:inline-block;" onsubmit="return confirm('Refuser cette réservation?');">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" value="<?= $res['id'] ?>">
                        <input type="hidden" name="statut" value="refusee">
                        <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.8rem;" <?= $res['statut'] == 'refusee' ? 'disabled' : '' ?> title="Refuser">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
