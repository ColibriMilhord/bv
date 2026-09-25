<?php
// admin/annonces.php — Publier un bandeau d'information sur le site public.

session_start();
require_once '../config/db.php';
require_once '../config/annonces.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$tablePrete = annonces_migrer($pdo);
$message    = '';
$succes     = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'enregistrer';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($action === 'activer') {
        $succes  = annonce_activer($pdo, $id);
        $message = $succes ? "Annonce publiée sur le site." : "Publication impossible.";
    } elseif ($action === 'desactiver') {
        $succes  = annonce_desactiver($pdo, $id);
        $message = $succes ? "Annonce retirée du site. Elle reste enregistrée." : "Retrait impossible.";
    } elseif ($action === 'supprimer') {
        $succes  = annonce_supprimer($pdo, $id);
        $message = $succes ? "Annonce supprimée." : "Suppression impossible.";
    } else {
        [$succes, $message] = annonce_enregistrer($pdo, $_POST);
    }

    // Redirection après écriture : évite qu'un rafraîchissement rejoue l'action.
    $_SESSION['annonce_message'] = [$succes, $message];
    header('Location: annonces.php');
    exit;
}

if (!empty($_SESSION['annonce_message'])) {
    [$succes, $message] = $_SESSION['annonce_message'];
    unset($_SESSION['annonce_message']);
}

$edition  = annonce_par_id($pdo, (int) ($_GET['modifier'] ?? 0));
$annonces = annonces_liste($pdo);
$e        = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Annonces - Administration</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/tailwind.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/tailwind.css'); ?>">
    <link rel="stylesheet" href="assets/icones.css?v=<?php echo (int) @filemtime(__DIR__ . '/assets/icones.css'); ?>">
</head>

