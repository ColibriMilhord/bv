<?php include 'includes/header.php'; ?>

<!-- Hero Section with Search -->
<div class="w-full bg-white dark:bg-background-dark">
    <div class="px-4 py-8 lg:px-40 lg:py-12 flex justify-center">
        <div class="flex flex-col max-w-[960px] flex-1">
            <div class="relative overflow-hidden rounded-xl bg-cover bg-center bg-no-repeat min-h-[320px] lg:min-h-[400px] flex flex-col items-center justify-center p-6 gap-6 shadow-lg"
                data-alt="Panoramic view of Aveyron valley with green hills and river"
                style='background-image: linear-gradient(rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.6) 100%), url("https://lh3.googleusercontent.com/aida-public/AB6AXuBAd99q8FrlCq58CQiFKnu-a6Y6y9MMIEynZ0lZIXQDC3UFgvUFGHUBqI_mVLjvRzB3eLKuAfgFP2kXeTRnglO4jsBPylOw4IuWhtxAcYVgbvTFa4zYscX4uh7VjoEWtFyWpi_Rdq_N1JGEE0JtE5xLKELQQLRjBHcfZ8ma33L85nQr935G5ujb8n--bIjnTWzoxKbJgQJtgNw14p6TAl7JI1VBWTE7Wwd8jNRyhJAshskEkYV_j26tu2p9sY8EMSrbH37od1bf_Mg");'>
                <div class="flex flex-col gap-3 text-center max-w-2xl">
                    <h1 class="text-white text-3xl lg:text-5xl font-black leading-tight tracking-tight">
                        Vos questions, notre priorité
                    </h1>
                    <p class="text-gray-200 text-base lg:text-lg font-medium leading-normal">
                        Tout savoir sur votre séjour d'exception à Sainte-Eulalie-d'Olt. Accès, équipements et services
                        sur-mesure.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main FAQ Categories -->
<main class="w-full px-4 lg:px-40 pb-20 flex justify-center bg-white dark:bg-background-dark">
    <div class="flex flex-col max-w-[960px] flex-1 gap-12">
        <!-- Category 1: Expérience -->
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-3 pb-2 border-b border-stone-200 dark:border-stone-800">
                <span class="material-symbols-outlined text-primary">spa</span>
                <h2 class="text-slate-900 dark:text-white text-xl font-bold">L'Expérience & Les Services</h2>
            </div>
            <div class="flex flex-col gap-3">
                <details
                    class="group rounded-lg border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-[#1a2632] open:bg-white dark:open:bg-[#1f2937] overflow-hidden transition-all duration-300">
                    <summary
                        class="flex cursor-pointer items-center justify-between gap-4 p-5 text-slate-900 dark:text-white font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <span>Quels sont les équipements de bien-être disponibles ?</span>
                        <span
                            class="material-symbols-outlined text-slate-500 transition-transform duration-300 group-open:rotate-180">expand_more</span>
                    </summary>
                    <div class="px-5 pb-5 pt-0 text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                        <p>Nous disposons d'un spa privatif complet comprenant un jacuzzi panoramique 5 places face à la
                            vallée du Lot, un sauna infrarouge pour une détente profonde, et une piscine à débordement
                            chauffée accessible de mai à octobre. Des peignoirs et chaussons de luxe sont fournis pour
                            votre confort.</p>
                    </div>
                </details>
                <details
                    class="group rounded-lg border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-[#1a2632] overflow-hidden transition-all duration-300">
                    <summary
                        class="flex cursor-pointer items-center justify-between gap-4 p-5 text-slate-900 dark:text-white font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <span>Le petit-déjeuner est-il inclus dans la réservation ?</span>
                        <span
                            class="material-symbols-outlined text-slate-500 transition-transform duration-300 group-open:rotate-180">expand_more</span>
                    </summary>
                    <div class="px-5 pb-5 pt-0 text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                        <p>Oui, un petit-déjeuner continental "Terroir de l'Aveyron" est inclus pour chaque nuitée. Il
                            est composé de produits frais locaux : fouace aveyronnaise, confitures artisanales, yaourts
                            de ferme, jus de fruits pressés et boissons chaudes. Il peut être servi en chambre ou sur la
                            terrasse principale.</p>
                    </div>
                </details>
            </div>
        </div>

        <!-- Category 2: Access & Situation -->
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-3 pb-2 border-b border-stone-200 dark:border-stone-800">
                <span class="material-symbols-outlined text-primary">map</span>
                <h2 class="text-slate-900 dark:text-white text-xl font-bold">Accès & Situation</h2>
            </div>
            <div class="flex flex-col gap-3">
                <details
                    class="group rounded-lg border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-[#1a2632] overflow-hidden transition-all duration-300">
                    <summary
                        class="flex cursor-pointer items-center justify-between gap-4 p-5 text-slate-900 dark:text-white font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <span>Comment accéder au gîte ? Le chemin est-il carrossable ?</span>
                        <span
                            class="material-symbols-outlined text-slate-500 transition-transform duration-300 group-open:rotate-180">expand_more</span>
                    </summary>
                    <div class="px-5 pb-5 pt-0 text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                        <p>Bellevue d'Aveyron se situe à 5 minutes du centre de Sainte-Eulalie-d'Olt. Les derniers 500
                            mètres se font par un chemin privé goudronné, parfaitement accessible aux véhicules
                            standards et sportifs.</p>
                    </div>
                </details>
            </div>
        </div>
    </div>
</main>

<!-- Footer / CTA -->
<section class="border-t border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-[#151b23] py-12 px-4 lg:px-40">
    <div class="flex flex-col items-center justify-center text-center gap-6 max-w-[960px] mx-auto">
        <h2 class="text-slate-900 dark:text-white text-2xl lg:text-3xl font-bold tracking-tight">Vous n'avez pas trouvé
            votre réponse ?</h2>
        <div class="flex gap-4 mt-2">
            <a href="mailto:contact@bellevue-aveyron.fr"
                class="flex min-w-[140px] cursor-pointer items-center justify-center rounded-lg h-12 px-6 border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-slate-900 dark:text-white font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Nous contacter
            </a>
            <a href="reservation.php"
                class="flex min-w-[140px] cursor-pointer items-center justify-center rounded-lg h-12 px-6 bg-primary text-white font-bold hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
                Réserver votre séjour
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>