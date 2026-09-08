<?php
// decouvrir.php — Page "Découvrir la région" pour Bellevue d'Aveyron
// Partie statique : points d'intérêt touristiques autour de Sainte-Eulalie-d'Olt
// Partie dynamique : agenda local via API Claude (JavaScript)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Découvrir la Région | Bellevue d'Aveyron</title>
    <link rel="icon" type="image/x-icon" href="images/BELLEVUE/logo.ico">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- HEADER (identique au site principal) -->
    <header id="navbar">
        <nav>
            <div class="logo">Bellevue d'Aveyron<span>VILLA 5 ÉTOILES</span></div>
            <div class="menu-toggle" onclick="toggleMenu()">
                <div class="bar"></div><div class="bar"></div><div class="bar"></div>
            </div>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php?skip=1#accueil" onclick="toggleMenu()">Accueil</a></li>
                <li><a href="index.php?skip=1#experience" onclick="toggleMenu()">La Villa</a></li>
                <li><a href="index.php?skip=1#services" onclick="toggleMenu()">Services</a></li>
                <li><a href="index.php?skip=1#tarifs" onclick="toggleMenu()">Tarifs</a></li>
                <li><a href="decouvrir.php" onclick="toggleMenu()" style="color:var(--gold-text);">Visiter l'Aveyron</a></li>
                <li><a href="index.php?skip=1#contact" onclick="toggleMenu()">Contact</a></li>
                <li class="menu-phone"><a href="tel:+33680907107" style="color:var(--gold-text); font-weight:600;">✆ 06 80 90 71 07</a></li>
                <li><a href="index.php?skip=1#reservation" class="btn-book-now" onclick="toggleMenu()">Réserver</a></li>
            </ul>
        </nav>
    </header>

    <!-- HERO SECTION -->
    <div class="region-hero">
        <div class="hero-content">
            <span class="badge" style="background: rgba(255,255,255,0.15); backdrop-filter:blur(5px);">Gîte Bellevue d'Aveyron</span>
            <h1 class="fade-in">Explorez l'Aveyron</h1>
            <p class="fade-in delay-1">Une sélection éditoriale pour des instants inoubliables au départ de Sainte-Eulalie-d'Olt.</p>
        </div>
    </div>

    <!-- INTRO -->
    <div class="region-intro">
        <p>
            Entre le plateau de l'<strong>Aubrac</strong> et les méandres du <strong>Lot</strong>, autour de votre base à Sainte-Eulalie-d'Olt, s'étirent des paysages qui varient à chaque saison — des genêts en fleur du printemps aux couleurs fauves de l'automne, en passant par les étés où la rivière invite à la baignade. Randonnées, pêche, villages classés, gastronomie, culture… <strong>Une semaine ne suffira pas.</strong>
        </p>
    </div>

    <!-- THÈMES TOURISTIQUES -->
    <section class="themes-section" style="padding-top: 100px;">
        <div class="section-header" style="text-align: center; margin-bottom: 80px;">
            <span class="subtitle" style="font-size:0.9rem;">Votre Carnet de Voyage</span>
            <h2 style="font-size:2.8rem; margin-top:10px;">Votre Séjour Idéal</h2>
        </div>

        <!-- 4 Expériences "Digital Curator" (Fusion du contenu lourd) -->
        <div class="curator-grid">

            <!-- Nature & Randonnée -->
            <a href="#agenda" onclick="document.querySelector('.agenda-filter-btn[data-filter=\'nature\']').click();" class="curator-card">
                <div class="curator-img" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/5/56/Plateau_de_l%27Aubrac.JPG/1280px-Plateau_de_l%27Aubrac.JPG');"></div>
                <div class="curator-body">
                    <h3>Nature & Randonnées</h3>
                    <p>De l'immensité du plateau volcanique de l'Aubrac aux berges boisées du Lot. Partez sur 3 sentiers balisés directement depuis la porte du gîte ou enfourchez un VTT face aux sommets aveyronnais.</p>
                    <span class="curator-btn">Trouver une Sortie Nature</span>
                </div>
            </a>

            <!-- Patrimoine & Culture -->
            <a href="#agenda" onclick="document.querySelector('.agenda-filter-btn[data-filter=\'culture\']').click();" class="curator-card">
                <div class="curator-img" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Village_de_Conques_%28Aveyron%29.JPG/1280px-Village_de_Conques_%28Aveyron%29.JPG');"></div>
                <div class="curator-body">
                    <h3>Patrimoine Merveilleux</h3>
                    <p>Découvrez Conques, haut-lieu de la chrétienté avec son fameux trésor, et les "Plus Beaux Villages de France" voisins comme Saint-Côme-d'Olt ou Sainte-Eulalie. Un voyage dans l'Histoire.</p>
                    <span class="curator-btn">Explorer le Patrimoine</span>
                </div>
            </a>

            <!-- Expériences Famille & Lac -->
            <a href="#agenda" onclick="document.querySelector('.agenda-filter-btn[data-filter=\'famille\']').click();" class="curator-card">
                <div class="curator-img" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Lot_river_Aveyron.jpg/1280px-Lot_river_Aveyron.jpg');"></div>
                <div class="curator-body">
                    <h3>Expériences en Famille</h3>
                    <p>Du bateau sans permis électrique sur le lac de Castelnau à l'initiation au paddle sur le Lot, en passant par la majesté du gigantesque Viaduc de Millau ou la découverte artisanale de Laguiole.</p>
                    <span class="curator-btn">Voir l'Agenda Famille</span>
                </div>
            </a>

            <!-- Gastronomie Locale -->
            <a href="#agenda" onclick="document.querySelector('.agenda-filter-btn[data-filter=\'restaurant\']').click();" class="curator-card">
                <div class="curator-img" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Aubrac_cow.jpg/1280px-Aubrac_cow.jpg');"></div>
                <div class="curator-body">
                    <h3>Gastronomie & Terroir</h3>
                    <p>Toute la convivialité aveyronnaise dans une assiette : tomme, aligot authentique en buron, bœuf de l'Aubrac et les innombrables marchés de producteurs à la tombée de la nuit en été.</p>
                    <span class="curator-btn">Découvrir les Marchés</span>
                </div>
            </a>

        </div>
    </section>

    <!-- DISTANCES CLÉS -->
    <section class="distances-section" style="padding: 40px 5%; background: var(--navy-dark);">
        <details style="max-width: 1200px; margin: 0 auto; cursor: pointer;">
            <summary style="color: #fff; font-family: 'Playfair Display', serif; font-size: 1.5rem; text-align: center; list-style: none; display: flex; align-items: center; justify-content: center; gap: 10px;">
                Bellevue d'Aveyron : Tout est proche (Voir les distances) <span>↓</span>
            </summary>
            <div style="margin-top: 40px;">
        <div class="distances-grid">
            <div class="dist-item">
                <div class="dist-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
                <div class="dist-info">
                    <span class="dist-name">Saint-Geniez-d'Olt</span>
                    <span class="dist-km">2 km · 5 min</span>
                </div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16c-4.68 0-8.68-2.6-10-6.5-1.32 3.9-5.32 6.5-10 6.5"/><path d="M22 20c-4.68 0-8.68-2.6-10-6.5C10.68 17.4 6.68 20 2 20"/><path d="M12 4v9.5"/></svg></div>
                <div class="dist-info">
                    <span class="dist-name">Lac de Castelnau</span>
                    <span class="dist-km">2 km · 5 min</span>
                </div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 22h16"/><path d="M4 6h16"/><path d="M4 6l8-4 8 4"/><path d="M6 22V6"/><path d="M10 22V6"/><path d="M14 22V6"/><path d="M18 22V6"/></svg></div>
                <div class="dist-info">
                    <span class="dist-name">Saint-Côme-d'Olt</span>
                    <span class="dist-km">5 km · 8 min</span>
                </div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 12c4 0 4 4 8 4s4-4 8-4 4 4 8 4"/><path d="M2 18c4 0 4 4 8 4s4-4 8-4 4 4 8 4"/><path d="M2 6c4 0 4 4 8 4s4-4 8-4 4 4 8 4"/></svg></div>
                <div class="dist-info">
                    <span class="dist-name">Espalion</span>
                    <span class="dist-km">12 km · 15 min</span>
                </div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22v-8"/><path d="M12 14c-3-2-5-6-5-6s4-1 5 6"/><path d="M12 14c3-2 5-6 5-6s-4-1-5 6"/></svg></div>
                <div class="dist-info">
                    <span class="dist-name">Aubrac (plateau)</span>
                    <span class="dist-km">25 km · 30 min</span>
                </div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v20"/><path d="M7 7h10"/></svg></div>
                <div class="dist-info">
                    <span class="dist-name">Conques</span>
                    <span class="dist-km">55 km · 50 min</span>
                </div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m14.5 9-6 6"/><path d="m9.5 5-2 2"/><path d="m15.5 15-2 2"/><path d="m11 11-4 4-2.5-2.5a2.12 2.12 0 1 1 3-3L11 11Z"/><path d="m14 14 4-4 2.5 2.5a2.12 2.12 0 1 1-3 3L14 14Z"/></svg></div>
                <div class="dist-info">
                    <span class="dist-name">Laguiole</span>
                    <span class="dist-km">30 km · 35 min</span>
                </div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 10v12"/><path d="M20 10v12"/><path d="M2 10h20"/><path d="M4 4v6"/><path d="M20 4v6"/></svg></div>
                <div class="dist-info">
                    <span class="dist-name">Viaduc de Millau</span>
                    <span class="dist-km">75 km · 1h</span>
                </div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10c1.06 0 1.95-.87 1.95-1.93 0-.49-.18-.95-.51-1.31-.32-.34-.52-.81-.52-1.32 0-1.05.85-1.94 1.9-1.94h2.24c3.27 0 5.94-2.67 5.94-5.94C23 6.08 18.06 2 12 2z"/></svg></div>
                <div class="dist-info">
                    <span class="dist-name">Rodez · Musée Soulages</span>
                    <span class="dist-km">50 km · 45 min</span>
                </div>
        </div>
            </div>
        </details>
    </section>
    <!-- AGENDA DYNAMIQUE (alimenté par l'API Claude) -->
    <section id="agenda" class="agenda-section">
        <div class="agenda-header-row">
            <div class="section-header">
                <span class="subtitle">Ce qui se passe en ce moment</span>
                <h2>Agenda de la Région</h2>
            </div>
            <div class="agenda-controls">
                <button class="agenda-filter-btn active" data-filter="all" onclick="loadAgenda('all', this)">Tout</button>
                <button class="agenda-filter-btn" data-filter="nature"   onclick="loadAgenda('nature', this)">Nature</button>
                <button class="agenda-filter-btn" data-filter="culture"  onclick="loadAgenda('culture', this)">Culture</button>
                <button class="agenda-filter-btn" data-filter="famille"  onclick="loadAgenda('famille', this)">Famille</button>
                <button class="agenda-filter-btn" data-filter="fete"     onclick="loadAgenda('fete', this)">Fêtes & Marchés</button>
                <button class="agenda-filter-btn" data-filter="restaurant" onclick="loadAgenda('restaurant', this)">Restaurant</button>
            </div>
        </div>

        <div id="agenda-output">
            <div class="agenda-loading" id="agenda-loading">
                <div class="agenda-spinner"></div>
                <span>Recherche des événements autour de Sainte-Eulalie-d'Olt…</span>
            </div>
        </div>
    </section>

    <!-- CTA RÉSERVATION -->
    <div class="region-cta">
        <h2>Prêt à vivre l'Aveyron ?</h2>
        <p>Bellevue d'Aveyron vous offre le confort 5 étoiles pour rayonner librement dans cette nature exceptionnelle. Piscine chauffée, VTT à disposition, borne électrique.</p>
        <a href="index.php?skip=1#reservation" class="btn-gold">Vérifier les disponibilités</a>
    </div>

    <!-- FOOTER -->
    <footer id="footer-luxe">
        <div class="footer-container">
            <div class="footer-col brand-col">
                <div class="footer-logo">Bellevue d'Aveyron<span>Villa 5 Étoiles</span></div>
                <p class="footer-desc">Un sanctuaire de paix au cœur de l'Aveyron.</p>
                <div class="footer-socials">
                    <a href="https://www.instagram.com/gitebellevuedaveyron/" target="_blank" class="social-link">Instagram</a>
                    <a href="https://www.facebook.com/gitebellevuedaveyron" target="_blank" class="social-link">Facebook</a>
                </div>
            </div>
            <div class="footer-col links-col">
                <h3>Explorer</h3>
                <ul>
                    <li><a href="index.php?skip=1#experience">La Villa & L'Histoire</a></li>
                    <li><a href="index.php?skip=1#services">Les Services 5★</a></li>
                    <li><a href="index.php?skip=1#tarifs">Nos Tarifs</a></li>
                    <li><a href="index.php?skip=1#temoignages">Livre d'Or</a></li>
                    <li><a href="decouvrir.php">La Région</a></li>
                </ul>
            </div>
            <div class="footer-col contact-col">
                <h3>Nous Trouver</h3>
                <ul class="contact-list">
                    <li><span class="icon">📍</span><span>12130 Sainte-Eulalie-d'Olt</span></li>
                    <li><span class="icon">📞</span><a href="tel:+33680907107">06 80 90 71 07</a></li>
                </ul>
                <a href="index.php?skip=1#reservation" class="btn-footer">Réserver maintenant</a>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="legal-links">
                <span>&copy; 2026 Bellevue d'Aveyron</span>
                <span class="separator">•</span>
                <a href="mentions.php">Mentions Légales</a>
                <span class="separator">•</span>
                <a href="politique.php">Politique de Confidentialité</a>
            </div>
            <div class="signature">Excellence & Tradition</div>
        </div>
    </footer>

    <script>
        // ── Filtrage des incontournables ──
        function filterIncontournables(category, btnEl) {
            // Mise à jour de l'état des boutons
            document.querySelectorAll('.inc-filter-btn').forEach(b => b.classList.remove('active'));
            if(btnEl) btnEl.classList.add('active');

            const cards = document.querySelectorAll('#themesGrid .theme-card');
            cards.forEach(card => {
                const cats = card.getAttribute('data-category').split(',');
                if (category === 'all' || cats.includes(category)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        // ── Header scroll ──
        window.addEventListener('scroll', () => {
            const h = document.getElementById('navbar');
            if (h) h.classList.toggle('scrolled', window.scrollY > 50);
        });
        function toggleMenu() {
            document.getElementById('navLinks').classList.toggle('active');
        }

        // ── AGENDA DYNAMIQUE — Intégré via Tourisme Aveyron ──
        async function loadAgenda(filter, btnEl) {
            // Mise à jour boutons filtres
            document.querySelectorAll('.agenda-filter-btn').forEach(b => b.classList.remove('active'));
            if (btnEl) btnEl.classList.add('active');

            const output = document.getElementById('agenda-output');
            output.innerHTML = `
                <div class="agenda-loading" id="agenda-loading">
                    <div class="agenda-spinner"></div>
                    <span>Recherche des dernières actualités en direct de Tourisme Aveyron…</span>
                </div>`;

            try {
                // Appel sécurisé au scraper PHP
                const resp = await fetch('ajax_agenda.php?category=' + encodeURIComponent(filter));
                if (!resp.ok) throw new Error('Erreur réseau');
                
                const events = await resp.json();
                renderAgendaCards(events, output);

            } catch (err) {
                console.error('Agenda API error:', err);
                output.innerHTML = `
                    <div class="agenda-empty">
                        <p>🌿 L'agenda est momentanément indisponible.<br>
                        Consultez <a href="https://www.tourisme-aveyron.com" target="_blank" style="color:var(--gold-text); font-weight: 500;">tourisme-aveyron.com</a> pour les événements en cours.</p>
                    </div>`;
            }
        }

        function renderAgendaCards(events, container) {
            if (!events || events.length === 0) {
                container.innerHTML = '<div class="agenda-empty" style="padding: 50px;">Aucun événement programmé pour le moment dans cette catégorie spécifique. Revenez plus tard !</div>';
                return;
            }
            const typeLabel = { nature:'Nature', culture:'Culture', famille:'Famille', fete:'Fête & Marché', restaurant: 'Restaurant & Dégustation' };
            const html = `<div class="agenda-cards">` +
                events.map((ev, i) => {
                    const imgHtml = ev.image
                        ? `<div class="ac-img" style="height:150px;background:url('${escHtml(ev.image)}') center/cover no-repeat;margin:-22px -24px 16px -24px;border-radius:4px 4px 0 0;position:relative;">
                               <div style="position:absolute;inset:0;background:linear-gradient(to top, rgba(5,9,20,0.4) 0%, transparent 60%);border-radius:4px 4px 0 0;"></div>
                           </div>`
                        : '';
                    return `
                    <div class="agenda-card" style="animation-delay:${i * 80}ms; padding-top: ${ev.image ? '0' : '22px'};">
                        ${imgHtml}
                        <div class="ac-date">${escHtml(ev.date || 'À venir')}</div>
                        <div class="ac-title">${escHtml(ev.titre || 'Événement mystère')}</div>
                        <div class="ac-loc">${escHtml(ev.lieu || 'Aveyron')}</div>
                        <div class="ac-desc">${escHtml(ev.description || 'Plus de détails sur place.')}</div>
                        <span class="ac-type">${typeLabel[ev.type] || ev.type || 'Découverte'}</span>
                    </div>`;
                }).join('') +
            `</div>`;
            container.innerHTML = html;
        }

        function escHtml(str) {
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        // Chargement initial
        document.addEventListener('DOMContentLoaded', () => {
            loadAgenda('all', document.querySelector('.agenda-filter-btn.active'));
        });
    </script>
</body>
</html>
