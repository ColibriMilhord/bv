<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<header class="relative pt-20">
    <div class="relative h-[85vh] w-full overflow-hidden">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 bg-cover bg-center"
            data-alt="Panoramic view of a medieval French village in a valley with a river running through it at sunset"
            style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAZWV5fNWpR5Il3GWuybozLDifGMQhnlaCscaa6a7khCOD3zZBrvPMndQkXbU6mC6KEBcGwr6HyJNdjYOH6g4R91B1Qxn5RQSUhhw08UcWKyjpsGsOvZjlq6X4qYbSU7d4EaBW5a59a8kwlpz9C_e4lqGviANhACy5S2murnoMsngKxlz4EIVd3Epyu-Luq9Vds11kGwKCJ7gRMUuYPu-pjcYnQ8gWeAhnkDiTdmpfMAndzzz4QbwyO48JQK0GVE9w83zjA6Nxn0m8');">
        </div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/20 to-black/60"></div>
        <!-- Content -->
        <div
            class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center items-center text-center">
            <span
                class="inline-block py-1 px-3 rounded-full bg-white/20 backdrop-blur-sm text-white text-xs font-bold tracking-widest uppercase mb-6 border border-white/30">
                Guide Local & Activités
            </span>
            <h1
                class="text-4xl md:text-6xl lg:text-7xl font-light text-white leading-tight tracking-tight mb-6 max-w-4xl">
                Sainte-Eulalie-d'Olt & <br /> <span class="font-bold">Expériences d'Exception</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-200 max-w-2xl font-light mb-10 leading-relaxed">
                Un voyage sensoriel entre patrimoine médiéval classé, gastronomie aveyronnaise et nature sauvage
                préservée.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a class="flex items-center justify-center h-12 px-8 rounded-full bg-white text-slate-900 text-sm font-bold hover:bg-gray-100 transition-all shadow-lg"
                    href="#patrimoine">
                    Explorer le Guide
                </a>
            </div>
        </div>
        <!-- Scroll Indicator -->
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce text-white/70">
            <span class="material-symbols-outlined text-4xl">keyboard_arrow_down</span>
        </div>
    </div>
</header>

