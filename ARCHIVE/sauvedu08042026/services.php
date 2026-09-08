<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<div class="relative w-full">
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        data-alt="Panoramic view of Sainte-Eulalie-d'Olt village at sunset"
        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAqIe3gs9HbxP3DR93UTZoPT7hFmMSyzZrGQAmdvO3iyFqFlnrbbSPedqT1iJv9ifJ_T6F6APu4gyqMw5-IutPx-W_8t9CKBhXy76C4x8T53N-VtcQV05f-beauztT1mheOD98rDoGojS04vvW2K8rzegd1J9fPQOJut9kcb21VdQ-eY0M-k5UNP_stz6uDEk0HLRgOBWuNSB84n8z6j0rZCubMYJ11hmqj8OF0CcjAcI9EeoJ-bOE_ffpfggiqkZlfgeU2k_4e5i4");'>
    </div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/30 to-black/70"></div>
    <div class="relative flex min-h-[600px] flex-col items-center justify-center gap-6 px-4 py-20 text-center md:px-10">
        <div class="flex flex-col gap-3 animate-fade-in-up">
            <h1 class="text-white text-5xl font-black leading-tight tracking-[-0.033em] md:text-6xl drop-shadow-lg">
                Services 5 Étoiles
            </h1>
            <h2 class="mx-auto max-w-2xl text-stone-100 text-lg font-medium leading-relaxed md:text-xl drop-shadow-md">
                Une expérience sur-mesure au cœur de l'Aveyron, où le luxe rencontre l'authenticité de
                Sainte-Eulalie-d'Olt.
            </h2>
        </div>
        <a href="#prestations"
            class="mt-4 flex h-12 min-w-[160px] cursor-pointer items-center justify-center rounded-lg bg-white px-6 text-primary text-base font-bold shadow-lg transition-transform hover:scale-105">
            <span class="mr-2">Découvrir les prestations</span>
            <span class="material-symbols-outlined text-sm">arrow_downward</span>
        </a>
    </div>
</div>

<!-- Introduction Text -->
<section class="flex justify-center px-4 py-16 md:px-40 bg-white dark:bg-background-dark">
    <div class="max-w-3xl text-center">
        <span class="mb-3 block text-xs font-bold uppercase tracking-widest text-primary">Prestige & Sérénité</span>
        <p class="text-2xl font-light leading-relaxed text-[#111418] dark:text-white md:text-3xl">
            "Nous redéfinissons le séjour de luxe en gîte. Chaque service est pensé pour vous offrir une parenthèse de
            bien-être absolu, face aux paysages grandioses de l'Aveyron."
        </p>
    </div>
</section>

