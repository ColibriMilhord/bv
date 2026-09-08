<?php
// decouvrir.php — Page "Découvrir la région" — Bellevue d'Aveyron ★★★★★
// Widget HIT Aveyron via Laetis/Diffusio-3 — approche iframes (robuste, sans MutationObserver)
//
// Architecture : 5 iframes pré-chargées, show/hide via JS (display:none/block)
// Avantage : pas de contrainte "1 widget par page", rechargement fiable à chaque onglet
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

    <style>
        /* ── Variables Bellevue ── */
        :root {
            --gold-text:  #c9a84c;
            --gold-dark:  #a07828;
            --navy-dark:  #050914;
            --navy-mid:   #0a1428;
            --cream:      #f8f5ef;
        }

        /* ══════════════════════════════════════
           HERO RÉGION
           ══════════════════════════════════════ */
        .region-hero {
            position: relative;
            height: 88vh;
            min-height: 560px;
            background:
                linear-gradient(160deg, rgba(5,9,20,.85) 0%, rgba(13,31,60,.75) 50%, rgba(26,47,26,.8) 100%),
                url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1600&auto=format&fit=crop') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
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
            font-size: .72rem;
            letter-spacing: .35em;
            color: var(--gold-text);
            border: 1px solid rgba(201,168,76,.4);
            padding: 6px 20px;
            border-radius: 50px;
            margin-bottom: 28px;
            backdrop-filter: blur(6px);
            background: rgba(255,255,255,.07);
        }
        .region-hero h1 {
            font-family: 'Cinzel', serif;
            font-size: clamp(2.8rem, 6vw, 5rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 22px;
            text-shadow: 0 4px 30px rgba(0,0,0,.5);
        }
        .region-hero h1 em { font-style: italic; color: var(--gold-text); }
        .region-hero p {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1rem, 2vw, 1.22rem);
            color: rgba(255,255,255,.85);
            max-width: 650px;
            margin: 0 auto 36px;
            line-height: 1.75;
        }
        .hero-cta-group { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
        .btn-gold-hero {
            background: linear-gradient(135deg, #bf953f, #fcf6ba, #b38728);
            color: #0d0d0d;
            font-family: 'Cinzel', serif;
            font-size: .78rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            padding: 16px 38px;
            text-decoration: none;
            font-weight: 700;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 4px 20px rgba(191,149,63,.45);
        }
        .btn-gold-hero:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(191,149,63,.55); }
        .btn-ghost-hero {
            border: 1px solid rgba(255,255,255,.4);
            color: #fff;
            font-family: 'Cinzel', serif;
            font-size: .78rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            padding: 15px 38px;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-ghost-hero:hover { background: rgba(255,255,255,.1); }

        /* ══════════════════════════════════════
           INTRO
           ══════════════════════════════════════ */
        .region-intro {
            background: var(--cream);
            padding: 70px 5%;
            text-align: center;
        }
        .region-intro p {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.05rem, 2vw, 1.28rem);
            color: #3a3028;
            max-width: 860px;
            margin: 0 auto;
            line-height: 1.9;
        }
        .region-intro p strong { color: var(--gold-dark); }

        /* ══════════════════════════════════════
           THÈMES — 4 cartes
           ══════════════════════════════════════ */
        .themes-section { background: #fff; padding: 90px 5%; }
        .themes-section .section-header { text-align: center; margin-bottom: 65px; }
        .themes-section .subtitle {
            display: block;
            font-family: 'Cinzel', serif;
            font-size: .72rem;
            letter-spacing: .3em;
            color: var(--gold-text);
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .themes-section h2 {
            font-family: 'Cinzel', serif;
            font-size: clamp(2rem, 4vw, 2.8rem);
            color: var(--navy-dark);
        }
        .curator-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 26px;
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
            transition: transform .3s, box-shadow .3s;
            background: #fff;
            cursor: pointer;
        }
        .curator-card:hover { transform: translateY(-6px); box-shadow: 0 16px 50px rgba(0,0,0,.11); }
        .curator-img {
            height: 210px;
            background-size: cover;
            background-position: center;
            transition: transform .4s;
        }
        .curator-card:hover .curator-img { transform: scale(1.04); }
        .curator-body { padding: 24px 22px 26px; flex: 1; display: flex; flex-direction: column; }
        .curator-body h3 { font-family: 'Cinzel', serif; font-size: 1.05rem; color: var(--navy-dark); margin-bottom: 11px; font-weight: 600; }
        .curator-body p { font-family: 'Montserrat', sans-serif; font-size: .87rem; color: #6a6258; line-height: 1.7; flex: 1; margin-bottom: 18px; }
        .curator-btn {
            font-family: 'Cinzel', serif;
            font-size: .66rem;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--gold-dark);
            border-bottom: 1px solid var(--gold-text);
            padding-bottom: 3px;
            transition: color .2s;
        }
        .curator-card:hover .curator-btn { color: var(--navy-dark); }

        /* ══════════════════════════════════════
           DISTANCES
           ══════════════════════════════════════ */
        .distances-section { background: var(--navy-dark); padding: 55px 5%; }
        .distances-section details summary {
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-size: 1.45rem;
            text-align: center;
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
        }
        .distances-section details summary::-webkit-details-marker { display: none; }
        .distances-section details summary .arrow { transition: transform .3s; color: var(--gold-text); }
        .distances-section details[open] summary .arrow { transform: rotate(180deg); }
        .distances-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 18px;
            max-width: 1200px;
            margin: 36px auto 0;
        }
        .dist-item {
            display: flex; align-items: center; gap: 14px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(201,168,76,.18);
            border-radius: 4px;
            padding: 14px 18px;
            transition: background .2s;
        }
        .dist-item:hover { background: rgba(201,168,76,.07); }
        .dist-icon { color: var(--gold-text); flex-shrink: 0; }
        .dist-name { font-family: 'Montserrat', sans-serif; font-size: .88rem; color: #fff; font-weight: 500; }
        .dist-km   { font-family: 'Montserrat', sans-serif; font-size: .76rem; color: rgba(201,168,76,.75); margin-top: 3px; }

        /* ══════════════════════════════════════════════════════════════
           SECTION AGENDA — WIDGET LAETIS / HIT AVEYRON
           ══════════════════════════════════════════════════════════════ */
        .agenda-section {
            background: var(--cream);
            padding: 90px 5% 80px;
        }

        /* ── En-tête de section ── */
        .agenda-header-row {
            max-width: 1300px;
            margin: 0 auto 50px;
        }
        .agenda-header-row .section-header { margin-bottom: 32px; }
        .agenda-header-row .subtitle {
            display: block;
            font-family: 'Cinzel', serif;
            font-size: .72rem;
            letter-spacing: .3em;
            color: var(--gold-text);
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .agenda-header-row h2 {
            font-family: 'Cinzel', serif;
            font-size: clamp(1.8rem, 3.5vw, 2.6rem);
            color: var(--navy-dark);
        }

        /* ══════════════════════════════════════════════════════════════
           ONGLETS FILTRES
           ══════════════════════════════════════════════════════════════ */
        .agenda-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 32px;
            max-width: 1300px;
        }
        .tab-btn {
            font-family: 'Cinzel', serif;
            font-size: .67rem;
            letter-spacing: .18em;
            text-transform: uppercase;
            padding: 11px 22px;
            border: 1px solid #ccc;
            background: #fff;
            color: #666;
            cursor: pointer;
            border-radius: 2px;
            transition: all .22s;
            white-space: nowrap;
            user-select: none;
        }
        .tab-btn:hover {
            border-color: var(--navy-dark);
            color: var(--navy-dark);
        }
        .tab-btn.active {
            background: var(--navy-dark);
            border-color: var(--navy-dark);
            color: var(--gold-text);
        }

        /* ══════════════════════════════════════════════════════════════
           ZONE IFRAME — chaque iframe correspond à un onglet
           Une seule est visible à la fois (display:block / display:none)
           ══════════════════════════════════════════════════════════════ */
        .widget-zone {
            max-width: 1300px;
            margin: 0 auto;
            position: relative;
        }

        /* Conteneur individual par onglet */
        .tab-panel {
            display: none;
            width: 100%;
        }
        .tab-panel.active {
            display: block;
        }

        /* Conteneur avec ratio imposé — empêche l'étirement des images Laetis */
        .iframe-wrapper {
            position: relative;
            width: 100%;
            /* ratio 16:9 → les images Laetis s'affichent correctement */
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            background: var(--cream);
        }
        .iframe-wrapper iframe {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        /* Skeleton loader visible tant que l'iframe n'est pas chargée */
        .iframe-skeleton {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #ede8e0 25%, #f5f2ec 50%, #ede8e0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            z-index: 1;
            pointer-events: none;
            transition: opacity .4s;
        }
        .iframe-skeleton.hidden { opacity: 0; }
        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Message de chargement dans chaque onglet */
        .tab-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 0;
            gap: 14px;
            font-family: 'Montserrat', sans-serif;
            font-size: .84rem;
            color: #999;
        }
        .tab-spinner {
            width: 22px; height: 22px;
            border: 2px solid rgba(160,120,40,.2);
            border-top-color: var(--gold-text);
            border-radius: 50%;
            animation: spin .8s linear infinite;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ══════════════════════════════════════
           CTA RÉSERVATION
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
            background: radial-gradient(circle, rgba(201,168,76,.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .region-cta h2 {
            font-family: 'Cinzel', serif;
            font-size: clamp(2rem, 4vw, 3rem);
            color: #fff;
            margin-bottom: 20px;
        }
        .region-cta h2::after {
            content: '';
            display: block;
            width: 60px; height: 1px;
            background: linear-gradient(to right, transparent, var(--gold-text), transparent);
            margin: 14px auto 0;
        }
        .region-cta p {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: rgba(255,255,255,.75);
            max-width: 680px;
            margin: 0 auto 40px;
            line-height: 1.8;
        }
        .btn-gold {
            display: inline-block;
            background: linear-gradient(135deg, #bf953f, #fcf6ba, #b38728);
            color: #0d0d0d;
            font-family: 'Cinzel', serif;
            font-size: .78rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            padding: 18px 44px;
            text-decoration: none;
            font-weight: 700;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 4px 24px rgba(191,149,63,.5);
        }
        .btn-gold:hover { transform: translateY(-3px); box-shadow: 0 10px 36px rgba(191,149,63,.6); }

        /* ══════════════════════════════════════
           FOOTER
           ══════════════════════════════════════ */
        #footer-luxe { background: #02040c; padding: 70px 5% 30px; }
        .footer-container {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 50px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-logo { font-family: 'Cinzel', serif; font-size: 1.3rem; font-weight: 700; color: #fff; margin-bottom: 14px; }
        .footer-logo span { display: block; font-size: .65rem; letter-spacing: .3em; color: var(--gold-text); margin-top: 4px; }
        .footer-desc { font-family: 'Montserrat', sans-serif; font-size: .84rem; color: rgba(255,255,255,.5); line-height: 1.7; margin-bottom: 20px; }
        .footer-socials { display: flex; gap: 14px; }
        .social-link { font-family: 'Cinzel', serif; font-size: .7rem; letter-spacing: .12em; color: var(--gold-text); text-decoration: none; border-bottom: 1px solid rgba(201,168,76,.3); padding-bottom: 2px; }
        .footer-col h3 { font-family: 'Cinzel', serif; font-size: .8rem; letter-spacing: .2em; color: var(--gold-text); text-transform: uppercase; margin-bottom: 20px; }
        .footer-col ul { list-style: none; padding: 0; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col ul li a { font-family: 'Montserrat', sans-serif; font-size: .84rem; color: rgba(255,255,255,.55); text-decoration: none; transition: color .2s; }
        .footer-col ul li a:hover { color: var(--gold-text); }
        .contact-list li { display: flex; align-items: flex-start; gap: 10px; }
        .contact-list .icon { color: var(--gold-text); font-size: .9rem; margin-top: 2px; }
        .btn-footer {
            display: inline-block;
            margin-top: 20px;
            border: 1px solid rgba(201,168,76,.5);
            color: var(--gold-text);
            font-family: 'Cinzel', serif;
            font-size: .68rem;
            letter-spacing: .15em;
            text-transform: uppercase;
            padding: 12px 24px;
            text-decoration: none;
            transition: background .2s;
        }
        .btn-footer:hover { background: rgba(201,168,76,.1); }
        .footer-bottom {
            max-width: 1200px;
            margin: 50px auto 0;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,.07);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .legal-links { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .legal-links span, .legal-links a { font-family: 'Montserrat', sans-serif; font-size: .76rem; color: rgba(255,255,255,.35); text-decoration: none; }
        .legal-links a:hover { color: var(--gold-text); }
        .legal-links .separator { color: rgba(201,168,76,.3); }
        .signature { font-family: 'Cinzel', serif; font-size: .7rem; letter-spacing: .2em; color: rgba(201,168,76,.4); }

        /* ── Responsive ── */
        @media (max-width: 900px) {
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
     HEADER
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
     HERO
     ══════════════════════════════════════════ -->
<div class="region-hero">
    <div class="hero-content">
        <span class="hero-eyebrow">★ Gîte Bellevue d'Aveyron · 5 Étoiles ★</span>
        <h1>L'Aveyron<br>à <em>portée de vue</em></h1>
        <p>Depuis Sainte-Eulalie-d'Olt — l'un des Plus Beaux Villages de France —
           partez explorer l'Aubrac, les gorges du Lot et les trésors médiévaux
           à quelques minutes de votre villa d'exception.</p>
        <div class="hero-cta-group">
            <a href="index.php?skip=1#reservation" class="btn-gold-hero">Réserver votre séjour</a>
            <a href="#agenda" class="btn-ghost-hero">Voir l'agenda →</a>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     INTRO
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
     THÈMES — 4 cartes éditoriales
     Chaque carte active l'onglet agenda correspondant
     ══════════════════════════════════════════ -->
<section class="themes-section">
    <div class="section-header">
        <span class="subtitle">Votre Carnet de Voyage</span>
        <h2>Choisissez votre Aveyron</h2>
    </div>
    <div class="curator-grid">

        <a href="#agenda" onclick="activateTab('activites', null); return true;" class="curator-card">
            <div class="curator-img" style="background-image:url('https://upload.wikimedia.org/wikipedia/commons/thumb/5/56/Plateau_de_l%27Aubrac.JPG/1280px-Plateau_de_l%27Aubrac.JPG');"></div>
            <div class="curator-body">
                <h3>Nature & Activités</h3>
                <p>De l'immensité volcanique de l'Aubrac aux berges boisées du Lot — VTT depuis le gîte, kayak, paddle, randonnées balisées. Nature grandeur nature.</p>
                <span class="curator-btn">Voir les activités →</span>
            </div>
        </a>

        <a href="#agenda" onclick="activateTab('agenda', null); return true;" class="curator-card">
            <div class="curator-img" style="background-image:url('https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/Sainte-Eulalie-d%27Olt_-_Le_village_vu_du_ch%C3%A2teau.JPG/1280px-Sainte-Eulalie-d%27Olt_-_Le_village_vu_du_ch%C3%A2teau.JPG');"></div>
            <div class="curator-body">
                <h3>Agenda &amp; Événements</h3>
                <p>Marchés nocturnes à Sainte-Eulalie, fêtes de l'Aubrac, festivals musique du Lot, Trail Aubrac à Saint-Geniez… Le calendrier de votre territoire ne s'arrête jamais.</p>
                <span class="curator-btn">Voir l'agenda →</span>
            </div>
        </a>

        <a href="#agenda" onclick="activateTab('conques', null); return true;" class="curator-card">
            <div class="curator-img" style="background-image:url('https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Village_de_Conques_%28Aveyron%29.JPG/1280px-Village_de_Conques_%28Aveyron%29.JPG');"></div>
            <div class="curator-body">
                <h3>Conques &amp; Patrimoine</h3>
                <p>Abbatiale romane, trésor médiéval, village classé parmi les Plus Beaux de France — Conques-en-Rouergue à 55 km, incontournable de tout séjour aveyronnais.</p>
                <span class="curator-btn">Explorer Conques →</span>
            </div>
        </a>

        <a href="#agenda" onclick="activateTab('restaurants', null); return true;" class="curator-card">
            <div class="curator-img" style="background-image:url('https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Aubrac_cow.jpg/1280px-Aubrac_cow.jpg');"></div>
            <div class="curator-body">
                <h3>Tables & Gastronomie</h3>
                <p>Aligot en buron d'Aubrac, bœuf Aubrac, tomme, roquefort, marchés de producteurs en été — les meilleures tables autour de votre villa.</p>
                <span class="curator-btn">Voir les restaurants →</span>
            </div>
        </a>

    </div>
</section>

<!-- ══════════════════════════════════════════
     DISTANCES
     ══════════════════════════════════════════ -->
<section class="distances-section">
    <details>
        <summary>
            Bellevue d'Aveyron : tout est proche
            <span class="arrow">↓</span>
        </summary>
        <div class="distances-grid">
            <div class="dist-item">
                <div class="dist-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
                <div><div class="dist-name">Saint-Geniez-d'Olt</div><div class="dist-km">2 km · 5 min</div></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10A15.3 15.3 0 0 1 12 2z"/></svg></div>
                <div><div class="dist-name">Lac de Castelnau</div><div class="dist-km">2 km · 5 min</div></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="9" width="18" height="12" rx="1"/><path d="M3 9l9-7 9 7"/></svg></div>
                <div><div class="dist-name">Saint-Côme-d'Olt ★</div><div class="dist-km">5 km · 8 min</div></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4.5 8-11.8A8 8 0 0 0 12 2a8 8 0 0 0-8 8.2c0 7.3 8 11.8 8 11.8z"/><circle cx="12" cy="10" r="3"/></svg></div>
                <div><div class="dist-name">Espalion</div><div class="dist-km">12 km · 15 min</div></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 17l4-8 4 4 4-6 4 10"/><path d="M3 21h18"/></svg></div>
                <div><div class="dist-name">Plateau de l'Aubrac</div><div class="dist-km">25 km · 30 min</div></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v20"/><path d="M7 7h10"/><path d="M7 17h10"/></svg></div>
                <div><div class="dist-name">Conques ★</div><div class="dist-km">55 km · 50 min</div></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m14.5 9-6 6"/><path d="m9.5 5-2 2"/><path d="m15.5 15-2 2"/><path d="m11 11-4 4-2.5-2.5a2.12 2.12 0 1 1 3-3L11 11Z"/><path d="m14 14 4-4 2.5 2.5a2.12 2.12 0 1 1-3 3L14 14Z"/></svg></div>
                <div><div class="dist-name">Laguiole</div><div class="dist-km">30 km · 35 min</div></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 10v12"/><path d="M20 10v12"/><path d="M2 10h20"/><path d="M4 4v6"/><path d="M20 4v6"/></svg></div>
                <div><div class="dist-name">Viaduc de Millau</div><div class="dist-km">75 km · 1h</div></div>
            </div>
            <div class="dist-item">
                <div class="dist-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg></div>
                <div><div class="dist-name">Musée Soulages · Rodez</div><div class="dist-km">50 km · 45 min</div></div>
            </div>
        </div>
    </details>
</section>

<!-- ══════════════════════════════════════════════════════════════════════
     SECTION AGENDA — Widget HIT Aveyron via iframes Laetis
     ══════════════════════════════════════════════════════════════════════

     ARCHITECTURE IFRAME (robuste) :
     • 1 iframe par onglet, toutes présentes dans le DOM dès le chargement
     • Seule l'iframe de l'onglet actif est visible (display:block)
     • Les autres sont cachées (display:none) mais restent chargées en cache
     • → Pas de limitation "1 widget par page", pas de MutationObserver
     • → Changement d'onglet = simple toggle CSS, instantané

     ZONES GÉOGRAPHIQUES :
     • sem_local=aubrac  → Sainte-Eulalie-d'Olt, Saint-Geniez, Espalion,
                           Saint-Côme-d'Olt, Saint-Chély-d'Aubrac, Conques…
     • sem_local=conques → Conques-en-Rouergue, Estaing, Bessuéjouls…
     • (sans sem_local)  → Tout l'Aveyron (Rodez, Millau, etc.)

     PARAMÈTRES COULEURS Bellevue :
     • bgc=%23F8F5EF (crème)  txtc=%23050914 (navy)  thc=%23A07828 (or)
     ══════════════════════════════════════════════════════════════════════ -->

<section class="agenda-section" id="agenda">

    <div class="agenda-header-row">
        <div class="section-header">
            <span class="subtitle">Données officielles HIT Aveyron</span>
            <h2>Agenda &amp; Bons Plans autour du Gîte</h2>
        </div>

        <!-- ── Onglets filtres ── -->
        <div class="agenda-tabs" role="tablist" aria-label="Catégories">

            <button class="tab-btn active"
                    role="tab" aria-selected="true"
                    data-tab="activites"
                    onclick="activateTab('activites', this)">
                Activités &amp; Loisirs
            </button>

            <button class="tab-btn"
                    role="tab" aria-selected="false"
                    data-tab="agenda"
                    onclick="activateTab('agenda', this)">
                Agenda &amp; Événements
            </button>

            <button class="tab-btn"
                    role="tab" aria-selected="false"
                    data-tab="conques"
                    onclick="activateTab('conques', this)">
                Conques &amp; Patrimoine
            </button>

            <button class="tab-btn"
                    role="tab" aria-selected="false"
                    data-tab="restaurants"
                    onclick="activateTab('restaurants', this)">
                Tables &amp; Gastronomie
            </button>

            <button class="tab-btn"
                    role="tab" aria-selected="false"
                    data-tab="rodez"
                    onclick="activateTab('rodez', this)">
                Rodez &amp; Tout l'Aveyron
            </button>

        </div>
    </div>

    <!-- ══════════════════════════════════════════
         PANNEAUX IFRAME — 1 par onglet
         ══════════════════════════════════════════ -->
    <div class="widget-zone">

        <!-- ── Onglet 1 : Activités & Loisirs (Aubrac, priorité Sainte-Eulalie) ── -->
        <div class="tab-panel active" id="panel-activites" role="tabpanel">
            <div class="iframe-wrapper">
                <div class="iframe-skeleton" id="skel-activites"></div>
                <iframe
                    title="Activités et loisirs en Aubrac et Vallée du Lot"
                    src="https://widget.laetis.fr/tourisme-aveyron/wactivites-loisirs?sem_local=aubrac&ordre=proximite&lat=44.5167&lng=2.9167&auto=0&nb=12&bgc=%23F8F5EF&txtc=%23050914&thc=%23A07828&mode=diaporama"
                    loading="eager"
                    onload="hideSkeleton('skel-activites')"
                    allowfullscreen>
                </iframe>
            </div>
        </div>

        <!-- ── Onglet 2 : Agenda & Événements (Aubrac, priorité Sainte-Eulalie) ── -->
        <div class="tab-panel" id="panel-agenda" role="tabpanel">
            <div class="iframe-wrapper">
                <div class="iframe-skeleton" id="skel-agenda"></div>
                <iframe
                    title="Agenda et événements en Aubrac et Vallée du Lot"
                    src="https://widget.laetis.fr/tourisme-aveyron/wagenda?sem_local=aubrac&ordre=proximite&lat=44.5167&lng=2.9167&auto=0&nb=12&bgc=%23F8F5EF&txtc=%23050914&thc=%23A07828&mode=diaporama"
                    loading="lazy"
                    onload="hideSkeleton('skel-agenda')"
                    allowfullscreen>
                </iframe>
            </div>
        </div>

        <!-- ── Onglet 3 : Conques & Patrimoine ── -->
        <div class="tab-panel" id="panel-conques" role="tabpanel">
            <div class="iframe-wrapper">
                <div class="iframe-skeleton" id="skel-conques"></div>
                <iframe
                    title="À découvrir autour de Conques-en-Rouergue"
                    src="https://widget.laetis.fr/tourisme-aveyron/wactivites-loisirs?sem_local=conques&ordre=proximite&lat=44.2880&lng=2.3970&auto=0&nb=12&bgc=%23F8F5EF&txtc=%23050914&thc=%23A07828&mode=diaporama"
                    loading="lazy"
                    onload="hideSkeleton('skel-conques')"
                    allowfullscreen>
                </iframe>
            </div>
        </div>

        <!-- ── Onglet 4 : Tables & Gastronomie (Aubrac, priorité Sainte-Eulalie) ── -->
        <div class="tab-panel" id="panel-restaurants" role="tabpanel">
            <div class="iframe-wrapper">
                <div class="iframe-skeleton" id="skel-restaurants"></div>
                <iframe
                    title="Restaurants et gastronomie en Aubrac"
                    src="https://widget.laetis.fr/tourisme-aveyron/wrestaurants?sem_local=aubrac&ordre=proximite&lat=44.5167&lng=2.9167&auto=0&nb=12&bgc=%23F8F5EF&txtc=%23050914&thc=%23A07828&mode=diaporama"
                    loading="lazy"
                    onload="hideSkeleton('skel-restaurants')"
                    allowfullscreen>
                </iframe>
            </div>
        </div>

        <!-- ── Onglet 5 : Rodez & Tout l'Aveyron ── -->
        <div class="tab-panel" id="panel-rodez" role="tabpanel">
            <div class="iframe-wrapper">
                <div class="iframe-skeleton" id="skel-rodez"></div>
                <iframe
                    title="Découvrir Rodez et tout l'Aveyron"
                    src="https://widget.laetis.fr/tourisme-aveyron/wactivites-loisirs?auto=0&nb=12&bgc=%23F8F5EF&txtc=%23050914&thc=%23A07828&mode=diaporama"
                    loading="lazy"
                    onload="hideSkeleton('skel-rodez')"
                    allowfullscreen>
                </iframe>
            </div>
        </div>

    </div>

    <!-- Lien fallback -->
    <p style="text-align:center; margin-top:28px; font-family:'Montserrat',sans-serif; font-size:.82rem; color:#999;">
        Données officielles HIT Aveyron ·
        <a href="https://www.tourisme-aveyron.com/fr/evenements/agenda-aveyron"
           target="_blank" rel="noopener"
           style="color:var(--gold-dark);">Voir tout l'agenda Aveyron</a>
    </p>

</section>

<!-- ══════════════════════════════════════════
     CTA RÉSERVATION
     ══════════════════════════════════════════ -->
<div class="region-cta">
    <h2>Prêt à vivre l'Aveyron&nbsp;?</h2>
    <p>
        Bellevue d'Aveyron vous offre le confort 5 étoiles pour rayonner librement
        dans cette nature d'exception. Piscine chauffée, VTT à disposition,
        borne électrique, vue panoramique sur la vallée du Lot.
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
     JAVASCRIPT — Gestion des onglets (show/hide iframes)
     ══════════════════════════════════════════════════════════════════

     PRINCIPE : chaque .tab-panel contient une iframe pré-chargée.
     activateTab() fait simplement :
       1. Retirer .active de tous les panneaux → display:none
       2. Ajouter .active au panneau cible → display:block
       3. Mettre à jour l'état visuel des boutons (active / aria-selected)
     Aucun rechargement d'iframe, aucun DOM swap — 100% fiable.
     ══════════════════════════════════════════════════════════════════ -->
<script>
(function() {
    "use strict";

    var currentTab = 'activites';

    /** Masque le skeleton loader d'une iframe une fois chargée */
    window.hideSkeleton = function(id) {
        var skel = document.getElementById(id);
        if (skel) skel.classList.add('hidden');
    };

    /**
     * Active un onglet donné.
     * @param {string} tabId  - clé de l'onglet ('activites', 'agenda', etc.)
     * @param {Element|null} btnEl - bouton cliqué (null si appelé depuis une carte)
     */
    window.activateTab = function(tabId, btnEl) {

        // 1. Masquer tous les panneaux
        var panels = document.querySelectorAll('.tab-panel');
        panels.forEach(function(p) { p.classList.remove('active'); });

        // 2. Afficher le panneau cible
        var target = document.getElementById('panel-' + tabId);
        if (target) {
            target.classList.add('active');
        }

        // 3. Mettre à jour les boutons
        document.querySelectorAll('.tab-btn').forEach(function(b) {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
        });

        // Activer le bon bouton (celui cliqué, ou le trouver par data-tab)
        var activeBtn = btnEl || document.querySelector('.tab-btn[data-tab="' + tabId + '"]');
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.setAttribute('aria-selected', 'true');
        }

        currentTab = tabId;

        // 4. Si appelé depuis une carte (pas depuis un onglet), scroll vers la section
        if (!btnEl) {
            var section = document.getElementById('agenda');
            if (section) {
                setTimeout(function() {
                    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 80);
            }
        }
    };

    /* ── Header scroll ── */
    var navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            navbar.classList.toggle('scrolled', window.scrollY > 60);
        }, { passive: true });
    }

    /* ── Menu mobile ── */
    window.toggleMenu = function() {
        var nav = document.getElementById('navLinks');
        if (nav) nav.classList.toggle('active');
    };

})();
</script>

</body>
</html>
