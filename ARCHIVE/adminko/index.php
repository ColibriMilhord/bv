<?php
require_once '../config/db.php';
require_once 'includes/header.php';

// Fetch Statistics
$stats = [
    'total_res' => 0,
    'revenue' => 0,
    'attente' => 0,
    'clients' => 0
];

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
    $stats['total_res'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT SUM(prix_total) FROM reservations WHERE statut = 'validee'");
    $stats['revenue'] = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'attente'");
    $stats['attente'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(DISTINCT client_email) FROM reservations WHERE client_email != ''");
    $stats['clients'] = $stmt->fetchColumn();

    // Upcoming reservations
    $stmtUpcoming = $pdo->prepare("SELECT * FROM reservations WHERE date_debut >= CURDATE() AND statut = 'validee' ORDER BY date_debut ASC LIMIT 5");
    $stmtUpcoming->execute();
    $upcoming = $stmtUpcoming->fetchAll();

} catch (PDOException $e) {
    // Handling error gracefully
    $error = "Erreur de chargement des statistiques.";
}
?>

<div class="flex justify-between items-center mb-4">
    <h1>Tableau de bord</h1>
    <a href="reservations.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle Réservation</a>
</div>

<div class="grid-cards">
    <div class="glass stat-card">
        <div class="stat-label">Réservations Totales</div>
        <div class="stat-value"><?= $stats['total_res'] ?></div>
        <i class="fas fa-book" style="position: absolute; right: 24px; bottom: 24px; font-size: 3rem; color: rgba(255,255,255,0.05);"></i>
    </div>
    
    <div class="glass stat-card">
        <div class="stat-label">Chiffre d'Affaires</div>
        <div class="stat-value"><?= number_format($stats['revenue'], 2, ',', ' ') ?> €</div>
        <i class="fas fa-euro-sign" style="position: absolute; right: 24px; bottom: 24px; font-size: 3rem; color: rgba(255,255,255,0.05);"></i>
    </div>

    <div class="glass stat-card">
        <div class="stat-label">En Attente</div>
        <div class="stat-value" style="color: var(--warning);"><?= $stats['attente'] ?></div>
        <i class="fas fa-clock" style="position: absolute; right: 24px; bottom: 24px; font-size: 3rem; color: rgba(255,255,255,0.05);"></i>
    </div>

    <div class="glass stat-card">
        <div class="stat-label">Clients Uniques</div>
        <div class="stat-value"><?= $stats['clients'] ?></div>
        <i class="fas fa-users" style="position: absolute; right: 24px; bottom: 24px; font-size: 3rem; color: rgba(255,255,255,0.05);"></i>
    </div>
</div>

<h2>Prochaines Arrivées</h2>
<div class="glass-panel table-container">
    <?php if (empty($upcoming)): ?>
        <p>Aucune arrivée prévue prochaînement.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Arrivée</th>
                    <th>Départ</th>
                    <th>Personnes</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($upcoming as $res): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($res['client_nom']) ?></strong><br>
                        <small style="color: var(--text-secondary);"><?= htmlspecialchars($res['client_tel']) ?></small>
                    </td>
                    <td><?= date('d/m/Y', strtotime($res['date_debut'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($res['date_fin'])) ?></td>
                    <td><?= $res['nombre_personnes'] ?> pers.</td>
                    <td><span class="badge validee">Confirmée</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