<!-- Main Content Layout -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 bg-white dark:bg-background-dark">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        <!-- Sticky Sidebar Navigation (Desktop) -->
        <aside class="hidden lg:block lg:col-span-3">
            <div class="sticky top-32 space-y-8">
                <div
                    class="bg-white dark:bg-[#1a2632] p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">Sommaire</h4>
                    <nav class="flex flex-col space-y-3">
                        <a class="group flex items-center gap-3 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-primary transition-colors"
                            href="#patrimoine">
                            <span
                                class="w-1.5 h-1.5 rounded-full bg-gray-300 group-hover:bg-primary transition-colors"></span>
                            Patrimoine & Architecture
                        </a>
                        <a class="group flex items-center gap-3 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-primary transition-colors"
                            href="#gastronomie">
                            <span
                                class="w-1.5 h-1.5 rounded-full bg-gray-300 group-hover:bg-primary transition-colors"></span>
                            Gastronomie Locale
                        </a>
                        <a class="group flex items-center gap-3 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-primary transition-colors"
                            href="#nature">
                            <span
                                class="w-1.5 h-1.5 rounded-full bg-gray-300 group-hover:bg-primary transition-colors"></span>
                            Nature & Aventure
                        </a>
                    </nav>
                </div>
                <!-- Mini Weather Widget -->
                <div class="bg-primary/5 p-6 rounded-2xl border border-primary/10">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-slate-900 dark:text-white">Météo locale</span>
                        <span class="material-symbols-outlined text-primary">wb_sunny</span>
                    </div>
                    <div class="text-3xl font-bold text-slate-900 dark:text-white mb-1">24°C</div>
                    <p class="text-xs text-slate-500">Ciel dégagé, parfait pour la randonnée.</p>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="lg:col-span-9 space-y-24">
            <!-- Introduction -->
            <section>
                <div class="max-w-3xl">
                    <h2 class="text-3xl font-light text-slate-900 dark:text-white mb-6 leading-tight">
                        Bienvenue dans l'un des <strong class="font-bold">Plus Beaux Villages de France</strong>
                    </h2>
                    <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                        Niché au cœur de la vallée, Sainte-Eulalie-d'Olt est un joyau médiéval où le temps semble s'être
                        arrêté.
                        Depuis votre <strong>gîte avec vue panoramique sur la vallée du Lot</strong>, vous êtes aux
                        premières loges pour explorer
                        ce territoire d'exception. Ruelles pavées, façades fleuries et art de vivre aveyronnais vous
                        attendent à chaque coin de rue.
                    </p>
                </div>
            </section>

            <!-- Heritage Section -->
            <section class="scroll-mt-32" id="patrimoine">
                <div class="flex items-center gap-4 mb-8">
                    <span
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 text-primary">
                        <span class="material-symbols-outlined">castle</span>
                    </span>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Patrimoine & Architecture</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Card 1 -->
                    <div
                        class="group relative bg-white dark:bg-[#1a2632] rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-800">
                        <div class="aspect-[4/3] overflow-hidden">
                            <img alt="Facade of a historic stone church with Romanesque architecture"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                                data-alt="Facade of a historic stone church with Romanesque architecture"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuChmHQqVdPMGbqtzSKEHO7qBfsbJ1PBGS3HZUjSwW3AK7RqiyLqpgoa9hea3VPmzI1kBP8lxaBMaN_yQCVDTGuGmzeNjiyaxtK6Hy38fr3SygjZVnWbYOmDO8VhWTuX20Sg9EQt8rohbIGFMqPKG0oFflxcBoeR_Q8_IxKpRGJRYFMZwQ4ZJnFW4sJ9GUeK-fYYt_PKqKafWI2habZxiNb_q2h7KgjyN1UxXerOmV5Y6V7_rmN7XrMHzYz6JvI-iU9osAUX-M6MOts" />
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">L'Église du XIe Siècle</h3>
                                <span
                                    class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-medium text-slate-500">5
                                    min à pied</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 text-sm mb-4 line-clamp-3">
                                Un chef-d'œuvre de l'art roman, célèbre pour ses reliques sacrées et son architecture
                                remaniée au gothique. Un incontournable pour les amateurs d'histoire.
                            </p>
                        </div>
                    </div>
                    <!-- Card 2 -->
                    <div
                        class="group relative bg-white dark:bg-[#1a2632] rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-800">
                        <div class="aspect-[4/3] overflow-hidden">
                            <img alt="Detail of a medieval castle tower against a blue sky"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                                data-alt="Detail of a medieval castle tower against a blue sky"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuB4HeQOsbNEZoPuRyYk66K6wJ1uWaORSwbyFbkkjRK0D5RX-dtt43ONsejNDJ8-SRlZcC6CgAhuIhxDwe_wwysLPM7sQP_O3G0ONtpyejWq9OTTVXc2heeHFWCLdvEFjW4yCvDX_qIfOS88prCL0fE05gDPWSjAvOMWzy6rJlWBwlipcpFTgGQVisxM2wuYI_Uy95h56jor2CLrqckXCmorvv-Qu-S6835U3eQkr7yVbw6CpZAivnLCBa_VFwppoSeTxGRR55PkSmo" />
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Château de Curières</h3>
                                <span
                                    class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-medium text-slate-500">15
                                    min voiture</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 text-sm mb-4 line-clamp-3">
                                Dominant la vallée, ce château privé ouvre ses portes lors des journées du patrimoine.
                                Ses jardins offrent une vue imprenable sur l'Aubrac.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Nature & Outdoors Section -->
            <section class="scroll-mt-32" id="nature">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <span
                            class="flex items-center justify-center w-10 h-10 rounded-full bg-green-50 dark:bg-green-900/30 text-green-600">
                            <span class="material-symbols-outlined">hiking</span>
                        </span>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Nature & Évasion</h2>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Activity 1 -->
                    <div
                        class="bg-white dark:bg-[#1a2632] rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-800">
                        <div class="h-48 rounded-lg bg-cover bg-center mb-4 relative"
                            data-alt="Green rolling hills landscape with blue sky"
                            style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAmnF-90CSTJDzZCLcSHHR9R_OgT36-QfkHel9CiiTtwfCNy95MWvmIKhj5QArUD0YFB0eJICBRoKIjb_y4Fc-5kOz8Y0ftw3Wlj_y_EQyCAPTg0AOedn1I0j7IEZejkcccOQ-24NW-w80BIcDdbvanABwe5gT1BOsjnYlpW9GccRZYheoB9icpkyTqJuoTXu0Qs8SL6x6eDYEtEBAr1mq6HIKGbYu2k10G82En-k2o3D89L-KQDIJZZq9GbtQjBN2GDbMWmmr1C9I');">
                            <span
                                class="absolute top-2 right-2 px-2 py-1 bg-white/90 backdrop-blur text-xs font-bold rounded text-slate-900">3h</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Randonnée sur l'Aubrac</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                            Des étendues infinies, une lumière unique. Le plateau de l'Aubrac est un désert vert
                            apaisant.
                        </p>
                    </div>
                    <!-- Activity 2 -->
                    <div
                        class="bg-white dark:bg-[#1a2632] rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-800">
                        <div class="h-48 rounded-lg bg-cover bg-center mb-4 relative"
                            data-alt="Kayaks on a calm river surrounded by lush green trees"
                            style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAquHCvtHQ90-Hr0_6heYXvvslPAKcyMrzmEqgHDS778Nrcj8b8gj8ZQpUg4PfdkLeKrc9XCiDeC7UsdiS1ij4XigU7dfh9wBOH7wPi01VdRqcP7SJl3_CBXXv_He7-2-kS5GJBK_hPV3UbNto72YNlxpAW88Q4oC7iFLxf-eFuziV8iulZfzDyOM2zMfUlCO3XrBrjRjKckdqTJS49yT_ImUQuedkHOk_lDCmHxaHOrFSWDx_H8ZEmaITnbcdX3TapnzGIlSLVr-I');">
                            <span
                                class="absolute top-2 right-2 px-2 py-1 bg-white/90 backdrop-blur text-xs font-bold rounded text-slate-900">2h</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Canoë sur le Lot</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                            Découvrez la vallée depuis l'eau. Une activité rafraîchissante accessible aux familles.
                        </p>
                    </div>
                    <!-- Activity 3 -->
                    <div
                        class="bg-white dark:bg-[#1a2632] rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-800">
                        <div class="h-48 rounded-lg bg-cover bg-center mb-4 relative"
                            data-alt="Person fly fishing in a river stream"
                            style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDQMizjMFvo6sV_ehZsFmdVQqwIV1zuWxK4ftsdDCEOSc63hfRPgmqsmdZFPFpf0QvMOK7UG9jvw38h33TMaKRHamWjQhDAqXaAdn3_9dDoWG9JGY4kI8eXuHgh8IzYH7sHhHMkx5SGdrB-ZXCYkzKOvZ6kT1v-JIdUO9SKBjrOsSlRK5cLmRdmYisvsjQ70qU3hbBcLy6MLB2jNyONOwbOQzZYLRdv2xVbd2RQFYsiSYxUt1HBWHJVCwByqcCuLeUae6i3chAKc_s');">
                            <span
                                class="absolute top-2 right-2 px-2 py-1 bg-white/90 backdrop-blur text-xs font-bold rounded text-slate-900">1/2
                                Journée</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Pêche à la Mouche</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                            La rivière Lot est réputée pour ses eaux poissonneuses. Initiation possible avec expert.
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>