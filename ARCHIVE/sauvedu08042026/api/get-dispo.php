<?php
// api/get-dispo.php
require_once '../config/db.php';
header('Content-Type: application/json');

try {
    // 1. Récupérer les jours réservés/indisponibles
    $stmt = $pdo->query("SELECT jour, statut FROM calendrier_dispo");
    $disponibilites = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [ '2026-01-14' => 'reserve', ... ]

    // 2. Récupérer les tarifs saisonniers pour afficher les prix
    $stmt2 = $pdo->query("SELECT * FROM tarifs_saison ORDER BY date_debut ASC");
    $tarifs = $stmt2->fetchAll();

    echo json_encode([
        'dispo' => $disponibilites,
        'tarifs' => $tarifs
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>