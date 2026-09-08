<?php
// index.php
session_start();

// 1. INCLUSION DE LA CONFIGURATION (Connexion BDD)
require_once 'config/db.php';

// Initialisation des variables pour l'affichage (Popups)
$bookingSuccess = false;
$bookingData = [];
$errorMsg = "";

// 2. RÉCUPÉRATION DES DATES INDISPONIBLES POUR LE CALENDRIER JS
// On va chercher dans la table 'calendrier_dispo' tous les jours marqués 'reserve' ou 'indisponible'.
// Cela permet au Javascript de griser ces cases.
$booked_dates = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT jour FROM calendrier_dispo WHERE statut != 'libre'");
        $booked_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        // Erreur silencieuse si la table n'existe pas, pour ne pas casser le site
    }
}
// On encode les dates en JSON pour que le JavaScript puisse les lire
$json_booked_dates = json_encode($booked_dates);


// 3. TRAITEMENT DU FORMULAIRE DE RÉSERVATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    try {
        if (!$pdo) throw new Exception("Connexion base de données impossible.");

        // A. Nettoyage des données (Sécurité anti-injection)
        $client_nom   = htmlspecialchars($_POST['customer_name']);
        $client_email = htmlspecialchars($_POST['customer_email']);
        $client_tel   = htmlspecialchars($_POST['customer_phone']);
        $date_debut   = $_POST['check_in'];
        $date_fin     = $_POST['check_out'];
        // Si la case ménage est cochée, on met 1, sinon 0
        $option_menage = isset($_POST['cleaning_fee']) ? 1 : 0;

        // B. Vérification de la durée (min 3 nuits)
        $d1 = new DateTime($date_debut);
        $d2 = new DateTime($date_fin);
        $interval = $d1->diff($d2);
        $nuits = $interval->days;

        if ($nuits < 3) {
            throw new Exception("Le séjour doit être de 3 nuits minimum.");
        }

        // C. Calcul du Prix (Logique métier complexe)
        // 1. On récupère les paramètres généraux (frais ménage, acompte %) depuis la BDD
        $settings = $pdo->query("SELECT * FROM gite_settings WHERE id = 1")->fetch();
        $frais_menage_bd = $settings['frais_menage'] ?? 220;
        $pourcentage_acompte = $settings['acompte_pourcentage'] ?? 30;

        // 2. On cherche si les dates correspondent à une "Saison" définie dans 'tarifs_saison'
        $stmt = $pdo->prepare("SELECT * FROM tarifs_saison WHERE date_debut <= ? AND date_fin >= ?");
        $stmt->execute([$date_debut, $date_fin]);
        $saison = $stmt->fetch();

        $prix_total = 0;

        // 3. Application du tarif
        if ($saison && $nuits >= 7) {
            // Si une saison est trouvée et séjour > 7 jours : Prix à la semaine au prorata
            $prix_jour_moyen = $saison['prix_semaine'] / 7;
            $prix_total = $prix_jour_moyen * $nuits;
        } else {
            // Sinon (Week-end ou hors saison définie) : Prix fixe par nuit (ex: 380€)
            // Note : Vous pouvez changer 380 par une valeur BDD si vous le souhaitez
            $prix_total = 380 * $nuits;
        }

        // 4. Ajout des options
        if ($option_menage) {
            $prix_total += $frais_menage_bd;
        }

        // 5. Calcul de l'acompte
        $acompte_montant = $prix_total * ($pourcentage_acompte / 100);

        // D. INSERTION EN BASE DE DONNÉES
        // IMPORTANT : On insère avec le statut 'attente' pour validation manuelle admin
        $sql = "INSERT INTO reservations (
                    client_nom, client_email, client_tel, 
                    date_debut, date_fin, 
                    prix_total, acompte_montant, option_menage, 
                    statut, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'attente', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $client_nom, 
            $client_email, 
            $client_tel, 
            $date_debut, 
            $date_fin, 
            $prix_total, 
            $acompte_montant, 
            $option_menage
        ]);

        // E. Validation pour l'affichage du popup
        $bookingSuccess = true;
        $bookingData = [
            'nuits' => $nuits,
            'total' => $prix_total
        ];

    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bellevue d'Aveyron - Villa 5 Étoiles Luxe</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital@0;1&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="loading">

    <?php include 'partials/intro.php'; ?>

<header id="navbar">
    <nav>
        <a href="#accueil" class="brand-logo">
            <span class="brand-main">Bellevue</span>
            <span class="brand-suffix">d'Aveyron</span>
            <div class="brand-divider"></div>
            <span class="brand-tagline">VILLA 5 ÉTOILES</span>
        </a>

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
            <li><a href="#reservation" onclick="toggleMenu()">Réserver</a></li>
            <li><a href="#contact" onclick="toggleMenu()">Contact</a></li>
        </ul>
    </nav>
