<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<header class="relative w-full h-screen min-h-[600px] flex items-center justify-center overflow-hidden">
    <!-- Background Image with Gradient Overlay -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/20 to-black/60 z-10"></div>
        <img alt="Panoramic view of the Lot Valley" class="w-full h-full object-cover animate-ken-burns"
            data-alt="Panoramic view of the Lot Valley and mountains at sunrise with mist"
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuB0wISknZ0qFNnFuO6L2C5pFp3Z4gel-sOyuZJphNl-L0rkDebOJO4CFimm1Owfx18g_1YtZ6QCYZtSGPpFfM23UZV14WDjRIj3BMXJ8UTfB54F1ysv740iQ0VBOrYJORb7DbaO6KMEZef1YBOtSBavXcf4z5k-xo_hnR4cbHSvl1XYmr6BNyD0DF_cuIPNcYmXfvdrX-bHfarR1WbIV4LZQGZwaffQPMVEDYkMDyL-RlIo5q-cH7N7SLvDy4WBEcSvWPO2ugpW5fQ" />
    </div>
    <!-- Content -->
    <div class="relative z-20 max-w-5xl mx-auto px-4 text-center text-white mt-16">
        <div
            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 mb-6">
            <span class="material-symbols-outlined text-sm">star</span>
            <span class="text-xs font-bold uppercase tracking-wider">Gîte 5 Étoiles en France</span>
        </div>
        <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold leading-tight tracking-tight mb-6 drop-shadow-lg">
            Le Sommet du Luxe à <br class="hidden md:block" /> Sainte-Eulalie-d'Olt
        </h1>
        <p class="text-lg md:text-xl font-normal text-slate-100 max-w-2xl mx-auto mb-10 leading-relaxed drop-shadow-md">
            Vivez un sanctuaire de prestige avec vue panoramique imprenable sur la vallée du Lot. Votre évasion privée
            vous attend.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="reservation.php"
                class="bg-primary hover:bg-primary/90 text-white text-base font-bold h-12 px-8 rounded-lg transition-all shadow-xl shadow-primary/30 flex items-center justify-center gap-2">
                <span>Réserver votre Séjour</span>
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
            <a href="suites.php"
                class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/30 text-base font-bold h-12 px-8 rounded-lg transition-all flex items-center justify-center gap-2">
                <span>Voir le Gîte</span>
                <span class="material-symbols-outlined text-sm">photo_library</span>
            </a>
        </div>
    </div>
    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-white/70 animate-bounce z-20">
        <span class="material-symbols-outlined text-3xl">keyboard_arrow_down</span>
    </div>
</header>

<!-- Intro Section: A Sanctuary of Prestige -->
<section class="py-20 md:py-32 bg-white dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-16 items-center">
            <div class="flex-1 space-y-8">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                    Un Sanctuaire de Prestige
                </h2>
                <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed">
                    Niché au cœur de l'un des "Plus Beaux Villages de France", Bellevue d'Aveyron offre une retraite
                    exclusive où le luxe moderne rencontre la nature intemporelle. Notre <strong>location gîte de luxe
                        Aveyron</strong> est conçue pour ceux qui recherchent la tranquillité sans compromis sur le
                    confort.
                </p>
                <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed">
                    Chaque détail, de l'architecture en pierre locale au design intérieur raffiné, a été soigné pour
                    s'harmoniser avec les environs époustouflants de la vallée du Lot.
                </p>
                <div class="pt-4">
                    <a class="inline-flex items-center text-primary font-bold hover:text-primary/80 transition-colors"
                        href="suites.php">
                        Découvrir le Gîte
                        <span class="material-symbols-outlined ml-2 text-sm">arrow_forward</span>
                    </a>
                </div>
            </div>
            <div class="flex-1 relative w-full h-[500px]">
                <div
                    class="absolute inset-0 bg-stone-100 dark:bg-stone-800 rounded-2xl transform rotate-3 transition-transform">
                </div>
                <div class="absolute inset-0 rounded-2xl overflow-hidden shadow-2xl">
                    <img alt="Luxury Gite Interior"
                        class="w-full h-full object-cover hover:scale-105 transition-transform duration-700"
                        data-alt="Luxurious modern interior living room with stone walls and large windows"
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuCQmZRAzmdOLfWnpb-vZ8GU2STyv3x4eGa-jBN1eWWHCG6vaLYxKYTiPZHusisL0372GD-g57RAnPYuZr0tQmq_RXmY-qrkQQVdFbFa8bNzo0rM37WP6qx2JX5JCOTKPGes2H6O1Gp7N6AM5kSJDhwOpWWU1EBT9IoMqoDq5RPMF4D3pvCxjldFMS6D7AhuzLXolEtOarEclpLRCznURx8Z-aygMf2nbxpMYrj2uCunoLcgOo2b31zLTcfORTJuy4gK3oiAg61NIOI" />
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-background-light dark:bg-stone-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">Services &
                Équipements Haut de Gamme</h2>
            <p class="text-slate-600 dark:text-slate-400 text-lg">
                Découvrez nos offres exclusives pour un séjour inoubliable dans notre gîte de luxe.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Service 1 -->
            <div
                class="group bg-white dark:bg-background-dark p-8 rounded-2xl border border-stone-100 dark:border-stone-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div
                    class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-3xl">spa</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Spa Privé & Bien-être</h3>
                <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                    Détendez-vous dans notre espace bien-être de pointe comprenant un sauna, un jacuzzi et des services
                    de massage sur demande.
                </p>
            </div>
            <!-- Service 2 -->
            <div
                class="group bg-white dark:bg-background-dark p-8 rounded-2xl border border-stone-100 dark:border-stone-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div
                    class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-3xl">pool</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Piscine à Débordement</h3>
                <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                    Profitez d'une baignade avec une vue inégalée sur la vallée. Notre piscine chauffée à débordement se
                    fond avec l'horizon.
                </p>
            </div>
            <!-- Service 3 -->
            <div
                class="group bg-white dark:bg-background-dark p-8 rounded-2xl border border-stone-100 dark:border-stone-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div
                    class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-3xl">concierge</span>
                </div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Service de Conciergerie</h3>
                <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                    Des services personnalisés pour répondre à tous vos besoins, des dîners de chef privé aux visites
                    guidées de la région Aveyron.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Visual Showcase: Panorama -->
