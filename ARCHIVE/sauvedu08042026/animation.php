<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animation Intro Luxe - Bellevue d'Aveyron</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Montserrat:wght@300;400&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-deep: #050914;
            --gold-gradient: linear-gradient(135deg, #bf953f 0%, #fcf6ba 50%, #b38728 100%);
            --gold-flat: #c5a059;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: var(--navy-deep);
            /* Subtil dégradé pour la profondeur */
            background-image: radial-gradient(circle at center, #0a1128 0%, #050914 100%);
            overflow: hidden;
            font-family: 'Cinzel', serif;
        }

        .intro-container {
            position: relative;
            text-align: center;
            padding: 50px;
            /* border: 1px solid red; debug */
        }

        /* --- LES ÉTOILES --- */
        .stars-wrapper {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .star-svg {
            width: 30px;
            height: 30px;
            fill: url(#goldGradientSvg); /* Utilisation du dégradé SVG défini plus bas */
            opacity: 0;
            transform: scale(0.5) rotate(-30deg);
            filter: drop-shadow(0 0 10px rgba(197, 160, 89, 0.3));
        }

        /* --- LES LIGNES SÉPARATRICES --- */
        .separator-line {
            height: 2px;
            width: 100%;
            max-width: 400px;
            background: var(--gold-gradient);
            margin: 0 auto;
            transform: scaleX(0); /* Invisible au départ */
            opacity: 0;
        }
        .line-top { margin-bottom: 40px; transform-origin: center left; }
        .line-bottom { margin-top: 40px; transform-origin: center right; }


        /* --- LE TITRE PRINCIPAL --- */
        .main-title-wrapper {
            overflow: hidden; /* Nécessaire pour l'effet de révélation */
            position: relative;
        }

        .main-title {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: 8px;
            text-transform: uppercase;
            color: transparent;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            opacity: 0;
            transform: translateY(40px); /* Position de départ plus bas */
            position: relative;
        }

        /* L'effet de brillance qui passe sur le texte */
        .shimmer-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent 0%,
                transparent 40%,
                rgba(255, 255, 255, 0.4) 50%,
                transparent 60%,
                transparent 100%
            );
            transform: translateX(-100%);
            pointer-events: none;
        }


        /* --- LE SOUS-TITRE --- */
        .subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.2rem;
            letter-spacing: 4px;
            color: var(--gold-flat);
            text-transform: uppercase;
            margin-top: 20px;
            opacity: 0;
        }

        /* --- SÉQUENCE D'ANIMATION --- */

        /* 1. Les lignes se dessinent */
        .animate .line-top { animation: drawLine 1.5s cubic-bezier(0.23, 1, 0.32, 1) 0.5s forwards; }
        .animate .line-bottom { animation: drawLine 1.5s cubic-bezier(0.23, 1, 0.32, 1) 0.7s forwards; }

        /* 2. Le titre monte et apparaît */
        .animate .main-title { animation: revealTitle 2s cubic-bezier(0.23, 1, 0.32, 1) 1.2s forwards; }

        /* 3. Les étoiles apparaissent en séquence */
        .animate .star-1 { animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 2.5s forwards; }
        .animate .star-2 { animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 2.7s forwards; }
        .animate .star-3 { animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 2.9s forwards; }
        .animate .star-4 { animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 3.1s forwards; }
        .animate .star-5 { animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 3.3s forwards; }

        /* 4. Le sous-titre apparaît */
        .animate .subtitle { animation: fadeSubtitle 2s ease-out 3.8s forwards; }

        /* 5. L'effet de brillance final */
        .animate .shimmer-overlay { animation: shimmerMove 2.5s ease-in-out 4.5s forwards; }


        /* --- KEYFRAMES DÉFINITIONS --- */
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


        /* Bouton rejouer pour la démo */
        .replay-btn {
            position: fixed; bottom: 30px; padding: 12px 30px;
            background: transparent; border: 1px solid var(--gold-flat);
            color: var(--gold-flat); font-family: 'Montserrat', sans-serif;
            text-transform: uppercase; letter-spacing: 2px; cursor: pointer;
            transition: 0.3s; opacity: 0; animation: fadeSubtitle 1s 6s forwards;
        }
        .replay-btn:hover { background: var(--gold-flat); color: var(--navy-deep); }

        @media (max-width: 768px) {
            .main-title { font-size: 2rem; letter-spacing: 4px; }
            .subtitle { font-size: 0.9rem; }
            .star-svg { width: 20px; height: 20px; }
        }
    </style>
</head>
<body class="animate"> <svg width="0" height="0" style="position: absolute;">
        <linearGradient id="goldGradientSvg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#bf953f;stop-opacity:1" />
            <stop offset="50%" style="stop-color:#fcf6ba;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#b38728;stop-opacity:1" />
        </linearGradient>
    </svg>

    <div class="intro-container">
        
        <div class="stars-wrapper">
            <svg class="star-svg star-1" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
            <svg class="star-svg star-2" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
            <svg class="star-svg star-3" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
            <svg class="star-svg star-4" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
            <svg class="star-svg star-5" viewBox="0 0 51 48"><path d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z"/></svg>
        </div>

        <div class="separator-line line-top"></div>

        <div class="main-title-wrapper">
            <h1 class="main-title">Bellevue d'Aveyron</h1>
            <div class="shimmer-overlay"></div>
        </div>

        <div class="separator-line line-bottom"></div>

        <div class="subtitle">Villa de Luxe 5 Étoiles</div>

    </div>

    <button class="replay-btn" onclick="replayAnimation()">Rejouer l'introduction</button>

    <script>
        function replayAnimation() {
            const body = document.body;
            // On retire la classe pour reset
            body.classList.remove('animate');
            
            // Hack pour forcer le navigateur à recalculer le style (reflow)
            void body.offsetWidth; 
            
            // On remet la classe pour relancer
            body.classList.add('animate');
        }
    </script>
</body>
</html>