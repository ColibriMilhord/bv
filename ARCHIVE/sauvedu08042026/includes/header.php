<?php
// includes/header.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html class="light scroll-smooth" lang="fr">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Bellevue d'Aveyron - Gîte de Luxe 5 Étoiles</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />

    <!-- Tailwind CSS (CDN for simplicity as per requirements) -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#197fe6",
                        "background-light": "#f6f7f8",
                        "background-dark": "#111921",
                        "stone": {
                            50: '#fafaf9',
                            100: '#f5f5f4',
                            200: '#e7e5e4',
                            800: '#292524',
                            900: '#1c1917',
                        }
                    },
                    fontFamily: {
                        "display": ["Plus Jakarta Sans", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "2xl": "1rem", "full": "9999px" },
                },
            },
        }
    </script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display antialiased overflow-x-hidden selection:bg-primary/20 selection:text-primary">

    <!-- Top Navigation -->
    <nav
        class="fixed top-0 left-0 w-full z-50 bg-white/90 dark:bg-background-dark/90 backdrop-blur-md border-b border-stone-200 dark:border-stone-800 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="index.php" class="flex items-center gap-3 cursor-pointer group">
                    <span
                        class="material-symbols-outlined text-primary text-3xl group-hover:scale-110 transition-transform">landscape</span>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Bellevue d'Aveyron</h1>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <a class="text-sm font-medium transition-colors <?php echo ($current_page == 'suites.php') ? 'text-primary' : 'text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary'; ?>"
                        href="suites.php">Le Gîte & Suites</a>
                    <a class="text-sm font-medium transition-colors <?php echo ($current_page == 'services.php') ? 'text-primary' : 'text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary'; ?>"
                        href="services.php">Services</a>
                    <a class="text-sm font-medium transition-colors <?php echo ($current_page == 'experiences.php') ? 'text-primary' : 'text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary'; ?>"
                        href="experiences.php">Expériences</a>
                    <a class="text-sm font-medium transition-colors <?php echo ($current_page == 'faq.php') ? 'text-primary' : 'text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-primary'; ?>"
                        href="faq.php">FAQ</a>

                    <a href="reservation.php"
                        class="bg-primary hover:bg-primary/90 text-white text-sm font-bold py-2.5 px-5 rounded-lg transition-all shadow-lg shadow-primary/20 hover:shadow-xl hover:-translate-y-0.5">
                        Réserver
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button class="text-slate-900 dark:text-white">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu Container (Hidden by default, would need JS to toggle) -->
    </nav>