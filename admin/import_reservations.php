<?php
// admin/import_reservations.php

// ── Garde d'accès ──────────────────────────────────────────────────────────
// Script de maintenance : il modifie la base. Réservé à un administrateur
// connecté, et jamais indexable.
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    header('X-Robots-Tag: noindex, nofollow');
    exit("Accès refusé. Connectez-vous à l'espace d'administration.");
}

// Connexion : identifiants lus depuis l'environnement ou config/secrets.php.
require_once __DIR__ . '/../config/db.php';

if (!$pdo) {
    exit("Connexion à la base impossible. Vérifiez config/secrets.php.\n");
}

// Define the reservations extracted from the image
// Format: [Start Date, End Date (Checkout), 'Note']
$reservations_to_add = [
    ['2026-02-01', '2026-04-25', 'Importé: Hiver/Printemps'],
    ['2026-05-23', '2026-08-23', 'Importé: Été'],
    ['2026-09-05', '2026-09-19', 'Importé: Septembre']
];

try {
    foreach ($reservations_to_add as $res) {
        $debut = $res[0];
        $fin = $res[1];
        $note = $res[2];

        // 1. Insert into reservations
        $stmt = $pdo->prepare("INSERT INTO reservations (client_nom, client_email, date_debut, date_fin, statut, prix_total, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $client = "Client Importé";
        $email = "import@exemple.com";
        $statut = "validee";
        $prix = 0.00;

        $stmt->execute([$client, $email, $debut, $fin, $statut, $prix, $note]);
        $id = $pdo->lastInsertId();

        echo "Created Reservation ID $id for $debut to $fin\n";

        // 2. Update calendrier_dispo
        $period = new DatePeriod(
            new DateTime($debut),
            new DateInterval('P1D'),
            new DateTime($fin)
        );

        $sql_cal = "INSERT INTO calendrier_dispo (jour, statut, id_reservation) VALUES (?, 'reserve', ?) ON DUPLICATE KEY UPDATE statut='reserve', id_reservation=?";
        $stmt_cal = $pdo->prepare($sql_cal);

        foreach ($period as $dt) {
            $stmt_cal->execute([$dt->format('Y-m-d'), $id, $id]);
        }
        echo "  - Calendar days updated.\n";
    }
    echo "\nImport Successful!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
echo "</pre>";
?>