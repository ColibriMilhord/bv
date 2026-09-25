<?php
// admin/tarifs.php
session_start();
require_once '../config/db.php';
require_once '../config/tarifs.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// La colonne « categorie » se crée d'elle-même au premier passage.
$colonne_saison = tarifs_migrer($pdo);

// Delete Action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM tarifs_saison WHERE id = ?")->execute([$id]);
    header('Location: tarifs.php');
    exit;
}

// Init Form Variables
$edit_mode = false;
$categorie_edit = '';
$id_edit = null;
$nom_edit = '';
$debut_edit = '';
$fin_edit = '';
$prix_edit = '';

// Edit Action (Load Data)
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id_edit = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM tarifs_saison WHERE id = ?");
    $stmt->execute([$id_edit]);
    $tarif_edit = $stmt->fetch();

    if ($tarif_edit) {
        $nom_edit = $tarif_edit['nom_saison'];
        $debut_edit = $tarif_edit['date_debut'];
        $fin_edit = $tarif_edit['date_fin'];
        $prix_edit = $tarif_edit['prix_semaine'];
        $categorie_edit = $tarif_edit['categorie'] ?? '';
    }
}

// Form Submission (Add or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom_saison'];
    $debut = $_POST['date_debut'];
    $fin = $_POST['date_fin'];
    $prix = floatval($_POST['prix_semaine']);

    // Saison : vide = classement automatique d'après le prix.
    $categorie = (string) ($_POST['categorie'] ?? '');
    if (!isset(tarifs_saisons()[$categorie])) $categorie = null;

    if (!empty($_POST['id'])) {
        // UPDATE
        $id = intval($_POST['id']);
        if ($colonne_saison) {
            $stmt = $pdo->prepare("UPDATE tarifs_saison SET nom_saison=?, date_debut=?, date_fin=?, prix_semaine=?, categorie=? WHERE id=?");
            $stmt->execute([$nom, $debut, $fin, $prix, $categorie, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE tarifs_saison SET nom_saison=?, date_debut=?, date_fin=?, prix_semaine=? WHERE id=?");
            $stmt->execute([$nom, $debut, $fin, $prix, $id]);
        }
    } else {
        // INSERT
        if ($colonne_saison) {
            $stmt = $pdo->prepare("INSERT INTO tarifs_saison (nom_saison, date_debut, date_fin, prix_semaine, categorie) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $debut, $fin, $prix, $categorie]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO tarifs_saison (nom_saison, date_debut, date_fin, prix_semaine) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $debut, $fin, $prix]);
        }
    }

    header('Location: tarifs.php');
    exit;
}

// Fetch Tarifs
$tarifs = $pdo->query("SELECT * FROM tarifs_saison ORDER BY date_debut")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Tarifs</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center text-gray-500 hover:text-gray-900 mr-4">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900">Tarifs des Saisons</h1>
                </div>
                <?php if ($edit_mode): ?>
                    <div class="flex items-center">
                        <a href="tarifs.php" class="text-sm font-medium text-blue-600 hover:underline flex items-center">
                            <span class="material-symbols-outlined text-sm mr-1">close</span> Annuler la modification
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-8">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left: List of Seasons -->
            <div class="lg:col-span-2 space-y-4">
                <h2 class="text-lg font-medium text-gray-700 mb-2">Périodes définies</h2>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($tarifs as $t): ?>
                            <li
                                class="p-4 hover:bg-gray-50 flex items-center justify-between group transition <?php echo ($id_edit == $t['id']) ? 'bg-orange-50 border-l-4 border-orange-500' : ''; ?>">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                        <?php echo substr($t['nom_saison'], 0, 1); ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                            <?php echo htmlspecialchars($t['nom_saison']); ?>
                                            <?php
                                            $prixTous = array_map(function ($x) { return (float) $x['prix_semaine']; }, $tarifs);
                                            $cat = tarifs_categorie($t, min($prixTous), max($prixTous));
                                            $auto = empty($t['categorie']);
                                            $couleurs = ['haute' => 'bg-amber-100 text-amber-800',
                                                         'moyenne' => 'bg-emerald-100 text-emerald-800',
                                                         'basse' => 'bg-sky-100 text-sky-800'];
                                            ?>
                                            <span class="text-xs px-2 py-0.5 rounded-full <?php echo $couleurs[$cat]; ?>"
                                                  title="<?php echo $auto ? 'Classement automatique d\'après le prix' : 'Saison choisie'; ?>">
                                                <?php echo htmlspecialchars(tarifs_saisons()[$cat]['nom']); ?><?php echo $auto ? ' ·auto' : ''; ?>
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Du <strong><?php echo date('d/m/Y', strtotime($t['date_debut'])); ?></strong>
                                            au <strong><?php echo date('d/m/Y', strtotime($t['date_fin'])); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <div class="text-right mr-4">
                                        <div class="text-sm font-bold text-gray-900">
                                            <?php echo number_format($t['prix_semaine'], 0, ',', ' '); ?> €</div>
                                        <div class="text-xs text-gray-400">/ semaine</div>
                                    </div>

                                    <a href="?edit=<?php echo $t['id']; ?>"
                                        class=" text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-full p-2 transition-all"
                                        title="Modifier">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>

                                    <a href="?delete=<?php echo $t['id']; ?>"
                                        class=" text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded-full p-2 transition-all"
                                        onclick="return confirm('Confirmer la suppression ?');" title="Supprimer">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>

                        <?php if (empty($tarifs)): ?>
                            <li class="p-8 text-center text-gray-400">Aucun tarif défini. Ajoutez-en un !</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Right: Add/Edit Form -->
            <div>
                <div
                    class="bg-white shadow rounded-lg p-6 sticky top-24 border-t-4 <?php echo $edit_mode ? 'border-orange-500' : 'border-blue-600'; ?>">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <span
                            class="material-symbols-outlined mr-2"><?php echo $edit_mode ? 'edit' : 'add_circle'; ?></span>
                        <?php echo $edit_mode ? 'Modifier la période' : 'Nouvelle Période'; ?>
                    </h3>

                    <form method="POST" class="space-y-4">
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $id_edit; ?>">
                        <?php endif; ?>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la saison</label>
                            <input type="text" name="nom_saison" value="<?php echo htmlspecialchars($nom_edit); ?>"
                                class="w-full rounded-md border-gray-300 border p-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Ex: Été 2026" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Saison</label>
                            <select name="categorie"
                                class="w-full rounded-md border-gray-300 border p-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Automatique (d'après le prix)</option>
                                <?php foreach (tarifs_saisons() as $cle => $s): ?>
                                    <option value="<?php echo htmlspecialchars($cle); ?>"
                                        <?php echo $categorie_edit === $cle ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Détermine le bloc dans lequel la période apparaît sur le site.
                                Laissé sur « Automatique », le classement suit le prix : les plus
                                élevés en haute saison, les plus bas en basse saison.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Du</label>
                                <input type="date" name="date_debut" value="<?php echo $debut_edit; ?>"
                                    class="w-full rounded-md border-gray-300 border p-2" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Au</label>
                                <input type="date" name="date_fin" value="<?php echo $fin_edit; ?>"
                                    class="w-full rounded-md border-gray-300 border p-2" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prix Semaine (€)</label>
                            <div class="relative">
                                <input type="number" name="prix_semaine" value="<?php echo $prix_edit; ?>" step="0.01"
                                    class="w-full rounded-md border-gray-300 border p-2 pr-8" placeholder="0.00"
                                    required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">€</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full <?php echo $edit_mode ? 'bg-orange-600 hover:bg-orange-700' : 'bg-blue-600 hover:bg-blue-700'; ?> text-white font-bold py-2 px-4 rounded-md shadow-lg transform transition hover:-translate-y-0.5">
                            <?php echo $edit_mode ? 'Mettre à jour' : 'Ajouter ce tarif'; ?>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</body>

</html>