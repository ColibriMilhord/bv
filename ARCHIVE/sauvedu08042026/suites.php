<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="relative min-h-[85vh] flex items-center justify-center overflow-hidden">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/20 to-black/60 z-10"></div>
        <div class="w-full h-full bg-cover bg-center animate-ken-burns"
            data-alt="Panoromic view of Aveyron valley from a luxury terrace at sunset"
            style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAAn1w1beQ-_hdCN76cxI8s91pcVRGzD6_9SNUdQGX5bU-Iw21ULiji0Qm-rU7jBp8fScRKffYvjedd9ZaEeRzR8CQERiS6gpfXEYfGYXpUSoAc8OLkere1UI4Vs67aEY5sDsZ3E9JPLq9-rm_wqW1Te5w7oR8UAbaTzFda1VAUyvXawYCU7GqueMlCGkYA3CB1q_9dartD2ZF_jQ3NkF1KpOlONje1zAQd9ofUYedOQqcFoShC-jXzsDBFWJK1_nr4-1a-RqHr_w0');">
        </div>
    </div>
    <div class="relative z-20 container mx-auto px-4 text-center text-white max-w-4xl">
        <span
            class="inline-block py-1 px-3 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 text-xs font-semibold uppercase tracking-wider mb-6">
            Hébergement de Prestige
        </span>
        <h1 class="text-5xl md:text-6xl lg:text-7xl font-black leading-tight tracking-tight mb-6 drop-shadow-lg">
            L'Excellence à Sainte-Eulalie-d'Olt
        </h1>
        <p class="text-lg md:text-xl font-light text-white/90 max-w-2xl mx-auto mb-10 leading-relaxed">
            Une parenthèse enchantée où le luxe contemporain rencontre l'authenticité de l'Aveyron. Découvrez nos
            espaces conçus pour l'exceptionnel.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a class="px-8 py-4 bg-white text-[#111418] rounded-lg font-bold text-sm uppercase tracking-wide hover:bg-gray-100 transition-colors shadow-lg"
                href="#gite">
                Découvrir Le Gîte
            </a>
            <a class="px-8 py-4 bg-primary/90 backdrop-blur-sm text-white rounded-lg font-bold text-sm uppercase tracking-wide hover:bg-primary transition-colors shadow-lg"
                href="#suites">
                Voir Les Suites
            </a>
        </div>
    </div>
    <!-- Scroll indicator -->
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 text-white animate-bounce">
        <span class="material-symbols-outlined !text-3xl">keyboard_arrow_down</span>
    </div>
</section>