<section class="py-20 bg-white dark:bg-background-dark overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
            <div class="max-w-2xl">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">Un
                    Panorama Inégalé</h2>
                <p class="text-slate-600 dark:text-slate-400 text-lg">
                    Réveillez-vous avec les brumes douces du Lot et regardez le coucher de soleil peindre le ciel de
                    teintes or et violettes.
                </p>
            </div>
            <a href="suites.php"
                class="text-primary font-bold hover:text-primary/80 flex items-center gap-1 transition-colors whitespace-nowrap">
                Voir la Galerie Complète <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
        <!-- Mosaic Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 grid-rows-2 gap-4 h-[600px] md:h-[500px]">
            <!-- Main Large Image -->
            <div class="md:col-span-2 md:row-span-2 relative rounded-xl overflow-hidden group">
                <img alt="Terrace View"
                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    data-alt="Outdoor dining terrace overlooking a green valley and river"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuAv9rFXPE3PS3bhsz8SGV92A8F4xPobJh0EnCckxlQMMprK6SWOZFaKjmZlI64anLJ5pONO0c-p1FxWmOKoyxFJtUopvhJhe6zPU9fOTCNNQ7XTPkwUeza08oaDKA5GmPlibQmDif6WRAp1HMPZc5eeIYiV1aPpllk_bKdbXP-_HW6zgkdQNw8jFEWYFw4EU2gTTUcsK-ooK79GVJQanAH3uWObjskzuYWSeQt5v2fOKAx_Hhg65r_YGmvMEBRgYwNAlTSbTx-xd0k" />
                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"></div>
            </div>
            <!-- Secondary Image Top -->
            <div class="md:col-span-2 relative rounded-xl overflow-hidden group">
                <img alt="Pool Detail"
                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    data-alt="Close up of a luxury infinity pool water surface"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuD7Pg5N35p3ZCS0Pd2gXwb6R5PBtKuAofkooOTa8b5u_a-I-To-iXeI1A9W7RMiMPAcPXgYFn_IQQ_fgzAF-g4KRth7uSUlJGCEYqw0A6eZycPxibbG0hlH36e6jNH_PXPoTSLjICBZcVdL24BDpneml9WDMaSmwreWyQVq8m023qO_VKB3ERyP9zhRjE3lHD3qZO5GFMDK9zt8ioYY7PWNLTe0io55h_rqDfgsD_zY-azdMmhBc60AtpehcjW4YTKAHwdxaE3mDdY" />
            </div>
            <!-- Secondary Image Bottom Left -->
            <div class="relative rounded-xl overflow-hidden group">
                <img alt="Bedroom Suite"
                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    data-alt="Elegant bedroom with white linens and stone wall accents"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuC8iJELsrlBsnq3qTP24gDf2DW4yWg0TwwzSN4nrrqgKt_8XpkCtxMKDdzGsi47plmOIn2PwYpxDWRr64Gbv_BF1WaEDisKqwNrRZZqkAF4cVqwOYp21kO5F8ddOR02iZIShVS90YlymU8fmrQ-WspWGwuSZSf8ItBvb1ukb5WMJ5JHh19fWHQorQuBBJdpm3SdzbL3Ys7XtgHG2dMLx5yLed8ou-bSKSCVEozorlpG8j1qlx8EpKPv4_-reK9kvgKPpl7ZvFLj4p8" />
            </div>
            <!-- Secondary Image Bottom Right -->
            <div class="relative rounded-xl overflow-hidden group">
                <img alt="Gourmet Breakfast"
                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    data-alt="Gourmet breakfast spread on a wooden table"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuATrmyIm3W_HFQu3dCKPPQv7fx5uJeeWRv2wlEr-c_l0Rg4ykUWUe3ONbzOhCu6xVyCPXW0UrkONxI5NLgOywi81ZDSFI9urxrgXLl5NlYc47VA7u0PSh9ABGwkqnvCWL830xiIAJ_gZ6Piyle_PIn-mMmss1f9j0Wie3Qr-qNHIbhnpQ6Q_bIACUbkyYpBK0JmVqmIOIHQOJILPOwaQFsdm23ZGKmQS9sysKELDXvD73nNZlOwJ2Qsguka4ColnLRkHUU6faTx0z0" />
            </div>
        </div>
    </div>
