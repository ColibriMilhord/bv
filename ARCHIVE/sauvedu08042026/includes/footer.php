<!-- includes/footer.php -->
<footer class="bg-white dark:bg-background-dark border-t border-slate-100 dark:border-slate-800 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <div class="col-span-1 md:col-span-1">
                <div class="flex items-center gap-2 mb-6">
                    <span class="material-symbols-outlined text-primary text-2xl">landscape</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Bellevue d'Aveyron</h3>
                </div>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Un gîte de luxe 5 étoiles offrant une vue panoramique et un confort exceptionnel au cœur de
                    l'Aveyron.
                </p>
            </div>
            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-4">Explorer</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li><a class="hover:text-primary transition-colors" href="suites.php">Le Gîte & Suites</a></li>
                    <li><a class="hover:text-primary transition-colors" href="services.php">Bien-être & Spa</a></li>
                    <li><a class="hover:text-primary transition-colors" href="experiences.php">La Région</a></li>
                    <li><a class="hover:text-primary transition-colors" href="faq.php">Questions Fréquentes</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-4">Contact</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-base mt-0.5">location_on</span>
                        <span>12130 Sainte-Eulalie-d'Olt, France</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">call</span>
                        <span>+33 5 65 00 00 00</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">mail</span>
                        <span>contact@bellevue-aveyron.fr</span>
                    </li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-4">Newsletter</h4>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Abonnez-vous pour nos offres exclusives.</p>
                <div class="flex gap-2">
                    <input
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none"
                        placeholder="Adresse email" type="email" />
                    <button class="bg-primary text-white p-2 rounded-lg hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined text-sm">send</span>
                    </button>
                </div>
            </div>
        </div>
        <div
            class="border-t border-slate-100 dark:border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-slate-400">©
                <?php echo date('Y'); ?> Bellevue d'Aveyron. Tous droits réservés.
            </p>
            <div class="flex gap-6 text-xs text-slate-400">
                <a class="hover:text-slate-600 dark:hover:text-slate-300" href="#">Politique de Confidentialité</a>
                <a class="hover:text-slate-600 dark:hover:text-slate-300" href="#">CGV</a>
                <a class="hover:text-slate-600 dark:hover:text-slate-300" href="#">Mentions Légales</a>
            </div>
        </div>
    </div>
</footer>
</body>

</html>