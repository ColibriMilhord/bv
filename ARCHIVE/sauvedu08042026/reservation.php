<?php include 'includes/header.php'; ?>
<?php
// Initialize variables
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
?>

<!-- Main Content Area -->
<main
    class="flex-1 flex flex-col items-center w-full px-4 md:px-8 pb-12 pt-16 md:pt-24 bg-background-light dark:bg-background-dark min-h-screen">
    <!-- Page Heading Section -->
    <div class="flex flex-col items-center gap-3 mb-10 text-center animate-fade-in-up max-w-3xl mx-auto">
        <h1
            class="text-slate-900 dark:text-white tracking-[0.2em] text-2xl md:text-3xl font-bold leading-tight uppercase">
            Votre Séjour au Bellevue
        </h1>
        <p
            class="text-slate-500 dark:text-slate-400 text-sm md:text-base font-light tracking-wide max-w-lg leading-relaxed">
            Réservez votre évasion de luxe en direct et profitez de nos meilleures offres garanties.
        </p>
    </div>

    <!-- Booking Form Container -->
    <div
        class="w-full max-w-4xl bg-white dark:bg-[#1e293b] rounded-2xl shadow-2xl border border-stone-100 dark:border-stone-700 overflow-hidden animate-fade-in-up delay-100">
        <div class="bg-primary/5 p-6 border-b border-primary/10">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">calendar_month</span>
                Détails de la réservation
            </h2>
        </div>

        <form action="save_reservation.php" method="POST" class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php
            // Fetch Settings for Fee
            require_once 'config/db.php';
            $settings = $pdo->query("SELECT * FROM gite_settings WHERE id = 1")->fetch();
            $frais_menage = $settings['frais_menage'] ?? 220;
            ?>
            <!-- Dates -->
            <div class="space-y-4">
                <h3
                    class="font-bold text-slate-900 dark:text-white border-b border-stone-100 dark:border-stone-700 pb-2">
                    Vos Dates</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Arrivée</label>
                        <input type="date" name="check_in" required value="<?php echo htmlspecialchars($checkin); ?>"
                            class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-slate-800 dark:text-white focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Départ</label>
                        <input type="date" name="check_out" required value="<?php echo htmlspecialchars($checkout); ?>"
                            class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-slate-800 dark:text-white focus:ring-primary focus:border-primary">
                    </div>
                </div>
                <!-- Cleaning Option -->
                <div
                    class="bg-stone-50 dark:bg-slate-800/50 p-4 rounded-lg border border-stone-100 dark:border-stone-700">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="cleaning_fee" value="1"
                            class="w-5 h-5 text-primary rounded border-gray-300 focus:ring-primary">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                            Option Ménage fin de séjour (+<?php echo number_format($frais_menage, 0); ?>€)
                        </span>
                    </label>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 italic">
                    * Séjour minimum de 3 nuits.
                </div>
            </div>

            <!-- Personal Info -->
            <div class="space-y-4">
                <h3
                    class="font-bold text-slate-900 dark:text-white border-b border-stone-100 dark:border-stone-700 pb-2">
                    Vos Coordonnées</h3>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nom Complet</label>
                    <input type="text" name="customer_name" required placeholder="Jean Dupont"
                        class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-slate-800 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input type="email" name="customer_email" required placeholder="jean@exemple.com"
                        class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-slate-800 dark:text-white focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Téléphone</label>
                    <input type="tel" name="customer_phone" placeholder="+33 6 00 00 00 00"
                        class="w-full rounded-lg border-stone-300 dark:border-stone-600 dark:bg-slate-800 dark:text-white focus:ring-primary focus:border-primary">
                </div>
            </div>

            <!-- Submit -->
            <div class="md:col-span-2 pt-6 border-t border-stone-100 dark:border-stone-700 flex flex-col items-center">
                <button type="submit"
                    class="bg-primary hover:bg-primary/90 text-white font-bold py-4 px-12 rounded-xl text-lg shadow-xl shadow-primary/20 transition-transform active:scale-95 flex items-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span>
                    Confirmer la Demande
                </button>
                <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                    Aucun paiement n'est requis à cette étape. Nous vous recontacterons sous 24h pour confirmer la
                    disponibilité.
                </p>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>