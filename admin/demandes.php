<?php
// admin/demandes.php — Toutes les demandes reçues par le formulaire.
//
// Le courriel n'est qu'un messager, et il peut se taire : c'est arrivé dix
// jours durant sans que personne ne s'en aperçoive. Cette page est la trace
// qui ne dépend de rien — ni de la boîte d'envoi, ni de l'hébergeur, ni du
// dossier des indésirables. Toute demande enregistrée y figure, notifiée ou
// non, avec ce qu'il faut pour rappeler le client dans la minute.

session_start();
require_once '../config/db.php';
require_once '../config/notifications.php';
require_once '../config/demandes.php';
require_once '../config/seo.php';   // pour seo_version_texte()

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Les colonnes que le site écrit se créent seules à l'ouverture de cet écran.
$suivi = demandes_migrer($pdo);

// ── Suppression ──
// En POST, et avec un jeton : une adresse qu'il suffirait de visiter pour
// effacer une demande serait déclenchée par n'importe quel aperçu de lien.
if (empty($_SESSION['jeton_demandes'])) {
    $_SESSION['jeton_demandes'] = bin2hex(random_bytes(16));
}

$avis = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'oublier-perdues') {
    if (!hash_equals($_SESSION['jeton_demandes'], (string) ($_POST['jeton'] ?? ''))) {
        $_SESSION['avis_demandes'] = "La liste n'a pas été vidée : la page avait expiré. Réessayez.";
    } else {
        $_SESSION['avis_demandes'] = demandes_perdues_oublier()
            ? 'Liste des demandes refusées vidée.'
            : "La liste n'a pas pu être vidée.";
    }
    header('Location: demandes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    if (!hash_equals($_SESSION['jeton_demandes'], (string) ($_POST['jeton'] ?? ''))) {
        $avis = "La demande n'a pas été effacée : la page avait expiré. Réessayez.";
    } elseif (demandes_supprimer($pdo, (int) ($_POST['id'] ?? 0))) {
        $avis = 'Demande effacée.';
    } else {
        $avis = "Cette demande n'a pas pu être effacée.";
    }

    // Redirection après écriture : un rafraîchissement ne rejoue pas la
    // suppression.
    $_SESSION['avis_demandes'] = $avis;
    $vers = 'demandes.php';
    if (!empty($_POST['filtre'])) {
        $vers .= '?filtre=' . rawurlencode((string) $_POST['filtre']);
    }
    header('Location: ' . $vers);
    exit;
}

if (!empty($_SESSION['avis_demandes'])) {
    $avis = (string) $_SESSION['avis_demandes'];
    unset($_SESSION['avis_demandes']);
}

// Demandes que la base avait refusées, relues du fichier de secours.
$perdues = demandes_perdues();

$filtre = $_GET['filtre'] ?? 'toutes';
if (!in_array($filtre, ['toutes', 'attente', 'non-notifiees'], true)) $filtre = 'toutes';

$condition = '';
if ($filtre === 'attente')            $condition = "WHERE statut = 'attente'";
elseif ($filtre === 'non-notifiees')  $condition = $suivi ? "WHERE notifie = 0" : '';

$demandes = [];
$compte   = ['toutes' => 0, 'attente' => 0, 'non_notifiees' => 0];

try {
    $demandes = $pdo->query(
        "SELECT * FROM reservations $condition ORDER BY created_at DESC, id DESC LIMIT 200"
    )->fetchAll();

    $compte['toutes']  = (int) $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    $compte['attente'] = (int) $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'attente'")->fetchColumn();
    if ($suivi) {
        $compte['non_notifiees'] = (int) $pdo->query("SELECT COUNT(*) FROM reservations WHERE notifie = 0")->fetchColumn();
    }
} catch (Throwable $e) {
    $erreur = $e->getMessage();
}

$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

/** « samedi 1er août 2026 », ou une chaîne vide. */
$jolie_date = function (?string $iso): string {
    if (!$iso) return '';
    $d = date_create($iso);
    if (!$d) return '';

    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois  = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
              'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $j = (int) $d->format('j');

    return $jours[(int) $d->format('w')] . ' ' . ($j === 1 ? '1er' : $j) . ' '
         . $mois[(int) $d->format('n')] . ' ' . $d->format('Y');
};