<!-- Le Gîte Detail Section -->
<section class="py-20 bg-background-light dark:bg-background-dark" id="gite">
    <div class="max-w-7xl mx-auto px-4 md:px-10">
        <!-- Header -->
        <div class="mb-16 md:flex md:items-end md:justify-between">
            <div class="max-w-2xl">
                <span class="text-primary font-semibold tracking-wider uppercase text-sm mb-2 block">Immersion
                    Panoramique</span>
                <h2 class="text-4xl md:text-5xl font-bold text-[#111418] dark:text-white mb-6">Le Gîte d'Exception</h2>
                <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed">
                    Conçu comme un belvédère sur la vallée du Lot, le Gîte offre des volumes généreux où la frontière
                    entre intérieur et extérieur s'efface. Une rénovation qui célèbre la lumière.
                </p>
            </div>
            <div class="mt-6 md:mt-0">
                <a href="reservation.php"
                    class="flex items-center gap-2 text-[#111418] dark:text-white font-bold border-b-2 border-primary pb-1 hover:text-primary transition-colors">
                    Réserver le gîte entier <span class="material-symbols-outlined">calendar_month</span>
                </a>
            </div>
        </div>
        <!-- Features Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-20">
            <!-- Feature 1: Architecture -->
            <div
                class="bg-white dark:bg-[#1a2632] p-8 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <span class="material-symbols-outlined">architecture</span>
                </div>
                <h3 class="text-xl font-bold mb-3 text-[#111418] dark:text-white">Matériaux Nobles</h3>
                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                    L'alliance subtile de la pierre de pays, du chêne massif et de l'acier corten. Chaque texture
                    raconte une histoire, ancrant la bâtisse dans son terroir tout en affirmant sa modernité.
                </p>
            </div>
            <!-- Feature 2: Comfort -->
            <div
                class="bg-white dark:bg-[#1a2632] p-8 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <span class="material-symbols-outlined">thermostat</span>
                </div>
                <h3 class="text-xl font-bold mb-3 text-[#111418] dark:text-white">Confort Thermique</h3>
                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                    Une atmosphère douce en toute saison grâce au chauffage au sol basse température et à une isolation
                    écologique biosourcée. Le luxe, c'est aussi le bien-être invisible.
                </p>
            </div>
            <!-- Feature 3: Tech -->
            <div
                class="bg-white dark:bg-[#1a2632] p-8 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                    <span class="material-symbols-outlined">home_iot_device</span>
                </div>
                <h3 class="text-xl font-bold mb-3 text-[#111418] dark:text-white">Haute Technologie</h3>
                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                    Système son Devialet, cuisine professionnelle équipée Gaggenau, et domotique intégrée pour piloter
                    l'ambiance lumineuse d'un simple geste.
                </p>
            </div>
        </div>
        <!-- Large Visual with Overlay Info -->
        <div class="relative rounded-3xl overflow-hidden h-[600px] group">
            <div class="absolute inset-0 bg-cover bg-center"
                data-alt="Wide angle shot of the main living area with floor to ceiling windows overlooking the valley"
                style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDEIj_zNsZUNdVXGCKluRqsn5q-XYlSRXzFMqyBoEABcIrYHQPdBGSbN3ZHW-HHOyR-J--VQ6ir8dWLDWQHAc9ftG1FX6Hj40ymnVDfPqiW-k6zWSZEUU_khpHyQ-4pRXMAvbof1tLaLs4Not5-uUqAESCCHxGKaqOD_ckj-4kzCmfZm9fo5t8CjJYzCKDFsKaMiquwHAZDtcOHX83LZqSdoGm6tf8glG1nJ734ZlewEc9PA3bHEvTCEKHjlkgL16mLUkvnEdyrba0');">
            </div>
            <div
                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent p-8 md:p-12">
                <div class="max-w-3xl">
                    <h3 class="text-white text-2xl md:text-3xl font-bold mb-4">Le Grand Salon Cathédrale</h3>
                    <p class="text-white/80 mb-6">
                        65m² de convivialité sous une charpente apparente spectaculaire. Le cœur battant du Gîte, ouvert
                        sur une terrasse de 120m² exposée plein sud.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <span
                            class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-white text-xs border border-white/20">Cheminée
                            suspendue</span>
                        <span
                            class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-white text-xs border border-white/20">TV
                            OLED 4K</span>
                        <span
                            class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-white text-xs border border-white/20">Billard
                            Français</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Transition / Divider -->
<div class="w-full h-24 bg-background-light dark:bg-background-dark flex items-center justify-center">
    <div class="h-[1px] w-1/3 bg-gray-200 dark:bg-gray-700"></div>
    <div class="mx-8 text-primary/40">
        <span class="material-symbols-outlined">star</span>
    </div>
    <div class="h-[1px] w-1/3 bg-gray-200 dark:bg-gray-700"></div>
</div>

