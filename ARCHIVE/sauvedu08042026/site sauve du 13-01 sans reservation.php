<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bellevue d'Aveyron - Villa 5 Étoiles Luxe</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital@0;1&display=swap" rel="stylesheet">
    
    <style>
        /* --- 1. CONFIGURATION DE BASE & COULEURS --- */
        :root {
            --gold-gradient: linear-gradient(135deg, #bf953f 0%, #fcf6ba 40%, #b38728 70%, #fbf5b7 100%);
            --gold-text: #c5a059;
            --navy-deep: #050914;
            --navy-light: #121b33;
            --white-soft: #f9f9f9;
        }

        html { scroll-behavior: smooth; }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Montserrat', sans-serif; 
            background-color: var(--white-soft); 
            color: #333; 
            overflow-x: hidden; /* Important pour éviter le scroll horizontal */
            line-height: 1.8;
        }

        /* Bloquer le scroll pendant l'intro */
        body.loading {
            overflow: hidden;
            height: 100vh;
        }

        h1, h2, h3, h4 { font-family: 'Cinzel', serif; text-transform: uppercase; letter-spacing: 2px; color: var(--navy-deep); }
        .subtitle { font-family: 'Playfair Display', serif; font-style: italic; color: var(--gold-text); font-size: 1.5rem; margin-bottom: 10px; display: block; }
        
        section { padding: 100px 5%; max-width: 1400px; margin: 0 auto; }
        .section-header { text-align: center; margin-bottom: 80px; }

        /* --- 2. STYLE DE L'INTRODUCTION (ANIMATION) --- */
        #intro-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--navy-deep);
            background-image: radial-gradient(circle at center, #0a1128 0%, #050914 100%);
            z-index: 9999; /* Au-dessus de tout */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            transition: opacity 1.5s ease-in-out, visibility 1.5s;
        }

        #intro-overlay.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        /* Conteneur interne de l'intro */
        .intro-content {
            position: relative;
            text-align: center;
            padding: 20px;
        }

        /* Étoiles Intro */
        .intro-stars-wrapper {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .intro-star-svg {
            width: 30px;
            height: 30px;
            fill: url(#goldGradientSvg);
            opacity: 0;
            transform: scale(0.5) rotate(-30deg);
            filter: drop-shadow(0 0 10px rgba(197, 160, 89, 0.3));
        }

        /* Lignes Intro */
        .intro-line {
            height: 2px;
            width: 100%;
            max-width: 400px;
            background: var(--gold-gradient);
            margin: 0 auto;
            transform: scaleX(0);
            opacity: 0;
        }
        .intro-line-top { margin-bottom: 40px; transform-origin: center left; }
        .intro-line-bottom { margin-top: 40px; transform-origin: center right; }

        /* Titre Intro */
        .intro-title-wrapper {
            overflow: hidden;
            position: relative;
        }

        .intro-main-title {
            font-family: 'Cinzel', serif;
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: 8px;
            text-transform: uppercase;
            color: transparent;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            opacity: 0;
            transform: translateY(40px);
            position: relative;
            margin: 0;
            line-height: 1.2;
        }

        /* Effet Shimmer Intro */
        .intro-shimmer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent 0%, transparent 40%, rgba(255, 255, 255, 0.6) 50%, transparent 60%, transparent 100%);
            transform: translateX(-100%);
            pointer-events: none;
        }

        .intro-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.2rem;
            letter-spacing: 4px;
            color: var(--gold-text);
            text-transform: uppercase;
            margin-top: 20px;
            opacity: 0;
        }

        /* --- ANIMATIONS INTRO KEYFRAMES --- */
        /* Séquencement déclenché par la classe .animate sur #intro-overlay */
        .animate .intro-line-top { animation: drawLine 1.5s cubic-bezier(0.23, 1, 0.32, 1) 0.5s forwards; }
        .animate .intro-line-bottom { animation: drawLine 1.5s cubic-bezier(0.23, 1, 0.32, 1) 0.7s forwards; }
        .animate .intro-main-title { animation: revealTitle 2s cubic-bezier(0.23, 1, 0.32, 1) 1.2s forwards; }
        
        .animate .star-1 { animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 2.5s forwards; }
        .animate .star-2 { animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 2.7s forwards; }
        .animate .star-3 { animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 2.9s forwards; }
        .animate .star-4 { animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 3.1s forwards; }
        .animate .star-5 { animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 3.3s forwards; }

        .animate .intro-subtitle { animation: fadeSubtitle 2s ease-out 3.8s forwards; }
        .animate .intro-shimmer { animation: shimmerMove 2.5s ease-in-out 4.5s forwards; }

        @keyframes drawLine {
            0% { transform: scaleX(0); opacity: 0; }
            100% { transform: scaleX(1); opacity: 1; }
        }
        @keyframes revealTitle {
            0% { transform: translateY(40px); opacity: 0; filter: blur(10px); }
            100% { transform: translateY(0); opacity: 1; filter: blur(0px); }
        }
        @keyframes popStar {
            0% { transform: scale(0.5) rotate(-30deg); opacity: 0; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }
        @keyframes fadeSubtitle {
            0% { opacity: 0; letter-spacing: 8px; }
            100% { opacity: 1; letter-spacing: 4px; }
        }
        @keyframes shimmerMove {
            0% { transform: translateX(-100%) skewX(-20deg); }
            100% { transform: translateX(100%) skewX(-20deg); }
        }

        @media (max-width: 768px) {
            .intro-main-title { font-size: 2rem; letter-spacing: 4px; }
            .intro-subtitle { font-size: 0.9rem; }
        }

        /* --- 3. NAVIGATION (HEADER) --- */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: 0.4s ease;
            padding: 20px 0;
            background: rgba(5, 9, 20, 0.2);
            /* Caché initialement pour laisser place à l'intro */
            opacity: 0;
            animation: fadeInHeader 1s ease-out 6.5s forwards;
        }
        @keyframes fadeInHeader { to { opacity: 1; } }

        header.scrolled {
            background: rgba(5, 9, 20, 0.98);
            padding: 10px 0;
            border-bottom: 1px solid rgba(197, 160, 89, 0.3);
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
        }

        nav {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 5%;
        }

        .logo {
            font-size: 1.4rem;
            color: white;
            font-weight: 700;
            display: flex;
            flex-direction: column;
            line-height: 1;
            z-index: 1001;
        }

        .logo span {
            font-size: 0.7rem;
            color: var(--gold-text);
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 4px;
            margin-top: 5px;
        }

        .nav-links { display: flex; gap: 3rem; list-style: none; }
        .nav-links a {
            color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.85rem; text-transform: uppercase;
            letter-spacing: 1px; transition: 0.3s; position: relative;
        }
        .nav-links a::after {
            content: ''; position: absolute; bottom: -5px; left: 0; width: 0; height: 1px;
            background: var(--gold-text); transition: 0.3s;
        }
        .nav-links a:hover::after { width: 100%; }
        .nav-links a:hover { color: var(--gold-text); }

        .menu-toggle { display: none; flex-direction: column; gap: 6px; cursor: pointer; z-index: 1001; }
        .bar { width: 30px; height: 2px; background-color: var(--gold-text); transition: 0.3s; }

        /* --- 4. HERO --- */
        .hero { position: relative; height: 100vh; width: 100%; overflow: hidden; display: flex; align-items: center; justify-content: center; padding: 0; }
        .video-background { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); min-width: 100%; min-height: 100%; width: 177.777vh; height: 56.25vw; z-index: 0; pointer-events: none; }
        .video-background iframe { width: 100%; height: 100%; transform: scale(1.3); }
        .mobile-fallback { display: none; position: absolute; inset: 0; background: url('https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1600') center/cover no-repeat; z-index: 0; }
        .hero-overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(5,9,20,0.4), rgba(5,9,20,0.7)); z-index: 1; }
        
        .hero-content {
            position: relative; z-index: 2; text-align: center; color: white; padding: 0 20px;
            opacity: 0; animation: fadeInHero 2s ease-out 7s forwards; /* Apparition retardée après l'intro */
        }
        @keyframes fadeInHero { to { opacity: 1; } }

        .hero-badge { display: inline-block; border: 1px solid var(--gold-text); padding: 10px 20px; margin-bottom: 30px; font-family: 'Cinzel', serif; letter-spacing: 3px; color: var(--gold-text); background: rgba(0,0,0,0.3); backdrop-filter: blur(5px); }
        .hero h1 { font-size: 4rem; color: white; margin-bottom: 20px; text-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .btn-gold { padding: 15px 40px; background: var(--gold-gradient); color: var(--navy-deep); text-decoration: none; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; transition: 0.3s; display: inline-block; border: none; cursor: pointer; margin-top: 20px; }
        .btn-gold:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(197, 160, 89, 0.4); }

        /* --- 5. RESTE DU SITE --- */
        .experience-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .stats-row { display: flex; gap: 40px; margin-top: 40px; border-top: 1px solid rgba(0,0,0,0.1); padding-top: 30px; }
        .stat-item h4 { font-size: 2.5rem; color: var(--gold-text); margin-bottom: 5px; }
        .stat-item span { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #888; }
        .image-stack { position: relative; height: 600px; }
        .image-stack img { position: absolute; object-fit: cover; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
        .img-main { width: 85%; height: 85%; top: 0; right: 0; z-index: 1; }
        .img-accent { width: 50%; height: 40%; bottom: 0; left: 0; z-index: 2; border: 5px solid var(--white-soft); }

        .amenities-section { background: var(--navy-light); color: white; }
        .amenities-section h2 { color: white; }
        .amenities-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; }
        .amenity-box { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 40px 30px; transition: 0.4s; text-align: center; }
        .amenity-box:hover { background: rgba(255,255,255,0.08); border-color: var(--gold-text); transform: translateY(-10px); }
        .amenity-icon { font-size: 2.5rem; color: var(--gold-text); margin-bottom: 20px; }

        .pricing-container { max-width: 1000px; margin: 0 auto; }
        .pricing-row { display: flex; justify-content: space-between; align-items: center; padding: 30px; border-bottom: 1px solid rgba(0,0,0,0.05); background: white; transition: 0.3s; }
        .pricing-row:hover { transform: scale(1.02); box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-color: transparent; }
        .pricing-row.featured { background: var(--navy-deep); color: white; border: none; margin: 20px 0; box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
        .pricing-row.featured h3, .pricing-row.featured .price { color: var(--gold-text); }
        .price { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: var(--navy-deep); }
        .unit { font-size: 0.8rem; text-transform: uppercase; }

        .contact-section { background: url('https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1600') center/cover fixed; position: relative; color: white; }
        .contact-section::before { content: ''; position: absolute; inset: 0; background: rgba(5, 9, 20, 0.9); }
        .contact-wrapper { position: relative; z-index: 2; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; }
        .contact-details h2 { color: white; margin-bottom: 30px; }
        .contact-item { margin-bottom: 30px; display: flex; align-items: center; gap: 20px; }
        .contact-item span { color: var(--gold-text); font-size: 1.5rem; }
        .contact-form input, .contact-form textarea { width: 100%; background: transparent; border: none; border-bottom: 1px solid rgba(255,255,255,0.2); padding: 15px 0; color: white; font-family: 'Montserrat', sans-serif; margin-bottom: 30px; }
        .contact-form input:focus, .contact-form textarea:focus { outline: none; border-bottom-color: var(--gold-text); }
        footer { background: black; color: rgba(255,255,255,0.5); padding: 50px 0; text-align: center; font-size: 0.8rem; border-top: 1px solid rgba(197, 160, 89, 0.2); }

        @media (max-width: 900px) {
            .hero h1 { font-size: 2.5rem; }
            .experience-grid, .contact-wrapper { grid-template-columns: 1fr; }
            .image-stack { height: 400px; margin-top: 30px; }
            .video-background { display: none; }
            .mobile-fallback { display: block; }
            .menu-toggle { display: flex; }
            .nav-links { position: fixed; top: 0; right: -100%; width: 70%; height: 100vh; background: var(--navy-deep); flex-direction: column; justify-content: center; transition: 0.4s; box-shadow: -10px 0 30px rgba(0,0,0,0.5); padding: 50px; }
            .nav-links.active { right: 0; }
        }
    </style>
</head>
<body class="loading">

    <svg width="0" height="0" style="position: absolute;">
        <linearGradient id="goldGradientSvg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#bf953f;stop-opacity:1" />
            <stop offset="50%" style="stop-color:#fcf6ba;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#b38728;stop-opacity:1" />
        </linearGradient>
    </svg>

    <div id="intro-overlay">
        <div class="intro-content">
            <div class="intro-stars-wrapper">
                <svg class="intro-star-svg star-1" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
                <svg class="intro-star-svg star-2" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
                <svg class="intro-star-svg star-3" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
                <svg class="intro-star-svg star-4" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
                <svg class="intro-star-svg star-5" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
            </div>
            <div class="intro-line intro-line-top"></div>
            <div class="intro-title-wrapper">
                <h1 class="intro-main-title">Bellevue d'Aveyron</h1>
                <div class="intro-shimmer"></div>
            </div>
            <div class="intro-line intro-line-bottom"></div>
            <div class="intro-subtitle">Villa de Luxe 5 Étoiles</div>
        </div>
    </div>

    <header id="navbar">
        <nav>
            <div class="logo">
                Bellevue d'Aveyron
                <span>VILLA 5 ÉTOILES</span>
            </div>
            <div class="menu-toggle" onclick="toggleMenu()">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>
            <ul class="nav-links" id="navLinks">
                <li><a href="#accueil" onclick="toggleMenu()">Accueil</a></li>
                <li><a href="#experience" onclick="toggleMenu()">La Villa</a></li>
                <li><a href="#services" onclick="toggleMenu()">Services</a></li>
                <li><a href="#tarifs" onclick="toggleMenu()">Tarifs</a></li>
                <li><a href="#contact" onclick="toggleMenu()">Réserver</a></li>
            </ul>
        </nav>
    </header>

    <section id="accueil" class="hero">
        <div class="video-background">
            <iframe 
                src="https://www.youtube.com/embed/19x6z3GPkL4?autoplay=1&mute=1&controls=0&loop=1&playlist=19x6z3GPkL4&playsinline=1&showinfo=0&rel=0&iv_load_policy=3&disablekb=1" 
                frameborder="0" 
                allow="autoplay; encrypted-media" 
                allowfullscreen>
            </iframe>
        </div>
        <div class="mobile-fallback"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-badge">★ Classé 5 Étoiles ★</div>
            <h1>L'Art de Vivre<br>en Aveyron</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.2rem; margin-bottom: 30px;">Une villa d'exception avec piscine chauffée et vue panoramique sur la vallée du Lot.</p>
            <a href="#contact" class="btn-gold">Planifier votre séjour</a>
            <div style="margin-top: 15px;">
                <a href="https://youtu.be/19x6z3GPkL4" target="_blank" style="color: rgba(255,255,255,0.6); font-size: 0.75rem; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.3);">
                    ▶ Voir la vidéo avec le son
                </a>
            </div>
        </div>
    </section>

    <section id="experience">
        <div class="section-header">
            <span class="subtitle">Un lieu unique</span>
            <h2>Entre Luxe & Nature</h2>
        </div>
        <div class="experience-grid">
            <div class="exp-text-block">
                <p style="margin-bottom: 20px; color: #555;">Niché sur les hauteurs de Sainte-Eulalie-d'Olt, l'un des plus beaux villages de France, Bellevue d'Aveyron n'est pas simplement une villa, c'est une retraite exclusive.</p>
                <p style="margin-bottom: 20px; color: #555;">Dans un domaine privé de 5000 m², profitez d'un calme absolu. La villa de 200 m² a été pensée pour fusionner le confort moderne avec l'âme rustique de l'Aubrac.</p>
                <div class="stats-row">
                    <div class="stat-item"><h4>200</h4><span>Mètres Carrés</span></div>
                    <div class="stat-item"><h4>10</h4><span>Invités</span></div>
                    <div class="stat-item"><h4>5</h4><span>Chambres</span></div>
                </div>
            </div>
            <div class="image-stack">
                <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800" class="img-main" alt="Salon Villa Luxe">
                <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600" class="img-accent" alt="Extérieur Villa">
            </div>
        </div>
    </section>

    <section id="services" class="amenities-section">
        <div class="section-header">
            <span class="subtitle">Tout inclus</span>
            <h2>Prestations d'Excellence</h2>
        </div>
        <div class="amenities-grid">
            <div class="amenity-box"><div class="amenity-icon">✦</div><h3>Piscine Chauffée</h3><p style="color:rgba(255,255,255,0.6)">Bassin privé 4x8m, sécurisé et chauffé pour des baignades d'avril à octobre.</p></div>
            <div class="amenity-box"><div class="amenity-icon">✦</div><h3>Vue Panoramique 360°</h3><p style="color:rgba(255,255,255,0.6)">Un spectacle quotidien sur la vallée du Lot et les contreforts de l'Aubrac.</p></div>
            <div class="amenity-box"><div class="amenity-icon">✦</div><h3>Borne Électrique</h3><p style="color:rgba(255,255,255,0.6)">Chargeur 18 kVA inclus pour vos véhicules électriques ou hybrides.</p></div>
            <div class="amenity-box"><div class="amenity-icon">✦</div><h3>Divertissement</h3><p style="color:rgba(255,255,255,0.6)">Fibre optique, 10 vélos à disposition, baby-foot, ping-pong et terrain de pétanque.</p></div>
        </div>
    </section>

    <section id="tarifs">
        <div class="section-header">
            <span class="subtitle">Saison 2026</span>
            <h2>Tarifs Hebdomadaires</h2>
        </div>
        <div class="pricing-container">
            <div class="pricing-row">
                <div class="season-info"><h3>Basse Saison</h3><div style="font-size:0.9rem; color:#777; font-style:italic;">Avril, Mai (hors ponts), Octobre, Novembre</div></div>
                <div class="price-block" style="text-align:right"><div class="price">1 590 €</div><div class="unit">/ semaine</div></div>
            </div>
            <div class="pricing-row">
                <div class="season-info"><h3>Moyenne Saison</h3><div style="font-size:0.9rem; color:#777; font-style:italic;">Mai (ponts), Juin, Septembre</div></div>
                <div class="price-block" style="text-align:right"><div class="price">1 790 €</div><div class="unit">/ semaine</div></div>
            </div>
            <div class="pricing-row featured">
                <div class="season-info"><h3>Haute Saison • Été</h3><div style="font-size:0.9rem; color:rgba(255,255,255,0.7); font-style:italic;">Juillet & Août</div></div>
                <div class="price-block" style="text-align:right"><div class="price">4 600 €</div><div class="unit">/ semaine</div></div>
            </div>
            <div class="pricing-row">
                <div class="season-info"><h3>Week-End Évasion</h3><div style="font-size:0.9rem; color:#777; font-style:italic;">3 nuits minimum (hors été)</div></div>
                <div class="price-block" style="text-align:right"><div class="price">380 €</div><div class="unit">/ nuit</div></div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 40px; font-style: italic; color: #777; font-size: 0.9rem;">* Ménage fin de séjour (optionnel) : 220€ | Taxe de séjour incluse | Capacité 10 personnes</div>
    </section>

    <section id="contact" class="contact-section">
        <div class="contact-wrapper">
            <div class="contact-details">
                <span class="subtitle">Nous contacter</span>
                <h2>Votre Séjour Commence Ici</h2>
                <div class="contact-item"><span>📞</span><div>06 80 90 71 07</div></div>
                <div class="contact-item"><span>📧</span><div>accueil@bellevuedaveyron.com</div></div>
                <div class="contact-item"><span>📍</span><div>12130 Sainte-Eulalie-d'Olt, France</div></div>
            </div>
            <form class="contact-form" onsubmit="event.preventDefault(); alert('Demande envoyée avec succès (Simulation).');">
                <input type="text" placeholder="Votre Nom" required>
                <input type="email" placeholder="Votre Email" required>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <input type="text" placeholder="Date d'arrivée" onfocus="(this.type='date')">
                    <input type="text" placeholder="Date de départ" onfocus="(this.type='date')">
                </div>
                <textarea rows="4" placeholder="Votre Message (Nombre de personnes, demandes spéciales...)"></textarea>
                <button type="submit" class="btn-gold">Envoyer la demande</button>
            </form>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 Bellevue d'Aveyron. Excellence & Tradition.</p>
    </footer>

    <script>
        // --- LOGIQUE DE L'INTRODUCTION ---
        window.addEventListener('load', () => {
            const overlay = document.getElementById('intro-overlay');
            const body = document.body;

            // 1. Démarrer l'animation immédiatement
            overlay.classList.add('animate');

            // 2. Après la fin de l'animation (env. 6.5s), cacher l'overlay
            setTimeout(() => {
                overlay.classList.add('hidden');
                body.classList.remove('loading'); // Réactiver le scroll
            }, 6800);
        });

        // --- FONCTIONS EXISTANTES DU SITE ---
        window.addEventListener('scroll', function() {
            const header = document.getElementById('navbar');
            if (window.scrollY > 50) { header.classList.add('scrolled'); } 
            else { header.classList.remove('scrolled'); }
        });

        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) { target.scrollIntoView({ behavior: 'smooth' }); }
            });
        });
    </script>
</body>
</html>