<?php
// admin/settings.php
session_start();
require_once '../config/db.php';
require_once '../config/mail_config.php';
require_once '../config/notifications.php';
require_once '../config/mail_smtp.php';
require_once '../config/seo.php';   // pour seo_version_texte()
require_once '../config/courriels.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Crée la colonne des destinataires si le site tourne encore sur l'ancien schéma.
$colonne_destinataires = notifications_migrer($pdo);

$message      = '';
$avertissement = '';
$essai        = [];   // résultat de l'envoi d'essai, destinataire par destinataire

// ── Envoi d'essai ──────────────────────────────────────────────────────────
// Le formulaire public ne dit pas grand-chose quand un envoi échoue, et ses
// garde-fous anti-robots compliquent les vérifications répétées. Ce bouton
// envoie un vrai message aux destinataires réglés et rapporte, pour chacun,
// la réponse exacte du serveur.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['essai_envoi'])) {
    $settings_essai = $pdo->query("SELECT * FROM gite_settings WHERE id = 1")->fetch() ?: [];
    $courriel = courriel_essai(date('d/m/Y à H\\hi'), SMTP_FROM);

    // Une adresse saisie remplace les destinataires réglés, le temps d'un
    // essai. C'est ce qu'attendent les services de notation du type
    // mail-tester.com, qui fournissent une adresse jetable à usage unique.
    $vise = trim((string) ($_POST['essai_adresse'] ?? ''));

    if ($vise !== '' && !filter_var($vise, FILTER_VALIDATE_EMAIL)) {
        $avertissement = "L'adresse d'essai « " . htmlspecialchars($vise) . " » n'est pas valide.";
        $vise = '';
        $cibles = [];
    } else {
        $cibles = $vise !== '' ? [$vise] : notifications_destinataires($settings_essai);
    }

    foreach ($cibles as $adresse) {
        $debut    = microtime(true);
        $resultat = send_smtp_mail(
            $adresse,
            $courriel['sujet'],
            $courriel['texte'],
            '',
            $courriel['html']       // même mise en forme que les vrais messages
        );
        $essai[] = [
            'adresse' => $adresse,
            'ok'      => $resultat === true,
            // La réponse est affichée telle quelle : elle porte l'identifiant
            // de file, seule prise pour faire tracer un message qui n'arrive pas.
            'detail'  => $resultat === true
                ? (smtp_derniere_reponse() ?: 'accepté par le serveur')
                : (string) $resultat,
            'duree'   => round(microtime(true) - $debut, 1),
        ];
    }

    if (!$essai && $avertissement === '') {
        $avertissement = "Aucun destinataire réglé : rien n'a pu être envoyé.";
    }
}

