<?php
// admin/statistiques.php — Audience du site : volumes, provenance, pages vues.

session_start();
require_once '../config/db.php';
require_once '../config/stats.php';
require_once '../config/antispam.php';
require_once '../config/seo.php';   // pour seo_asset() : versionne les fichiers servis

// Les fichiers de la carte sont servis par le site lui-même. Le chemin est
// relatif à la racine, d'où le « ../ » depuis /admin/.
$carte_js  = '../' . seo_asset('js/vendor/jsvectormap/jsvectormap.min.js');
$carte_css = '../' . seo_asset('js/vendor/jsvectormap/jsvectormap.min.css');
$carte_monde = '../' . seo_asset('js/vendor/jsvectormap/world.js');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$tablesPretes = stats_migrer($pdo);

// Les pays en attente sont résolus par petits lots, à l'ouverture de l'écran :
// aucun appel réseau ne pèse ainsi sur la navigation des visiteurs.
$geo = $tablesPretes ? stats_resoudre_pays($pdo) : ['resolus' => 0, 'restants' => 0, 'erreur' => ''];

if (isset($_GET['purger'])) {
    $supprimees = stats_purger($pdo);
}

$periode = (int) ($_GET['periode'] ?? 30);
if (!in_array($periode, [7, 30, 90, 365], true)) $periode = 30;

$jour   = stats_synthese($pdo, 1);
$sept   = stats_synthese($pdo, 7);
$courant = stats_synthese($pdo, $periode);

$parJour     = stats_par_jour($pdo, min($periode, 90));
$formulaire  = antispam_bilan($pdo, $periode);
$parPays     = stats_par_pays($pdo, $periode);
$pages       = stats_classement($pdo, 'page', $periode);
$referents   = stats_classement($pdo, 'referent', $periode);
$appareils   = stats_classement($pdo, 'appareil', $periode);

$totalVues = array_sum(array_column($parPays, 'vues')) ?: 1;
$proches   = stats_pays_proches();

// Séparation France et voisins / reste du monde : c'est la lecture demandée.
$listeProches = [];
$listeAutres  = [];
foreach ($parPays as $p) {
    if ($p['pays'] !== null && isset($proches[$p['pays']])) $listeProches[] = $p;
    else $listeAutres[] = $p;
}

$valeursCarte = [];
foreach ($parPays as $p) {
    if ($p['pays']) $valeursCarte[$p['pays']] = (int) $p['vues'];
}