<!-- Main Content: Services List (Z-Pattern) -->
<main class="flex flex-col gap-8 px-4 pb-20 md:px-40 md:gap-16 bg-white dark:bg-background-dark" id="prestations">
    <!-- Service 1: Accueil (Image Left) -->
    <div class="group mx-auto max-w-[1100px] w-full">
        <div
            class="flex flex-col gap-8 overflow-hidden rounded-xl bg-white dark:bg-[#1a2632] shadow-[0_4px_20px_rgba(0,0,0,0.03)] transition-shadow hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)] md:flex-row md:items-stretch border border-gray-100 dark:border-gray-800">
            <div class="relative min-h-[300px] w-full flex-1 md:min-h-auto overflow-hidden">
                <div class="absolute inset-0 h-full w-full bg-cover bg-center transition-transform duration-700 hover:scale-105"
                    data-alt="Welcome basket with local wine and cheese on a rustic table"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBtTNW0LW5XgfiniSGbwkDW7VO154QcFc5n_SjDBij8i9VPtB04-PBedFCNj1rC7LwegwQn8hNeQyz-0znTHuB5e6C6zR-30Hqth_sI1CIQZo_R-mHjUDfAVW3pGP8_S3QhztrE_nqI5y2dOyr14gv0UL5V_Yoj7xfP8DO1KdV8zJvSFJElM0G2OqRukI6xKZEqRneHF_Y0ukwBpcOOS_qAcvDMi1s7hDmtRJ6OHglOOYlsiEe4y0_rHWWq3LGJvlpVGOAoEF-BYFw");'>
                </div>
            </div>
            <div class="flex flex-1 flex-col justify-center gap-4 p-8 md:p-12">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <span class="material-symbols-outlined">concierge</span>
                    </span>
                    <h2 class="text-2xl font-bold text-[#111418] dark:text-white">Accueil Personnalisé</h2>
                </div>
                <p class="text-lg leading-relaxed text-[#637588] dark:text-slate-400">
                    Dès votre arrivée, plongez dans l'art de vivre aveyronnais. Un panier de bienvenue exclusif vous
                    attend : vins locaux sélectionnés, fromages du terroir et notre guide privé de la région pour une
                    immersion immédiate.
                </p>
                <ul class="mt-2 flex flex-col gap-2 text-sm font-medium text-[#111418] dark:text-white">
                    <li class="flex items-center gap-2"><span
                            class="material-symbols-outlined text-primary text-base">check_circle</span> Check-in privé
                    </li>
                    <li class="flex items-center gap-2"><span
                            class="material-symbols-outlined text-primary text-base">check_circle</span> Produits du
                        terroir offerts</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Service 2: Confort (Image Right) -->
    <div class="group mx-auto max-w-[1100px] w-full">
        <div
            class="flex flex-col gap-8 overflow-hidden rounded-xl bg-white dark:bg-[#1a2632] shadow-[0_4px_20px_rgba(0,0,0,0.03)] transition-shadow hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)] md:flex-row-reverse md:items-stretch border border-gray-100 dark:border-gray-800">
            <div class="relative min-h-[300px] w-full flex-1 md:min-h-auto overflow-hidden">
                <div class="absolute inset-0 h-full w-full bg-cover bg-center transition-transform duration-700 hover:scale-105"
                    data-alt="High thread count white linens on a luxurious bed"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuC5YznoTmu7TV2U0t3Bsh25URKifWYamvQ3_MenZwAiOXSaw14LxpjjzN9-VDiAgBVqJA2lu1ZAZWAdsZyU1dAurpaWb11OouovXc7_E5F9LBKH4gFINYMbaepL6O-FhtEgMj82Mo6RIAdj94OzQpMuaOA-GEhIF_77pGG0jTOVe7fUaKWIfLAFdBd7Rl4QfRk4drQbsICUTkv501RhSnsUffnhipnd9VxrEm2en2_rXiQfM3iwczaAu3Midz3ym5afFiDUpoh0fb8");'>
                </div>
            </div>
            <div class="flex flex-1 flex-col justify-center gap-4 p-8 md:p-12">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <span class="material-symbols-outlined">bed</span>
                    </span>
                    <h2 class="text-2xl font-bold text-[#111418] dark:text-white">Confort Premium</h2>
                </div>
                <p class="text-lg leading-relaxed text-[#637588] dark:text-slate-400">
                    Le luxe, c'est avant tout un sommeil réparateur. Profitez de draps en lin lavé, d'une literie haut
                    de gamme (King Size) et d'un service de ménage quotidien sur demande. Nous fournissons également des
                    peignoirs moelleux et des produits de bain bio.
                </p>
                <ul class="mt-2 flex flex-col gap-2 text-sm font-medium text-[#111418] dark:text-white">
                    <li class="flex items-center gap-2"><span
                            class="material-symbols-outlined text-primary text-base">check_circle</span> Literie 5
                        étoiles</li>
                    <li class="flex items-center gap-2"><span
                            class="material-symbols-outlined text-primary text-base">check_circle</span> Linge de maison
                        inclus</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Service 3: Conciergerie (Image Left) -->
    <div class="group mx-auto max-w-[1100px] w-full">
        <div
            class="flex flex-col gap-8 overflow-hidden rounded-xl bg-white dark:bg-[#1a2632] shadow-[0_4px_20px_rgba(0,0,0,0.03)] transition-shadow hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)] md:flex-row md:items-stretch border border-gray-100 dark:border-gray-800">
            <div class="relative min-h-[300px] w-full flex-1 md:min-h-auto overflow-hidden">
                <div class="absolute inset-0 h-full w-full bg-cover bg-center transition-transform duration-700 hover:scale-105"
                    data-alt="Two people looking at a map outdoors in a sunny village"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDYCZ0Sil7S0UG6hh2sW_GXOujKMBOmNu3ppXCxX5RTCdAUskq0rFlguaX-D8IHMo2UyBRY09JTp1MS5qtKSJdZOspUkyq_6l3dnQUzwxOtVPNWlq9tr9V97ZNoaVO5ZoEvtkdD2YhEUKQk8TMy7ZC217TEXQXCMFk2enmMmp-VB-avecGedK-95cn9rPpdxFyoeZS_AXvtXiGG6UCWH9DJhP08ddOUV2qlVpl9Tydjc647nQnlJN_rOLusOKiHprdfZoq3B4cdHg4");'>
                </div>
            </div>
            <div class="flex flex-1 flex-col justify-center gap-4 p-8 md:p-12">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <span class="material-symbols-outlined">explore</span>
                    </span>
                    <h2 class="text-2xl font-bold text-[#111418] dark:text-white">Conciergerie & Expériences</h2>
                </div>
                <p class="text-lg leading-relaxed text-[#637588] dark:text-slate-400">
                    Laissez-nous organiser votre séjour. Notre service de conciergerie gère vos réservations de
                    restaurants gastronomiques, vos visites privées de Sainte-Eulalie-d'Olt et vos activités on-site.
                </p>
                <ul class="mt-2 flex flex-col gap-2 text-sm font-medium text-[#111418] dark:text-white">
                    <li class="flex items-center gap-2"><span
                            class="material-symbols-outlined text-primary text-base">check_circle</span> Réservations
                        prioritaires</li>
                    <li class="flex items-center gap-2"><span
                            class="material-symbols-outlined text-primary text-base">check_circle</span> Carnet
                        d'adresses secret</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<!-- Icon Grid / Amenities Summary -->
