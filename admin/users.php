<?php
// admin/users.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Add User Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            $success = "Utilisateur ajouté avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur: Ce nom d'utilisateur existe probablement déjà.";
        }
    }
}

// Delete User Action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Prevent deleting self? Use admin_id from session if stored.
    // Assuming $_SESSION['admin_id'] is the ID of current user.
    if ($id == $_SESSION['admin_id']) {
        $error = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$id]);
        header('Location: users.php');
        exit;
    }
}

// Fetch Users
$users = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gérer les Utilisateurs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center text-gray-500 hover:text-gray-900 mr-4">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900">Utilisateurs / Admins</h1>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- List -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-800">Administrateurs</h2>
                </div>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <li class="p-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <div
                                    class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">Créé le
                                        <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                <a href="?delete=<?php echo $user['id']; ?>" class="text-red-500 hover:text-red-700 p-2"
                                    onclick="return confirm('Etes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            <?php else: ?>
                                <span class="text-xs text-gray-400 italic px-2">Vous</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Add Form -->
            <div class="bg-white shadow rounded-lg p-6 h-fit">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <span class="material-symbols-outlined mr-2">person_add</span>
                    Ajouter un Admin
                </h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                        <input type="text" name="username"
                            class="w-full rounded-md border-gray-300 border p-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                        <input type="password" name="password"
                            class="w-full rounded-md border-gray-300 border p-2 focus:ring-indigo-500 focus:border-indigo-500"
                            required>
                    </div>
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded-md hover:bg-indigo-700 shadow transform transition hover:-translate-y-0.5">
                        Créer l'utilisateur
                    </button>
                </form>
            </div>

        </div>
    </div>
</body>

</html>