<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center text-gray-500 hover:text-gray-900 mr-4">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900">Annonces du site</h1>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto py-10 px-4">

        <?php if ($message): ?>
            <div class="<?php echo $succes ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> border px-4 py-3 rounded mb-6">
                <?php echo $e($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!$tablePrete): ?>
            <div class="bg-amber-50 border border-amber-300 text-amber-800 px-4 py-3 rounded mb-6">
                La table des annonces n'a pas pu être créée. Vérifiez les droits de la base de données.
            </div>
        <?php endif; ?>

        <!-- ── Formulaire ───────────────────────────────────────────────── -->
        <div class="bg-white shadow sm:rounded-lg mb-10">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    <?php echo $edition ? 'Modifier l\'annonce' : 'Nouvelle annonce'; ?>
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Le bandeau apparaît en bas de page sur le site, après une courte
                    temporisation. Le visiteur peut le refermer d'un clic sur « J'ai vu » ;
                    il ne le reverra plus, sauf si vous publiez une nouvelle annonce.
                </p>

                <form method="POST" class="mt-6 space-y-5">
                    <input type="hidden" name="action" value="enregistrer">
                    <input type="hidden" name="id" value="<?php echo (int) ($edition['id'] ?? 0); ?>">

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label for="titre" class="block text-sm font-medium text-gray-700">Titre</label>
                            <input type="text" name="titre" id="titre" required maxlength="120"
                                placeholder="Dernière semaine de juillet disponible"
                                value="<?php echo $e($edition['titre'] ?? ''); ?>"
                                class="mt-1 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="ton" class="block text-sm font-medium text-gray-700">Étiquette</label>
                            <select name="ton" id="ton"
                                class="mt-1 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach (ANNONCE_TONS as $cle => $libelle): ?>
                                    <option value="<?php echo $e($cle); ?>"
                                        <?php echo ($edition['ton'] ?? 'info') === $cle ? 'selected' : ''; ?>>
                                        <?php echo $e($libelle); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea name="message" id="message" rows="3" required maxlength="400"
                            placeholder="Du 11 au 18 juillet, 2 800 € au lieu de 3 200 €. Ménage de fin de séjour offert."
                            class="mt-1 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border focus:ring-blue-500 focus:border-blue-500"><?php echo $e($edition['message'] ?? ''); ?></textarea>
                        <p class="mt-1 text-xs text-gray-500">400 caractères au maximum. Deux phrases valent mieux que dix.</p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="lien_libelle" class="block text-sm font-medium text-gray-700">Bouton (facultatif)</label>
                            <input type="text" name="lien_libelle" id="lien_libelle" maxlength="60"
                                placeholder="Voir les disponibilités"
                                value="<?php echo $e($edition['lien_libelle'] ?? ''); ?>"
                                class="mt-1 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="lien_url" class="block text-sm font-medium text-gray-700">Destination du bouton</label>
                            <input type="text" name="lien_url" id="lien_url" maxlength="255"
                                placeholder="#reservation"
                                value="<?php echo $e($edition['lien_url'] ?? ''); ?>"
                                class="mt-1 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500"><code>#reservation</code>, <code>#tarifs</code> ou une adresse complète.</p>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="date_debut" class="block text-sm font-medium text-gray-700">Afficher à partir du</label>
                            <input type="date" name="date_debut" id="date_debut"
                                value="<?php echo $e($edition['date_debut'] ?? ''); ?>"
                                class="mt-1 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="date_fin" class="block text-sm font-medium text-gray-700">Retirer après le</label>
                            <input type="date" name="date_fin" id="date_fin"
                                value="<?php echo $e($edition['date_fin'] ?? ''); ?>"
                                class="mt-1 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 -mt-2">
                        Dates facultatives : l'annonce apparaît et disparaît toute seule.
                    </p>

                    <label class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-md px-4 py-3 cursor-pointer">
                        <input type="checkbox" name="active" value="1"
                            <?php echo !empty($edition['active']) ? 'checked' : ''; ?>
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">
                            <strong>Publier sur le site</strong> — les autres annonces seront retirées.
                        </span>
                    </label>

                    <div class="flex justify-end gap-3 pt-2">
                        <?php if ($edition): ?>
                            <a href="annonces.php"
                               class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Annuler
                            </a>
                        <?php endif; ?>
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <?php echo $edition ? 'Enregistrer les modifications' : 'Créer l\'annonce'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Liste ────────────────────────────────────────────────────── -->
        <h3 class="text-lg font-medium text-gray-900 mb-4">Annonces enregistrées</h3>

        <?php if (!$annonces): ?>
            <div class="bg-white shadow sm:rounded-lg px-4 py-8 text-center text-gray-500 text-sm">
                Aucune annonce pour le moment. Créez-en une avec le formulaire ci-dessus.
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($annonces as $a): ?>
                    <?php $visible = annonce_est_visible($a); ?>
                    <div class="bg-white shadow sm:rounded-lg px-4 py-4 sm:px-6 flex flex-wrap gap-4 items-start justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="text-xs uppercase tracking-wider px-2 py-0.5 rounded-full border border-gray-300 text-gray-600">
                                    <?php echo $e(ANNONCE_TONS[$a['ton']] ?? 'Information'); ?>
                                </span>
                                <?php if ($visible): ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-800 font-medium">En ligne</span>
                                <?php elseif (!empty($a['active'])): ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-medium">Publiée, hors période</span>
                                <?php else: ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Brouillon</span>
                                <?php endif; ?>
                                <span class="text-xs text-gray-400"><?php echo $e(annonce_periode($a)); ?></span>
                            </div>
                            <p class="font-medium text-gray-900"><?php echo $e($a['titre']); ?></p>
                            <p class="text-sm text-gray-600 mt-0.5"><?php echo $e($a['message']); ?></p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="?modifier=<?php echo (int) $a['id']; ?>"
                               class="text-sm px-3 py-1.5 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Modifier
                            </a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="id" value="<?php echo (int) $a['id']; ?>">
                                <input type="hidden" name="action" value="<?php echo !empty($a['active']) ? 'desactiver' : 'activer'; ?>">
                                <button type="submit"
                                    class="text-sm px-3 py-1.5 rounded-md <?php echo !empty($a['active'])
                                        ? 'border border-gray-300 text-gray-700 hover:bg-gray-50'
                                        : 'bg-blue-600 text-white hover:bg-blue-700'; ?>">
                                    <?php echo !empty($a['active']) ? 'Retirer' : 'Publier'; ?>
                                </button>
                            </form>
                            <form method="POST" class="inline"
                                  onsubmit="return confirm('Supprimer définitivement cette annonce ?');">
                                <input type="hidden" name="id" value="<?php echo (int) $a['id']; ?>">
                                <input type="hidden" name="action" value="supprimer">
                                <button type="submit" class="text-sm px-2 py-1.5 rounded-md text-red-600 hover:bg-red-50">
                                    <span class="material-symbols-outlined text-base align-middle">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