<section class="bg-white dark:bg-background-dark py-20 border-t border-gray-100 dark:border-gray-800">
    <div class="mx-auto max-w-[960px] px-4 md:px-10">
        <div class="mb-12 text-center">
            <h2 class="text-3xl font-bold text-[#111418] dark:text-white">Tous nos équipements</h2>
            <p class="mt-2 text-[#637588] dark:text-slate-400">Tout est pensé pour que vous ne manquiez de rien.</p>
        </div>
        <div class="grid grid-cols-2 gap-y-10 gap-x-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            <!-- Icon Item -->
            <div class="flex flex-col items-center gap-3 text-center">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-white transition-colors hover:bg-primary hover:text-white">
                    <span class="material-symbols-outlined text-2xl">wifi</span>
                </div>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">Wi-Fi Haut Débit</span>
            </div>
            <!-- Icon Item -->
            <div class="flex flex-col items-center gap-3 text-center">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-white transition-colors hover:bg-primary hover:text-white">
                    <span class="material-symbols-outlined text-2xl">local_parking</span>
                </div>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">Parking Privé</span>
            </div>
            <!-- Icon Item -->
            <div class="flex flex-col items-center gap-3 text-center">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-white transition-colors hover:bg-primary hover:text-white">
                    <span class="material-symbols-outlined text-2xl">ac_unit</span>
                </div>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">Climatisation</span>
            </div>
            <!-- Icon Item -->
            <div class="flex flex-col items-center gap-3 text-center">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-white transition-colors hover:bg-primary hover:text-white">
                    <span class="material-symbols-outlined text-2xl">kitchen</span>
                </div>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">Cuisine Équipée</span>
            </div>
            <!-- Icon Item -->
            <div class="flex flex-col items-center gap-3 text-center">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-white transition-colors hover:bg-primary hover:text-white">
                    <span class="material-symbols-outlined text-2xl">local_laundry_service</span>
                </div>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">Laverie</span>
            </div>
            <!-- Icon Item -->
            <div class="flex flex-col items-center gap-3 text-center">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 dark:bg-stone-800 text-stone-800 dark:text-white transition-colors hover:bg-primary hover:text-white">
                    <span class="material-symbols-outlined text-2xl">landscape</span>
                </div>
                <span class="text-sm font-semibold text-slate-900 dark:text-white">Vue Panoramique</span>
            </div>
        </div>
    </div>
</section>

<!-- CTA Banner -->
<section class="relative overflow-hidden bg-background-dark py-24 text-center text-white">
    <div class="absolute inset-0 opacity-20" data-alt="Abstract dark luxury texture"
        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBp0vYKTn91r-s-v5XDLal8TBOD-QhM3jlh4NxWchZdDiILzR7JYB0QvPCByBUDUt2bNDj0EXXXJtp_v3_JiDXu-d2B8Gkcesy_gCBetZEzmUs70ok8MhY3mmjN-B5yk3H2nfAZ7VtF8tcjiKiEtY7NztCNyck5ln1cdcSZgTMPPlBTzjH0yNNxVw6huP_koesjKenYtktN_AJ9mQ33pqCPTdGteULPega4Q5NQr_vFFStJJ3RGzSN-Kbv6Hr-vTJcz33x_LNaZVIo"); background-size: cover; background-position: center;'>
    </div>
    <div class="relative z-10 mx-auto max-w-3xl px-4">
        <h2 class="mb-6 text-4xl font-bold tracking-tight">Prêt à vivre l'exceptionnel ?</h2>
        <p class="mb-10 text-lg font-light text-stone-300">Réservez dès maintenant votre séjour au Bellevue d'Aveyron et
            profitez de nos services 5 étoiles inclus.</p>
        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="reservation.php"
                class="rounded-lg bg-primary px-8 py-4 text-base font-bold text-white transition-all hover:bg-blue-600 hover:shadow-lg hover:shadow-primary/25">
                Vérifier les disponibilités
            </a>
            <a href="contact.php"
                class="rounded-lg border border-white/20 bg-white/5 px-8 py-4 text-base font-bold text-white backdrop-blur-sm transition-all hover:bg-white/10">
                Contacter la conciergerie
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>