// ── Les en-têtes tels qu'ils partent ──────────────────────────────────────
// L'hébergeur a demandé d'« inspecter le message généré par le site ». Ce
// bouton le montre, sans rien envoyer : un seul From avec une adresse valide,
// l'adresse du visiteur en Reply-To, Date et Message-ID présents. C'est la
// pièce à joindre à une réclamation.
$entetes_exemple = '';
$entetes_alerte  = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voir_entetes'])) {
    // Sans secrets lisibles, SMTP_FROM est vide et l'en-tête devient
    // « From: <> » — le message que les filtres rejettent comme non conforme.
    // C'est ce qui arrive après un déploiement, qui efface config/secrets.php.
    if (!filter_var(SMTP_FROM, FILTER_VALIDATE_EMAIL)) {
        $entetes_alerte = "L'adresse d'expédition est vide : les messages porteraient un "
                        . "« From: <> » que les serveurs de messagerie rejettent. "
                        . "Le fichier des secrets n'est pas lu — c'est ce qui se produit "
                        . "après une mise en production, qui l'efface.";
    }

    $exemple = courriel_proprietaires([
        'nom'           => 'Marie-Hélène Dubreuil-Fontanier',
        'email'         => 'visiteur@exemple.fr',
        'telephone'     => '06 12 34 56 78',
        'message'       => "La piscine sera-t-elle chauffée fin août ?",
        'has_dates'     => true,
        'date_debut'    => '2026-08-01',
        'date_fin'      => '2026-08-15',
        'nuits'         => 14,
        'prix_total'    => 4900,
        'acompte'       => 1470,
        'option_menage' => 1,
        'recu_le'       => date('d/m/Y à H\\hi'),
    ]);

    $message = smtp_message_construire([
        'destinataires' => notifications_destinataires(
            $pdo->query("SELECT * FROM gite_settings WHERE id = 1")->fetch() ?: []
        ),
        'sujet'    => $exemple['sujet'],
        'texte'    => $exemple['texte'],
        'html'     => $exemple['html'],
        'reply_to' => 'visiteur@exemple.fr',
        'domaine'  => substr(strrchr(SMTP_FROM, '@'), 1) ?: 'bellevuedaveyron.fr',
    ]);

    // Seuls les en-têtes : le corps n'apprendrait rien et tiendrait dix écrans.
    $coupure = strpos($message, "\r\n\r\n");
    $entetes_exemple = $coupure === false ? $message : substr($message, 0, $coupure);
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['essai_envoi'])) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Paramètres</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="assets/tailwind.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/tailwind.css'); ?>">
    <link rel="stylesheet" href="assets/icones.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/icones.css'); ?>">
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

                <!-- ── Envoi d'essai ─────────────────────────────────────────
                     Formulaire distinct : un formulaire imbriqué serait ignoré
                     par le navigateur, et surtout l'essai ne doit pas dépendre
                     de l'enregistrement des réglages. -->
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h3 class="text-base font-medium text-gray-900">Vérifier l'envoi</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Envoie un vrai message aux destinataires ci-dessus et rapporte, pour
                        chacun, la réponse du serveur de messagerie. C'est le moyen le plus
                        direct de savoir si la chaîne d'envoi fonctionne : contrairement au
                        formulaire public, rien n'est filtré et la réponse est affichée telle quelle.
                    </p>

                    <form method="POST" class="mt-4 sm:flex sm:items-end sm:gap-3">
                        <div class="flex-1">
                            <label for="essai_adresse" class="block text-sm font-medium text-gray-700">
                                Envoyer plutôt à cette adresse <span class="font-normal text-gray-500">(facultatif)</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500">
                                Laissez vide pour utiliser les destinataires ci-dessus. Pour faire noter
                                la qualité de vos envois, collez ici l'adresse jetable fournie par
                                <span class="font-mono">mail-tester.com</span>, puis retournez sur ce site
                                lire le résultat.
                            </p>
                            <input type="email" name="essai_adresse" id="essai_adresse"
                                placeholder="exemple : test-a1b2c3@srv1.mail-tester.com"
                                class="mt-2 focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border font-mono">
                        </div>
                        <button type="submit" name="essai_envoi" value="1"
                            class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Envoyer un message d'essai
                        </button>
                    </form>

                    <?php if ($essai): ?>
                        <ul class="mt-5 space-y-2">
                            <?php foreach ($essai as $ligne): ?>
                                <li class="flex items-start gap-3 rounded-md border px-4 py-3 text-sm <?php
                                    echo $ligne['ok'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50';
                                ?>">
                                    <span class="font-semibold <?php echo $ligne['ok'] ? 'text-green-700' : 'text-red-700'; ?>">
                                        <?php echo $ligne['ok'] ? '✔' : '✖'; ?>
                                    </span>
                                    <span>
                                        <span class="font-mono"><?php echo htmlspecialchars($ligne['adresse']); ?></span>
                                        — <?php echo htmlspecialchars($ligne['detail']); ?>
                                        <span class="text-gray-400">(<?php echo $ligne['duree']; ?> s)</span>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if (array_filter(array_column($essai, 'ok'))): ?>
                            <p class="mt-3 text-sm text-gray-500">
                                Une réponse en <span class="font-mono">250</span> signifie que le
                                message a bien quitté le site, et le <span class="font-mono">queued as …</span>
                                est son identifiant chez l'hébergeur. S'il n'arrive ni dans la boîte
                                ni dans les <strong>indésirables</strong>, communiquez cet identifiant
                                à l'assistance : lui seul permet de savoir ce que le message est
                                devenu après avoir quitté le serveur.
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- ── En-têtes du message ────────────────────────────── -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-base font-medium text-gray-900">Les en-têtes du message</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Ce que le site inscrit en tête de chaque message, sans rien envoyer.
                            À joindre à une réclamation lorsque l'hébergeur ou un service de
                            messagerie met en cause la conformité du message.
                        </p>

                        <form method="POST" class="mt-3">
                            <button type="submit" name="voir_entetes" value="1"
                                class="w-full sm:w-auto inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Afficher les en-têtes
                            </button>
                        </form>

                        <?php if ($entetes_alerte !== ''): ?>
                            <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                <?php echo htmlspecialchars($entetes_alerte); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($entetes_exemple !== ''): ?>
                            <pre class="mt-4 overflow-x-auto rounded-md bg-slate-900 text-green-300 text-xs p-4 leading-relaxed"><?php
                                echo htmlspecialchars($entetes_exemple, ENT_QUOTES, 'UTF-8');
                            ?></pre>
                            <ul class="mt-3 text-sm text-gray-600 space-y-1 list-disc list-inside">
                                <li>un seul <span class="font-mono">From:</span>, avec une adresse fixe du domaine ;</li>
                                <li>l'adresse du visiteur en <span class="font-mono">Reply-To:</span>, jamais en expéditeur ;</li>
                                <li><span class="font-mono">Date</span> et <span class="font-mono">Message-ID</span> présents, comme l'exige la RFC 5322 ;</li>
                                <li>le sujet accentué découpé en mots encodés d'au plus 75 caractères.</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-gray-400">
                Version en ligne : <?php echo htmlspecialchars(seo_version_texte() ?: 'inconnue'); ?>
                — à comparer au dernier enregistrement du dépôt si un réglage attendu n'apparaît pas.
            </p>
        </div>
    </div>
</body>

</html>