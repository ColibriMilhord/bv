<?php
// admin/integration.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Generate the absolute URL for the iframe
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// Assuming admin/ is one level deep
$path = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');
$iframe_url = "$protocol://$host$path/iframe_calendar.php";
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Intégration Site - Administration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-50 min-h-screen font-sans">

    <!-- Nav -->
    <nav class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center text-slate-500 hover:text-blue-600 transition mr-4">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <h1 class="text-xl font-bold text-slate-800">Intégration Site (Wix)</h1>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 mb-8">
            <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                <span class="material-symbols-outlined mr-2 text-teal-600">code</span>
                Code d'Intégration Iframe
            </h2>
            <p class="text-slate-600 mb-6">
                Copiez le code ci-dessous et collez-le dans un widget "HTML/Iframe" sur votre site Wix.
                Il affichera le calendrier avec vos disponibilités et tarifs, mis à jour en temps réel.
            </p>

            <div class="relative">
                <textarea id="iframeCode" readonly
                    class="w-full h-32 p-4 bg-slate-900 text-green-400 font-mono text-sm rounded-lg border border-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500"><iframe src="<?php echo $iframe_url; ?>" width="100%" height="800" frameborder="0" style="border:0;" allowfullscreen></iframe></textarea>

                <button onclick="copyCode()"
                    class="absolute top-2 right-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold py-1 px-3 rounded shadow transition">
                    Copier
                </button>
            </div>

            <div class="mt-4 p-4 bg-blue-50 text-blue-800 text-sm rounded-lg flex items-start">
                <span class="material-symbols-outlined mr-2 text-base mt-0.5">info</span>
                <p>
                    <strong>Note pour Wix :</strong> Assurez-vous d'ajuster la hauteur du widget dans l'éditeur Wix pour
                    éviter les barres de défilement (hauteur conseillée : 800px ou plus).
                    Si vous changez de nom de domaine, ce code restera valide tant que ce site d'administration reste
                    accessible.
                </p>
            </div>
        </div>

        <!-- Preview -->
        <h3 class="text-lg font-bold text-slate-900 mb-4">Aperçu en direct</h3>
        <div class="border border-slate-300 rounded-xl overflow-hidden shadow-lg bg-white">
            <iframe src="../iframe_calendar.php" width="100%" height="800" frameborder="0"></iframe>
        </div>

    </div>

    <script>
        function copyCode() {
            var copyText = document.getElementById("iframeCode");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            alert("Code copié !");
        }
    </script>
</body>

</html>