$maxJour = max(array_column($parJour, 'vues') ?: [0]);
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Statistiques - Administration</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo $e($carte_css); ?>">
    <style>
        #carteMonde { height: 420px; }
        .barre { background: linear-gradient(90deg, #b8912f, #e0c56a); }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center text-gray-500 hover:text-gray-900 mr-4">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900">Audience du site</h1>
                </div>
                <div class="flex gap-1 text-sm">
                    <?php foreach ([7 => '7 j', 30 => '30 j', 90 => '90 j', 365 => '1 an'] as $j => $lib): ?>
                        <a href="?periode=<?php echo $j; ?>"
                           class="px-3 py-1.5 rounded-md <?php echo $periode === $j
                               ? 'bg-gray-900 text-white'
                               : 'text-gray-600 hover:bg-gray-100'; ?>">
                            <?php echo $lib; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        <?php if (!$tablesPretes): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                Les tables de mesure n'ont pas pu être créées. Vérifiez les droits de la base de données.
            </div>
        <?php endif; ?>

        <?php if (isset($supprimees)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo (int) $supprimees; ?> visite(s) de plus de 13 mois supprimée(s).
            </div>
        <?php endif; ?>

        <!-- ── Chiffres de synthèse ─────────────────────────────────────── -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <?php
            $tuiles = [
                ["Aujourd'hui",              $jour['pages_vues'],    $jour['visiteurs'] . ' visiteur(s)'],
                ['7 derniers jours',         $sept['pages_vues'],    $sept['visiteurs'] . ' visiteur(s)'],
                ['Sur ' . $periode . ' jours', $courant['pages_vues'], $courant['visiteurs'] . ' visiteur(s)'],
                ['Pays représentés',         $courant['pays'],       'sur la période'],
            ];
            foreach ($tuiles as [$titre, $valeur, $detail]): ?>
                <div class="bg-white shadow rounded-lg px-5 py-4">
                    <p class="text-xs uppercase tracking-wider text-gray-500"><?php echo $e($titre); ?></p>
                    <p class="text-3xl font-semibold text-gray-900 mt-1"><?php echo number_format((int) $valeur, 0, ',', ' '); ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?php echo $e($detail); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Carte du monde ───────────────────────────────────────────── -->
        <div class="bg-white shadow rounded-lg p-5 mb-8">
            <div class="flex items-baseline justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-900">D'où viennent les visiteurs</h2>
                <span class="text-xs text-gray-400">Pages vues sur <?php echo $periode; ?> jours</span>
            </div>

            <div id="carteMonde"></div>

            <div id="carteRepli" hidden class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-md px-4 py-6 text-center">
                La carte n'a pas pu s'afficher. Vérifiez que le dossier
                <code>js/vendor/jsvectormap/</code> a bien été téléversé sur le serveur.
                Le détail par pays reste disponible ci-dessous.
            </div>

            <?php if ($geo['restants'] > 0 || $geo['resolus'] > 0 || $geo['erreur']): ?>
                <p class="mt-3 text-xs text-gray-400">
                    <?php if ($geo['resolus']): ?>
                        <?php echo (int) $geo['resolus']; ?> origine(s) identifiée(s) à l'ouverture de cette page.
                    <?php endif; ?>
                    <?php if ($geo['restants']): ?>
                        <?php echo (int) $geo['restants']; ?> encore en attente — rechargez la page pour continuer.
                    <?php endif; ?>
                    <?php if ($geo['erreur']): ?>
                        <span class="text-amber-600">Service de géolocalisation : <?php echo $e($geo['erreur']); ?></span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="grid lg:grid-cols-2 gap-6 mb-8">

            <!-- ── France et pays limitrophes ───────────────────────────── -->
            <div class="bg-white shadow rounded-lg p-5">
                <h2 class="text-base font-semibold text-gray-900 mb-1">France et pays limitrophes</h2>
                <p class="text-xs text-gray-400 mb-4">Le cœur de la clientèle du gîte</p>

                <?php if (!$listeProches): ?>
                    <p class="text-sm text-gray-500 py-4">Aucune visite identifiée pour l'instant.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($listeProches as $p): ?>
                            <?php $part = round($p['vues'] * 100 / $totalVues); ?>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-800 font-medium"><?php echo $e(stats_nom_pays($p['pays'])); ?></span>
                                    <span class="text-gray-500">
                                        <?php echo number_format((int) $p['vues'], 0, ',', ' '); ?> vues
                                        <span class="text-gray-400">· <?php echo $part; ?> %</span>
                                    </span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="barre h-full rounded-full" style="width: <?php echo max(2, $part); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ── Reste du monde ───────────────────────────────────────── -->
            <div class="bg-white shadow rounded-lg p-5">
                <h2 class="text-base font-semibold text-gray-900 mb-1">Reste du monde</h2>
                <p class="text-xs text-gray-400 mb-4">Toutes les autres origines</p>

                <?php if (!$listeAutres): ?>
                    <p class="text-sm text-gray-500 py-4">Aucune visite hors de la zone proche.</p>
                <?php else: ?>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach (array_slice($listeAutres, 0, 12) as $p): ?>
                                <tr>
                                    <td class="py-2 text-gray-800"><?php echo $e(stats_nom_pays($p['pays'])); ?></td>
                                    <td class="py-2 text-right text-gray-500">
                                        <?php echo number_format((int) $p['vues'], 0, ',', ' '); ?> vues
                                    </td>
                                    <td class="py-2 text-right text-gray-400 w-16">
                                        <?php echo round($p['vues'] * 100 / $totalVues); ?> %
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Courbe des visites ───────────────────────────────────────── -->
        <div class="bg-white shadow rounded-lg p-5 mb-8">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Visites jour par jour</h2>
            <?php if (!$parJour || $maxJour === 0): ?>
                <p class="text-sm text-gray-500 py-4">Pas encore de données. Les visites apparaîtront ici dès la mise en ligne.</p>
            <?php else: ?>
                <div class="flex items-end gap-[2px] h-40">
                    <?php foreach ($parJour as $j => $d): ?>
                        <div class="flex-1 bg-gray-100 hover:bg-gray-200 rounded-t relative group"
                             style="height: <?php echo max(2, round($d['vues'] * 100 / $maxJour)); ?>%"
                             title="<?php echo date('d/m/Y', strtotime($j)); ?> — <?php echo (int) $d['vues']; ?> vues, <?php echo (int) $d['visiteurs']; ?> visiteurs">
                            <div class="barre absolute inset-0 rounded-t opacity-80"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="flex justify-between text-xs text-gray-400 mt-2">
                    <span><?php echo date('d/m/Y', strtotime(array_key_first($parJour))); ?></span>
                    <span>Maximum : <?php echo (int) $maxJour; ?> vues</span>
                    <span><?php echo date('d/m/Y', strtotime(array_key_last($parJour))); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Détail ───────────────────────────────────────────────────── -->
        <div class="grid lg:grid-cols-3 gap-6">
            <?php
            $blocs = [
                ['Pages les plus vues', $pages, 'page'],
                ['Sites référents',     $referents, 'referent'],
                ['Appareils',           $appareils, 'appareil'],
            ];
            foreach ($blocs as [$titre, $donnees, $type]): ?>
                <div class="bg-white shadow rounded-lg p-5">
                    <h2 class="text-base font-semibold text-gray-900 mb-4"><?php echo $e($titre); ?></h2>
                    <?php if (!$donnees): ?>
                        <p class="text-sm text-gray-500">
                            <?php echo $type === 'referent'
                                ? 'Aucun site référent : les visiteurs arrivent en direct.'
                                : 'Pas encore de données.'; ?>
                        </p>
                    <?php else: ?>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($donnees as $d): ?>
                                    <tr>
                                        <td class="py-2 text-gray-800 truncate max-w-[180px]" title="<?php echo $e($d['cle']); ?>">
                                            <?php echo $e($d['cle'] === '/' ? "Accueil" : $d['cle']); ?>
                                        </td>
                                        <td class="py-2 text-right text-gray-500"><?php echo number_format((int) $d['vues'], 0, ',', ' '); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Formulaire de réservation ────────────────────────────────── -->
        <div class="bg-white shadow rounded-lg p-5 mt-6">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Demandes reçues par le formulaire</h2>
            <p class="text-sm text-gray-500 mb-4">
                Sur <?php echo (int) $periode; ?> jours. Les demandes écartées n'ont déclenché
                aucun envoi : elles ne consomment pas la boîte d'envoi du gîte.
            </p>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="rounded-md bg-gray-50 p-4">
                    <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($formulaire['acceptes'], 0, ',', ' '); ?></p>
                    <p class="text-sm text-gray-600">demandes transmises</p>
                </div>
                <div class="rounded-md bg-gray-50 p-4">
                    <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($formulaire['refuses'], 0, ',', ' '); ?></p>
                    <p class="text-sm text-gray-600">écartées automatiquement</p>
                </div>
            </div>
            <?php if ($formulaire['motifs']): ?>
                <table class="w-full text-sm mt-4">
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($formulaire['motifs'] as $motif): ?>
                            <tr>
                                <td class="py-2 text-gray-800"><?php echo $e(ucfirst((string) $motif['motif'])); ?></td>
                                <td class="py-2 text-right text-gray-500"><?php echo number_format((int) $motif['n'], 0, ',', ' '); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif (!$formulaire['refuses']): ?>
                <p class="text-sm text-gray-500 mt-4">Aucune tentative écartée sur la période.</p>
            <?php endif; ?>
        </div>

        <p class="mt-8 text-xs text-gray-400 leading-relaxed">
            Mesure interne, sans cookie ni traceur tiers : aucun bandeau de consentement n'est requis.
            L'adresse IP n'est jamais conservée en entier — seul le préfixe réseau l'est, le temps
            d'identifier le pays. Les visites de plus de 13 mois sont supprimées.
            <a href="?periode=<?php echo $periode; ?>&amp;purger=1" class="text-gray-500 underline">Purger maintenant</a>
        </p>
    </div>

    <!-- La carte était chargée depuis un réseau de diffusion externe, et ne
         s'affichait pas quand celui-ci était injoignable. Les deux fichiers
         sont désormais servis par le site : plus aucune dépendance extérieure,
         et aucune requête vers un tiers depuis l'administration.
         jsVectorMap 1.7.0, licence MIT — voir js/vendor/jsvectormap/LICENSE. -->
    <script src="<?php echo $e($carte_js); ?>"></script>
    <script src="<?php echo $e($carte_monde); ?>"></script>
    <script>
    (function () {
        var valeurs = <?php echo json_encode($valeursCarte, JSON_UNESCAPED_UNICODE); ?>;

        // La carte est servie par le site, mais un fichier absent après un
        // téléversement partiel reste possible : on bascule alors sur le repli
        // plutôt que de laisser une zone vide.
        // L'attribut « hidden » est natif : le repli reste correct même si la
        // feuille de style externe de l'administration n'a pas été chargée.
        function replier() {
            document.getElementById('carteMonde').hidden = true;
            document.getElementById('carteRepli').hidden = false;
        }

        if (typeof jsVectorMap === 'undefined') { replier(); return; }

        try {
            new jsVectorMap({
                selector: '#carteMonde',
                map: 'world',
                zoomButtons: true,
                regionStyle: {
                    initial: { fill: '#e9e7e2', stroke: '#ffffff', strokeWidth: 0.6 },
                    hover:   { fill: '#c9b271' }
                },
                // Dégradé du plus clair au plus soutenu, selon le nombre de
                // pages vues. Les pays sans visite gardent le gris initial.
                visualizeData: {
                    scale: ['#efe9dc', '#b8912f'],
                    values: valeurs
                },
                onRegionTooltipShow: function (event, tooltip, code) {
                    var n = valeurs[code] || 0;
                    tooltip.text(tooltip.text() + ' — ' + n + (n > 1 ? ' pages vues' : ' page vue'), true);
                }
            });
        } catch (e) {
            replier();
        }
    })();
    </script>
</body>

</html>
