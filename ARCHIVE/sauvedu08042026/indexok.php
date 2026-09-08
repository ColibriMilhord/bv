<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vœux 2026 - Bellevue d'Aveyron</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Montserrat:wght@300;400&family=Playfair+Display:italic,wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: linear-gradient(135deg, #c5a059 0%, #f1e4c1 50%, #b8860b 100%);
            --navy: #0a1128;
            --soft-white: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: radial-gradient(circle at center, #1a2a4a 0%, #0a1128 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            color: var(--soft-white);
        }

        /* Ambiance stellaire */
        #canvas-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .container {
            position: relative;
            z-index: 10;
            width: 90%;
            max-width: 850px;
            perspective: 1000px;
        }

        .card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px; /* Coins moins arrondis pour plus de modernité/luxe */
            padding: 80px 50px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            text-align: center;
            animation: fadeIn 2s ease-out;
        }

        /* Bordure dorée fine */
        .card::after {
            content: '';
            position: absolute;
            inset: 10px;
            border: 1px solid rgba(197, 160, 89, 0.3);
            pointer-events: none;
        }

        .stars-rating {
            margin-bottom: 30px;
            letter-spacing: 8px;
        }

        .star-icon {
            background: var(--gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 18px;
        }

        h1 {
            font-family: 'Cinzel', serif;
            font-weight: 400;
            font-size: 1.2rem;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: #d4af37;
        }

        .year {
            font-family: 'Playfair Display', serif;
            font-size: 6rem;
            font-weight: 700;
            line-height: 1;
            margin: 20px 0;
            background: var(--gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }

        .subtitle {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-size: 1.5rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .divider {
            width: 60px;
            height: 1px;
            background: var(--gold);
            margin: 40px auto;
        }

        .message {
            font-size: 1.1rem;
            line-height: 2;
            font-weight: 300;
            max-width: 600px;
            margin: 0 auto;
            color: rgba(255, 255, 255, 0.8);
        }

        .signature {
            margin-top: 60px;
        }

        .villa-name {
            font-family: 'Cinzel', serif;
            font-size: 1.8rem;
            letter-spacing: 4px;
            margin-bottom: 5px;
        }

        .location {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #c5a059;
        }

        .coming-soon {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .coming-soon p {
            font-size: 0.9rem;
            letter-spacing: 1px;
            font-style: italic;
            color: rgba(255, 255, 255, 0.5);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Neige et étoiles via JS pour fluidité */
        .snowflake {
            position: fixed;
            background: white;
            border-radius: 50%;
            pointer-events: none;
            z-index: 2;
        }

        @media (max-width: 768px) {
            .card { padding: 50px 20px; }
            .year { font-size: 4rem; }
            .villa-name { font-size: 1.3rem; }
            .message { font-size: 1rem; }
        }
    </style>
</head>
<body>

    <div id="canvas-container"></div>

    <div class="container">
        <div class="card">
            <div class="stars-rating">
                <span class="star-icon">★★★★★</span>
            </div>

            <h1>Meilleurs Vœux</h1>
            <div class="year">2026</div>
            <div class="subtitle">L'élégance au cœur de l'Aveyron</div>

            <div class="divider"></div>

            <div class="message">
                Que cette nouvelle année soit le théâtre de vos plus beaux souvenirs. 
                Sérénité, luxe et nature se préparent à vous accueillir pour une saison 
                placée sous le signe de l'exception.
            </div>

            <div class="signature">
                <div class="villa-name">Bellevue d'Aveyron</div>
                <div class="location">Villa de Prestige • Sainte-Eulalie-d'Olt</div>
            </div>

            <div class="coming-soon">
                <p>— Notre nouvelle expérience digitale est en cours de création —</p>
                <p style="margin-top: 10px; font-size: 0.7rem;">bellevuedaveyron.fr</p>
            </div>
        </div>
    </div>

    <script>
        // Création des particules (étoiles/neige)
        function createParticles() {
            const container = document.getElementById('canvas-container');
            const particleCount = 60;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'snowflake';
                
                // Propriétés aléatoires
                const size = Math.random() * 3 + 1;
                const posX = Math.random() * 100;
                const delay = Math.random() * 20;
                const duration = Math.random() * 10 + 10;
                const opacity = Math.random() * 0.5 + 0.2;

                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.top = `-10px`;
                particle.style.opacity = opacity;
                particle.style.filter = `blur(1px)`;
                
                // Animation
                particle.animate([
                    { transform: `translateY(0vh) translateX(0px)`, opacity: opacity },
                    { transform: `translateY(100vh) translateX(${Math.random() * 100 - 50}px)`, opacity: 0 }
                ], {
                    duration: duration * 1000,
                    iterations: Infinity,
                    delay: delay * 1000
                });

                container.appendChild(particle);
            }
        }

        // Étoiles fixes scintillantes
        function createBackdrop() {
            const container = document.getElementById('canvas-container');
            for (let i = 0; i < 100; i++) {
                const star = document.createElement('div');
                star.className = 'snowflake';
                star.style.width = `1px`;
                star.style.height = `1px`;
                star.style.left = `${Math.random() * 100}%`;
                star.style.top = `${Math.random() * 100}%`;
                star.style.opacity = Math.random();
                
                star.animate([
                    { opacity: 0.2 },
                    { opacity: 1 },
                    { opacity: 0.2 }
                ], {
                    duration: Math.random() * 3000 + 2000,
                    iterations: Infinity
                });
                container.appendChild(star);
            }
        }

        createParticles();
        createBackdrop();
    </script>
</body>
</html>