/**
 * js/animations.js — Ajouts dynamiques pour Bellevue d'Aveyron
 * À inclure dans index.php juste avant </body>
 * <script src="js/animations.js"></script>
 */

document.addEventListener('DOMContentLoaded', () => {

    // ─────────────────────────────────────────────────────────────
    // 1. SCROLL REVEAL — Intersection Observer
    // ─────────────────────────────────────────────────────────────
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                // Délai en cascade pour les éléments enfants
                const delay = parseInt(entry.target.dataset.delay || 0);
                setTimeout(() => entry.target.classList.add('visible'), delay);
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    // Appliquer la classe reveal sur les sections clés
    const revealTargets = [
        { selector: '.amenity-box',    cls: 'reveal',       baseDelay: 80  },
        { selector: '.stat-item',      cls: 'reveal',       baseDelay: 100 },
        { selector: '.review-card',    cls: 'reveal',       baseDelay: 120 },
        { selector: '.access-item',    cls: 'reveal',       baseDelay: 80  },
        { selector: '.pricing-row',    cls: 'reveal',       baseDelay: 100 },
        { selector: '.exp-text-block', cls: 'reveal-left',  baseDelay: 0   },
        { selector: '.villa-visual',   cls: 'reveal-right', baseDelay: 0   },
    ];

    revealTargets.forEach(({ selector, cls, baseDelay }) => {
        document.querySelectorAll(selector).forEach((el, idx) => {
            el.classList.add(cls);
            el.dataset.delay = idx * baseDelay;
            revealObserver.observe(el);
        });
    });

    // ─────────────────────────────────────────────────────────────
    // 2. FLIP CARDS — Refactoring des .amenity-box
    // ─────────────────────────────────────────────────────────────
    // Données des faces arrière (info supplémentaire)
    const flipData = [
        { title: 'Piscine Chauffée', extra: 'Volet sécurisé homologué. Température maintenue à 28°C d\'avril à octobre. Transat et parasols inclus.' },
        { title: 'Vue Panoramique',  extra: 'Orientation plein sud. Lever et coucher de soleil spectaculaires sur la vallée du Lot et les monts d\'Aubrac.' },
        { title: 'Borne Électrique', extra: 'Chargeur 18 kVA compatible toutes marques (Tesla, Renault, Peugeot…). Accès gratuit pour nos hôtes.' },
        { title: 'Divertissement',   extra: 'Fibre optique 1 Gbit/s. Baby-foot Bonzini professionnel. 2 VTT adultes et 1 VTT enfant à disposition.' },
    ];

    document.querySelectorAll('.amenity-box').forEach((box, i) => {
        const data = flipData[i];
        if (!data) return;

        const icon    = box.querySelector('.amenity-icon')?.innerHTML || '✦';
        const title   = box.querySelector('h3')?.textContent          || data.title;
        const textFront = box.querySelector('p')?.textContent         || '';

        box.innerHTML = `
            <div class="amenity-box-inner">
                <div class="amenity-face amenity-front">
                    <div class="amenity-icon">${icon}</div>
                    <h3>${title}</h3>
                    <p>${textFront}</p>
                </div>
                <div class="amenity-face amenity-back">
                    <div class="amenity-icon" style="font-size:1.8rem; margin-bottom:14px;">ℹ</div>
                    <h3>${title}</h3>
                    <p>${data.extra}</p>
                </div>
            </div>`;
        box.setAttribute('tabindex', '0');
        box.setAttribute('role', 'button');
        box.setAttribute('aria-label', `Plus d'infos sur ${title}`);
    });

    // ─────────────────────────────────────────────────────────────
    // 3. ANIMATED COUNTERS pour les stats
    // ─────────────────────────────────────────────────────────────
    function animateCounter(el, target, suffix = '') {
        const duration = 1800;
        const start    = performance.now();
        const startVal = 0;

        function update(now) {
            const elapsed  = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // Easing: easeOutExpo
            const ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            const current = Math.round(startVal + (target - startVal) * ease);
            el.textContent = current + suffix;
            if (progress < 1) requestAnimationFrame(update);
        }
        requestAnimationFrame(update);
    }

    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const h4 = entry.target.querySelector('h4');
            if (!h4 || h4.dataset.animated) return;
            h4.dataset.animated = '1';
            const val = parseInt(h4.textContent);
            if (!isNaN(val)) animateCounter(h4, val);
            statsObserver.unobserve(entry.target);
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stat-item').forEach(el => statsObserver.observe(el));

    // ─────────────────────────────────────────────────────────────
    // 4. HERO — Ajout du scroll indicator et des strips saison
    // ─────────────────────────────────────────────────────────────
    const hero = document.querySelector('.hero');
    if (hero) {
        // Scroll indicator
        const si = document.createElement('div');
        si.className = 'scroll-indicator';
        si.setAttribute('title', 'Défiler');
        si.onclick = () => document.getElementById('experience')?.scrollIntoView({ behavior: 'smooth' });
        si.innerHTML = `<div class="si-text">Découvrir</div><div class="si-line"></div>`;
        hero.appendChild(si);

        // Season strip (dynamique selon mois)
        const month = new Date().getMonth(); // 0 = janv
        const seasonItems = [
            // Printemps (mars-mai)
            ...(month >= 2 && month <= 4 ? [
                { icon: '🌸', label: 'Printemps en fleur' },
                { icon: '🎣', label: 'Ouverture pêche' },
                { icon: '🥾', label: 'Randonnées idéales' },
            ] : []),
            // Été (juin-août)
            ...(month >= 5 && month <= 7 ? [
                { icon: '☀️', label: 'Piscine chauffée' },
                { icon: '🛶', label: 'Canoë & Paddle' },
                { icon: '🎪', label: 'Fêtes locales' },
            ] : []),
            // Automne (sept-nov)
            ...(month >= 8 && month <= 10 ? [
                { icon: '🍂', label: 'Couleurs d\'automne' },
                { icon: '🍄', label: 'Forêts & Nature' },
                { icon: '⛪', label: 'Nocturnes Conques' },
            ] : []),
            // Hiver (déc-fév)
            ...(month >= 11 || month <= 1 ? [
                { icon: '❄️',  label: 'Séjour ressourçant' },
                { icon: '🔥', label: 'Feu de cheminée' },
                { icon: '🧀', label: 'Gastronomie hivernale' },
            ] : []),
        ];

        if (seasonItems.length > 0) {
            const strip = document.createElement('div');
            strip.className = 'hero-season-strip';
            strip.innerHTML = seasonItems.map(s =>
                `<div class="hero-season-item"><span class="sicon">${s.icon}</span><span>${s.label}</span></div>`
            ).join('');
            hero.appendChild(strip);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 5. PARALLAX léger sur le hero au scroll
    // ─────────────────────────────────────────────────────────────
    if (window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
        const heroContent = document.querySelector('.hero-content');
        if (heroContent) {
            window.addEventListener('scroll', () => {
                const scrolled = window.scrollY;
                if (scrolled < window.innerHeight) {
                    heroContent.style.transform = `translateY(${scrolled * 0.25}px)`;
                    heroContent.style.opacity   = 1 - (scrolled / (window.innerHeight * 0.7));
                }
            }, { passive: true });
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 6. NAVIGATION — Ajout lien "La Région" avec badge
    // ─────────────────────────────────────────────────────────────
    const navLinks = document.getElementById('navLinks');
    if (navLinks) {
        // Vérifier si le lien n'existe pas déjà
        const existing = navLinks.querySelector('a[href="decouvrir.php"]');
        if (!existing) {
            const li = document.createElement('li');
            li.innerHTML = `<a href="decouvrir.php" class="nav-region">La Région <span class="nav-region-badge">Nouveau</span></a>`;
            // Insérer avant "Contact" ou en dernier
            const contactLi = Array.from(navLinks.querySelectorAll('li'))
                .find(l => l.querySelector('a')?.getAttribute('href') === '#contact');
            if (contactLi) {
                navLinks.insertBefore(li, contactLi);
            } else {
                navLinks.appendChild(li);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 7. TÉMOIGNAGES — Auto-highlight du central au scroll
    // ─────────────────────────────────────────────────────────────
    const reviewsSection = document.getElementById('temoignages');
    if (reviewsSection) {
        const reviewObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Animation cascade
                    document.querySelectorAll('.review-card').forEach((card, i) => {
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, i * 150);
                    });
                    reviewObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });
        reviewObserver.observe(reviewsSection);

        // Init invisible
        document.querySelectorAll('.review-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 8. SMOOTH SCROLL pour tous les liens ancres
    // ─────────────────────────────────────────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

});