/** Le libellé stocké en base, écrit comme on l'écrirait à la main. */
$etat_lisible = function (?string $statut): string {
    $mots = [
        'attente'   => 'en attente',
        'validee'   => 'validée',
        'confirmee' => 'confirmée',
        'annulee'   => 'annulée',
        'refusee'   => 'refusée',
    ];
    $statut = (string) $statut;
    return $mots[$statut] ?? ($statut !== '' ? $statut : '—');
};

$nuits = function (?string $a, ?string $b): int {
    $d1 = $a ? date_create($a) : null;
    $d2 = $b ? date_create($b) : null;
    return ($d1 && $d2) ? max(0, (int) $d1->diff($d2)->days) : 0;
};
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Demandes reçues — Administration</title>
    <link rel="stylesheet" href="assets/tailwind.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/tailwind.css'); ?>">
    <link rel="stylesheet" href="assets/icones.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/icones.css'); ?>">
</head>

<body class="bg-slate-50 min-h-screen">

    <nav class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center h-16">
                <a href="index.php" class="flex items-center text-gray-500 hover:text-gray-900 mr-3 shrink-0">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h1 class="text-base sm:text-xl font-bold text-gray-900 truncate">Demandes reçues</h1>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto py-6 sm:py-10 px-4 sm:px-6">

        <p class="text-sm text-slate-500 mb-5 leading-relaxed">
            Toutes les demandes envoyées depuis le formulaire du site, la plus récente en tête.
            Cette liste ne dépend pas du courriel&nbsp;: même si les notifications ne partent plus,
            rien ne se perd.
        </p>

        <?php if ($avis !== ''): ?>
            <div class="mb-5 rounded-md border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                <?php echo $e($avis); ?>
            </div>
        <?php endif; ?>

        <?php if (!$suivi): ?>
            <div class="mb-5 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Le suivi des notifications n'a pas pu être installé dans la base&nbsp;: les demandes
                s'affichent, mais sans indiquer si elles vous ont été envoyées.
            </div>
        <?php endif; ?>

        <?php if ($compte['non_notifiees'] > 0): ?>
            <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <strong><?php echo $compte['non_notifiees']; ?></strong>
                demande<?php echo $compte['non_notifiees'] > 1 ? 's' : ''; ?>
                n'<?php echo $compte['non_notifiees'] > 1 ? 'ont' : 'a'; ?> pas pu vous être
                envoyée<?php echo $compte['non_notifiees'] > 1 ? 's' : ''; ?> par courriel.
                Traitez-<?php echo $compte['non_notifiees'] > 1 ? 'les' : 'la'; ?> depuis cette page.
            </div>
        <?php endif; ?>

        <!-- ── Filtres ───────────────────────────────────────────────────── -->
        <div class="flex flex-wrap gap-2 mb-6">
            <?php
            $onglets = [
                'toutes'        => ['Toutes', $compte['toutes']],
                'attente'       => ['En attente', $compte['attente']],
                'non-notifiees' => ['Non notifiées', $compte['non_notifiees']],
            ];
            foreach ($onglets as $cle => [$libelle, $n]):
                $actif = $filtre === $cle;
            ?>
                <a href="?filtre=<?php echo $cle; ?>"
                   class="rounded-full px-4 py-1.5 text-sm font-medium border transition <?php
                       echo $actif
                           ? 'bg-slate-900 text-white border-slate-900'
                           : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400';
                   ?>">
                    <?php echo $e($libelle); ?>
                    <span class="<?php echo $actif ? 'text-slate-300' : 'text-slate-400'; ?>"><?php echo (int) $n; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($perdues): ?>
            <section class="mb-6 rounded-lg border border-red-200 bg-red-50 overflow-hidden">
                <div class="px-4 sm:px-5 py-4 border-b border-red-200">
                    <h2 class="text-sm font-semibold text-red-900">
                        <?php echo count($perdues); ?>
                        demande<?php echo count($perdues) > 1 ? 's' : ''; ?>
                        que la base a refusée<?php echo count($perdues) > 1 ? 's' : ''; ?>
                    </h2>
                    <p class="mt-1 text-sm text-red-800">
                        Elles n'ont pas pu être écrites dans la base, mais rien n'est perdu :
                        le site les a recopiées dans un fichier. Rappelez ces clients, puis
                        signalez-le — le défaut qui les a bloquées est corrigé de lui-même
                        à la première demande suivante.
                    </p>
                </div>

                <?php foreach ($perdues as $p): ?>
                    <div class="px-4 sm:px-5 py-4 border-b border-red-100 last:border-0 text-sm">
                        <p class="font-semibold text-slate-900"><?php echo $e($p['nom'] ?? 'sans nom'); ?></p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Reçue le <?php echo $e(isset($p['quand']) ? date('d/m/Y à H\hi', strtotime($p['quand'])) : '?'); ?>
                        </p>

                        <?php if (!empty($p['email'])): ?>
                            <p class="mt-2">
                                <a href="mailto:<?php echo $e($p['email']); ?>"
                                   class="text-blue-600 hover:underline"><?php echo $e($p['email']); ?></a>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($p['telephone'])): ?>
                            <p>
                                <a href="tel:<?php echo $e(preg_replace('/[^0-9+]/', '', (string) $p['telephone'])); ?>"
                                   class="text-blue-600 hover:underline"><?php echo $e($p['telephone']); ?></a>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($p['date_debut']) && !empty($p['date_fin'])): ?>
                            <p class="mt-2 text-slate-700">
                                Du <strong><?php echo $e($jolie_date($p['date_debut'])); ?></strong>
                                au <strong><?php echo $e($jolie_date($p['date_fin'])); ?></strong>
                            </p>
                        <?php else: ?>
                            <p class="mt-2 text-slate-500 italic">Demande d'information — aucune date précisée.</p>
                        <?php endif; ?>

                        <?php if (trim((string) ($p['message'] ?? '')) !== ''): ?>
                            <div class="mt-2 rounded-md bg-white border-l-2 border-red-300 px-3 py-2 text-slate-700 leading-relaxed">
                                <?php echo nl2br($e(html_entity_decode((string) $p['message'], ENT_QUOTES, 'UTF-8'))); ?>
                            </div>
                        <?php endif; ?>

                        <p class="mt-2 text-xs text-slate-400 font-mono break-all">
                            <?php echo $e((string) ($p['motif'] ?? '')); ?>
                        </p>
                    </div>
                <?php endforeach; ?>

                <div class="px-4 sm:px-5 py-3">
                    <form method="post"
                          onsubmit="return confirm('Vider cette liste ? Ces coordonnées ne seront plus consultables ici.');">
                        <input type="hidden" name="action" value="oublier-perdues">
                        <input type="hidden" name="jeton" value="<?php echo $e($_SESSION['jeton_demandes']); ?>">
                        <button type="submit" class="text-sm font-medium text-red-700 hover:underline">
                            J'ai rappelé ces clients — vider la liste
                        </button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!$demandes): ?>
            <div class="rounded-lg border border-slate-200 bg-white px-5 py-10 text-center text-sm text-slate-500">
                Aucune demande dans cette sélection.
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($demandes as $d):
                    $avec_dates = !empty(($d['date_debut'] ?? null)) && !empty(($d['date_fin'] ?? null));
                    $n = $avec_dates ? $nuits(($d['date_debut'] ?? null), ($d['date_fin'] ?? null)) : 0;
                    $tel_brut = preg_replace('/[^0-9+]/', '', (string) ($d['client_tel'] ?? ''));
                ?>
                <article class="rounded-lg border border-slate-200 bg-white overflow-hidden">

                    <!-- Bandeau : identité et état -->
                    <div class="flex flex-wrap items-start justify-between gap-3 px-4 sm:px-5 py-4 border-b border-slate-100">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-slate-900 truncate">
                                <?php echo $e(($d['client_nom'] ?? '')); ?>
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Reçue le <?php echo $e(date('d/m/Y à H\\hi', strtotime((string) ($d['created_at'] ?? '')))); ?>
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <?php if ($suivi && isset($d['notifie']) && (int) $d['notifie'] === 0): ?>
                                <span class="rounded-full bg-red-100 text-red-700 px-2.5 py-0.5 text-xs font-medium">
                                    non notifiée
                                </span>
                            <?php endif; ?>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium <?php
                                echo ($d['statut'] ?? '') === 'validee'
                                    ? 'bg-green-100 text-green-700'
                                    : (($d['statut'] ?? '') === 'attente' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-600');
                            ?>"><?php echo $e($etat_lisible(($d['statut'] ?? ''))); ?></span>
                        </div>
                    </div>

                    <!-- Coordonnées : cliquables, c'est ce qui sert en premier -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 px-4 sm:px-5 py-4 text-sm">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="material-symbols-outlined text-base text-slate-400 shrink-0">mail</span>
                            <a href="mailto:<?php echo $e(($d['client_email'] ?? '')); ?>"
                               class="text-blue-600 hover:underline truncate"><?php echo $e(($d['client_email'] ?? '')); ?></a>
                        </div>
                        <?php if ($tel_brut !== ''): ?>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-slate-400 shrink-0">call</span>
                            <a href="tel:<?php echo $e($tel_brut); ?>"
                               class="text-blue-600 hover:underline"><?php echo $e(($d['client_tel'] ?? '')); ?></a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Séjour -->
                    <div class="px-4 sm:px-5 pb-4 text-sm text-slate-700">
                        <?php if ($avec_dates): ?>
                            <p>
                                Du <strong><?php echo $e($jolie_date(($d['date_debut'] ?? null))); ?></strong>
                                au <strong><?php echo $e($jolie_date(($d['date_fin'] ?? null))); ?></strong>
                                <span class="text-slate-400">— <?php echo $n; ?> nuits</span>
                            </p>
                            <p class="text-slate-500 mt-1">
                                <?php if ((float) ($d['prix_total'] ?? 0) > 0): ?>
                                    Montant estimé <?php echo number_format((float) ($d['prix_total'] ?? 0), 0, ',', ' '); ?> €
                                    <?php if ((float) ($d['acompte_montant'] ?? 0) > 0): ?>
                                        · acompte <?php echo number_format((float) ($d['acompte_montant'] ?? 0), 0, ',', ' '); ?> €
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (!empty(($d['option_menage'] ?? 0))): ?>
                                    · ménage demandé
                                <?php endif; ?>
                            </p>
                        <?php else: ?>
                            <p class="text-slate-500 italic">Demande d'information — aucune date précisée.</p>
                        <?php endif; ?>
                    </div>

                    <?php if (trim((string) ($d['client_message'] ?? '')) !== ''): ?>
                        <div class="mx-4 sm:mx-5 mb-4 rounded-md bg-slate-50 border-l-2 border-slate-300 px-4 py-3 text-sm text-slate-700 leading-relaxed">
                            <?php echo nl2br($e(html_entity_decode((string) ($d['client_message'] ?? ''), ENT_QUOTES, 'UTF-8'))); ?>
                        </div>
                    <?php endif; ?>

                    <div class="px-4 sm:px-5 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <a href="mailto:<?php echo $e(($d['client_email'] ?? '')); ?>?subject=<?php
                               echo rawurlencode("Votre séjour à Bellevue d'Aveyron");
                           ?>" class="text-sm font-medium text-blue-600 hover:underline">
                            Répondre au client
                        </a>

                        <form method="post" class="shrink-0"
                              onsubmit="return confirm('Effacer définitivement la demande de <?php
                                  echo $e(addslashes((string) ($d['client_nom'] ?? '')));
                              ?> ? Cette page est la seule trace qui en reste.');">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="id" value="<?php echo (int) ($d['id'] ?? 0); ?>">
                            <input type="hidden" name="filtre" value="<?php echo $e($filtre); ?>">
                            <input type="hidden" name="jeton" value="<?php echo $e($_SESSION['jeton_demandes']); ?>">
                            <button type="submit"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-slate-400 hover:text-red-600 transition">
                                <span class="material-symbols-outlined text-base">delete</span>
                                Effacer
                            </button>
                        </form>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <?php if (count($demandes) >= 200): ?>
                <p class="mt-6 text-xs text-slate-400 text-center">
                    Les 200 demandes les plus récentes sont affichées.
                </p>
            <?php endif; ?>
        <?php endif; ?>

        <p class="mt-8 text-center text-xs text-slate-400">
            Version en ligne : <?php echo $e(seo_version_texte() ?: 'inconnue'); ?>
        </p>
    </div>
</body>

</html>
