<?php
// admin/settings.php
session_start();
require_once '../config/db.php';
require_once '../config/mail_config.php';
require_once '../config/notifications.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Crée la colonne des destinataires si le site tourne encore sur l'ancien schéma.
$colonne_destinataires = notifications_migrer($pdo);

$message      = '';
$avertissement = '';

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frais_menage = floatval($_POST['frais_menage']);
    $acompte = intval($_POST['acompte']);
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    [$destinataires, $rejets] = notifications_parser($_POST['emails_destinataires'] ?? '');

    if ($colonne_destinataires) {
        $stmt = $pdo->prepare("UPDATE gite_settings SET frais_menage = ?, acompte_pourcentage = ?, check_in = ?, check_out = ?, emails_destinataires = ? WHERE id = 1");
        $ok = $stmt->execute([$frais_menage, $acompte, $check_in, $check_out, notifications_format($destinataires)]);
    } else {
        $stmt = $pdo->prepare("UPDATE gite_settings SET frais_menage = ?, acompte_pourcentage = ?, check_in = ?, check_out = ? WHERE id = 1");
        $ok = $stmt->execute([$frais_menage, $acompte, $check_in, $check_out]);
        $avertissement = "Les montants sont enregistrés, mais la liste des destinataires n'a pas pu être sauvegardée : la colonne est absente de la base.";
    }

    if ($ok) {
        $message = "Paramètres mis à jour avec succès.";
    } else {
        $message = "Erreur lors de la mise à jour.";
    }

    if ($rejets) {
        $avertissement .= ($avertissement ? ' ' : '')
            . "Adresse(s) ignorée(s) car invalide(s) : " . implode(', ', $rejets) . ".";
    }
    if ($colonne_destinataires && !$destinataires) {
        $avertissement .= ($avertissement ? ' ' : '')
            . "Aucun destinataire valide : les demandes partiront vers les adresses par défaut ("
            . implode(', ', notifications_defaut()) . ").";
    }
}

// Fetch Settings
$stmt = $pdo->query("SELECT * FROM gite_settings WHERE id = 1");
$settings = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Administration - Paramètres</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center text-gray-500 hover:text-gray-900 mr-4">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900">Paramètres du Gîte</h1>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto py-10 px-4">
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($avertissement): ?>
            <div class="bg-amber-50 border border-amber-300 text-amber-800 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($avertissement); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Configuration Générale</h3>
                <div class="mt-2 text-sm text-gray-500">
                    <p>Modifiez ici les frais et horaires par défaut.</p>
                </div>
                <form class="mt-5 space-y-6" method="POST">
                    <div>
                        <label for="frais_menage" class="block text-sm font-medium text-gray-700">Frais de Ménage
                            (€)</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">€</span>
                            </div>
                            <input type="number" name="frais_menage" id="frais_menage" required step="0.01"
                                value="<?php echo htmlspecialchars($settings['frais_menage']); ?>"
                                class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md py-2 border">
                        </div>
                    </div>

                    <div>
                        <label for="acompte" class="block text-sm font-medium text-gray-700">Pourcentage Acompte
                            (%)</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number" name="acompte" id="acompte" required min="0" max="100"
                                value="<?php echo htmlspecialchars($settings['acompte_pourcentage']); ?>"
                                class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="check_in" class="block text-sm font-medium text-gray-700">Heure Arrivée</label>
                            <input type="time" name="check_in" id="check_in" required
                                value="<?php echo htmlspecialchars($settings['check_in']); ?>"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                        </div>
                        <div>
                            <label for="check_out" class="block text-sm font-medium text-gray-700">Heure Départ</label>
                            <input type="time" name="check_out" id="check_out" required
                                value="<?php echo htmlspecialchars($settings['check_out']); ?>"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border">
                        </div>
                    </div>

                    <hr class="border-gray-200">

                    <div>
                        <label for="emails_destinataires" class="block text-sm font-medium text-gray-700">
                            Destinataires des demandes du formulaire
                        </label>
                        <p class="mt-1 text-sm text-gray-500">
                            Adresses qui reçoivent les demandes de réservation et d'information
                            envoyées depuis le site. Une par ligne (la virgule et le point-virgule
                            sont acceptés aussi), <?php echo NOTIF_MAX_DESTINATAIRES; ?> au maximum.
                        </p>
                        <textarea name="emails_destinataires" id="emails_destinataires" rows="4"
                            placeholder="<?php echo htmlspecialchars(notifications_format(notifications_defaut())); ?>"
                            class="mt-2 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border font-mono"><?php
                                echo htmlspecialchars(notifications_format(notifications_destinataires($settings)));
                            ?></textarea>
                        <p class="mt-2 text-sm text-gray-500">
                            <span class="font-medium text-gray-700">Expéditeur :</span>
                            <span class="font-mono"><?php
                                echo SMTP_FROM !== ''
                                    ? htmlspecialchars(SMTP_FROM)
                                    : 'boîte reservation@ (non configurée sur ce serveur)';
                            ?></span> —
                            non modifiable. C'est le compte authentifié auprès du serveur d'envoi :
                            expédier depuis une autre adresse ferait classer les messages en indésirables.
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            Le client, lui, reçoit toujours son accusé de réception, et les réponses
                            à ces messages partent vers son adresse.
                        </p>
                        <?php if (!$colonne_destinataires): ?>
                            <p class="mt-2 text-sm text-amber-700">
                                La base de données n'expose pas encore ce réglage : les demandes
                                partent vers les adresses par défaut.
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="pt-5">
                        <div class="flex justify-end">
                            <button type="submit"
                                class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>