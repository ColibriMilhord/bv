<?php
// --- 1. LOGIQUE MÉTIER ---
session_start();

if (file_exists('config/db.php')) {
    require_once 'config/db.php';
} else {
    $host    = '127.0.0.1';
    $db      = 'u424962071_rbellevue';
    $user    = 'root';
    $pass    = '';
    $charset = 'utf8mb4';
    $dsn     = "mysql:host=$host;dbname=$db;charset=$charset";
    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    } catch (\PDOException $e) {
        $pdo = null;
    }
}

$bookingSuccess = false;
$bookingData    = [];
$errorMsg       = '';

// Récupération des dates réservées
$booked_dates = [];
if ($pdo) {
    try {
        $stmt         = $pdo->query("SELECT jour FROM calendrier_dispo WHERE statut != 'libre'");
        $booked_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) { /* Table pas encore créée */ }
}
$json_booked_dates = json_encode($booked_dates);

// Récupération des tarifs
$tarifs_display = [];
if ($pdo) {
    try {
        $stmt           = $pdo->query("SELECT * FROM tarifs_saison ORDER BY prix_semaine ASC");
        $tarifs_display = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { /* Table pas encore créée */ }
}

// TRAITEMENT RÉSERVATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    try {
        if (!$pdo) throw new Exception("Erreur de connexion à la base de données.");

        $settings     = $pdo->query("SELECT * FROM gite_settings WHERE id = 1")->fetch();
        $client_nom   = htmlspecialchars($_POST['customer_name']);
        $client_email = htmlspecialchars($_POST['customer_email']);
        $client_tel   = htmlspecialchars($_POST['customer_phone']);
        $date_debut   = $_POST['check_in'];
        $date_fin     = $_POST['check_out'];
        $option_menage = isset($_POST['cleaning_fee']) ? 1 : 0;

        $d1    = new DateTime($date_debut);
        $d2    = new DateTime($date_fin);
        $nuits = $d1->diff($d2)->days;

        if ($nuits < 3) throw new Exception("Le séjour doit être de 3 nuits minimum.");

        $stmt = $pdo->prepare("SELECT * FROM tarifs_saison WHERE date_debut <= ? AND date_fin >= ?");
        $stmt->execute([$date_debut, $date_fin]);
        $saison = $stmt->fetch();

        $prix_total = 0;
        if ($saison && $nuits >= 7) {
            $prix_total = ($saison['prix_semaine'] / 7) * $nuits;
        } else {
            $prix_total = 380 * $nuits;
        }
        if ($option_menage) $prix_total += ($settings['frais_menage'] ?? 220);
        $acompte_montant = $prix_total * (($settings['acompte_pourcentage'] ?? 30) / 100);

        $sql  = "INSERT INTO reservations (client_nom, client_email, client_tel, date_debut, date_fin, prix_total, acompte_montant, option_menage, statut, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'attente', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$client_nom, $client_email, $client_tel, $date_debut, $date_fin, $prix_total, $acompte_montant, $option_menage]);

        $bookingSuccess = true;
        $bookingData    = ['nuits' => $nuits, 'total' => $prix_total];

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
    <title>Bellevue d'Aveyron — Villa 5 Étoiles Luxe | Sainte-Eulalie-d'Olt</title>
    <meta name="description" content="Gîte de luxe 5 étoiles en Aveyron à Sainte-Eulalie-d'Olt. Piscine chauffée, vue panoramique sur la vallée du Lot, villa familiale d'exception. Réservez votre séjour.">
    <link rel="icon" type="image/x-icon" href="images/BELLEVUE/logo.ico">

    <!-- Fonts Google — chargement non bloquant -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap"
          rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    </noscript>

    <!-- Feuille de style principale -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="loading">

    <!-- ══ SVG Gradient (intro) ══ -->
    <svg width="0" height="0" style="position:absolute;">
        <linearGradient id="goldGradientSvg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%"   style="stop-color:#bf953f;stop-opacity:1"/>
            <stop offset="50%"  style="stop-color:#fcf6ba;stop-opacity:1"/>
            <stop offset="100%" style="stop-color:#b38728;stop-opacity:1"/>
        </linearGradient>
    </svg>

    <!-- ══════════════════════════════════════════
         ÉCRAN D'INTRODUCTION LUXE
         ══════════════════════════════════════════ -->
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


    <!-- ══════════════════════════════════════════
         HEADER / NAVIGATION
         ══════════════════════════════════════════ -->
    <header id="navbar">
        <nav>
            <div class="logo">Bellevue d'Aveyron<span>VILLA 5 ÉTOILES</span></div>

            <div class="menu-toggle" onclick="toggleMenu()" aria-label="Ouvrir le menu" aria-expanded="false">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>

            <ul class="nav-links" id="navLinks">
                <li><a href="#accueil"     onclick="toggleMenu()">Accueil</a></li>
                <li><a href="#experience"  onclick="toggleMenu()">La Villa</a></li>
                <li><a href="#services"    onclick="toggleMenu()">Services</a></li>
                <li><a href="#tarifs"      onclick="toggleMenu()">Tarifs</a></li>
                <li><a href="decouvrir.php" onclick="toggleMenu()" style="color:var(--gold-text);">Visiter l'Aveyron</a></li>
                <li><a href="#contact"     onclick="openContactModal(); toggleMenu();">Contact</a></li>
                <li class="menu-phone"><a href="tel:+33680907107" style="color:var(--gold-text);font-weight:600;">✆ 06 80 90 71 07</a></li>
                <li><a href="#reservation" class="btn-book-now" onclick="toggleMenu()">Réserver</a></li>
            </ul>
        </nav>
    </header>


    <!-- ══════════════════════════════════════════
         SECTION HERO — VIDÉO PLEIN ÉCRAN
         Fond avant vidéo : images/34.jpg (à placer dans le dossier images/)
         Fallback : images/accueil.jpg si 34.jpg absent
         ══════════════════════════════════════════ -->
    <section id="accueil" class="hero">

        <!-- Image de fond affichée avant et pendant le chargement de la vidéo.
             Placez votre fichier 34.jpg dans le dossier images/ pour l'utiliser ici. -->
        <div class="hero-poster" id="heroPoster"
             style="position:absolute;inset:0;background:url('images/34.jpg') center/cover no-repeat,url('images/accueil.jpg') center/cover no-repeat;z-index:0;transition:opacity 1.5s ease;">
        </div>

        <div class="video-background">
            <video id="heroVideo" muted loop playsinline
                   style="opacity:0;transition:opacity 1.5s ease;width:100%;height:100%;object-fit:cover;">
                <!-- src chargé dynamiquement par JS après l'intro -->
            </video>
        </div>

        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-badge">★ Classé 5 Étoiles ★</div>
            <h1>L'Art de Vivre<br>en Aveyron</h1>
            <p style="color:rgba(255,255,255,0.9);font-size:1.2rem;margin-bottom:30px;">
                Une villa d'exception avec piscine chauffée et vue panoramique sur la vallée du Lot.
            </p>
            <a href="#reservation" class="btn-gold">Planifier votre séjour</a>
        </div>
    </section>


    <!-- ══════════════════════════════════════════
         SECTION EXPÉRIENCE — Histoire de la Villa
         ══════════════════════════════════════════ -->
    <section id="experience">
        <div class="section-header">
            <span class="subtitle">L'Héritage de Bellevue</span>
            <h2>Une Histoire de famille, une âme de pierre</h2>
        </div>

        <div class="experience-grid">
            <div class="exp-text-block">
                <p style="margin-bottom:20px;color:#555;font-style:italic;border-left:3px solid var(--gold-text);padding-left:15px;">
                    "Avant d'être cette villa 5 étoiles, cette maison était le cœur battant de ma famille, une ferme vigneronne ancrée dans la roche de Sainte-Eulalie-d'Olt."
                </p>
                <p style="margin-bottom:15px;color:#555;">
                    <strong>L'Intelligence du lieu :</strong> Mes grands-parents ont choisi cet emplacement dominant la vallée pour une raison précise : offrir à leurs vignes l'exposition la plus généreuse. Aujourd'hui, ce microclimat privilégié est devenu le gardien de vos vacances, garantissant un ensoleillement unique du matin au soir sur la piscine.
                </p>
                <p style="margin-bottom:15px;color:#555;">
                    <strong>Des racines et des ailes :</strong> Rénover ce bâtiment était une promesse. Nous avons conservé les murs épais en <em>galets du Lot</em>, façonnés par la rivière, et la toiture traditionnelle en lauze qui protège la maison depuis des décennies. À l'intérieur, le confort contemporain dialogue avec cette histoire pour offrir un luxe authentique, sans artifice.
                </p>
                <p style="margin-bottom:20px;color:#555;">
                    <strong>De notre famille à la vôtre :</strong> Le parc de 5000 m², autrefois terre de labeur, est devenu un sanctuaire de liberté pour vos enfants et un havre de paix pour les amoureux de nature. En séjournant ici, vous n'êtes pas de simples touristes, mais les dépositaires, le temps d'un séjour, de la douceur de vivre de Bellevue.
                </p>
                <div class="stats-row">
                    <div class="stat-item"><h4>200</h4><span>Mètres Carrés</span></div>
                    <div class="stat-item"><h4>10</h4><span>Invités</span></div>
                    <div class="stat-item"><h4>5</h4><span>Chambres</span></div>
                </div>
            </div>

            <div class="villa-visual">
                <?php
                $dossier_famille = 'images/BELLEVUE/';
                $famille_images  = [];
                foreach (['jpg','jpeg','png','webp','JPG','JPEG'] as $ext) {
                    $found = glob($dossier_famille . '*.' . $ext);
                    if ($found) $famille_images = array_merge($famille_images, $found);
                }
                if (!empty($famille_images)) shuffle($famille_images);
                ?>
                <?php if (!empty($famille_images)): ?>
                    <div class="family-slideshow" id="familySlideshow">
                        <?php foreach ($famille_images as $index => $img): ?>
                            <img src="<?php echo $img; ?>" alt="La Villa Bellevue"
                                 class="family-slide <?php echo ($index === 0) ? 'active' : ''; ?>">
                        <?php endforeach; ?>
                        <div class="pmr-frame-overlay"></div>
                    </div>
                <?php else: ?>
                    <img src="images/accueil.jpg" alt="Bellevue d'Aveyron : Entre histoire familiale et luxe contemporain">
                <?php endif; ?>
            </div>
        </div>
    </section>


    <!-- ══════════════════════════════════════════
         SECTION ACCESSIBILITÉ (PMR)
         ══════════════════════════════════════════ -->
    <section id="accessibilite" class="access-section">
        <?php
        $dossier    = 'images/PMR/';
        $pmr_images = [];
        $extensions = ['jpg','jpeg','png','webp','JPG','JPEG'];
        foreach ($extensions as $ext) {
            $found = glob($dossier . '*.' . $ext);
            if ($found) $pmr_images = array_merge($pmr_images, $found);
        }
        if (!empty($pmr_images)) shuffle($pmr_images);
        ?>
        <div class="access-container">
            <div class="access-visual-side">
                <?php if (!empty($pmr_images)): ?>
                    <div class="pmr-slideshow" id="pmrSlideshow">
                        <?php foreach ($pmr_images as $index => $img): ?>
                            <img src="<?php echo $img; ?>" alt="Accessibilité Bellevue d'Aveyron"
                                 class="pmr-slide <?php echo ($index === 0) ? 'active' : ''; ?>">
                        <?php endforeach; ?>
                        <div class="pmr-frame-overlay"></div>
                    </div>
                <?php else: ?>
                    <div class="access-icon-wrapper error-mode">
                        <div style="width:100%;height:300px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;flex-direction:column;border:1px solid #ccc;">
                            <p style="color:#999;font-style:italic;">Images PMR non trouvées</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="access-content">
                <div class="section-header" style="margin-bottom:30px;text-align:left;">
                    <span class="subtitle">Hospitalité & Inclusivité</span>
                    <h2 style="font-size:2rem;">L'Aveyron pour Tous</h2>
                </div>
                <p class="access-intro">
                    Pour des vacances en toute tranquillité, nous avons conçu Bellevue d'Aveyron comme un espace ouvert à tous. L'accessibilité n'est pas une option, c'est une promesse de sérénité partagée.
                </p>
                <div class="access-features">
                    <div class="access-item">
                        <span class="check-gold">✓</span>
                        <p><strong>Plain-pied intégral :</strong> Du parking aux espaces de vie (cuisine, salon, terrasses), tout est pensé pour une circulation fluide sans obstacle.</p>
                    </div>
                    <div class="access-item">
                        <span class="check-gold">✓</span>
                        <p><strong>Espace nuit adapté :</strong> Une chambre et une salle de bain entièrement équipées sont accessibles directement au rez-de-chaussée.</p>
                    </div>
                    <div class="access-item">
                        <span class="check-gold">✓</span>
                        <p><strong>Aménagements extérieurs :</strong> Une piste aménagée relie le parking à la maison pour un accès facilité en toutes circonstances.</p>
                    </div>
                </div>
                <p class="access-footer">
                    Idéalement situé pour visiter les merveilles accessibles de la région (Beaux villages, Viaduc de Millau, Musée Soulages...).
                    <br><em>N'hésitez pas à nous contacter pour préparer votre venue.</em>
                </p>
            </div>
        </div>
    </section>


    <!-- ══════════════════════════════════════════
         SECTION SERVICES / PRESTATIONS
         ══════════════════════════════════════════ -->
    <section id="services" class="amenities-section">
        <div class="section-header">
            <span class="subtitle">Tout inclus</span>
            <h2>Prestations d'Excellence</h2>
        </div>

        <div class="amenities-grid">
            <div class="flip-card">
              <div class="flip-card-inner">
                <div class="flip-card-front">
                    <div class="amenity-icon">✦</div>
                    <h3>Piscine<br>Chauffée</h3>
                    <p>Bassin privé de 4x8m, sécurisé par volet roulant et chauffé à 28°C d'avril à octobre.</p>
                    <div class="card-info-icon">ℹ</div>
                </div>
                <div class="flip-card-back">
                    <h3>Piscine<br>Chauffée</h3>
                    <p>Volet sécurisé homologué. Température maintenue à 28°C en haute saison pour un confort optimal garanti.</p>
                </div>
              </div>
            </div>

            <div class="flip-card">
              <div class="flip-card-inner">
                <div class="flip-card-front">
                    <div class="amenity-icon">✦</div>
                    <h3>Vue<br>Panoramique</h3>
                    <p>Un spectacle quotidien époustouflant à 360° sur la vallée du Lot et les monts d'Aubrac.</p>
                    <div class="card-info-icon">ℹ</div>
                </div>
                <div class="flip-card-back">
                    <h3>Vue<br>Panoramique</h3>
                    <p>Orientation plein sud. Lever et coucher de soleil imprenables depuis les différentes immenses terrasses de la villa.</p>
                </div>
              </div>
            </div>

            <div class="flip-card">
              <div class="flip-card-inner">
                <div class="flip-card-front">
                    <div class="amenity-icon">✦</div>
                    <h3>Borne<br>Électrique</h3>
                    <p>Chargeur rapide 18 kVA inclus pour votre véhicule, pour explorer l'Aveyron l'esprit libre.</p>
                    <div class="card-info-icon">ℹ</div>
                </div>
                <div class="flip-card-back">
                    <h3>Borne<br>Électrique</h3>
                    <p>Chargeur 18 kVA compatible toutes marques (Tesla, Renault, etc). Accès privé sur le parking sécurisé de la propriété.</p>
                </div>
              </div>
            </div>

            <div class="flip-card">
              <div class="flip-card-inner">
                <div class="flip-card-front">
                    <div class="amenity-icon">✦</div>
                    <h3>Divertissement</h3>
                    <p>Fibre optique très haut débit, Wi-Fi partout, VTT à disposition et Baby-foot Bonzini.</p>
                    <div class="card-info-icon">ℹ</div>
                </div>
                <div class="flip-card-back">
                    <h3>Divertissement</h3>
                    <p>Fibre optique 1 Gbit/s. Baby-foot Bonzini professionnel. 2 VTT adultes et 1 VTT enfant à disposition gratuitement.</p>
                </div>
              </div>
            </div>
        </div>
    </section>


    <!-- ══════════════════════════════════════════
         SECTION TARIFS
         ══════════════════════════════════════════ -->
    <section id="tarifs">
        <div class="section-header">
            <span class="subtitle">Saison 2026</span>
            <h2>Tarifs Hebdomadaires</h2>
        </div>
        <div class="pricing-container">
            <?php if (empty($tarifs_display)): ?>
                <div class="pricing-row">
                    <div class="season-info"><h3>Tarifs indisponibles</h3></div>
                    <div class="price-block"><div class="price">- €</div></div>
                </div>
            <?php else: ?>
                <?php foreach ($tarifs_display as $t):
                    $isHigh    = $t['prix_semaine'] > 2000;
                    $class     = $isHigh ? 'pricing-row featured' : 'pricing-row';
                    $styleTitle = $isHigh ? 'color:var(--gold-text);' : '';
                    $styleSub  = $isHigh ? 'color:rgba(255,255,255,0.7);' : 'color:#777;';
                    $d1 = new DateTime($t['date_debut']);
                    $d2 = new DateTime($t['date_fin']);
                    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
                    $formatter->setPattern('d MMMM');
                ?>
                    <div class="<?php echo $class; ?>">
                        <div class="season-info">
                            <h3 style="<?php echo $styleTitle; ?>"><?php echo htmlspecialchars($t['nom_saison']); ?></h3>
                            <div style="font-size:0.9rem;<?php echo $styleSub; ?>">
                                Du <?php echo $formatter->format($d1); ?> au <?php echo $formatter->format($d2); ?>
                            </div>
                        </div>
                        <div class="price-block">
                            <div class="price" style="<?php echo $isHigh ? 'color:var(--gold-text);' : ''; ?>">
                                <?php echo number_format($t['prix_semaine'], 0, ',', ' '); ?> €
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>


    <!-- ══════════════════════════════════════════
         SECTION RÉSERVATION — Calendrier + Formulaire
         ══════════════════════════════════════════ -->
    <section id="reservation">
        <div class="section-header">
            <span class="subtitle">Disponibilités</span>
            <h2>Réservez Votre Séjour</h2>
        </div>

        <div class="booking-layout">
            <!-- Calendrier -->
            <div class="calendar-side">
                <div class="calendar-header">
                    <button class="cal-nav" onclick="changeMonth(-1)">❮</button>
                    <span class="month-label" id="calendarTitle">Juillet 2026</span>
                    <button class="cal-nav" onclick="changeMonth(1)">❯</button>
                </div>
                <div class="days-grid">
                    <div class="day-label">L</div>
                    <div class="day-label">M</div>
                    <div class="day-label">M</div>
                    <div class="day-label">J</div>
                    <div class="day-label">V</div>
                    <div class="day-label">S</div>
                    <div class="day-label">D</div>
                </div>
                <div class="days-grid" id="calendarDays"></div>
                <div style="margin-top:20px;display:flex;gap:15px;font-size:0.8rem;justify-content:center;">
                    <div style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;border:1px solid #ddd;display:inline-block;"></span> Libre</div>
                    <div style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:var(--gold-gradient);display:inline-block;"></span> Sélection</div>
                    <div style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:50%;background:#ddd;display:inline-block;"></span> Occupé</div>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="form-side">
                <h3 class="form-title">Votre Demande</h3>

                <div class="summary-box" id="bookingSummary">
                    <p>Veuillez sélectionner vos dates dans le calendrier (min 3 nuits).</p>
                </div>

                <form method="POST" action="index.php#reservation">
                    <input type="hidden" name="action" value="book">
                    <input type="hidden" name="check_in"  id="input_check_in"  required>
                    <input type="hidden" name="check_out" id="input_check_out" required>

                    <input type="text"  name="customer_name"    class="lux-input" placeholder="Nom Complet"      required>
                    <input type="email" name="customer_email"   class="lux-input" placeholder="Adresse E-mail"   required>
                    <input type="tel"   name="customer_phone"   class="lux-input" placeholder="Téléphone"        required>
                    <textarea name="customer_message" class="lux-input" rows="3" placeholder="Une demande particulière ? (Lit bébé, arrivée tardive, question...)"></textarea>

                    <label class="option-check">
                        <input type="checkbox" name="cleaning_fee" value="1">
                        Option Ménage fin de séjour (+220€)
                    </label>

                    <button type="submit" class="btn-gold" style="width:100%;border-radius:4px;">Envoyer la demande</button>
                    <p style="font-size:0.75rem;color:#888;margin-top:15px;text-align:center;">
                        Un acompte de 30% sera demandé après validation.
                    </p>
                </form>
            </div>
        </div>
    </section>


    <!-- ══════════════════════════════════════════
         SECTION AVIS CLIENTS
         ══════════════════════════════════════════ -->
    <section id="temoignages" class="reviews-section" style="background-color:#fafafa;padding:70px 0;">
        <style>
            .ti-widget { max-width:1200px; margin:0 auto; padding:0 20px; font-family:'Montserrat',sans-serif; color:#333; }
            .ti-header { display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eaeaea; padding-bottom:25px; margin-bottom:35px; flex-wrap:wrap; gap:20px; }
            .ti-header-left h2 { font-family:'Playfair Display',serif; font-size:2.5rem; font-weight:400; color:#222; margin:0 0 8px 0; }
            .ti-location { color:#777; font-size:0.95rem; display:flex; align-items:center; gap:6px; }
            .ti-header-right { background:white; border:1px solid #eaeaea; border-radius:12px; padding:15px 30px; display:flex; align-items:center; gap:25px; box-shadow:0 4px 15px rgba(0,0,0,0.03); }
            .ti-rating-score { font-size:2.2rem; font-family:'Playfair Display',serif; color:#333; text-align:center; line-height:1; margin-bottom:5px; }
            .ti-stars { color:#f5b211; font-size:1.1rem; letter-spacing:2px; }
            .ti-review-count { text-align:center; font-size:0.85rem; color:#888; border-left:1px solid #eee; padding-left:25px; }
            .ti-review-count-num { font-size:1.2rem; font-family:'Playfair Display',serif; color:#333; display:block; margin-bottom:3px; }
            .ti-controls { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:30px; flex-wrap:wrap; gap:15px; }
            .ti-subtitle h3 { font-family:'Playfair Display',serif; font-style:italic; font-size:1.6rem; font-weight:400; margin:0 0 8px 0; color:#444; }
            .ti-meta { font-size:0.75rem; color:#aaa; display:flex; align-items:center; gap:10px; }
            .ti-badge { background:#f0f0f0; padding:3px 8px; border-radius:4px; font-weight:600; letter-spacing:0.5px; color:#666; }
            .ti-actions { display:flex; gap:20px; align-items:center; }
            .ti-btn-refresh { background:none; border:none; color:#999; font-size:0.75rem; cursor:pointer; letter-spacing:1px; display:flex; align-items:center; gap:6px; text-transform:uppercase; font-weight:500; }
            .ti-nav-btns { display:flex; gap:10px; }
            .ti-nav { width:38px; height:38px; border-radius:50%; border:1px solid #ddd; background:white; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#555; font-size:1rem; transition:all 0.3s; }
            .ti-nav:hover { background:#f5f5f5; color:#222; }
            .ti-cards { display:flex; gap:20px; overflow-x:auto; padding:10px 0 30px 0; scrollbar-width:none; }
            .ti-cards::-webkit-scrollbar { display:none; }
            .ti-card { background:white; border:1px solid #eaeaea; border-radius:16px; padding:35px 30px; min-width:340px; flex:1; flex-basis:340px; position:relative; box-shadow:0 5px 20px rgba(0,0,0,0.02); }
            .ti-card-header { display:flex; align-items:center; gap:15px; margin-bottom:20px; }
            .ti-avatar { width:45px; height:45px; border-radius:50%; background:#f4f4f4; display:flex; align-items:center; justify-content:center; font-size:1.1rem; color:#888; font-family:'Playfair Display',serif; }
            .ti-author-name { font-weight:500; font-size:1.05rem; margin-bottom:2px; color:#222; }
            .ti-author-date { font-size:0.7rem; color:#aaa; text-transform:uppercase; letter-spacing:0.5px; }
            .ti-quote-icon { position:absolute; top:25px; right:30px; font-family:'Playfair Display',serif; font-size:5rem; color:#f7f7f7; line-height:0.8; user-select:none; }
            .ti-card-stars { color:#f5b211; font-size:1.2rem; margin-bottom:15px; letter-spacing:1px; }
            .ti-card-text { font-size:0.95rem; line-height:1.7; color:#666; font-style:italic; }
            @media (max-width:768px) { .ti-header { flex-direction:column; align-items:flex-start; } .ti-header-right { width:100%; justify-content:space-around; } }
        </style>

        <div class="ti-widget">
            <div class="ti-header">
                <div class="ti-header-left">
                    <h2>Avis Clients</h2>
                    <div class="ti-location">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        Gîte Bellevue d'Aveyron, Sainte-Eulalie-d'Olt
                    </div>
                </div>
                <div class="ti-header-right">
                    <div>
                        <div class="ti-rating-score">5</div>
                        <div class="ti-stars">★★★★★</div>
                    </div>
                    <div class="ti-review-count">
                        <span class="ti-review-count-num">102</span>
                        avis Google
                    </div>
                </div>
            </div>

            <div class="ti-controls">
                <div class="ti-subtitle">
                    <h3>Derniers témoignages</h3>
                    <div class="ti-meta">
                        Dernière mise à jour : <?php echo date('d/m/Y H:i'); ?>
                        <span class="ti-badge">SOURCE: CACHE</span>
                    </div>
                </div>
                <div class="ti-actions">
                    <button class="ti-btn-refresh" onclick="window.location.reload();">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2v6h-6"></path><path d="M21 13a9 9 0 1 1-3-7.7L21 8"></path></svg>
                        ACTUALISER
                    </button>
                    <div class="ti-nav-btns">
                        <button class="ti-nav" onclick="document.querySelector('.ti-cards').scrollBy({left:-360,behavior:'smooth'})">❮</button>
                        <button class="ti-nav" onclick="document.querySelector('.ti-cards').scrollBy({left:360,behavior:'smooth'})">❯</button>
                    </div>
                </div>
            </div>

            <div class="ti-cards">
                <!-- Review 1 -->
                <div class="ti-card">
                    <div class="ti-quote-icon">"</div>
                    <div class="ti-card-header">
                        <div class="ti-avatar">A</div>
                        <div>
                            <div class="ti-author-name">Ansar A.</div>
                            <div class="ti-author-date">Il y a environ 1 an et 7 mois</div>
                        </div>
                    </div>
                    <div class="ti-card-stars">★★★★★</div>
                    <div class="ti-card-text">"... et Daniel ne me pardonneraient jamais - cela devrait être partagé !!!"</div>
                </div>

                <!-- Review 2 -->
                <div class="ti-card">
                    <div class="ti-quote-icon">"</div>
                    <div class="ti-card-header">
                        <div class="ti-avatar">R</div>
                        <div>
                            <div class="ti-author-name">Rob B.</div>
                            <div class="ti-author-date">Il y a environ 7 ans et 6 mois</div>
                        </div>
                    </div>
                    <div class="ti-card-stars">★★★★★</div>
                    <div class="ti-card-text">"L'une des plus belles vues de France. Rien ne peut vous préparer aux plus belles vues, les photos ne leur rendent pas justice. Il y a tout ce que vous pourriez souhaiter dans une maison de vacances et tout est de la plus haute qualité. Machine à glaçons, machine à café, cave à vin, four à vapeur, machine à laver, télévision par satellite, belles douches, piscine, salle de jeux, vélos et ai-je mentionné la vue..."</div>
                </div>

                <!-- Review 3 -->
                <div class="ti-card">
                    <div class="ti-quote-icon">"</div>
                    <div class="ti-card-header">
                        <div class="ti-avatar">M</div>
                        <div>
                            <div class="ti-author-name">Marielle V.</div>
                            <div class="ti-author-date">Il y a environ 3 ans</div>
                        </div>
                    </div>
                    <div class="ti-card-stars">★★★★★</div>
                    <div class="ti-card-text">"Simplement un paradis... Ayant loué Bellevue d'Aveyron pour les vacances... cuisine ouverte où vous n'avez besoin de rien... les chambres sont magnifiques avec une clarté et des nuits douces merveilleuses..."</div>
                </div>
            </div>

            <div style="text-align:center;margin-top:20px;">
                <a href="https://g.page/r/CVWZLGkfDaptEAE/review" target="_blank" class="btn-gold-outline">
                    Lire tous les avis sur Google
                </a>
            </div>
        </div>
    </section>


    <!-- ══════════════════════════════════════════
         FOOTER
         ══════════════════════════════════════════ -->
    <footer id="footer-luxe">
        <div class="footer-container">
            <div class="footer-col brand-col">
                <div class="footer-logo">Bellevue d'Aveyron<span>Villa 5 Étoiles</span></div>
                <p class="footer-desc">
                    Un sanctuaire de paix au cœur de l'Aveyron.
                    L'alliance parfaite entre l'authenticité et le luxe contemporain.
                </p>
                <div class="footer-socials">
                    <a href="https://www.instagram.com/gitebellevuedaveyron/" target="_blank" class="social-link">Instagram</a>
                    <a href="https://www.facebook.com/gitebellevuedaveyron" target="_blank" class="social-link">Facebook</a>
                </div>
            </div>

            <div class="footer-col links-col">
                <h3>Explorer</h3>
                <ul>
                    <li><a href="#experience">La Villa & L'Histoire</a></li>
                    <li><a href="#services">Les Services 5★</a></li>
                    <li><a href="#tarifs">Nos Tarifs</a></li>
                    <li><a href="#accessibilite">Accessibilité (PMR)</a></li>
                    <li><a href="#temoignages">Livre d'Or</a></li>
                    <li><a href="decouvrir.php">Visiter l'Aveyron</a></li>
                </ul>
            </div>

            <div class="footer-col contact-col">
                <h3>Nous Trouver</h3>
                <ul class="contact-list">
                    <li>
                        <span class="icon">📍</span>
                        <span>12130 Sainte-Eulalie-d'Olt<br><em style="font-size:0.8em;opacity:0.7;">Plus Beaux Villages de France</em></span>
                    </li>
                    <li>
                        <span class="icon">📞</span>
                        <a href="tel:+33680907107">06 80 90 71 07</a>
                    </li>
                </ul>
                <a href="#reservation" class="btn-footer">Réserver maintenant</a>
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
            <div class="signature">Excellence &amp; Tradition</div>
        </div>
    </footer>


    <!-- ══ MODALE SUCCÈS RÉSERVATION ══ -->
    <div class="modal-overlay <?php echo $bookingSuccess ? 'active' : ''; ?>" id="successModal">
        <div class="modal-card">
            <div class="success-icon">✓</div>
            <h3 style="font-family:'Cinzel',serif;color:var(--navy-deep);margin-bottom:15px;">Demande Reçue</h3>
            <p style="color:#666;margin-bottom:20px;">
                Merci de votre confiance.<br>
                Votre demande pour <?php echo isset($bookingData['nuits']) ? $bookingData['nuits'] : ''; ?> nuits a bien été enregistrée.
                <br><br>
                <strong>Montant estimé : <?php echo isset($bookingData['total']) ? number_format($bookingData['total'], 2) : ''; ?> €</strong><br>
                Nos hôtes vous répondront sous 24h.
            </p>
            <button onclick="document.getElementById('successModal').classList.remove('active')" class="btn-gold">Fermer</button>
        </div>
    </div>

    <!-- ══ MODALE CONCIERGERIE / CONTACT ══ -->
    <div id="contactModal" class="modal-overlay">
        <div class="modal-card contact-card">
            <span class="close-modal" onclick="closeContactModal()">×</span>
            <div class="modal-header">
                <span class="subtitle">À votre écoute</span>
                <h3>Conciergerie</h3>
            </div>
            <div class="concierge-grid">
                <div class="concierge-item">
                    <div class="icon-gold">📞</div>
                    <h4>Par Téléphone</h4>
                    <p>Une question urgente ou besoin de précisions ?</p>
                    <a href="tel:+33680907107" class="btn-gold-outline">06 80 90 71 07</a>
                </div>
                <div class="concierge-item">
                    <div class="icon-gold">✉️</div>
                    <h4>Par Email</h4>
                    <p>Pour des demandes spécifiques ou devis.</p>
                    <a href="mailto:accueil@bellevuedaveyron.com" class="btn-gold-outline">Nous écrire</a>
                </div>
            </div>
            <div class="concierge-footer">
                <p>Nous vous répondons sous 24h, 7j/7.</p>
            </div>
        </div>
    </div>

    <?php if ($errorMsg): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof showNotification === 'function') {
                    showNotification("<?php echo addslashes($errorMsg); ?>", "error");
                } else {
                    alert("Erreur : <?php echo addslashes($errorMsg); ?>");
                }
            });
        </script>
    <?php endif; ?>

    <!-- Script principal (chargé en fin de body) -->
    <script>
        /* ── Variables PHP → JS ── */
        const bookedDates = <?php echo $json_booked_dates ?: '[]'; ?>;
    </script>
    <script src="js/script.js"></script>

    <script>
        /* ─────────────────────────────────────────────────────
           INTRO & VIDÉO : chargement image 34.jpg → puis vidéo
        ───────────────────────────────────────────────────── */
        (function () {
            'use strict';

            /* ── Ouverture / fermeture modale contact ── */
            window.openContactModal  = function () { document.getElementById('contactModal').classList.add('active'); };
            window.closeContactModal = function () { document.getElementById('contactModal').classList.remove('active'); };

            /* ── Clic hors modale pour fermer ── */
            document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
                overlay.addEventListener('click', function (e) {
                    if (e.target === overlay) overlay.classList.remove('active');
                });
            });

            /* ── Initialisation vidéo après intro ──
               1) L'image 34.jpg est déjà affichée via #heroPoster (CSS inline)
               2) On charge la vidéo une fois l'intro terminée
               3) Quand la vidéo peut se lire → on la fait apparaître et on masque le poster
            ── */
            function initVideo() {
                var video  = document.getElementById('heroVideo');
                var poster = document.getElementById('heroPoster');
                if (!video) return;

                // Charger la source vidéo
                video.src = 'Video/bellevuedaveyron.mp4';
                video.load();

                video.addEventListener('canplaythrough', function () {
                    video.play()
                        .then(function () {
                            video.style.opacity = '1';          // Vidéo apparaît
                            if (poster) poster.style.opacity = '0'; // Poster disparaît
                        })
                        .catch(function () {
                            // Autoplay bloqué → le poster reste visible
                        });
                });
            }

            /* ── Gestion intro overlay ── */
            window.addEventListener('load', function () {
                var overlay       = document.getElementById('intro-overlay');
                var body          = document.body;
                var successModal  = document.getElementById('successModal');
                var isSuccess     = successModal && successModal.classList.contains('active');

                if (overlay) {
                    if (isSuccess) {
                        overlay.style.display = 'none';
                        body.classList.remove('loading');
                        if (history.replaceState) history.replaceState(null, null, window.location.pathname);
                        initVideo();
                    } else {
                        overlay.classList.add('animate');
                        setTimeout(function () {
                            overlay.classList.add('hidden');
                            body.classList.remove('loading');
                            initVideo();
                        }, 6800);
                    }
                } else {
                    initVideo();
                }
            });

            /* ── Slideshows (famille + PMR) ── */
            function initSlideshow(selector, interval) {
                var slides = document.querySelectorAll(selector);
                if (slides.length <= 1) return;
                var idx = 0;
                setInterval(function () {
                    slides[idx].classList.remove('active');
                    idx = (idx + 1) % slides.length;
                    slides[idx].classList.add('active');
                }, interval);
            }

            document.addEventListener('DOMContentLoaded', function () {
                initSlideshow('.pmr-slide', 4500);
                initSlideshow('.family-slide', 5000);
            });

        }());
    </script>

</body>
</html>