<!-- Les Suites Detail Section -->
<section class="py-20 bg-white dark:bg-[#151b23]" id="suites">
    <div class="max-w-7xl mx-auto px-4 md:px-10">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <h2 class="text-4xl md:text-5xl font-bold text-[#111418] dark:text-white mb-6">Les Suites</h2>
            <p class="text-lg text-slate-600 dark:text-slate-300">
                L'intimité raffinée. Chaque suite possède sa propre identité, inspirée par les nuances de l'Aubrac et de
                la Vallée du Lot.
            </p>
        </div>
        <!-- Suite 1: Left Aligned -->
        <div class="flex flex-col md:flex-row gap-10 items-center mb-24">
            <div class="w-full md:w-1/2">
                <div class="relative rounded-2xl overflow-hidden aspect-[4/3] shadow-lg group">
                    <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110"
                        data-alt="Modern bedroom with wooden accents and soft beige tones"
                        style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBviG9r2krg5eClj_42p22M2Vmm2nJgFPyaSrg2DDqChR8K9he96AxpFTUyyd_MuPeG7dbmNq-cRH8XPCqpAssGl0nmL0WS4EUP0kBykZRqUnNapdUSCEIUHcRc-9u3901iPCV9-F4L950o3O6El2YD4-h0sooB5ewYu6X56gftu35fD764e6OWS8SkSNtpt-G-FLFORYXMRKunBIZmTm2qKbtJOOzAFEO9Nb352M5a9c1BfXLe5HEQeBiU_C58yolLWd1RAJX9Bck');">
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 md:pl-8">
                <h3 class="text-3xl font-bold text-[#111418] dark:text-white mb-4">La Suite "Aubrac"</h3>
                <p class="text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                    Une ode à la terre et à la matière. Les murs chaulés et le bois brut créent un cocon apaisant. La
                    salle de bain ouverte dispose d'une baignoire îlot face à la nature.
                </p>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-3 text-sm text-[#111418] dark:text-white font-medium">
                        <span class="material-symbols-outlined text-primary">king_bed</span> Lit King Size (180x200)
                        literie hôtelière
                    </li>
                    <li class="flex items-center gap-3 text-sm text-[#111418] dark:text-white font-medium">
                        <span class="material-symbols-outlined text-primary">shower</span> Douche à l'italienne effet
                        pluie
                    </li>
                    <li class="flex items-center gap-3 text-sm text-[#111418] dark:text-white font-medium">
                        <span class="material-symbols-outlined text-primary">coffee_maker</span> Machine Nespresso
                        Vertuo
                    </li>
                </ul>
                <a href="reservation.php"
                    class="text-primary font-bold text-sm uppercase tracking-wide border-b border-primary pb-1 hover:text-primary/80 transition-colors">
                    Voir les disponibilités
                </a>
            </div>
        </div>
        <!-- Suite 2: Right Aligned -->
        <div class="flex flex-col md:flex-row-reverse gap-10 items-center">
            <div class="w-full md:w-1/2">
                <div class="relative rounded-2xl overflow-hidden aspect-[4/3] shadow-lg group">
                    <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110"
                        data-alt="Luxury bedroom with large window and blue accents"
                        style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAdydTEHDH_iYN8BKPfh45hlHaS0WoRFxmc5v1XXC92ygXqyEKwetC_JOyOP21x681eLALJenGrTe2cPoWqqf5m2nLKsHoWXJJZRSOqcWvCw0vORNll0yw8VgZaUvyp0QasrolpYLBSShR01FW4o3hU_XK-xs6ZoHDqe6Z0bVcBfEmuTiaYm6D94WmsRuqzu5H9gYdt2H8Ue7KLhwopfT-m9rfunUjFGrkOmMJ5h0KlG5PPkgozjm9v2fPmXxqPPPJgcdSdEZx9Ix8');">
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 md:pr-8">
                <h3 class="text-3xl font-bold text-[#111418] dark:text-white mb-4">La Suite "Rivière"</h3>
                <p class="text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                    Baignée de lumière, cette suite joue avec les reflets de l'eau. Les tons bleus et minéraux invitent
                    à la rêverie. Idéale pour se ressourcer au son du Lot en contrebas.
                </p>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-3 text-sm text-[#111418] dark:text-white font-medium">
                        <span class="material-symbols-outlined text-primary">balcony</span> Terrasse privative suspendue
                    </li>
                    <li class="flex items-center gap-3 text-sm text-[#111418] dark:text-white font-medium">
                        <span class="material-symbols-outlined text-primary">ac_unit</span> Climatisation réversible
                        silencieuse
                    </li>
                    <li class="flex items-center gap-3 text-sm text-[#111418] dark:text-white font-medium">
                        <span class="material-symbols-outlined text-primary">wifi</span> Fibre optique dédiée
                    </li>
                </ul>
                <a href="reservation.php"
                    class="text-primary font-bold text-sm uppercase tracking-wide border-b border-primary pb-1 hover:text-primary/80 transition-colors">
                    Voir les disponibilités
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>