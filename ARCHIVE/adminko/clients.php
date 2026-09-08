<?php
require_once '../config/db.php';
require_once 'includes/header.php';

try {
    // Group clients from reservations
    $stmt = $pdo->query("SELECT 
        client_nom, 
        client_email, 
        client_tel, 
        COUNT(*) as total_reservations, 
        SUM(prix_total) as total_depense, 
        MAX(created_at) as derniere_activite
        FROM reservations 
        WHERE client_email != ''
        GROUP BY client_email, client_nom, client_tel
        ORDER BY derniere_activite DESC");
        
    $clients = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur base de données");
}
?>

<div class="flex justify-between items-center mb-4">
    <h1>Liste des Clients</h1>
    <div>
        <!-- Potential Export buttons as in mathieuweb -->
        <button class="btn btn-secondary"><i class="fas fa-file-export"></i> Exporter CSV</button>
    </div>
</div>

<div class="glass-panel table-container">
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Contact</th>
                <th>Nb Réservations</th>
                <th>Total Dépensé</th>
                <th>Dernière activité</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($clients as $c): ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['client_nom']) ?></strong></td>
                <td>
                    <i class="fas fa-envelope" style="color:var(--text-secondary)"></i> <a href="mailto:<?= htmlspecialchars($c['client_email']) ?>" style="color:var(--accent-primary); text-decoration:none;"><?= htmlspecialchars($c['client_email']) ?></a><br>
                    <i class="fas fa-phone" style="color:var(--text-secondary)"></i> <?= htmlspecialchars($c['client_tel']) ?>
                </td>
                <td><span class="badge" style="background:rgba(255,255,255,0.1); color:white;"><?= $c['total_reservations'] ?></span></td>
                <td><?= number_format($c['total_depense'], 2) ?> €</td>
                <td><?= date('d/m/Y H:i', strtotime($c['derniere_activite'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
