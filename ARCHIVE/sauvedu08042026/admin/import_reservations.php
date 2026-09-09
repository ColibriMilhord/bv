<?php
// admin/import_reservations.php

// Do NOT include config/db.php blindly. Use robust connection logic.
$credentials = [
    ['localhost', 'u424962071_rbellevue', 'root', ''],        // Local Standard
    ['localhost', 'u424962071_rbellevue', 'root', 'MOT_DE_PASSE_RETIRE'],    // MAMP/Other
    ['localhost', 'u424962071_rbellevue', 'u424962071_rbellevue', 'MOT_DE_PASSE_RETIRE'], // Prod/Config
];

$pdo = null;

echo "<pre>\n";
foreach ($credentials as $cred) {
    try {
        $pdo = new PDO("mysql:host=$cred[0];dbname=$cred[1];charset=utf8mb4", $cred[2], $cred[3]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected successfully using user: " . $cred[2] . "\n";
        break;
    } catch (PDOException $e) {
        continue;
    }
}

if (!$pdo) {
    die("Could not connect to database with any credentials.\n");
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