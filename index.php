<?php
session_start();

require_once 'config/mail_config.php';
require_once 'config/mail_smtp.php';
require_once 'config/seo.php';
require_once 'config/notifications.php';
require_once 'config/avis.php';
require_once 'config/annonces.php';
require_once 'config/stats.php';
require_once 'config/tarifs.php';
require_once 'config/antispam.php';
require_once 'config/courriels.php';

// Avis Google : note, compteur et trois derniers avis (cache 12 h, repli
// éditorial). Un incident sur ce bloc — réseau, cache en lecture seule,
// extension manquante — ne doit jamais empêcher la page de s'afficher.
$avis_google = ['note' => 5.0, 'total' => 0, 'avis' => [], 'maj' => time(), 'source' => 'secours'];
try {
    $avis_google = avis_donnees();
} catch (Throwable $e) {
    error_log('[bellevue] avis indisponibles : ' . $e->getMessage());
}
    
// Le journal d'envois « bellevue_debug_mail.log » n'existe plus : il était
// écrit à la racine du site, contenait les adresses e-mail des clients, et se
// consultait par « index.php?show_log=1 ». Les envois sont désormais consignés
// dans le journal d'erreurs de PHP, hors du dossier public. Le fichier restant
// éventuellement sur le serveur est à supprimer (voir docs/DEPLOIEMENT.md).

// ── Connexion BDD ──
if (file_exists('config/db.php')) {
    require_once 'config/db.php';
} else {
    $dsn = "mysql:host=127.0.0.1;dbname=u424962071_rbellevue;charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        $pdo = null;
    }
}

$bookingSuccess = false;
$bookingData    = [];
$errorMsg       = '';
$mailDebug      = '';

