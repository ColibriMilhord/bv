<?php
// admin/api/events.php
require_once '../../config/db.php';
header('Content-Type: application/json');

// Check Auth
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch Events
    try {
        $start = $_GET['start'] ?? date('Y-m-d');
        $end = $_GET['end'] ?? date('Y-m-d', strtotime('+1 year'));

        // Fetch Reservations
        $stmt = $pdo->prepare("SELECT id, client_nom, date_debut, date_fin, statut, prix_total, notes FROM reservations WHERE date_fin >= ? AND date_debut <= ?");
        $stmt->execute([$start, $end]);
        $rows = $stmt->fetchAll();

        $events = [];
        $statusColors = [
            'validee' => '#22c55e', // Green
            'attente' => '#fb923c', // Orange
            'refusee' => '#ef4444', // Red
            'indisponible' => '#6b7280' // Gray
        ];

        foreach ($rows as $row) {
            $events[] = [
                'id' => $row['id'],
                'title' => $row['client_nom'],
                // FullCalendar expects: start (inclusive), end (exclusive) for allDay
                'start' => $row['date_debut'],
                'end' => date('Y-m-d', strtotime($row['date_fin'] . ' +1 day')),
                'backgroundColor' => $statusColors[$row['statut']] ?? '#3b82f6',
                'borderColor' => $statusColors[$row['statut']] ?? '#3b82f6',
                'allDay' => true,
                'extendedProps' => [
                    'status' => $row['statut'],
                    'price' => $row['prix_total'],
                    'notes' => $row['notes'] ?? ''
                ]
            ];
        }
        echo json_encode($events);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    // Create / Update / Delete
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    try {
        if ($action === 'save') {
            // Create or Update
            $id = $input['id'] ?? null;
            $client = $input['client_nom'];
            $debut = $input['date_debut'];
            $fin = $input['date_fin'];
            $statut = $input['statut'];
            $prix = $input['prix_total'];
            $notes = $input['notes'] ?? null;

            if ($id) {
                // Update
                $stmt = $pdo->prepare("UPDATE reservations SET client_nom=?, date_debut=?, date_fin=?, statut=?, prix_total=?, notes=? WHERE id=?");
                $stmt->execute([$client, $debut, $fin, $statut, $prix, $notes, $id]);
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO reservations (client_nom, client_email, date_debut, date_fin, statut, prix_total, notes) VALUES (?, '', ?, ?, ?, ?, ?)");
                $stmt->execute([$client, $debut, $fin, $statut, $prix, $notes]);
                $id = $pdo->lastInsertId();
            }

            // Update Calendar Availability Table
            updateCalendarAvailability($pdo, $id, $debut, $fin, $statut);

            echo json_encode(['success' => true]);

        } elseif ($action === 'move') {
            // Drag & Drop Update
            $id = $input['id'];
            $debut = $input['date_debut'];
            $fin = $input['date_fin'];

            $stmt = $pdo->prepare("UPDATE reservations SET date_debut=?, date_fin=? WHERE id=?");
            $stmt->execute([$debut, $fin, $id]);

            // Fetch current status to update calendar correctly
            $curr = $pdo->query("SELECT statut FROM reservations WHERE id=$id")->fetch();
            updateCalendarAvailability($pdo, $id, $debut, $fin, $curr['statut']);

            echo json_encode(['success' => true]);

        } elseif ($action === 'delete') {
            $id = $input['id'];
            $pdo->prepare("DELETE FROM reservations WHERE id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM calendrier_dispo WHERE id_reservation=?")->execute([$id]);
            echo json_encode(['success' => true]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function updateCalendarAvailability($pdo, $resId, $start, $end, $status)
{
    // Remove old entries
    $pdo->prepare("DELETE FROM calendrier_dispo WHERE id_reservation = ?")->execute([$resId]);

    // Only block calendar if Confirmed or Unavailable
    if ($status === 'validee' || $status === 'indisponible') {
        try {
            $startDate = new DateTime($start);
            $endDate = new DateTime($end);

            // DatePeriod is exclusive of the end date, which matches the logic of 
            // CheckIn (Inclusive) -> CheckOut (Exclusive) for nightly occupancy.
            $period = new DatePeriod(
                $startDate,
                new DateInterval('P1D'),
                $endDate
            );

            $stmt = $pdo->prepare("INSERT INTO calendrier_dispo (jour, statut, id_reservation) VALUES (?, 'reserve', ?)");

            foreach ($period as $dt) {
                $stmt->execute([$dt->format('Y-m-d'), $resId]);
            }
        } catch (Exception $e) {
            // Log error or ignore if date format is invalid
        }
    }
}
?>