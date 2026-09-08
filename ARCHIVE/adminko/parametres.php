<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $capacite = $_POST['capacite'];
    $frais_menage = $_POST['frais_menage'];
    $acompte = $_POST['acompte'];
    
    $stmt = $pdo->prepare("UPDATE gite_settings SET nom=?, capacite_max=?, frais_menage=?, acompte_pourcentage=? WHERE id=1");
    $stmt->execute([$nom, $capacite, $frais_menage, $acompte]);
    
    header("Location: parametres.php?msg=success");
    exit;
}

require_once 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM gite_settings WHERE id = 1");
$settings = $stmt->fetch();
?>

<div class="flex justify-between items-center mb-4">
    <h1>Paramètres du Gîte</h1>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> Paramètres mis à jour.
    </div>
<?php endif; ?>

<div class="grid-cards" style="grid-template-columns: 1fr 1fr;">
    <div class="glass-panel">
        <h2>Informations Générales</h2>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nom du Gîte</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($settings['nom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Capacité Maximale (personnes)</label>
                <input type="number" name="capacite" class="form-control" value="<?= htmlspecialchars($settings['capacite_max']) ?>" required min="1">
            </div>
            
            <div class="form-group">
                <label class="form-label">Frais de Ménage (€)</label>
                <input type="number" step="0.01" name="frais_menage" class="form-control" value="<?= htmlspecialchars($settings['frais_menage']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Pourcentage d'Acompte (%)</label>
                <input type="number" name="acompte" class="form-control" value="<?= htmlspecialchars($settings['acompte_pourcentage']) ?>" required min="0" max="100">
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer les modifications</button>
        </form>
    </div>
    
    <div class="glass-panel">
        <h2>Saisons et Tarifs</h2>
        <p style="margin-bottom:1rem;">Gestion des différentes périodes de tarification (Basse, moyenne, haute saison).</p>
        
        <?php
        $st = $pdo->query("SELECT * FROM tarifs_saison ORDER BY date_debut ASC");
        $tarifs = $st->fetchAll();
        ?>
        <div style="max-height: 400px; overflow-y: auto;">
            <table style="width:100%; border-collapse:collapse;">
                <?php foreach($tarifs as $t): ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <td style="padding: 10px 0;"><strong><?= htmlspecialchars($t['nom_saison']) ?></strong><br>
                    <small style="color:var(--text-secondary);"><?= date('d/m/Y', strtotime($t['date_debut'])) ?> au <?= date('d/m/Y', strtotime($t['date_fin'])) ?></small></td>
                    <td style="text-align:right; font-weight:bold; color:var(--success);"><?= htmlspecialchars($t['prix_semaine']) ?> €</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <button class="btn btn-secondary mt-4" style="width: 100%;"><i class="fas fa-plus"></i> Ajouter une saison</button>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
