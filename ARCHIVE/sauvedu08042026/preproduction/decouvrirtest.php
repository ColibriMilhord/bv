<?php
// decouvrir.php — Page "Découvrir la région" — Bellevue d'Aveyron ★★★★★
// Intégration widget HIT Aveyron (Apidae) + fallback intelligent
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Découvrir la Région | Bellevue d'Aveyron — Villa 5 Étoiles</title>
    <meta name="description" content="Explorez l'Aveyron depuis votre villa 5 étoiles à Sainte-Eulalie-d'Olt : agenda des événements, randonnées, gastronomie, patrimoine. Réservez votre séjour d'exception.">
    <link rel="icon" type="image/x-icon" href="images/BELLEVUE/logo.ico">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <!-- ═══════════════════════════════════════════════════════════════
         STYLES SPÉCIFIQUES WIDGET HIT AVEYRON & SECTION DECOUVRIR
         ═══════════════════════════════════════════════════════════════ -->
    <style>
        /* ── Variables héritées du site principal ── */
        :root {
            --gold-text: #c9a84c;
            --gold-dark: #a07828;
            --navy-dark: #050914;
            --navy-mid: #0a1428;
            --cream: #f8f5ef;
        }

        /* ══════════════════════════════════════
           HERO RÉGION — Immersif 5 étoiles
           ══════════════════════════════════════ */
        .region-hero {
            position: relative;
            height: 88vh;
            min-height: 560px;
            background: linear-gradient(160deg, #050914 0%, #0d1f3c 50%, #1a2f1a 100%),
                        url('https://upload.wikimedia.org/wikipedia/commons/thumb/0/02/Sainte-Eulalie-d%27Olt_%282%29.jpg/1280px-Sainte-Eulalie-d%27Olt_%282%29.jpg') center/cover no-repeat;
            background-blend-mode: multiply;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }
        .region-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at center, transparent 30%, rgba(5,9,20,0.6) 100%);
        }
        .region-hero::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 120px;
            background: linear-gradient(to top, var(--cream), transparent);
        }
        .region-hero .hero-content {
            position: relative;
            z-index: 2;
            max-width: 900px;
            padding: 0 30px;
        }
        .hero-eyebrow {
            display: inline-block;
            font-family: 'Cinzel', serif;
            font-size: 0.72rem;
            letter-spacing: 0.35em;
            color: var(--gold-text);
            text-transform: uppercase;
            border: 1px solid rgba(201,168,76,0.4);
            padding: 6px 20px;
            border-radius: 50px;
            margin-bottom: 28px;
            backdrop-filter: blur(6px);
            background: rgba(255,255,255,0.07);
        }
        .region-hero h1 {
            font-family: 'Cinzel', serif;
            font-size: clamp(2.8rem, 6vw, 5rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 22px;
            text-shadow: 0 4px 30px rgba(0,0,0,0.5);
        }
        .region-hero h1 em {
            font-style: italic;
            color: var(--gold-text);
        }
        .region-hero p {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1rem, 2vw, 1.25rem);
            color: rgba(255,255,255,0.85);
            max-width: 650px;
            margin: 0 auto 36px;
            line-height: 1.7;
        }
        .hero-cta-group {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-gold-hero {
            display: inline-block;
            background: linear-gradient(135deg, #bf953f, #fcf6ba, #b38728);
            color: #0d0d0d;
            font-family: 'Cinzel', serif;
            font-size: 0.78rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 16px 38px;
            border-radius: 2px;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 20px rgba(191,149,63,0.4);
        }
        .btn-gold-hero:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(191,149,63,0.5); }
        .btn-ghost-hero {
            display: inline-block;
            border: 1px solid rgba(255,255,255,0.4);
            color: #fff;
            font-family: 'Cinzel', serif;
            font-size: 0.78rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 15px 38px;
            border-radius: 2px;
            text-decoration: none;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-ghost-hero:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.7); }

        /* ══════════════════════════════════════
           INTRO ÉDITORIALE
           ══════════════════════════════════════ */
        .region-intro {
            background: var(--cream);
            padding: 80px 5%;
            text-align: center;
        }
        .region-intro p {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.05rem, 2vw, 1.3rem);
            color: #3a3028;
            max-width: 860px;
            margin: 0 auto;
            line-height: 1.9;
        }
        .region-intro p strong { color: var(--gold-dark); }

        /* ══════════════════════════════════════
           THÈMES CURATORIAUX — Grille 4 colonnes
           ══════════════════════════════════════ */
        .themes-section {
            background: #fff;
            padding: 100px 5%;
        }
        .themes-section .section-header { text-align: center; margin-bottom: 70px; }
        .themes-section .subtitle {
            display: block;
            font-family: 'Cinzel', serif;
            font-size: 0.72rem;
            letter-spacing: 0.3em;
            color: var(--gold-text);
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .themes-section h2 {
            font-family: 'Cinzel', serif;
            font-size: clamp(2rem, 4vw, 2.8rem);
            color: var(--navy-dark);
            font-weight: 700;
        }
        .curator-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 28px;
            max-width: 1300px;
            margin: 0 auto;
        }
        .curator-card {
            text-decoration: none;
            display: flex;
            flex-direction: column;
            border: 1px solid #e8e2d6;
            border-radius: 6px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            background: #fff;
            cursor: pointer;
        }
        .curator-card:hover { transform: translateY(-6px); box-shadow: 0 16px 50px rgba(0,0,0,0.12); }
        .curator-img {
            height: 220px;
            background-size: cover;
            background-position: center;
            transition: transform 0.4s;
        }
        .curator-card:hover .curator-img { transform: scale(1.04); }
        .curator-body {
            padding: 26px 24px 28px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .curator-body h3 {
            font-family: 'Cinzel', serif;
            font-size: 1.1rem;
            color: var(--navy-dark);
            margin-bottom: 12px;
            font-weight: 600;
        }
        .curator-body p {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.88rem;
            color: #6a6258;
            line-height: 1.7;
            flex: 1;
            margin-bottom: 20px;
        }
        .curator-btn {
            display: inline-block;
            font-family: 'Cinzel', serif;
            font-size: 0.68rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--gold-dark);
            border-bottom: 1px solid var(--gold-text);
            padding-bottom: 3px;
            transition: color 0.2s;
        }
        .curator-card:hover .curator-btn { color: var(--navy-dark); }

        /* ══════════════════════════════════════
           DISTANCES — Accordéon élégant
           ══════════════════════════════════════ */
        .distances-section {
            background: var(--navy-dark);
            padding: 60px 5%;
        }
        .distances-section details summary {
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            text-align: center;
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            user-select: none;
        }
        .distances-section details summary::-webkit-details-marker { display: none; }
        .distances-section details summary .arrow { transition: transform 0.3s; display: inline-block; color: var(--gold-text); }
        .distances-section details[open] summary .arrow { transform: rotate(180deg); }
        .distances-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 40px auto 0;
        }
        .dist-item {
            display: flex;
            align-items: center;
            gap: 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(201,168,76,0.2);
            border-radius: 4px;
            padding: 16px 20px;
            transition: background 0.2s;
        }
        .dist-item:hover { background: rgba(201,168,76,0.08); }
        .dist-icon { color: var(--gold-text); flex-shrink: 0; }
        .dist-info { display: flex; flex-direction: column; }
        .dist-name { font-family: 'Montserrat', sans-serif; font-size: 0.9rem; color: #fff; font-weight: 500; }
        .dist-km { font-family: 'Montserrat', sans-serif; font-size: 0.78rem; color: rgba(201,168,76,0.8); margin-top: 3px; }

        /* ══════════════════════════════════════════════════════════════
           SECTION AGENDA — Widget HIT Aveyron
           ══════════════════════════════════════════════════════════════

           ARCHITECTURE :
           - Mode A : Widget Apidae natif (après obtention de l'ID widget)
           - Mode B : Fallback JS local (agenda_agenda.php) si widget absent
           ══════════════════════════════════════════════════════════════ */
        .agenda-section {
            background: var(--cream);
            padding: 100px 5% 80px;
        }
        .agenda-header-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
            max-width: 1300px;
            margin: 0 auto 60px;
        }
        .agenda-header-row .section-header { flex: 1; min-width: 240px; }
        .agenda-header-row .subtitle {
            display: block;
            font-family: 'Cinzel', serif;
            font-size: 0.72rem;
            letter-spacing: 0.3em;
            color: var(--gold-text);
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .agenda-header-row h2 {
            font-family: 'Cinzel', serif;
            font-size: clamp(1.8rem, 3.5vw, 2.6rem);
            color: var(--navy-dark);
        }

        /* ── Onglets de filtres ── */
        .agenda-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .agenda-filter-btn {
            font-family: 'Cinzel', serif;
            font-size: 0.65rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            padding: 10px 20px;
            border: 1px solid #ccc;
            background: #fff;
            color: #666;
            cursor: pointer;
            border-radius: 2px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .agenda-filter-btn:hover,
        .agenda-filter-btn.active {
            background: var(--navy-dark);
            border-color: var(--navy-dark);
            color: var(--gold-text);
        }

        /* ── Zone widget Apidae ──
           Le widget Apidae s'injecte dans #widgit (div standard)
           On enveloppe dans .apidae-wrapper pour surcharger ses styles
        ── */
        .apidae-wrapper {
            max-width: 1300px;
            margin: 0 auto;
            /* Overrides cosmétiques du widget Apidae pour coller à la charte Bellevue */
        }
        /* Surcharge couleurs widget Apidae — adapté à votre palette */
        .apidae-wrapper .widgit-container,
        .apidae-wrapper .widgit {
            font-family: 'Montserrat', sans-serif !important;
        }
        /* Couleur d'accentuation → or Bellevue */
        .apidae-wrapper a,
        .apidae-wrapper .widgit-pagination button:hover,
        .apidae-wrapper .widgit-filter__option.active { color: var(--gold-dark) !important; }
        .apidae-wrapper .widgit-filter__option.active,
        .apidae-wrapper .widgit-pagination button.active {
            border-color: var(--gold-dark) !important;
            background: var(--gold-dark) !important;
            color: #fff !important;
        }

        /* ── Fallback : cartes manuelles (agenda_agenda.php) ── */
        #agenda-fallback-zone { max-width: 1300px; margin: 0 auto; }
        .agenda-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        .agenda-card {
            background: #fff;
            border: 1px solid #e8e2d6;
            border-radius: 5px;
            padding: 26px 24px;
            display: flex;
            flex-direction: column;
            animation: fadeUp 0.5s both;
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .agenda-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
        @keyframes fadeUp { from { opacity:0; transform: translateY(20px); } to { opacity:1; transform: translateY(0); } }
        .ac-date { font-family: 'Cinzel', serif; font-size: 0.68rem; letter-spacing: 0.18em; color: var(--gold-text); text-transform: uppercase; margin-bottom: 10px; }
        .ac-title { font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--navy-dark); font-weight: 600; margin-bottom: 8px; }
        .ac-loc { font-family: 'Montserrat', sans-serif; font-size: 0.78rem; color: #888; margin-bottom: 12px; display: flex; align-items: center; gap: 5px; }
        .ac-loc::before { content: '📍'; font-size: 0.7rem; }
        .ac-desc { font-family: 'Montserrat', sans-serif; font-size: 0.84rem; color: #6a6258; line-height: 1.6; flex: 1; }
        .ac-img { height: 160px; background-size: cover; background-position: center; margin: -26px -24px 20px; border-radius: 5px 5px 0 0; }
        .ac-type {
            display: inline-block;
            font-family: 'Cinzel', serif;
            font-size: 0.6rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--gold-dark);
            border: 1px solid rgba(160,120,40,0.3);
            padding: 4px 10px;
            border-radius: 2px;
            margin-top: 14px;
            align-self: flex-start;
        }
        .agenda-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 60px 0;
            font-family: 'Montserrat', sans-serif;
            color: #888;
            font-size: 0.9rem;
        }
        .agenda-spinner {
            width: 24px; height: 24px;
            border: 2px solid rgba(160,120,40,0.2);
            border-top-color: var(--gold-text);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .agenda-empty {
            text-align: center;
            padding: 50px;
            font-family: 'Montserrat', sans-serif;
            color: #888;
            font-size: 0.9rem;
            line-height: 1.8;
        }

        /* Bandeau info widget Apidae non configuré */
        .widget-setup-notice {
            background: linear-gradient(135deg, #fffbf0, #fef3d0);
            border: 1px solid rgba(201,168,76,0.4);
            border-left: 4px solid var(--gold-text);
            border-radius: 4px;
            padding: 24px 28px;
            max-width: 860px;
            margin: 0 auto 40px;
            font-family: 'Montserrat', sans-serif;
        }
        .widget-setup-notice h4 {
            font-family: 'Cinzel', serif;
            font-size: 0.85rem;
            letter-spacing: 0.1em;
            color: var(--gold-dark);
            margin-bottom: 10px;
        }
        .widget-setup-notice p { font-size: 0.84rem; color: #5a4a30; line-height: 1.7; margin-bottom: 8px; }
        .widget-setup-notice a { color: var(--gold-dark); font-weight: 600; }
        .widget-setup-notice code {
            background: rgba(0,0,0,0.06);
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.82rem;
        }

        /* ══════════════════════════════════════
           CTA FINAL — Réservation
           ══════════════════════════════════════ */
        .region-cta {
            background: linear-gradient(135deg, var(--navy-dark) 0%, #0d1f3c 60%, #12200d 100%);
            padding: 100px 5%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .region-cta::before {
            content: '';
            position: absolute;
            top: -60px; left: 50%;
            transform: translateX(-50%);
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(201,168,76,0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .region-cta h2 {
            font-family: 'Cinzel', serif;
            font-size: clamp(2rem, 4vw, 3rem);
            color: #fff;
            margin-bottom: 20px;
            position: relative;
        }
        .region-cta h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--gold-text), transparent);
            margin: 16px auto 0;
        }
        .region-cta p {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: rgba(255,255,255,0.75);
            max-width: 680px;
            margin: 0 auto 40px;
            line-height: 1.8;
        }
        .btn-gold {
            display: inline-block;
            background: linear-gradient(135deg, #bf953f, #fcf6ba, #b38728);
            color: #0d0d0d;
            font-family: 'Cinzel', serif;
            font-size: 0.78rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 18px 44px;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 24px rgba(191,149,63,0.5);
        }
        .btn-gold:hover { transform: translateY(-3px); box-shadow: 0 10px 36px rgba(191,149,63,0.6); }

        /* ══════════════════════════════════════
           FOOTER (identique index.php)
           ══════════════════════════════════════ */
        #footer-luxe {
            background: #02040c;
            padding: 70px 5% 30px;
        }
        .footer-container {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 50px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-logo {
            font-family: 'Cinzel', serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 14px;
        }
        .footer-logo span {
            display: block;
            font-size: 0.65rem;
            letter-spacing: 0.3em;
            color: var(--gold-text);
            margin-top: 4px;
        }
        .footer-desc { font-family: 'Montserrat', sans-serif; font-size: 0.84rem; color: rgba(255,255,255,0.5); line-height: 1.7; margin-bottom: 20px; }
        .footer-socials { display: flex; gap: 14px; }
        .social-link { font-family: 'Cinzel', serif; font-size: 0.7rem; letter-spacing: 0.12em; color: var(--gold-text); text-decoration: none; border-bottom: 1px solid rgba(201,168,76,0.3); padding-bottom: 2px; }
        .footer-col h3 { font-family: 'Cinzel', serif; font-size: 0.8rem; letter-spacing: 0.2em; color: var(--gold-text); text-transform: uppercase; margin-bottom: 20px; }
        .footer-col ul { list-style: none; padding: 0; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col ul li a { font-family: 'Montserrat', sans-serif; font-size: 0.84rem; color: rgba(255,255,255,0.55); text-decoration: none; transition: color 0.2s; }
        .footer-col ul li a:hover { color: var(--gold-text); }
        .contact-list li { display: flex; align-items: flex-start; gap: 10px; }
        .contact-list .icon { color: var(--gold-text); font-size: 0.9rem; margin-top: 2px; }
        .btn-footer {
            display: inline-block;
            margin-top: 20px;
            border: 1px solid rgba(201,168,76,0.5);
            color: var(--gold-text);
            font-family: 'Cinzel', serif;
            font-size: 0.68rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            padding: 12px 24px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-footer:hover { background: rgba(201,168,76,0.1); }
        .footer-bottom {
            max-width: 1200px;
            margin: 50px auto 0;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.07);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .legal-links { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .legal-links span, .legal-links a { font-family: 'Montserrat', sans-serif; font-size: 0.76rem; color: rgba(255,255,255,0.35); text-decoration: none; }
        .legal-links a:hover { color: var(--gold-text); }
        .legal-links .separator { color: rgba(201,168,76,0.3); }
        .signature { font-family: 'Cinzel', serif; font-size: 0.7rem; letter-spacing: 0.2em; color: rgba(201,168,76,0.4); }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .agenda-header-row { flex-direction: column; align-items: flex-start; }
            .curator-grid { grid-template-columns: 1fr 1fr; }
            .footer-container { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 600px) {
            .curator-grid, .footer-container { grid-template-columns: 1fr; }
            .hero-cta-group { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════
     HEADER — Identique index.php
     ══════════════════════════════════════════ -->
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

<!-- ══════════════════════════════════════════
     HERO — Photo aérienne Sainte-Eulalie-d'Olt
     ══════════════════════════════════════════ -->
<div class="region-hero">
    <div class="hero-content">
        <span class="hero-eyebrow">★ Gîte Bellevue d'Aveyron · 5 Étoiles ★</span>
        <h1>L'Aveyron<br>à <em>portée de vue</em></h1>
        <p>Depuis Sainte-Eulalie-d'Olt — l'un des Plus Beaux Villages de France —
           partez explorer l'Aubrac, les gorges du Lot et les trésors médiévaux à
           quelques minutes de votre villa d'exception.</p>
        <div class="hero-cta-group">
            <a href="index.php?skip=1#reservation" class="btn-gold-hero">Réserver votre séjour</a>
            <a href="#agenda" class="btn-ghost-hero">Voir l'agenda →</a>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     INTRO ÉDITORIALE
     ══════════════════════════════════════════ -->
<div class="region-intro">
    <p>
        Entre le plateau de l'<strong>Aubrac</strong> et les méandres dorés du <strong>Lot</strong>,
        votre villa Bellevue est nichée au cœur d'un territoire où chaque virage révèle un village
        médiéval, une table de producteurs ou un sentier de légende. Randonnées, pêche, villages classés,
        gastronomie d'exception, culture vivante… <strong>Une semaine ne suffira pas.</strong>
    </p>
</div>

<!-- ══════════════════════════════════════════
     THÈMES — 4 portes d'entrée éditoriales
     Chaque carte renvoie vers le filtre agenda
     ══════════════════════════════════════════ -->
<section class="themes-section">
    <div class="section-header">
        <span class="subtitle">Votre Carnet de Voyage</span>
        <h2>Choisissez votre Aveyron</h2>
    </div>

    <div class="curator-grid">

        <!-- Nature & Randonnée -->
        <a href="#agenda" onclick="switchAgendaFilter('nature'); return true;" class="curator-card">
            <div class="curator-img" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/5/56/Plateau_de_l%27Aubrac.JPG/1280px-Plateau_de_l%27Aubrac.JPG');"></div>
            <div class="curator-body">
                <h3>Nature & Randonnées</h3>
                <p>De l'immensité volcanique du plateau de l'Aubrac aux berges boisées du Lot — 3 sentiers balisés partent directement de la porte du gîte. VTT à disposition, kayak, baignade en rivière.</p>
                <span class="curator-btn">Trouver une Sortie Nature →</span>
            </div>
        </a>

        <!-- Patrimoine & Culture -->
        <a href="#agenda" onclick="switchAgendaFilter('culture'); return true;" class="curator-card">
            <div class="curator-img" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Village_de_Conques_%28Aveyron%29.JPG/1280px-Village_de_Conques_%28Aveyron%29.JPG');"></div>
            <div class="curator-body">
                <h3>Patrimoine Médiéval</h3>
                <p>Sainte-Eulalie-d'Olt, Conques et ses enluminures romanes, Saint-Côme-d'Olt et sa tour tordue… La route des Plus Beaux Villages est littéralement votre voisinage. Histoire vivante garantie.</p>
                <span class="curator-btn">Explorer le Patrimoine →</span>
            </div>
        </a>

        <!-- Famille & Loisirs -->
        <a href="#agenda" onclick="switchAgendaFilter('famille'); return true;" class="curator-card">
            <div class="curator-img" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Lot_river_Aveyron.jpg/1280px-Lot_river_Aveyron.jpg');"></div>
            <div class="curator-body">
                <h3>Famille & Plein Air</h3>
                <p>Bateau électrique sans permis sur le lac de Castelnau, initiation au paddle sur le Lot, viaduc de Millau à couper le souffle, découverte des couteliers de Laguiole avec les enfants.</p>
                <span class="curator-btn">Voir l'Agenda Famille →</span>
            </div>
        </a>

        <!-- Gastronomie -->
        <a href="#agenda" onclick="switchAgendaFilter('restaurant'); return true;" class="curator-card">
            <div class="curator-img" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Aubrac_cow.jpg/1280px-Aubrac_cow.jpg');"></div>
            <div class="curator-body">
                <h3>Gastronomie & Terroir</h3>
                <p>Aligot en buron d'Aubrac, bœuf Aubrac élevé sous la mère, tomme et roquefort, marchés nocturnes de producteurs en été — le plateau aveyronnais est une fête permanente du goût.</p>
                <span class="curator-btn">Découvrir les Saveurs →</span>
            </div>
        </a>

    </div>
</section>

<!-- ══════════════════════════════════════════
     DISTANCES CLÉS — Accordéon
     ══════════════════════════════════════════ -->
<section class="distances-section">
    <details>
        <summary>
            <span>Bellevue d'Aveyron : tout est proche</span>
            <span class="arrow">↓</span>
        </summary>
        <div class="distances-grid">
            <div class="dist-item">
                <div class="dist-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
                <div class="dist-info"><span class="dist-name">Saint-Geniez-d'Olt</span><span class="dist-km">2 km · 5 min</span></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10A15.3 15.3 0 0 1 8 12a15.3 15.3 0 0 1 4-10z"/></svg></div>
                <div class="dist-info"><span class="dist-name">Lac de Castelnau</span><span class="dist-km">2 km · 5 min</span></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="9" width="18" height="12" rx="1"/><path d="M3 9l9-7 9 7"/></svg></div>
                <div class="dist-info"><span class="dist-name">Saint-Côme-d'Olt ★</span><span class="dist-km">5 km · 8 min</span></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4.5 8-11.8A8 8 0 0 0 12 2a8 8 0 0 0-8 8.2c0 7.3 8 11.8 8 11.8z"/><circle cx="12" cy="10" r="3"/></svg></div>
                <div class="dist-info"><span class="dist-name">Espalion</span><span class="dist-km">12 km · 15 min</span></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 17l4-8 4 4 4-6 4 10"/><path d="M3 21h18"/></svg></div>
                <div class="dist-info"><span class="dist-name">Plateau de l'Aubrac</span><span class="dist-km">25 km · 30 min</span></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v20"/><path d="M7 7h10"/><path d="M7 17h10"/></svg></div>
                <div class="dist-info"><span class="dist-name">Conques ★ (abbatiale)</span><span class="dist-km">55 km · 50 min</span></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m14.5 9-6 6"/><path d="m9.5 5-2 2"/><path d="m15.5 15-2 2"/><path d="m11 11-4 4-2.5-2.5a2.12 2.12 0 1 1 3-3L11 11Z"/><path d="m14 14 4-4 2.5 2.5a2.12 2.12 0 1 1-3 3L14 14Z"/></svg></div>
                <div class="dist-info"><span class="dist-name">Laguiole (couteliers)</span><span class="dist-km">30 km · 35 min</span></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 10v12"/><path d="M20 10v12"/><path d="M2 10h20"/><path d="M4 4v6"/><path d="M20 4v6"/></svg></div>
                <div class="dist-info"><span class="dist-name">Viaduc de Millau</span><span class="dist-km">75 km · 1h</span></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg></div>
                <div class="dist-info"><span class="dist-name">Musée Soulages · Rodez</span><span class="dist-km">50 km · 45 min</span></div>
            </div>
        </div>
    </details>
</section>

<!-- ══════════════════════════════════════════════════════════════════
     SECTION AGENDA — WIDGET HIT AVEYRON (Apidae)
     ══════════════════════════════════════════════════════════════════

     ╔══════════════════════════════════════════════════════════════╗
     ║  MODE OPÉRATOIRE — COMMENT OBTENIR VOTRE WIDGET APIDAE      ║
     ║                                                              ║
     ║  1. Créez un compte sur https://www.apidae-tourisme.com/     ║
     ║  2. Contactez l'Office de Tourisme HIT Aveyron pour être     ║
     ║     parrainé (contact : Maxime Fabre, ADAT — 05 65 75 55 72)║
     ║  3. Une fois parrainé, connectez-vous sur :                  ║
     ║     https://widgets.apidae-tourisme.com                      ║
     ║  4. Créez un widget "Agenda" avec ces paramètres :           ║
     ║     - Latitude  : 44.5167   (Sainte-Eulalie-d'Olt)          ║
     ║     - Longitude : 2.9167                                     ║
     ║     - Rayon     : 30 km                                      ║
     ║     - Site hôte : www.bellevuedaveyron.fr (votre domaine)    ║
     ║  5. Remplacez VOTRE_ID_WIDGET_APIDAE ci-dessous              ║
     ║     par l'identifiant numérique obtenu (ex: 7053)            ║
     ║                                                              ║
     ║  En attendant, le fallback local (ajax_agenda.php) s'affiche ║
     ╚══════════════════════════════════════════════════════════════╝
     ══════════════════════════════════════════════════════════════════ -->
<section id="agenda" class="agenda-section">

    <div class="agenda-header-row">
        <div class="section-header">
            <span class="subtitle">Ce qui se passe en ce moment</span>
            <h2>Agenda de la Région</h2>
        </div>
        <div class="agenda-controls" id="agendaControls">
            <button class="agenda-filter-btn active" data-filter="all"        onclick="switchAgendaFilter('all',this)">Tout</button>
            <button class="agenda-filter-btn"        data-filter="nature"     onclick="switchAgendaFilter('nature',this)">Nature</button>
            <button class="agenda-filter-btn"        data-filter="culture"    onclick="switchAgendaFilter('culture',this)">Culture</button>
            <button class="agenda-filter-btn"        data-filter="famille"    onclick="switchAgendaFilter('famille',this)">Famille</button>
            <button class="agenda-filter-btn"        data-filter="fete"       onclick="switchAgendaFilter('fete',this)">Fêtes & Marchés</button>
            <button class="agenda-filter-btn"        data-filter="restaurant" onclick="switchAgendaFilter('restaurant',this)">Restaurant</button>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────────────────────
         ZONE WIDGET APIDAE NATIF
         Décommentez et remplacez VOTRE_ID_WIDGET_APIDAE une fois
         votre compte Apidae configuré avec l'OT HIT Aveyron.
         Note : 1 seul widget Apidae possible par page.
         ─────────────────────────────────────────────────────────── -->
    <!--
    <div class="apidae-wrapper" id="apidae-zone">
        <script src="https://widgets.apidae-tourisme.com/widget/VOTRE_ID_WIDGET_APIDAE.js" async></script>
        <div id="widgit"></div>
    </div>
    -->

    <!-- ─────────────────────────────────────────────────────────────
         NOTICE D'ACTIVATION (à supprimer une fois le widget actif)
         ─────────────────────────────────────────────────────────── -->
    <div class="widget-setup-notice" id="widgetSetupNotice">
        <h4>⚙ Widget HIT Aveyron — Activation requise</h4>
        <p>
            Pour afficher l'agenda officiel en temps réel (base de données Apidae / HIT Aveyron),
            contactez <strong>Maxime Fabre</strong> à l'ADAT :
            <a href="tel:+33565755572">05 65 75 55 72</a> ou via
            <a href="https://www.aveyron-attractivite.fr/accompagnement/professionnels-touristiques/la-boite-a-outils/widgets-tourisme-aveyron/widget-agenda/" target="_blank">ce formulaire</a>.
        </p>
        <p>
            Une fois votre widget créé sur <a href="https://widgets.apidae-tourisme.com" target="_blank">widgets.apidae-tourisme.com</a>,
            décommentez le bloc <code>&lt;!-- ZONE WIDGET APIDAE NATIF --&gt;</code>
            dans <code>decouvrir.php</code> en remplaçant <code>VOTRE_ID_WIDGET_APIDAE</code>
            par votre identifiant numérique (ex : <code>7053</code>).
        </p>
        <p style="font-size:0.78rem; color:#8a7048;">
            En attendant, l'agenda ci-dessous est alimenté automatiquement par Datatourisme
            et les données locales.
        </p>
    </div>

    <!-- ─────────────────────────────────────────────────────────────
         FALLBACK — Cartes d'événements via ajax_agenda.php
         Ce bloc est remplacé par le widget Apidae natif une fois actif.
         ─────────────────────────────────────────────────────────── -->
    <div id="agenda-fallback-zone">
        <div class="agenda-loading" id="agenda-loading">
            <div class="agenda-spinner"></div>
            <span>Chargement des événements autour de Sainte-Eulalie-d'Olt…</span>
        </div>
    </div>

</section>

<!-- ══════════════════════════════════════════
     CTA FINAL — RÉSERVATION
     ══════════════════════════════════════════ -->
<div class="region-cta">
    <h2>Prêt à vivre l'Aveyron&nbsp;?</h2>
    <p>
        Bellevue d'Aveyron vous offre le confort 5 étoiles pour rayonner librement
        dans cette nature d'exception. Piscine chauffée à débordement, VTT à disposition,
        borne de recharge électrique, vue panoramique sur la vallée du Lot.
    </p>
    <a href="index.php?skip=1#reservation" class="btn-gold">Vérifier les disponibilités</a>
</div>

<!-- ══════════════════════════════════════════
     FOOTER
     ══════════════════════════════════════════ -->
<footer id="footer-luxe">
    <div class="footer-container">
        <div class="footer-col brand-col">
            <div class="footer-logo">Bellevue d'Aveyron<span>Villa 5 Étoiles</span></div>
            <p class="footer-desc">Un sanctuaire de paix au cœur de l'Aveyron, à Sainte-Eulalie-d'Olt.</p>
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
                <li><span class="icon">📍</span><span>12130 Sainte-Eulalie-d'Olt, Aveyron</span></li>
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

<!-- ══════════════════════════════════════════════════════════════════
     JAVASCRIPT — Gestion de l'agenda & du widget Apidae
     ══════════════════════════════════════════════════════════════════ -->
<script>
/* ═══════════════════════════════════════════════════════════════════
   CONFIGURATION WIDGET APIDAE
   Modifiez cette constante une fois votre widget créé.
   ═══════════════════════════════════════════════════════════════════ */
const APIDAE_WIDGET_ID = null; // Remplacez null par votre ID numérique, ex: 7053
// Paramètres géographiques — Sainte-Eulalie-d'Olt
const SAINTE_EULALIE = { lat: 44.5167, lng: 2.9167, rayon: 30 }; // 30 km de rayon

/* ═══════════════════════════════════════════════════════════════════
   GESTION DES FILTRES
   Synchronise les onglets avec le widget Apidae natif OU le fallback
   ═══════════════════════════════════════════════════════════════════ */
function switchAgendaFilter(filter, btnEl) {
    // Mise à jour des onglets
    document.querySelectorAll('.agenda-filter-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) {
        btnEl.classList.add('active');
    } else {
        // Appelé depuis les cartes thèmes (sans btnEl) — on met à jour l'onglet correspondant
        const target = document.querySelector(`.agenda-filter-btn[data-filter="${filter}"]`);
        if (target) target.classList.add('active');
    }

    // Scroll vers la section
    const agendaSection = document.getElementById('agenda');
    if (agendaSection) {
        setTimeout(() => agendaSection.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
    }

    // Mode A — Widget Apidae natif présent ?
    if (APIDAE_WIDGET_ID !== null && document.getElementById('widgit')) {
        // Le widget Apidae a sa propre gestion de filtres.
        // On déclenche le clic sur son filtre interne si disponible.
        const apidaeFilter = document.querySelector(`.widgit-filter__option[data-type="${filter}"]`);
        if (apidaeFilter) apidaeFilter.click();
        return;
    }

    // Mode B — Fallback local ajax_agenda.php
    loadAgendaFallback(filter);
}

/* ═══════════════════════════════════════════════════════════════════
   FALLBACK — Chargement via ajax_agenda.php
   ═══════════════════════════════════════════════════════════════════ */
async function loadAgendaFallback(filter) {
    const zone = document.getElementById('agenda-fallback-zone');
    if (!zone) return;

    zone.innerHTML = `
        <div class="agenda-loading">
            <div class="agenda-spinner"></div>
            <span>Recherche des dernières actualités autour de Sainte-Eulalie-d'Olt…</span>
        </div>`;

    try {
        const resp = await fetch('ajax_agenda.php?category=' + encodeURIComponent(filter || 'all'));
        if (!resp.ok) throw new Error('Réseau indisponible');
        const events = await resp.json();
        renderAgendaCards(events, zone);
    } catch (err) {
        console.warn('Agenda fallback error:', err);
        zone.innerHTML = `
            <div class="agenda-empty">
                <p>🌿 L'agenda est momentanément indisponible.<br>
                Retrouvez tous les événements en Aveyron sur
                <a href="https://www.tourisme-aveyron.com/fr/evenements/agenda-aveyron"
                   target="_blank" style="color:var(--gold-text); font-weight:500;">
                   tourisme-aveyron.com</a>.</p>
            </div>`;
    }
}

/* ─── Rendu des cartes événements ─── */
function renderAgendaCards(events, container) {
    if (!events || events.length === 0) {
        container.innerHTML = '<div class="agenda-empty">Aucun événement programmé pour cette catégorie pour le moment. Revenez prochainement !</div>';
        return;
    }
    const typeLabel = {
        nature: 'Nature', culture: 'Culture',
        famille: 'Famille', fete: 'Fête & Marché', restaurant: 'Restaurant & Dégustation'
    };
    const html = '<div class="agenda-cards">' +
        events.map((ev, i) => {
            const imgHtml = ev.image
                ? `<div class="ac-img" style="background-image:url('${esc(ev.image)}');"></div>`
                : '';
            return `
            <div class="agenda-card" style="animation-delay:${i * 70}ms;">
                ${imgHtml}
                <div class="ac-date">${esc(ev.date || 'À venir')}</div>
                <div class="ac-title">${esc(ev.titre || 'Événement')}</div>
                <div class="ac-loc">${esc(ev.lieu || 'Aveyron')}</div>
                <div class="ac-desc">${esc(ev.description || '')}</div>
                <span class="ac-type">${typeLabel[ev.type] || ev.type || 'Découverte'}</span>
            </div>`;
        }).join('') +
    '</div>';
    container.innerHTML = html;
}

function esc(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ═══════════════════════════════════════════════════════════════════
   INITIALISATION
   ═══════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {

    // Header scroll
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 50);
    });

    // Menu mobile
    window.toggleMenu = function() {
        document.getElementById('navLinks').classList.toggle('active');
    };

    // Si widget Apidae configuré → on cache la notice et le fallback
    if (APIDAE_WIDGET_ID !== null) {
        const notice = document.getElementById('widgetSetupNotice');
        if (notice) notice.style.display = 'none';
        const fallback = document.getElementById('agenda-fallback-zone');
        if (fallback) fallback.style.display = 'none';
        // Les filtres sont masqués (le widget Apidae a les siens)
        // ou on peut laisser nos onglets et les connecter via l'événement widgit:filter
    } else {
        // Mode fallback — chargement initial "Tout"
        loadAgendaFallback('all');

        // Écoute de l'événement Apidae pour mise à jour future
        // (prêt à recevoir le widget dès activation)
        document.addEventListener('widgit:render', function() {
            const fallback = document.getElementById('agenda-fallback-zone');
            const notice = document.getElementById('widgetSetupNotice');
            if (fallback) fallback.style.display = 'none';
            if (notice) notice.style.display = 'none';
        });
    }
});
</script>

</body>
</html>
