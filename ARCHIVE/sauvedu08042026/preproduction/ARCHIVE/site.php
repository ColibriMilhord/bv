<?php
// --- 1. LOGIQUE METIER ---
session_start();
// Vérification de l'existence du fichier de config
if (file_exists('config/db.php')) {
    require_once 'config/db.php';
} else {
    // Fallback connexion si config/db.php n'est pas encore créé (pour le test)
    $host = '127.0.0.1';
    $db = 'u424962071_rbellevue';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    } catch (\PDOException $e) {
        $pdo = null;
    }
}

$bookingSuccess = false;
$bookingData = [];
$errorMsg = "";

// Récupération des dates réservées
$booked_dates = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT jour FROM calendrier_dispo WHERE statut != 'libre'");
        $booked_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) { /* Table pas encore créée */
    }
}
$json_booked_dates = json_encode($booked_dates);

// TRAITEMENT RÉSERVATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    try {
        if (!$pdo)
            throw new Exception("Erreur de connexion à la base de données.");

        $settings = $pdo->query("SELECT * FROM gite_settings WHERE id = 1")->fetch();

        $client_nom = htmlspecialchars($_POST['customer_name']);
        $client_email = htmlspecialchars($_POST['customer_email']);
        $client_tel = htmlspecialchars($_POST['customer_phone']);
        $date_debut = $_POST['check_in'];
        $date_fin = $_POST['check_out'];
        $option_menage = isset($_POST['cleaning_fee']) ? 1 : 0;

        $d1 = new DateTime($date_debut);
        $d2 = new DateTime($date_fin);
        $nuits = $d1->diff($d2)->days;

        if ($nuits < 3)
            throw new Exception("Le séjour doit être de 3 nuits minimum.");

        // Calcul Prix
        $stmt = $pdo->prepare("SELECT * FROM tarifs_saison WHERE date_debut <= ? AND date_fin >= ?");
        $stmt->execute([$date_debut, $date_fin]);
        $saison = $stmt->fetch();

        $prix_total = 0;
        if ($saison && $nuits >= 7) {
            $prix_total = ($saison['prix_semaine'] / 7) * $nuits;
        } else {
            $prix_total = 380 * $nuits;
        }
        if ($option_menage)
            $prix_total += ($settings['frais_menage'] ?? 220);
        $acompte_montant = $prix_total * (($settings['acompte_pourcentage'] ?? 30) / 100);

        // Insertion
        $sql = "INSERT INTO reservations (client_nom, client_email, client_tel, date_debut, date_fin, prix_total, acompte_montant, option_menage, statut, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'attente', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$client_nom, $client_email, $client_tel, $date_debut, $date_fin, $prix_total, $acompte_montant, $option_menage]);

        $bookingSuccess = true;
        $bookingData = ['nuits' => $nuits, 'total' => $prix_total];

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

    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital@0;1&display=swap"
        rel="stylesheet">

    <style>
        /* --- CSS DU DESIGN LUXE --- */
        :root {
            --gold-gradient: linear-gradient(135deg, #bf953f 0%, #fcf6ba 40%, #b38728 70%, #fbf5b7 100%);
            --gold-text: #c5a059;
            --navy-deep: #050914;
            --navy-light: #121b33;
            --white-soft: #f9f9f9;
        }

        html {
            scroll-behavior: smooth;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--white-soft);
            color: #333;
            overflow-x: hidden;
            line-height: 1.8;
        }

        body.loading {
            overflow: hidden;
            height: 100vh;
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Cinzel', serif;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--navy-deep);
        }

        .subtitle {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            color: var(--gold-text);
            font-size: 1.5rem;
            margin-bottom: 10px;
            display: block;
        }

        section {
            padding: 100px 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }

        /* --- INTRO OVERLAY --- */
        #intro-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--navy-deep);
            background-image: radial-gradient(circle at center, #0a1128 0%, #050914 100%);
            z-index: 9999;
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

        .intro-line {
            height: 2px;
            width: 100%;
            max-width: 400px;
            background: var(--gold-gradient);
            margin: 0 auto;
            transform: scaleX(0);
            opacity: 0;
        }

        .intro-line-top {
            margin-bottom: 40px;
            transform-origin: center left;
        }

        .intro-line-bottom {
            margin-top: 40px;
            transform-origin: center right;
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
            text-align: center;
            width: 100%;
        }

        .animate .intro-line-top {
            animation: drawLine 1.5s cubic-bezier(0.23, 1, 0.32, 1) 0.5s forwards;
        }

        .animate .intro-line-bottom {
            animation: drawLine 1.5s cubic-bezier(0.23, 1, 0.32, 1) 0.7s forwards;
        }

        .animate .intro-main-title {
            animation: revealTitle 2s cubic-bezier(0.23, 1, 0.32, 1) 1.2s forwards;
        }

        .animate .intro-star-svg {
            animation: popStar 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) 2.5s forwards;
        }

        .animate .intro-subtitle {
            animation: fadeSubtitle 2s ease-out 3.8s forwards;
        }

        .animate .intro-shimmer {
            animation: shimmerMove 2.5s ease-in-out 4.5s forwards;
        }

        @keyframes drawLine {
            0% {
                transform: scaleX(0);
                opacity: 0;
            }

            100% {
                transform: scaleX(1);
                opacity: 1;
            }
        }

        @keyframes revealTitle {
            0% {
                transform: translateY(40px);
                opacity: 0;
                filter: blur(10px);
            }

            100% {
                transform: translateY(0);
                opacity: 1;
                filter: blur(0px);
            }
        }

        @keyframes popStar {
            0% {
                transform: scale(0.5) rotate(-30deg);
                opacity: 0;
            }

            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        @keyframes fadeSubtitle {
            0% {
                opacity: 0;
                letter-spacing: 8px;
            }

            100% {
                opacity: 1;
                letter-spacing: 4px;
            }
        }

        @keyframes shimmerMove {
            0% {
                transform: translateX(-100%) skewX(-20deg);
            }

            100% {
                transform: translateX(100%) skewX(-20deg);
            }
        }

        /* --- HEADER --- */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: 0.4s ease;
            padding: 20px 0;
            background: rgba(5, 9, 20, 0.2);
            opacity: 0;
            animation: fadeInHeader 1s ease-out 6.5s forwards;
        }

        @keyframes fadeInHeader {
            to {
                opacity: 1;
            }
        }

        header.scrolled {
            background: rgba(5, 9, 20, 0.98);
            padding: 10px 0;
            border-bottom: 1px solid rgba(197, 160, 89, 0.3);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
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

        .nav-links {
            display: flex;
            gap: 3rem;
            list-style: none;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 1px;
            background: var(--gold-text);
            transition: 0.3s;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: var(--gold-text);
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            gap: 6px;
            cursor: pointer;
            z-index: 1001;
        }

        .bar {
            width: 30px;
            height: 2px;
            background-color: var(--gold-text);
            transition: 0.3s;
        }

        /* --- HERO --- */
        .hero {
            position: relative;
            height: 100vh;
            width: 100%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .video-background {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            min-width: 100%;
            min-height: 100%;
            width: 177.777vh;
            height: 56.25vw;
            z-index: 0;
            pointer-events: none;
        }

        .video-background iframe {
            width: 100%;
            height: 100%;
            transform: scale(1.3);
        }

        .mobile-fallback {
            display: none;
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1600') center/cover no-repeat;
            z-index: 0;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(5, 9, 20, 0.4), rgba(5, 9, 20, 0.7));
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            padding: 0 20px;
            opacity: 0;
            animation: fadeInHero 2s ease-out 7s forwards;
        }

        @keyframes fadeInHero {
            to {
                opacity: 1;
            }
        }

        .hero-badge {
            display: inline-block;
            border: 1px solid var(--gold-text);
            padding: 10px 20px;
            margin-bottom: 30px;
            font-family: 'Cinzel', serif;
            letter-spacing: 3px;
            color: var(--gold-text);
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
        }

        .hero h1 {
            font-size: 4rem;
            color: white;
            margin-bottom: 20px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .btn-gold {
            padding: 15px 40px;
            background: var(--gold-gradient);
            color: var(--navy-deep);
            text-decoration: none;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            transition: 0.3s;
            display: inline-block;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn-gold:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(197, 160, 89, 0.4);
        }

        /* --- SECTION EXPERIENCE & MONTAGE PHOTO --- */
        .experience-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .stats-row {
            display: flex;
            gap: 40px;
            margin-top: 40px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 30px;
        }

        .stat-item h4 {
            font-size: 2.5rem;
            color: var(--gold-text);
            margin-bottom: 5px;
        }

        .stat-item span {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
        }

        /* Nouveau CSS pour l'image unique (Montage) */
        .villa-visual {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(197, 160, 89, 0.2);
        }

        .villa-visual img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.8s ease;
        }

        .villa-visual:hover img {
            transform: scale(1.02);
        }

        .amenities-section {
            background: var(--navy-light);
            color: white;
        }

        .amenities-section h2 {
            color: white;
        }

        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .amenity-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 40px 30px;
            transition: 0.4s;
            text-align: center;
        }

        .amenity-box:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--gold-text);
            transform: translateY(-10px);
        }

        .amenity-icon {
            font-size: 2.5rem;
            color: var(--gold-text);
            margin-bottom: 20px;
        }

        .pricing-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .pricing-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background: white;
            transition: 0.3s;
        }

        .pricing-row:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border-color: transparent;
        }

        .pricing-row.featured {
            background: var(--navy-deep);
            color: white;
            border: none;
            margin: 20px 0;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .pricing-row.featured h3,
        .pricing-row.featured .price {
            color: var(--gold-text);
        }

        .price {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--navy-deep);
        }

        .unit {
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        /* --- SECTION RÉSERVATION --- */
        #reservation {
            background: #f4f4f4;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }

        .booking-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
        }

        .calendar-side {
            padding: 40px;
            background: white;
            border-right: 1px solid #eee;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .month-label {
            font-family: 'Cinzel', serif;
            font-size: 1.2rem;
            color: var(--navy-deep);
            font-weight: bold;
        }

        .cal-nav {
            background: none;
            border: 1px solid #eee;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            transition: 0.3s;
        }

        .cal-nav:hover {
            background: var(--navy-deep);
            color: white;
        }

        .days-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            text-align: center;
            font-size: 0.9rem;
        }

        .day-label {
            color: #999;
            font-size: 0.75rem;
            margin-bottom: 10px;
        }

        .day-cell {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            transition: 0.2s;
            font-weight: 500;
        }

        .day-cell:hover:not(.disabled) {
            background: #f0f0f0;
        }

        .day-cell.disabled {
            color: #ccc;
            cursor: not-allowed;
            text-decoration: line-through;
        }

        .day-cell.selected {
            background: var(--gold-gradient);
            color: var(--navy-deep);
            font-weight: bold;
        }

        .day-cell.range {
            background: rgba(197, 160, 89, 0.2);
            border-radius: 0;
        }

        .day-cell.range-start {
            border-radius: 50% 0 0 50%;
        }

        .day-cell.range-end {
            border-radius: 0 50% 50% 0;
        }

        .form-side {
            padding: 40px;
            background: #fafafa;
        }

        .form-title {
            font-family: 'Cinzel', serif;
            color: var(--navy-deep);
            margin-bottom: 20px;
            font-size: 1.4rem;
        }

        .summary-box {
            background: white;
            padding: 20px;
            border-left: 3px solid var(--gold-text);
            margin-bottom: 30px;
            font-size: 0.9rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        }

        .lux-input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
        }

        .lux-input:focus {
            border-color: var(--gold-text);
            outline: none;
        }

        .option-check {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            color: #555;
        }

        .option-check input {
            width: 18px;
            height: 18px;
            accent-color: var(--gold-text);
        }

        .contact-section {
            background: url('https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1600') center/cover fixed;
            position: relative;
            color: white;
        }

        .contact-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(5, 9, 20, 0.9);
        }

        .contact-wrapper {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
        }

        .contact-details h2 {
            color: white;
            margin-bottom: 30px;
        }

        .contact-item {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .contact-item span {
            color: var(--gold-text);
            font-size: 1.5rem;
        }

        footer {
            background: black;
            color: rgba(255, 255, 255, 0.5);
            padding: 50px 0;
            text-align: center;
            font-size: 0.8rem;
            border-top: 1px solid rgba(197, 160, 89, 0.2);
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: 0.3s;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-card {
            background: white;
            padding: 50px;
            border-radius: 8px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            position: relative;
            border-top: 5px solid var(--gold-text);
        }

        .success-icon {
            width: 60px;
            height: 60px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 20px;
        }

        @media (max-width: 900px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .experience-grid,
            .contact-wrapper,
            .booking-layout {
                grid-template-columns: 1fr;
            }

            .calendar-side,
            .form-side {
                padding: 20px;
            }

            .video-background {
                display: none;
            }

            .mobile-fallback {
                display: block;
            }

            .menu-toggle {
                display: flex;
            }

            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 70%;
                height: 100vh;
                background: var(--navy-deep);
                flex-direction: column;
                justify-content: center;
                transition: 0.4s;
                box-shadow: -10px 0 30px rgba(0, 0, 0, 0.5);
                padding: 50px;
            }

            .nav-links.active {
                right: 0;
            }
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
                <svg class="intro-star-svg star-1" viewBox="0 0 51 48">
                    <path
                        d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z" />
                </svg>
                <svg class="intro-star-svg star-2" viewBox="0 0 51 48">
                    <path
                        d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z" />
                </svg>
                <svg class="intro-star-svg star-3" viewBox="0 0 51 48">
                    <path
                        d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z" />
                </svg>
                <svg class="intro-star-svg star-4" viewBox="0 0 51 48">
                    <path
                        d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z" />
                </svg>
                <svg class="intro-star-svg star-5" viewBox="0 0 51 48">
                    <path
                        d="M25.5 0L31.2414 18.2336H50.2414L35.0000 29.5328L40.7414 47.7664L25.5 36.4672L10.2586 47.7664L16.0000 29.5328L0.758621 18.2336H19.7586L25.5 0Z" />
                </svg>
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
            <div class="logo">Bellevue d'Aveyron<span>VILLA 5 ÉTOILES</span></div>
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
        <div class="video-background"><iframe
                src="https://www.youtube.com/embed/19x6z3GPkL4?autoplay=1&mute=1&controls=0&loop=1&playlist=19x6z3GPkL4&playsinline=1&showinfo=0&rel=0&iv_load_policy=3&disablekb=1"
                frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>
        <div class="mobile-fallback"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-badge">★ Classé 5 Étoiles ★</div>
            <h1>L'Art de Vivre<br>en Aveyron</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.2rem; margin-bottom: 30px;">Une villa d'exception avec
                piscine chauffée et vue panoramique sur la vallée du Lot.</p>
            <a href="#reservation" class="btn-gold">Planifier votre séjour</a>
        </div>
    </section>

    <section id="experience">
        <div class="section-header"><span class="subtitle">Un lieu unique</span>
            <h2>Entre Luxe & Nature</h2>
        </div>
        <div class="experience-grid">
            <div class="exp-text-block">
                <p style="margin-bottom: 20px; color: #555;">Niché sur les hauteurs de Sainte-Eulalie-d'Olt, l'un des
                    plus beaux villages de France, Bellevue d'Aveyron n'est pas simplement une villa, c'est une retraite
                    exclusive.</p>
                <div class="stats-row">
                    <div class="stat-item">
                        <h4>200</h4><span>Mètres Carrés</span>
                    </div>
                    <div class="stat-item">
                        <h4>10</h4><span>Invités</span>
                    </div>
                    <div class="stat-item">
                        <h4>5</h4><span>Chambres</span>
                    </div>
                </div>
            </div>
            <div class="villa-visual">
                <img src="images/accueil.jpg" alt="Montage Panoramique Villa Bellevue">
            </div>
        </div>
    </section>

    <section id="services" class="amenities-section">
        <div class="section-header"><span class="subtitle">Tout inclus</span>
            <h2>Prestations d'Excellence</h2>
        </div>
        <div class="amenities-grid">
            <div class="amenity-box">
                <div class="amenity-icon">✦</div>
                <h3>Piscine Chauffée</h3>
                <p style="color:rgba(255,255,255,0.6)">Bassin privé 4x8m, sécurisé et chauffé.</p>
            </div>
            <div class="amenity-box">
                <div class="amenity-icon">✦</div>
                <h3>Vue Panoramique 360°</h3>
                <p style="color:rgba(255,255,255,0.6)">Un spectacle quotidien sur la vallée du Lot.</p>
            </div>
            <div class="amenity-box">
                <div class="amenity-icon">✦</div>
                <h3>Borne Électrique</h3>
                <p style="color:rgba(255,255,255,0.6)">Chargeur 18 kVA inclus.</p>
            </div>
            <div class="amenity-box">
                <div class="amenity-icon">✦</div>
                <h3>Divertissement</h3>
                <p style="color:rgba(255,255,255,0.6)">Fibre optique, vélos, baby-foot.</p>
            </div>
        </div>
    </section>

    <section id="tarifs">
        <div class="section-header"><span class="subtitle">Saison 2026</span>
            <h2>Tarifs Hebdomadaires</h2>
        </div>
        <div class="pricing-container">
            <div class="pricing-row">
                <div class="season-info">
                    <h3>Basse Saison</h3>
                    <div style="font-size:0.9rem; color:#777;">Avril, Octobre, Novembre</div>
                </div>
                <div class="price-block">
                    <div class="price">1 590 €</div>
                </div>
            </div>
            <div class="pricing-row">
                <div class="season-info">
                    <h3>Moyenne Saison</h3>
                    <div style="font-size:0.9rem; color:#777;">Mai, Juin, Septembre</div>
                </div>
                <div class="price-block">
                    <div class="price">1 790 €</div>
                </div>
            </div>
            <div class="pricing-row featured">
                <div class="season-info">
                    <h3>Haute Saison</h3>
                    <div style="font-size:0.9rem; color:rgba(255,255,255,0.7);">Juillet & Août</div>
                </div>
                <div class="price-block">
                    <div class="price">4 600 €</div>
                </div>
            </div>
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
                    <div class="day-label">L</div>
                    <div class="day-label">M</div>
                    <div class="day-label">M</div>
                    <div class="day-label">J</div>
                    <div class="day-label">V</div>
                    <div class="day-label">S</div>
                    <div class="day-label">D</div>
                </div>
                <div class="days-grid" id="calendarDays">
                </div>
                <div style="margin-top:20px; display:flex; gap:15px; font-size:0.8rem; justify-content:center;">
                    <div style="display:flex; align-items:center; gap:5px;"><span
                            style="width:10px; height:10px; border-radius:50%; border:1px solid #ddd;"></span> Libre
                    </div>
                    <div style="display:flex; align-items:center; gap:5px;"><span
                            style="width:10px; height:10px; border-radius:50%; background:var(--gold-gradient);"></span>
                        Sélection</div>
                    <div style="display:flex; align-items:center; gap:5px;"><span
                            style="width:10px; height:10px; border-radius:50%; background:#ddd; text-decoration:line-through;"></span>
                        Occupé</div>
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

                    <button type="submit" class="btn-gold" style="width:100%; border-radius:4px;">Envoyer la
                        demande</button>
                    <p style="font-size:0.75rem; color:#888; margin-top:15px; text-align:center;">
                        Un acompte de 30% sera demandé après validation.
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
                <div class="contact-item"><span>📞</span>
                    <div>06 80 90 71 07</div>
                </div>
                <div class="contact-item"><span>📧</span>
                    <div>accueil@bellevuedaveyron.com</div>
                </div>
                <div class="contact-item"><span>📍</span>
                    <div>12130 Sainte-Eulalie-d'Olt</div>
                </div>
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
                Votre demande pour <?php echo isset($bookingData['nuits']) ? $bookingData['nuits'] : ''; ?> nuits a bien
                été enregistrée.
                <br><br>
                <strong>Montant estimé :
                    <?php echo isset($bookingData['total']) ? number_format($bookingData['total'], 2) : ''; ?>
                    €</strong><br>
                Nos hôtes vous répondront sous 24h.
            </p>
            <button onclick="document.getElementById('successModal').classList.remove('active')"
                class="btn-gold">Fermer</button>
        </div>
    </div>

    <?php if ($errorMsg): ?>
        <script>alert("Erreur : <?php echo addslashes($errorMsg); ?>");</script>
    <?php endif; ?>

    <script>
        // Intro
        window.addEventListener('load', () => {
            const overlay = document.getElementById('intro-overlay');
            if (overlay) {
                overlay.classList.add('animate');
                setTimeout(() => { overlay.classList.add('hidden'); document.body.classList.remove('loading'); }, 6800);
            }
        });

        // Toggle Menu
        function toggleMenu() { document.getElementById('navLinks').classList.toggle('active'); }

        // CALENDRIER JS
        const bookedDates = <?php echo $json_booked_dates ?: '[]'; ?>;
        let currentDate = new Date();
        let selectedStart = null;
        let selectedEnd = null;

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const monthNames = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
            document.getElementById('calendarTitle').innerText = `${monthNames[month]} ${year}`;

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            let startDayIndex = firstDay.getDay() - 1;
            if (startDayIndex === -1) startDayIndex = 6;

            const grid = document.getElementById('calendarDays');
            grid.innerHTML = '';

            for (let i = 0; i < startDayIndex; i++) grid.innerHTML += `<div></div>`;

            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const el = document.createElement('div');
                el.className = 'day-cell';
                el.innerText = day;

                const isBooked = bookedDates.includes(dateStr);
                const isPast = new Date(dateStr) < new Date().setHours(0, 0, 0, 0);

                if (isBooked || isPast) {
                    el.classList.add('disabled');
                } else {
                    el.onclick = () => selectDate(dateStr);
                }

                if (selectedStart === dateStr) { el.classList.add('selected', 'range-start'); }
                if (selectedEnd === dateStr) { el.classList.add('selected', 'range-end'); }
                if (selectedStart && selectedEnd && dateStr > selectedStart && dateStr < selectedEnd) {
                    el.classList.add('range');
                }

                grid.appendChild(el);
            }
        }

        function selectDate(dateStr) {
            if (!selectedStart || (selectedStart && selectedEnd)) {
                selectedStart = dateStr;
                selectedEnd = null;
            } else if (dateStr < selectedStart) {
                selectedStart = dateStr;
            } else {
                if (checkAvailability(selectedStart, dateStr)) {
                    selectedEnd = dateStr;
                } else {
                    alert("Certaines dates sélectionnées sont déjà réservées.");
                    selectedStart = dateStr;
                }
            }
            renderCalendar();
            updateForm();
        }

        function checkAvailability(start, end) {
            let curr = new Date(start);
            let last = new Date(end);
            while (curr <= last) {
                if (bookedDates.includes(curr.toISOString().split('T')[0])) return false;
                curr.setDate(curr.getDate() + 1);
            }
            return true;
        }

        function updateForm() {
            document.getElementById('input_check_in').value = selectedStart || '';
            document.getElementById('input_check_out').value = selectedEnd || '';

            const summary = document.getElementById('bookingSummary');
            if (selectedStart && selectedEnd) {
                summary.innerHTML = `
                    <strong>Séjour sélectionné :</strong><br>
                    Arrivée : ${new Date(selectedStart).toLocaleDateString('fr-FR')}<br>
                    Départ : ${new Date(selectedEnd).toLocaleDateString('fr-FR')}
                `;
            } else if (selectedStart) {
                summary.innerHTML = `Arrivée : ${new Date(selectedStart).toLocaleDateString('fr-FR')}<br>Sélectionnez la date de départ...`;
            }
        }

        function changeMonth(delta) {
            currentDate.setMonth(currentDate.getMonth() + delta);
            renderCalendar();
        }

        renderCalendar();

        window.addEventListener('scroll', function () {
            const header = document.getElementById('navbar');
            if (window.scrollY > 50) { header.classList.add('scrolled'); }
            else { header.classList.remove('scrolled'); }
        });
    </script>
</body>

</html>