// ── Dates réservées ──
$booked_dates = [];
if ($pdo) {
    try {
        $booked_dates = $pdo->query("SELECT jour FROM calendrier_dispo WHERE statut != 'libre'")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {}
}
$json_booked_dates = json_encode(array_values(array_unique($booked_dates)));

// ── Mesure d'audience interne et bandeau d'annonce ──
// Sans cookie ni traceur tiers : l'enregistrement est silencieux et ne peut
// pas empêcher la page de s'afficher.
stats_enregistrer($pdo, '/');
$annonce = annonce_active($pdo);

// ── Tarifs ──
$tarifs_display = [];
if ($pdo) {
    try {
        $tarifs_display = $pdo->query("SELECT * FROM tarifs_saison ORDER BY prix_semaine ASC")->fetchAll();
    } catch (Exception $e) {}
}

// ── Grille tarifaire regroupée par saison ──
$tarifs_grille   = tarifs_grille($tarifs_display);
$tarif_mini      = tarifs_a_partir_de($tarifs_display);

// Périodes tarifaires transmises au calendrier : le récapitulatif annonce la
// saison et le prix à la semaine dès que la date d'arrivée est choisie, sans
// que le visiteur ait à remonter à la grille.
$prix_grille  = array_map(function ($l) { return (float) ($l['prix_semaine'] ?? 0); }, $tarifs_display);
$prix_mini    = $prix_grille ? min($prix_grille) : 0.0;
$prix_maxi    = $prix_grille ? max($prix_grille) : 0.0;
$json_saisons = json_encode(array_values(array_map(
    function ($l) use ($prix_mini, $prix_maxi) {
        $saison = tarifs_saisons()[tarifs_categorie($l, $prix_mini, $prix_maxi)] ?? null;
        return [
            'debut' => (string) ($l['date_debut'] ?? ''),
            'fin'   => (string) ($l['date_fin'] ?? ''),
            'prix'  => (int) round((float) ($l['prix_semaine'] ?? 0)),
            'nom'   => $saison ? $saison['nom'] : '',
        ];
    },
    $tarifs_display
)), JSON_UNESCAPED_UNICODE);
$tarifs_settings = [];
if ($pdo) {
    try {
        $tarifs_settings = $pdo->query("SELECT * FROM gite_settings WHERE id = 1")->fetch() ?: [];
    } catch (Throwable $e) {}
}

// ── Traitement du formulaire de réservation ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    try {
        if (!$pdo) throw new Exception("Erreur de connexion à la base de données.");

        // ── Contrôle anti-abus, avant toute écriture et tout envoi ──
        // Chaque soumission déclenche deux mails depuis la boîte du gîte, dont
        // un vers une adresse choisie par le visiteur : sans garde-fou, le
        // formulaire sert de relais à spam et la boîte finit suspendue.
        antispam_migrer($pdo);
        list($antispam_ok, $antispam_motif) = antispam_verifier($pdo, $_POST);
        antispam_journaliser($pdo, $antispam_ok, $antispam_motif);

        if (!$antispam_ok) {
            error_log('[bellevue] demande refusée — ' . $antispam_motif);
            throw new Exception(antispam_message_refus($antispam_motif));
        }

        $settings = $pdo->query("SELECT * FROM gite_settings WHERE id = 1")->fetch() ?: [];

        // Valeurs brutes pour les emails : débarrassées des retours à la ligne,
        // qui sont ce qui permet d'ajouter des destinataires dans un en-tête.
        $raw_nom       = antispam_nettoyer(trim((string) ($_POST['customer_name'] ?? '')));
        $raw_tel       = antispam_nettoyer(trim((string) ($_POST['customer_phone'] ?? '')), 40);
        $raw_note      = trim((string) ($_POST['customer_message'] ?? ''));
        // Valeurs encodées pour l'affichage HTML (page web)
        $client_nom    = htmlspecialchars($raw_nom);
        // Adresse déjà validée par antispam_verifier() : on reprend la même
        // règle, et non un simple nettoyage, qui laisse passer « a@b\r\nBcc: ».
        $client_email  = filter_var(trim((string) ($_POST['customer_email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';
        $client_tel    = htmlspecialchars($raw_tel);
        $client_note   = htmlspecialchars($raw_note);
        $date_debut    = !empty($_POST['check_in'])  ? $_POST['check_in']  : null;
        $date_fin      = !empty($_POST['check_out']) ? $_POST['check_out'] : null;
        $option_menage = isset($_POST['cleaning_fee']) ? 1 : 0;

        $nuits = $prix_total = $acompte_montant = 0;

        if ($date_debut && $date_fin) {
            $d1    = new DateTime($date_debut);
            $d2    = new DateTime($date_fin);
            $nuits = $d1->diff($d2)->days;
            if ($nuits < 3) throw new Exception("Le séjour doit être de 3 nuits minimum.");

            $stmt = $pdo->prepare("SELECT * FROM tarifs_saison WHERE date_debut <= ? AND date_fin >= ?");
            $stmt->execute([$date_debut, $date_fin]);
            $saison = $stmt->fetch();

            $prix_total      = ($saison && $nuits >= 7) ? ($saison['prix_semaine'] / 7) * $nuits : 380 * $nuits;
            if ($option_menage) $prix_total += ($settings['frais_menage'] ?? 220);
            $acompte_montant = $prix_total * (($settings['acompte_pourcentage'] ?? 30) / 100);
        }

        // Insertion BDD
        try {
            // Horodatage calculé en PHP plutôt que par NOW() : la requête reste
            // ainsi vérifiable hors MySQL, comme le reste des modules.
            $pdo->prepare("INSERT INTO reservations (client_nom, client_email, client_tel, date_debut, date_fin, prix_total, acompte_montant, option_menage, client_message, statut, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'attente', ?)")
                ->execute([$client_nom, $client_email, $client_tel, $date_debut, $date_fin, $prix_total, $acompte_montant, $option_menage, $client_note, date('Y-m-d H:i:s')]);

            // Identifiant retenu pour consigner, après l'envoi, si la demande
            // a bien prévenu quelqu'un.
            $demande_id  = (int) $pdo->lastInsertId();
            $bookingData = ['nuits' => $nuits, 'total' => $prix_total];
        } catch (Exception $e) {}

        // ── Les deux messages ──
        // Le texte et la mise en forme sortent de config/courriels.php : la
        // page ne fabrique plus de corps de message. Chaque envoi part en deux
        // versions, texte et HTML, dans un même message.
        $has_dates = ($date_debut && $date_fin && $nuits > 0);

        $demande = [
            'nom'           => $raw_nom,
            'email'         => $client_email,
            'telephone'     => $raw_tel,
            'message'       => $raw_note,
            'has_dates'     => $has_dates,
            'date_debut'    => $date_debut,
            'date_fin'      => $date_fin,
            'nuits'         => $nuits,
            'prix_total'    => $prix_total,
            'acompte'       => $acompte_montant,
            'option_menage' => $option_menage,
            'recu_le'       => date('d/m/Y à H\\hi'),
        ];

        $courriel_proprio = courriel_proprietaires($demande);
        $courriel_visiteur = courriel_client($demande);

        // ── Envoi aux propriétaires — un message par destinataire ──
        // Destinataires réglables depuis l'administration (Paramètres du Gîte).
        // Repli sur les adresses par défaut si le réglage est vide.
        // L'expéditeur reste la boîte SMTP reservation@.
        $mailSent    = false;
        $admin_list  = notifications_destinataires($settings);
        $mail_errors = [];

        foreach ($admin_list as $admin) {
            $r = send_smtp_mail(
                $admin,
                $courriel_proprio['sujet'],
                $courriel_proprio['texte'],
                $client_email,
                $courriel_proprio['html']
            );

            if ($r === true) {
                $mailSent = true;
            } else {
                // Journal du serveur, et non un fichier à la racine du site :
                // celui-ci se téléchargeait, avec les adresses des clients.
                error_log('[bellevue] envoi propriétaire en échec — ' . $r);
                $mail_errors[] = $admin . " : " . $r;
            }
        }

        // ── Accusé de réception du client ──
        // Il part vers une adresse fournie par le visiteur : c'est le seul
        // envoi détournable. On ne l'émet que si la demande a bien été reçue
        // par les propriétaires — un robot n'obtient donc rien d'un formulaire
        // dont l'envoi principal a échoué.
        if ($mailSent) {
            $ack_result = send_smtp_mail(
                $client_email,
                $courriel_visiteur['sujet'],
                $courriel_visiteur['texte'],
                '',
                $courriel_visiteur['html']
            );

            if ($ack_result !== true) {
                error_log('[bellevue] accusé de réception en échec — ' . $ack_result);
            }
        }

        // ── Ce que voit le visiteur ────────────────────────────────────────
        // La confirmation n'est affichée que si un message est réellement
        // parti. Auparavant elle l'était dans tous les cas : un visiteur
        // repartait rassuré alors que sa demande n'était arrivée nulle part.
        // La demande reste enregistrée en base, elle : elle n'est pas perdue.
        // Le tableau de bord doit pouvoir dire, au premier coup d'œil, que les
        // notifications ne partent plus : sans cela la panne reste invisible.
        notifications_marquer($mailSent, $mailSent ? '' : implode(' | ', $mail_errors));
        notifications_marquer_demande($pdo, $demande_id ?? 0, $mailSent);

        if ($mailSent) {
            $bookingSuccess = true;
        } else {
            error_log('[bellevue] aucune notification envoyée — ' . implode(' | ', $mail_errors));
            $errorMsg = "Votre demande est bien enregistrée, mais notre serveur de messagerie "
                      . "ne répond pas. Merci de nous appeler au " . SEO_PHONE_HUMAN
                      . " pour que nous la traitions sans attendre.";
        }

        $mailDebug = $mailSent ? "SMTP OK" : "SMTP FAIL";

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
<?php seo_head([
        'title'       => "Gîte de luxe 5 étoiles avec piscine, 10 personnes en Aveyron — Bellevue d'Aveyron",
        'description' => "Gîte de luxe 5 étoiles avec piscine chauffée en Aveyron, pour 10 personnes : 200 m², 5 chambres, parc de 5 000 m², rez-de-chaussée accessible PMR, à Sainte-Eulalie-d'Olt. Location en direct, "
            . number_format($avis_google['note'], 1, ',', '') . "/5 sur " . (int) $avis_google['total'] . " avis Google.",
        'path'        => '',
        'type'        => 'website',
    ]); ?>
    <link rel="icon" type="image/x-icon" href="images/BELLEVUE/logo.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    </noscript>
    <link rel="stylesheet" href="<?php echo seo_asset('css/style.css'); ?>">
</head>
<body class="loading">

<svg width="0" height="0" style="position:absolute;">
    <linearGradient id="goldGradientSvg" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%"   style="stop-color:#bf953f;stop-opacity:1"/>
        <stop offset="50%"  style="stop-color:#fcf6ba;stop-opacity:1"/>
        <stop offset="100%" style="stop-color:#b38728;stop-opacity:1"/>
    </linearGradient>
</svg>

<script>
    var navEntry = performance.getEntriesByType("navigation")[0];
    if (navEntry && navEntry.type === "reload") {
        sessionStorage.removeItem('introPlayed');
    }

    if (sessionStorage.getItem('introPlayed') ||
        <?php echo $bookingSuccess ? 'true' : 'false'; ?> ||
        window.location.hash.length > 1) {
        document.write('<style>#intro-overlay{display:none!important}body.loading{opacity:1!important;visibility:visible!important}#navbar{opacity:1!important;animation:none!important}</style>');

        document.addEventListener('DOMContentLoaded', function(){ document.body.classList.remove('loading'); });
    }
</script>

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
            <div class="intro-main-title" role="presentation">Bellevue d'Aveyron</div>
            <div class="intro-shimmer"></div>
        </div>
        <div class="intro-line intro-line-bottom"></div>
        <div class="intro-subtitle">Villa de Luxe 5 Étoiles</div>
    </div>
</div>

<!-- ══ HEADER ══ -->

<header id="navbar">
    <nav>
        <a href="#accueil" class="logo logo-link" style="text-decoration:none;">Bellevue d'Aveyron<span>VILLA 5 ÉTOILES</span></a>
        <div class="menu-toggle" onclick="toggleMenu()" aria-label="Ouvrir le menu" aria-expanded="false">
            <div class="bar"></div><div class="bar"></div><div class="bar"></div>
        </div>
        <ul class="nav-links" id="navLinks">
            <li><a href="#accueil" onclick="toggleMenu()">Accueil</a></li>
            <li><a href="#experience" onclick="toggleMenu()">La Villa</a></li>
            <li><a href="#services" onclick="toggleMenu()">Services</a></li>
            <li><a href="#tarifs" onclick="toggleMenu()">Tarifs</a></li>
            <li><a href="decouvrir.php" onclick="toggleMenu()" class="nav-gold">Visiter l'Aveyron</a></li>
            <li><a href="javascript:void(0)" onclick="openContactModal(); toggleMenu();">Contact</a></li>
            <li class="menu-phone"><a href="tel:+33680907107" class="nav-phone">✆ 06 80 90 71 07</a></li>
            <li><a href="#reservation" class="btn-book-now" onclick="toggleMenu()">Réserver</a></li>
        </ul>
    </nav>
</header>

<!-- ══ HERO ══ -->

<section id="accueil" class="hero">
    <div class="hero-poster" id="heroPoster"
        style="position:absolute;inset:0;background:url('images/34.jpg') center/cover no-repeat,url('images/accueil.jpg') center/cover no-repeat;z-index:0;transition:opacity 1.5s ease;"></div>
    <div class="video-background">
        <video id="heroVideo" muted loop playsinline style="opacity:0;transition:opacity 1.5s ease;width:100%;height:100%;object-fit:cover;"></video>
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-trust-badge">
            <div class="htb-left">
                <div class="htb-stars">★★★★★</div>
                <div class="htb-text">Villa Classée 5 Étoiles</div>
            </div>
            <div class="htb-sep"></div>
            <div class="htb-right">
                <svg width="20" height="20" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.16v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.16C1.43 8.55 1 10.22 1 12s.43 3.45 1.16 4.93l3.68-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.16 7.07l3.68 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                <div class="htb-score-wrap">
                    <span class="htb-score"><?php echo number_format($avis_google['note'], 1, ',', ''); ?></span>
                    <span class="htb-stars-yellow">★★★★★</span>
                    <span class="htb-count"><?php echo (int) $avis_google['total']; ?> avis Google</span>
                </div>
            </div>
        </div>
        <h1>L'Art de Vivre<br>en Aveyron</h1>
        <p class="hero-baseline">
            Une villa d'exception avec piscine chauffée et vue panoramique sur la vallée du Lot.
        </p>
        <p class="hero-facts">
            Gîte de luxe 5 étoiles à Sainte-Eulalie-d'Olt (12130), en Aveyron —
            200 m², 5 chambres, jusqu'à 10 personnes, parc privé de 5 000 m².
        </p>
        <a href="#reservation" class="btn-gold">Planifier votre séjour</a>
    </div>
    <div class="hero-bottom-gradient"></div>
</section>

<!-- ══ CHIFFRES CLÉS ══ -->

<section class="chiffres-cles" aria-label="Le gîte en chiffres">
    <ul class="chiffres-grid">
        <?php foreach (seo_chiffres_cles() as $chiffre):
            $texte = seo_chiffre_texte($chiffre);
            // Largeur réservée d'après le nombre final : le compteur défile
            // sans décaler le libellé au-dessous.
            $largeur = max(1, mb_strlen(number_format((int) $chiffre['nombre'], 0, ',', ' ')));
        ?>
        <li class="chiffre">
            <span class="chiffre-valeur">
                <span class="chiffre-nombre"
                      data-compteur="<?php echo (int) $chiffre['nombre']; ?>"
                      style="min-width:<?php echo $largeur; ?>ch"><?php
                    echo seo_e(number_format((int) $chiffre['nombre'], 0, ',', ' '));
                ?></span><?php if ($chiffre['suffixe'] !== ''): ?><span class="chiffre-suffixe"><?php echo seo_e($chiffre['suffixe']); ?></span><?php endif; ?>
            </span>
            <span class="chiffre-libelle"><?php echo seo_e($chiffre['libelle']); ?></span>
            <span class="chiffre-precision"><?php echo seo_e($chiffre['precision']); ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- ══ EXPÉRIENCE ══ -->

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
            $famille_images = [];
            foreach (['jpg','jpeg','png','webp','JPG','JPEG'] as $ext) {
                $found = glob('images/BELLEVUE/*.' . $ext);
                if ($found) $famille_images = array_merge($famille_images, $found);
            }
            if (!empty($famille_images)) shuffle($famille_images);
            ?>
            <?php if (!empty($famille_images)): ?>
                <div class="family-slideshow" id="familySlideshow">
                    <?php foreach ($famille_images as $i => $img): ?>
                        <img src="<?php echo $img; ?>" alt="Villa Bellevue d'Aveyron à Sainte-Eulalie-d'Olt — photo <?php echo $i+1; ?>" loading="lazy" class="family-slide <?php echo $i===0?'active':''; ?>">
                    <?php endforeach; ?>
                    <div class="pmr-frame-overlay"></div>
                </div>
            <?php else: ?>
                <img src="images/accueil.jpg" alt="Vue panoramique sur la vallée du Lot depuis la villa Bellevue d'Aveyron et sa piscine chauffée" loading="lazy">
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ══ ACCESSIBILITÉ ══ -->

<section id="accessibilite" class="access-section">
    <?php
    $pmr_images = [];
    foreach (['jpg','jpeg','png','webp','JPG','JPEG'] as $ext) {
        $found = glob('images/PMR/*.' . $ext);
        if ($found) $pmr_images = array_merge($pmr_images, $found);
    }
    if (!empty($pmr_images)) shuffle($pmr_images);
    ?>
    <div class="access-container">
        <div class="access-visual-side">
            <?php if (!empty($pmr_images)): ?>
                <div class="pmr-slideshow" id="pmrSlideshow">
                    <?php foreach ($pmr_images as $i => $img): ?>
                        <img src="<?php echo $img; ?>" alt="Rez-de-chaussée de plain-pied accessible PMR du gîte Bellevue d'Aveyron — photo <?php echo $i+1; ?>" loading="lazy" class="pmr-slide <?php echo $i===0?'active':''; ?>">
                    <?php endforeach; ?>
                    <div class="pmr-frame-overlay"></div>
                </div>
            <?php else: ?>
                <div style="width:100%;height:300px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                    <p style="color:#999;font-style:italic;">Images PMR non trouvées</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="access-content">
            <div class="section-header" style="margin-bottom:30px;text-align:left;">
                <span class="subtitle">Hospitalité & Inclusivité</span>
                <h2 style="font-size:2rem;">L'Aveyron pour Tous</h2>
            </div>
            <p class="access-intro">Pour des vacances en toute tranquillité, nous avons conçu Bellevue d'Aveyron comme un espace ouvert à tous. L'accessibilité n'est pas une option, c'est une promesse de sérénité partagée.</p>
            <div class="access-features">
                <div class="access-item"><span class="check-gold">✓</span><p><strong>Rez-de-chaussée sans une marche :</strong> Du parking aux espaces de vie (cuisine, salon, terrasses), tout est de plain-pied et pensé pour une circulation fluide sans obstacle.</p></div>
                <div class="access-item"><span class="check-gold">✓</span><p><strong>Espace nuit adapté :</strong> Une chambre et une salle de bain entièrement équipées sont accessibles directement au rez-de-chaussée. La villa compte un étage, mais un séjour complet s'y vit sans jamais emprunter l'escalier.</p></div>
                <div class="access-item"><span class="check-gold">✓</span><p><strong>Aménagements extérieurs :</strong> Une piste aménagée relie le parking à la maison pour un accès facilité en toutes circonstances.</p></div>
            </div>
            <p class="access-footer">Idéalement situé pour visiter les merveilles accessibles de la région (Beaux villages, Viaduc de Millau, Musée Soulages...).<br><em>N'hésitez pas à nous contacter pour préparer votre venue.</em></p>
        </div>
    </div>
</section>

<!-- ══ SERVICES ══ -->

<section id="services" class="amenities-section">
    <div class="section-header">
        <span class="subtitle">Tout inclus</span>
        <h2>Prestations d'Excellence</h2>
    </div>

    <div class="amenities-grid">
        <div class="flip-card"><div class="flip-card-inner">
            <div class="flip-card-front"><div class="amenity-icon">✦</div><h3>Piscine<br>Chauffée</h3><p>Bassin privé de 4x8m, sécurisé par volet roulant et chauffé à partir du mois de mai.</p><div class="card-info-icon">ℹ</div></div>
            <div class="flip-card-back"><h3>Piscine<br>Chauffée</h3><p>Volet sécurisé homologué. Température maintenue pour un confort optimal garanti à partir du mois de mai.</p></div>
        </div></div>
        <div class="flip-card"><div class="flip-card-inner">
            <div class="flip-card-front"><div class="amenity-icon">✦</div><h3>Vue<br>Panoramique</h3><p>Un spectacle quotidien époustouflant à 360° sur la vallée du Lot et les monts d'Aubrac.</p><div class="card-info-icon">ℹ</div></div>
            <div class="flip-card-back"><h3>Vue<br>Panoramique</h3><p>Orientation plein sud. Lever et coucher de soleil imprenables depuis les différentes immenses terrasses de la villa.</p></div>
        </div></div>
        <div class="flip-card"><div class="flip-card-inner">
            <div class="flip-card-front"><div class="amenity-icon">✦</div><h3>Borne<br>Électrique</h3><p>Chargeur rapide 18 kVA inclus pour votre véhicule, pour explorer l'Aveyron l'esprit libre.</p><div class="card-info-icon">ℹ</div></div>
            <div class="flip-card-back"><h3>Borne<br>Électrique</h3><p>Chargeur 18 kVA compatible toutes marques (Tesla, Renault, etc). Accès privé sur le parking sécurisé de la propriété.</p></div>
        </div></div>
        <div class="flip-card"><div class="flip-card-inner">
            <div class="flip-card-front"><div class="amenity-icon">✦</div><h3>Divertissement</h3><p>Fibre optique très haut débit, Wi-Fi partout, vélos à disposition et Baby-foot Bonzini.</p><div class="card-info-icon">ℹ</div></div>
            <div class="flip-card-back"><h3>Divertissement</h3><p>Fibre optique 1 Gbit/s. Baby-foot Bonzini professionnel. 6 vélos adultes et 5 vélos enfants à disposition gratuitement.</p></div>
        </div></div>
    </div>
</section>

<!-- ══ TARIFS ══ -->

<section id="tarifs">
    <div class="section-header">
        <span class="subtitle">Location à la semaine</span>
        <h2>Nos Tarifs par Saison</h2>
        <?php if ($tarif_mini > 0): ?>
            <p class="tarifs-accroche">
                La villa entière, pour votre seul groupe, à partir de
                <strong><?php echo number_format($tarif_mini, 0, ',', ' '); ?> €</strong> la semaine.
            </p>
        <?php endif; ?>
    </div>

    <?php if (empty($tarifs_grille)): ?>
        <p class="tarifs-vide">
            La grille tarifaire est en cours de mise à jour.
            Écrivez-nous ou appelez le <a href="tel:<?php echo SEO_PHONE; ?>"><?php echo SEO_PHONE_HUMAN; ?></a>,
            nous vous répondons sous 24 heures.
        </p>
    <?php else: ?>

    <div class="tarifs-saisons">
        <?php foreach ($tarifs_grille as $cle => $saison): ?>
        <article class="saison saison--<?php echo seo_e($cle); ?>">
            <div class="saison-tete">
                <h3><?php echo seo_e($saison['nom']); ?></h3>
                <p class="saison-resume"><?php echo seo_e($saison['resume']); ?></p>
            </div>

            <ul class="saison-lignes">
                <?php foreach ($saison['lignes'] as $l): ?>
                <li class="saison-ligne">
                    <div class="ligne-periode">
                        <span class="ligne-nom"><?php echo seo_e($l['nom']); ?></span>
                        <span class="ligne-dates"><?php echo seo_e($l['periode']); ?></span>
                    </div>
                    <div class="ligne-prix">
                        <span class="prix-semaine"><?php echo number_format($l['semaine'], 0, ',', ' '); ?> €</span>
                        <span class="prix-unite">la semaine</span>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>

            <p class="saison-conseil"><?php echo seo_e($saison['conseil']); ?></p>
        </article>
        <?php endforeach; ?>
    </div>

    <div class="tarifs-conditions">
        <h3>Ce qu'il faut savoir avant de réserver</h3>
        <dl>
            <?php foreach (tarifs_conditions($tarifs_settings) as [$titre, $detail]): ?>
                <div class="condition">
                    <dt><?php echo seo_e($titre); ?></dt>
                    <dd><?php echo seo_e($detail); ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
        <a href="#reservation" class="btn-gold">Vérifier les disponibilités</a>
    </div>

    <?php endif; ?>
</section>

<!-- ══ RÉSERVATION ══ -->
<!--
    Une seule demande, un seul bouton.
    L'ancien écran proposait « réserver » et « demander une information » par le
    même formulaire, départagés par une fenêtre qui s'ouvrait quand les dates
    manquaient : le visiteur découvrait la question après avoir cliqué. Ici les
    dates sont facultatives et annoncées comme telles, et l'intitulé du bouton
    dit à tout moment ce que le clic va produire.
-->

<section id="reservation" class="resa">
    <div class="section-header">
        <span class="subtitle">Disponibilités</span>
        <h2>Réservez Votre Séjour</h2>
        <p class="resa-chapeau">
            En direct auprès des propriétaires, sans commission.
            Réponse sous 24&nbsp;heures.
        </p>
    </div>

    <div class="resa-grille">

        <!-- ── Calendrier ────────────────────────────────────────────── -->
        <div class="resa-calendrier">
            <div class="resa-etape">
                <span class="resa-numero">1</span>
                <div>
                    <h3>Vos dates</h3>
                    <p>Facultatif — cliquez l'arrivée, puis le départ. Minimum 3&nbsp;nuits.</p>
                </div>
            </div>

            <div class="cal-barre">
                <button type="button" class="cal-fleche" id="calPrec" aria-label="Mois précédent">
                    <span aria-hidden="true">&#8249;</span>
                </button>
                <span class="cal-titre" id="calTitre" aria-live="polite">&nbsp;</span>
                <button type="button" class="cal-fleche" id="calSuiv" aria-label="Mois suivant">
                    <span aria-hidden="true">&#8250;</span>
                </button>
            </div>

            <div class="cal-mois" id="calMois"></div>

            <noscript>
                <!-- Sans JavaScript, le calendrier ne s'affiche pas : deux champs
                     de date natifs prennent le relais. Ils portent les mêmes noms
                     que les champs cachés et, placés après eux, l'emportent. -->
                <div class="cal-sansjs">
                    <p>Votre navigateur n'exécute pas JavaScript : saisissez vos dates ici.</p>
                    <div class="champ">
                        <label for="sansjs_arrivee">Arrivée</label>
                        <input type="date" id="sansjs_arrivee" name="check_in" form="bookingForm">
                    </div>
                    <div class="champ">
                        <label for="sansjs_depart">Départ</label>
                        <input type="date" id="sansjs_depart" name="check_out" form="bookingForm">
                    </div>
                </div>
            </noscript>

            <ul class="cal-legende">
                <li><span class="pastille pastille--libre"></span>Libre</li>
                <li><span class="pastille pastille--choix"></span>Votre séjour</li>
                <li><span class="pastille pastille--occupe"></span>Déjà réservé</li>
            </ul>

            <p class="cal-aide" id="calAide" role="status"></p>

            <ul class="cal-reperes">
                <li>Location principalement à la semaine, du samedi au samedi.</li>
                <li>Séjour de 3 nuits minimum, possible sur certaines périodes — écrivez-nous.</li>
                <li>Vous réservez en direct : aucune commission de plateforme.</li>
            </ul>
        </div>

        <!-- ── Formulaire ────────────────────────────────────────────── -->
        <div class="resa-formulaire">
            <div class="resa-etape">
                <span class="resa-numero">2</span>
                <div>
                    <h3>Vos coordonnées</h3>
                    <p>Nous vous répondons personnellement, sous 24&nbsp;heures.</p>
                </div>
            </div>

            <div class="resa-recap" id="resaRecap">
                <p class="resa-recap-vide">Aucune date sélectionnée — nous répondrons à vos questions.</p>
            </div>

            <form method="POST" action="index.php#reservation" id="bookingForm" novalidate>
                <input type="hidden" name="action" value="book">
                <input type="hidden" name="check_in" id="input_check_in">
                <input type="hidden" name="check_out" id="input_check_out">
                <?php echo antispam_champs(); ?>

                <div class="champ">
                    <label for="champ_nom">Nom et prénom <span aria-hidden="true">*</span></label>
                    <input type="text" id="champ_nom" name="customer_name" autocomplete="name" required>
                </div>

                <div class="champ-duo">
                    <div class="champ">
                        <label for="champ_email">Adresse e-mail <span aria-hidden="true">*</span></label>
                        <input type="email" id="champ_email" name="customer_email" autocomplete="email" inputmode="email" required>
                    </div>
                    <div class="champ">
                        <label for="champ_tel">Téléphone <span aria-hidden="true">*</span></label>
                        <input type="tel" id="champ_tel" name="customer_phone" autocomplete="tel" inputmode="tel" required>
                    </div>
                </div>

                <div class="champ">
                    <label for="champ_message">Votre message</label>
                    <textarea id="champ_message" name="customer_message" rows="3"
                              placeholder="Nombre de personnes, lit bébé, arrivée tardive, une question…"></textarea>
                </div>

                <label class="champ-case">
                    <input type="checkbox" name="cleaning_fee" value="1">
                    <span>Ajouter le ménage de fin de séjour
                        <em><?php echo (int) ($tarifs_settings['frais_menage'] ?? 220); ?> €, en option</em>
                    </span>
                </label>

                <button type="submit" class="resa-envoyer" id="resaEnvoyer">Envoyer ma demande</button>

                <p class="resa-mentions">
                    Champs obligatoires marqués d'un astérisque. Aucun paiement à cette étape :
                    nous vérifions la disponibilité, puis vous confirmons le tarif exact.
                </p>
            </form>

            <p class="resa-telephone">
                Vous préférez appeler ? <a href="tel:<?php echo SEO_PHONE; ?>"><?php echo SEO_PHONE_HUMAN; ?></a>
            </p>
        </div>

    </div>
</section>

<!-- ══ AVIS CLIENTS ══ -->

<section id="temoignages" class="reviews-section" style="background-color:#fafafa;">

    <div class="ti-widget">
        <div class="ti-header">
            <div class="ti-header-left">
                <h2>Avis Clients</h2>
                <div class="ti-location">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Gîte Bellevue d'Aveyron, Sainte-Eulalie-d'Olt
                </div>
            </div>
            <div class="ti-header-right">
                <div><div class="ti-rating-score"><?php echo number_format($avis_google['note'], 1, ',', ''); ?></div><div class="ti-stars">★★★★★</div></div>
                <div class="ti-review-count"><span class="ti-review-count-num"><?php echo (int) $avis_google['total']; ?></span>avis Google</div>
            </div>
        </div>
        <div class="ti-controls">
            <div class="ti-subtitle">
                <h3>Derniers témoignages</h3>
                <div class="ti-meta">
                    <?php echo seo_e(avis_libelle_source($avis_google)); ?>
                    <span class="ti-badge"><?php echo $avis_google['source'] === 'google' ? 'GOOGLE' : 'SÉLECTION'; ?></span>
                </div>
            </div>
            <div class="ti-actions">
                <button class="ti-btn-refresh" onclick="window.location.reload();">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2v6h-6"/><path d="M21 13a9 9 0 1 1-3-7.7L21 8"/></svg>
                    ACTUALISER
                </button>
                <div class="ti-nav-btns">
                    <button class="ti-nav" onclick="document.querySelector('.ti-cards').scrollBy({left:-360,behavior:'smooth'})">❮</button>
                    <button class="ti-nav" onclick="document.querySelector('.ti-cards').scrollBy({left:360,behavior:'smooth'})">❯</button>
                </div>
            </div>
        </div>
        <div class="ti-cards">
            <?php foreach ($avis_google['avis'] as $avis): ?>
            <div class="ti-card">
                <div class="ti-quote-icon">"</div>
                <div class="ti-card-header">
                    <div class="ti-avatar"><?php echo seo_e($avis['initiale']); ?></div>
                    <div>
                        <div class="ti-author-name"><?php echo seo_e($avis['auteur']); ?></div>
                        <div class="ti-author-date"><?php echo seo_e($avis['date']); ?></div>
                    </div>
                </div>
                <div class="ti-card-stars"><?php echo str_repeat('★', (int) $avis['note']); ?></div>
                <div class="ti-card-text">"<?php echo seo_e($avis['texte']); ?>"</div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:20px;">
            <a href="https://g.page/r/CVWZLGkfDaptEAE/review" target="_blank" class="btn-gold-outline">Lire tous les avis sur Google</a>
        </div>
    </div>
</section>

<!-- ══ FAQ (questions fréquentes — balisées en FAQPage) ══ -->

<section id="faq" class="faq-section">
    <div class="section-header">
        <span class="subtitle">Bon à savoir</span>
        <h2>Questions Fréquentes</h2>
    </div>
    <div class="faq-grid">
        <?php foreach (seo_faq() as [$question, $reponse]): ?>
        <article class="faq-item">
            <h3 class="faq-question"><?php echo seo_e($question); ?></h3>
            <p class="faq-answer"><?php echo seo_e($reponse); ?></p>
        </article>
        <?php endforeach; ?>
    </div>
    <p class="faq-contact">
        <a href="decouvrir.php">Visiter l'Aveyron : les grands sites à moins d'une heure du gîte</a>
    </p>
    <p class="faq-contact faq-contact-secondaire">
        Une question qui n'est pas dans cette liste ?
        <a href="tel:<?php echo SEO_PHONE; ?>"><?php echo SEO_PHONE_HUMAN; ?></a>
        &nbsp;•&nbsp;
        <a href="#reservation">Nous écrire</a>
    </p>
</section>

<!-- ══ FOOTER ══ -->

<footer id="footer-luxe">
    <div class="footer-container">
        <div class="footer-col brand-col">
            <div class="footer-logo">Bellevue d'Aveyron<span>Villa 5 Étoiles</span></div>
            <p class="footer-desc">Un sanctuaire de paix au cœur de l'Aveyron. L'alliance parfaite entre l'authenticité et le luxe contemporain.</p>
            <div class="footer-socials">
                <a href="https://www.instagram.com/gitebellevuedaveyron/" target="_blank" class="social-link">Instagram</a>
                <a href="https://www.facebook.com/gitebellevuedaveyron" target="_blank" class="social-link">Facebook</a>
            </div>
        </div>
        <div class="footer-col links-col">
            <h3>Explorer</h3>
            <ul>
                <li><a href="#experience">La Villa</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#tarifs">Tarifs</a></li>
                <li><a href="#accessibilite">Accessibilité (PMR)</a></li>
                <li><a href="#temoignages">Livre d'Or</a></li>
                <li><a href="#faq">Questions fréquentes</a></li>
                <li><a href="decouvrir.php">Visiter l'Aveyron</a></li>
            </ul>
        </div>
        <div class="footer-col contact-col">
            <h3>Nous Trouver</h3>
            <ul class="contact-list">
                <li><span class="icon">📍</span><span>12130 Sainte-Eulalie-d'Olt<br><em style="font-size:.8em;opacity:.7;">Plus Beaux Villages de France</em></span></li>
                <li><span class="icon">📞</span><a href="tel:+33680907107">06 80 90 71 07</a></li>
            </ul>
            <a href="#reservation" class="btn-footer">Réserver maintenant</a>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="legal-links">
            <span>&copy; 2026 Bellevue d'Aveyron</span>
            <span class="separator">•</span>
            <a href="mentions.php?retour=index.php&amp;section=reservation">Mentions Légales</a>
            <span class="separator">•</span>
            <a href="politique.php?retour=index.php&amp;section=reservation">Politique de Confidentialité</a>
        </div>
        <div class="signature">
            Excellence &amp; Tradition
            <?php $v = seo_version_texte(); if ($v !== ''): ?>
                <span class="version" title="Version en ligne — à comparer au dépôt en cas de doute"><?php echo seo_e($v); ?></span>
            <?php endif; ?>
        </div>
    </div>
</footer>

<!-- ══ MODALE SUCCÈS ══ -->

<div class="modal-overlay <?php echo $bookingSuccess ? 'active' : ''; ?>" id="successModal">
    <div class="modal-card">
        <div class="success-icon">✓</div>
        <h3 style="font-family:'Cinzel',serif;color:var(--navy-deep);margin-bottom:15px;">Demande Reçue</h3>
        <p style="color:#666;margin-bottom:20px;">
            Merci pour l'intérêt que vous portez à notre gîte.<br>
            Votre demande <?php echo (!empty($bookingData['nuits']) && $bookingData['nuits'] > 0) ? 'pour '.$bookingData['nuits'].' nuits' : 'd\'information'; ?> a bien été enregistrée.
            <br><br>
            Nous reviendrons vers vous au plus vite pour confirmer votre demande.
            <br><br>
        </p>
        <button onclick="document.getElementById('successModal').classList.remove('active')" class="btn-gold">Fermer</button>
    </div>
</div>

<div id="contactModal" role="dialog" aria-modal="true" aria-labelledby="contactModalTitle">
    <div class="contact-card" role="document">

    <!-- Poignée swipe (mobile) -->
    <div class="contact-handle"><div class="contact-handle-bar"></div></div>

    <!-- En-tête -->
    <div class="contact-modal-header">
        <div>
            <span class="contact-subtitle">À votre écoute</span>
            <h3 id="contactModalTitle">Contactez-nous</h3>
        </div>
        <button class="contact-close-btn" onclick="closeContactModal()" aria-label="Fermer">✕</button>
    </div>

    <!-- Corps -->
    <div class="contact-body">
        <a href="tel:+33680907107" class="contact-row" aria-label="Appeler le 06 80 90 71 07">
            <div class="contact-row-icon">📞</div>
            <div class="contact-row-text">
                <strong>Par Téléphone</strong>
                <span>06 80 90 71 07</span>
            </div>
            <span class="contact-row-arrow">›</span>
        </a>

        <a href="mailto:accueil@bellevuedaveyron.com" class="contact-row" aria-label="Envoyer un email">
            <div class="contact-row-icon">✉️</div>
            <div class="contact-row-text">
                <strong>Par Email</strong>
                <span>accueil@bellevuedaveyron.com</span>
            </div>
            <span class="contact-row-arrow">›</span>
        </a>

        <a href="#reservation" class="contact-row" onclick="closeContactModal()" aria-label="Faire une demande de réservation">
            <div class="contact-row-icon">📅</div>
            <div class="contact-row-text">
                <strong>Demande de réservation</strong>
                <span>Formulaire en ligne</span>
            </div>
            <span class="contact-row-arrow">›</span>
        </a>
    </div>

    <!-- Pied -->
    <div class="contact-footer">
        <p>Réponse garantie sous 24h · 7j/7</p>
    </div>

</div>
</div>

<?php if ($errorMsg): ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof showNotification === 'function') {
            showNotification("<?php echo addslashes($errorMsg); ?>", "error");
        } else {
            alert("<?php echo addslashes($errorMsg); ?>");
        }
    });
</script>
<?php endif; ?>

<script>
    // Données du calendrier, produites par le serveur.
    const bookedDates = <?php echo $json_booked_dates ?: '[]'; ?>;
    const tarifSaisons = <?php echo $json_saisons ?: '[]'; ?>;
</script>

<?php
// ── Données structurées JSON-LD (Google, ChatGPT, Perplexity, Gemini…) ──
seo_jsonld([
    seo_node_website(),
    seo_node_lodging($tarifs_display, $avis_google),
    seo_node_webpage('', "Bellevue d'Aveyron — Villa 5 étoiles avec piscine, Sainte-Eulalie-d'Olt",
        "Gîte de luxe 5 étoiles à Sainte-Eulalie-d'Olt en Aveyron : 200 m², 5 chambres, 10 personnes, piscine chauffée, parc de 5 000 m², accès PMR."),
    seo_node_breadcrumb([['Accueil', '']]),
    seo_node_faq(seo_faq()),
]);
?>
<?php annonce_bandeau($annonce); ?>

<script src="<?php echo seo_asset('js/script.js'); ?>"></script>



</body>
</html>