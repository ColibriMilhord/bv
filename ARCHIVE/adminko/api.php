<?php
// admin/api.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

require_once '../config/db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_events') {
    // Return all validated reservations and calendar manual dispo
    try {
        $events = [];

        // Fetch validated blocks
        $stmt = $pdo->query("SELECT id, client_nom, date_debut, date_fin, statut FROM reservations WHERE statut = 'validee'");
        $reservations = $stmt->fetchAll();

        foreach ($reservations as $r) {
            $events[] = [
                'id' => $r['id'],
                'title' => $r['client_nom'],
                'start' => $r['date_debut'],
                'end' => $r['date_fin'],
                'status' => 'reserve',
                'type' => 'reservation'
            ];
        }
        
        echo json_encode($events);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur de base de données']);
    }
}
?>