</header>

    <section id="accueil" class="hero">
        <div class="video-background">
            <iframe src="https://www.youtube.com/embed/19x6z3GPkL4?autoplay=1&mute=1&controls=0&loop=1&playlist=19x6z3GPkL4&playsinline=1&showinfo=0&rel=0&iv_load_policy=3&disablekb=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
        <div class="mobile-fallback"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-badge">★ Classé 5 Étoiles ★</div>
            <h1>L'Art de Vivre<br>en Aveyron</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.2rem; margin-bottom: 30px;">Une villa d'exception avec piscine chauffée et vue panoramique sur la vallée du Lot.</p>
            <a href="#reservation" class="btn-gold">Planifier votre séjour</a>
        </div>
    </section>

<section id="experience">
        <div class="section-header">
            <span class="subtitle">L'Héritage de Bellevue</span>
            <h2>Une Histoire de Famille, Une Âme de Pierre</h2>
        </div>
        
        <div class="experience-grid">
            <div class="exp-text-block">
                <p style="margin-bottom: 20px; color: #555; font-style: italic; border-left: 3px solid var(--gold-text); padding-left: 15px;">
                    "Avant d'être cette villa 5 étoiles, cette maison était le cœur battant de ma famille, une ferme vigneronne ancrée dans la roche de Sainte-Eulalie-d'Olt."
                </p>
                
                <p style="margin-bottom: 15px; color: #555;">
                    <strong>L'Intelligence du lieu :</strong> Mes grands-parents ont choisi cet emplacement dominant la vallée pour une raison précise : offrir à leurs vignes l'exposition la plus généreuse. Aujourd'hui, ce microclimat privilégié est devenu le gardien de vos vacances, garantissant un ensoleillement unique du matin au soir sur la piscine.
                </p>

                <p style="margin-bottom: 15px; color: #555;">
                    <strong>Des racines et des ailes :</strong> Rénover ce bâtiment était une promesse. Nous avons conservé les murs épais en <em>galets du Lot</em>, façonnés par la rivière, et la toiture traditionnelle en lauze qui protège la maison depuis des décennies. À l'intérieur, le confort contemporain dialogue avec cette histoire pour offrir un luxe authentique, sans artifice.
                </p>

                <p style="margin-bottom: 20px; color: #555;">
                    <strong>De notre famille à la vôtre :</strong> Le parc de 5000 m², autrefois terre de labeur, est devenu un sanctuaire de liberté pour vos enfants et un havre de paix pour les amoureux de nature. En séjournant ici, vous n'êtes pas de simples touristes, mais les dépositaires, le temps d'un séjour, de la douceur de vivre de Bellevue.
                </p>

                <div class="stats-row">
                    <div class="stat-item"><h4>200</h4><span>Mètres Carrés</span></div>
                    <div class="stat-item"><h4>10</h4><span>Invités</span></div>
                    <div class="stat-item"><h4>5</h4><span>Chambres</span></div>
                </div>
            </div>

            <div class="villa-visual">
                <img src="images/accueil.jpg" alt="Bellevue d'Aveyron : Entre histoire familiale et luxe contemporain">
            </div>
        </div>
    </section>

    <section id="services" class="amenities-section">
        <div class="section-header"><span class="subtitle">Tout inclus</span><h2>Prestations d'Excellence</h2></div>
        <div class="amenities-grid">
            <div class="amenity-box"><div class="amenity-icon">✦</div><h3>Piscine Chauffée</h3><p style="color:rgba(255,255,255,0.6)">Bassin privé 4x8m, sécurisé et chauffé.</p></div>
            <div class="amenity-box"><div class="amenity-icon">✦</div><h3>Vue Panoramique 360°</h3><p style="color:rgba(255,255,255,0.6)">Un spectacle quotidien sur la vallée du Lot.</p></div>
            <div class="amenity-box"><div class="amenity-icon">✦</div><h3>Borne Électrique</h3><p style="color:rgba(255,255,255,0.6)">Chargeur 18 kVA inclus.</p></div>
            <div class="amenity-box"><div class="amenity-icon">✦</div><h3>Divertissement</h3><p style="color:rgba(255,255,255,0.6)">Fibre optique, vélos, baby-foot.</p></div>
        </div>
    </section>

    <section id="tarifs">
        <div class="section-header"><span class="subtitle">Saison 2026</span><h2>Tarifs Hebdomadaires</h2></div>
        <div class="pricing-container">
            <div class="pricing-row"><div class="season-info"><h3>Basse Saison</h3><div style="font-size:0.9rem; color:#777;">Avril, Octobre, Novembre</div></div><div class="price-block"><div class="price">1 590 €</div></div></div>
            <div class="pricing-row"><div class="season-info"><h3>Moyenne Saison</h3><div style="font-size:0.9rem; color:#777;">Mai, Juin, Septembre</div></div><div class="price-block"><div class="price">1 790 €</div></div></div>
            <div class="pricing-row featured"><div class="season-info"><h3>Haute Saison</h3><div style="font-size:0.9rem; color:rgba(255,255,255,0.7);">Juillet & Août</div></div><div class="price-block"><div class="price">4 600 €</div></div></div>
        </div>
    </section>

    <section id="reservation">
        <div class="section-header">
            <span class="subtitle">Disponibilités</span>
            <h2>Réservez Votre Séjour</h2>
        </div>
        
        <div class="booking-layout">
            <div class="calendar-side">
                <div class="calendar-header">
                    <button class="cal-nav" onclick="changeMonth(-1)">❮</button>
                    <span class="month-label" id="calendarTitle">Juillet 2026</span>
                    <button class="cal-nav" onclick="changeMonth(1)">❯</button>
                </div>
                <div class="days-grid">
                    <div class="day-label">L</div><div class="day-label">M</div><div class="day-label">M</div><div class="day-label">J</div><div class="day-label">V</div><div class="day-label">S</div><div class="day-label">D</div>
                </div>
                <div class="days-grid" id="calendarDays">
                    </div>
                <div style="margin-top:20px; display:flex; gap:15px; font-size:0.8rem; justify-content:center;">
                    <div style="display:flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:50%; border:1px solid #ddd;"></span> Libre</div>
                    <div style="display:flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:50%; background:var(--gold-gradient);"></span> Sélection</div>
                    <div style="display:flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:50%; background:#ddd; text-decoration:line-through;"></span> Occupé</div>
                </div>
            </div>

            <div class="form-side">
                <h3 class="form-title">Votre Demande</h3>
                
                <div class="summary-box" id="bookingSummary">
                    <p>Veuillez sélectionner vos dates dans le calendrier (min 3 nuits).</p>
                </div>

                <form method="POST" action="index.php#reservation">
                    <input type="hidden" name="action" value="book">
                    <input type="hidden" name="check_in" id="input_check_in" required>
                    <input type="hidden" name="check_out" id="input_check_out" required>

                    <input type="text" name="customer_name" class="lux-input" placeholder="Nom Complet" required>
                    <input type="email" name="customer_email" class="lux-input" placeholder="Adresse E-mail" required>
                    <input type="tel" name="customer_phone" class="lux-input" placeholder="Téléphone" required>
                    
                    <label class="option-check">
                        <input type="checkbox" name="cleaning_fee" value="1">
                        Option Ménage fin de séjour (+220€)
                    </label>

                    <button type="submit" class="btn-gold" style="width:100%; border-radius:4px;">Envoyer la demande</button>
                    <p style="font-size:0.75rem; color:#888; margin-top:15px; text-align:center;">
                        Un acompte de 30% sera demandé après validation par nos soins.
                    </p>
                </form>
            </div>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <div class="contact-wrapper">
            <div class="contact-details">
                <span class="subtitle">Nous contacter</span>
                <h2>Coordonnées</h2>
                <div class="contact-item"><span>📞</span><div>06 80 90 71 07</div></div>
                <div class="contact-item"><span>📧</span><div>accueil@bellevuedaveyron.com</div></div>
                <div class="contact-item"><span>📍</span><div>12130 Sainte-Eulalie-d'Olt</div></div>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 Bellevue d'Aveyron. Excellence & Tradition.</p>
    </footer>

    <div class="modal-overlay <?php echo $bookingSuccess ? 'active' : ''; ?>" id="successModal">
        <div class="modal-card">
            <div class="success-icon">✓</div>
            <h3 style="font-family:'Cinzel', serif; color:var(--navy-deep); margin-bottom:15px;">Demande Reçue</h3>
            <p style="color:#666; margin-bottom:20px;">
                Merci de votre confiance. <br>
                Votre demande pour <strong><?php echo isset($bookingData['nuits']) ? $bookingData['nuits'] : ''; ?> nuits</strong> a bien été enregistrée.
                <br><br>
                <strong>Montant estimé : <?php echo isset($bookingData['total']) ? number_format($bookingData['total'], 2) : ''; ?> €</strong><br>
                Nos hôtes traiteront votre demande sous 24h.
            </p>
            <button onclick="document.getElementById('successModal').classList.remove('active')" class="btn-gold">Fermer</button>
        </div>
    </div>

    <?php if ($errorMsg): ?>
    <script>alert("Erreur lors de la réservation : <?php echo addslashes($errorMsg); ?>");</script>
    <?php endif; ?>

    <script>
        const bookedDates = <?php echo $json_booked_dates ?: '[]'; ?>;
    </script>

    <script src="js/script.js"></script>

</body>
</html>