</section>

<!-- Testimonial Section -->
<section class="py-24 bg-stone-50 dark:bg-stone-900">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <span class="material-symbols-outlined text-6xl text-primary/30 mb-8">format_quote</span>
        <blockquote
            class="text-2xl md:text-3xl font-medium text-slate-800 dark:text-slate-200 leading-relaxed mb-10 font-display italic">
            "Une pure merveille en Aveyron. Les vues sont indescriptibles, le silence est d'or et le service est rien
            moins que parfait. L'ultime escapade luxueuse."
        </blockquote>
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 bg-slate-200 rounded-full mb-3 overflow-hidden">
                <img alt="Guest Portrait" class="w-full h-full object-cover" data-alt="Portrait of a smiling woman"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuCjnpRFfrdFsaAXXArAPvzVDli0DbYJYCOAO37YOeMtH-ppBiju_soo1cbmrfU_83bZ6zdq0WlR75d7ksByLKzJKp8PpK51YOyC3SXus2cxdQSapnWBYJyj6lbuyBKDxMDiNhxeLKZ_KSKlJfl5imyAwR4YEpZunPZFLfa7qPViMUBsVoWyonFlclTWOKtwvIXTppCnXglVWX8Md-6AY_4gVLwbW827_eXLz9Vn_ZEkrLeutlnCQol5BLPhxDJ7iXNF2b3zL3T-hXE" />
            </div>
            <cite class="not-italic font-bold text-slate-900 dark:text-white">Isabelle & Marc D.</cite>
            <span class="text-sm text-slate-500">Paris, France</span>
        </div>
    </div>
</section>

<!-- Location Preview -->
<section class="relative w-full h-[400px] flex items-center justify-center bg-slate-900">
    <img alt="Sainte-Eulalie-d'Olt Landscape" class="absolute inset-0 w-full h-full object-cover opacity-60"
        data-alt="Aerial drone view of green rolling hills and a winding river in Aveyron"
        data-location="Sainte-Eulalie-d'Olt, France"
        src="https://lh3.googleusercontent.com/aida-public/AB6AXuBZ3HnrK_P2y0C6d2jopzCrwrKOKELqedui6FbEsNqenagxtHh45I11TmrXNSpK-PVbQYR7mStBfMXaJHCr5wYzGGsHnwcvuJrot6T7pABATSojInLdpSPDY8Di9T5fb4HkNlHbHmtKjvi7Lzty63dctTFCKmrMyYDvTvxRse4nZX5cc2twVM-AUfrV75Yrjnrhe7kVSRHTvt3AGY0hGpY8RjbnoTjMraQQi2HfSMpP9FZtkgKMDtMbQP0gCHlSijhhJy-c-u_zfJE" />
    <div class="relative z-10 text-center px-4">
        <h2 class="text-3xl font-bold text-white mb-4">Sainte-Eulalie-d'Olt</h2>
        <p class="text-slate-200 text-lg mb-8 max-w-lg mx-auto">Découvrez l'un des plus beaux villages de France, à deux
            pas de votre porte.</p>
        <a href="experiences.php"
            class="bg-white text-slate-900 hover:bg-slate-100 font-bold py-3 px-6 rounded-lg transition-colors flex items-center gap-2 mx-auto w-fit">
            <span class="material-symbols-outlined text-primary">map</span>
            Voir la Localisation
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>