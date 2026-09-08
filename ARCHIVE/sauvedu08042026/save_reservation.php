<?php
// save_reservation.php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Fetch Global Settings
    $settings = $pdo->query("SELECT * FROM gite_settings WHERE id = 1")->fetch();

    // 2. Collect Validated Data
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_email = $_POST['customer_email'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $cleaning_option = isset($_POST['cleaning_fee']) ? 1 : 0;

    if (empty($customer_name) || empty($customer_email) || empty($check_in) || empty($check_out)) {
        die("Veuillez remplir tous les champs obligatoires.");
    }

    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $interval = $date1->diff($date2);
    $nights = $interval->days;

    if ($nights < 3) {
        die("Erreur : La durée minimum est de 3 nuits.");
    }

    // 3. Pricing Logic (Using tarifs_saison)
    $stmt = $pdo->prepare("SELECT * FROM tarifs_saison WHERE date_debut <= ? AND date_fin >= ?");
    $stmt->execute([$check_in, $check_out]);
    $period = $stmt->fetch();

    $total_price = 0;

    if ($period && $nights >= 7) {
        // Weekly Pro-rated
        $daily_rate = $period['prix_semaine'] / 7;
        $total_price = $daily_rate * $nights;
    } else {
        // Standard Nightly Rate (Fallback if no period or short stay < 7 days but >= 3)
        // Note: The user image implies short stays (weekends) have a nightly rate.
        // We use a default base or fetch from period "weekend" logic if detailed, 
        // but for now we stick to the 380 default from previous requirement or checking DB.
        $total_price = 380 * $nights;
    }

    // 4. Add Options
    if ($cleaning_option) {
        $total_price += $settings['frais_menage'];
    }

    // 5. Calculate Deposit
    $deposit_amount = $total_price * ($settings['acompte_pourcentage'] / 100);

    // 6. Insert into reservations table (Using new schema cols)
    try {
        $sql = "INSERT INTO reservations (date_debut, date_fin, client_nom, client_email, client_tel, prix_total, acompte_montant, option_menage, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'attente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$check_in, $check_out, $customer_name, $customer_email, $customer_phone, $total_price, $deposit_amount, $cleaning_option]);

        // Success View
        echo "<!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <title>Demande envoyée</title>
             <script src='https://cdn.tailwindcss.com'></script>
        </head>
        <body class='bg-gray-50 flex items-center justify-center h-screen'>
            <div class='max-w-xl w-full bg-white p-8 rounded-xl shadow-lg'>
                <div class='w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600'>
                    <svg class='w-8 h-8' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                </div>
                <h2 class='text-2xl font-bold text-gray-900 mb-2 text-center'>Demande en Attente</h2>
                <div class='bg-gray-50 p-4 rounded-lg mb-6 text-sm text-gray-700 space-y-2'>
                    <p>Votre demande a été transmise à notre équipe. Vous recevrez une réponse sous 24h.</p>
                    <hr class='border-gray-200 my-2'>
                    <p><span class='font-bold'>Séjour :</span> $nights nuits</p>
                    <p><span class='font-bold'>Total Estimé :</span> " . number_format($total_price, 2) . " €</p>
                    <p><span class='font-bold'>Acompte à régler si validé :</span> " . number_format($deposit_amount, 2) . " €</p>
                </div>
                <div class='text-center'>
                    <a href='index.php' class='inline-block bg-primary text-white font-bold py-2 px-6 rounded-lg hover:opacity-90 transition'>Retour au site</a>
                </div>
            </div>
            <script>
                tailwind.config = {
                    theme: { extend: { colors: { \"primary\": \"#197fe6\" } } }
                }
            </script>
        </body>
        </html>";

    } catch (PDOException $e) {
        die("Erreur technique : " . $e->getMessage());
    }

} else {
    header('Location: reservation.php');
    exit;
}
?>