<?php
/**
 * decouvrir.php — Page "Découvrir la Région" — Bellevue d'Aveyron ★★★★★
 * Version 5 — Architecture séparée CSS / JS / PHP
 * Point de référence du gîte : constantes SEO_LAT / SEO_LNG (config/seo.php).
 */
require_once __DIR__ . '/config/seo.php';
require_once __DIR__ . '/config/medias.php';
require_once __DIR__ . '/config/agenda.php';

// Agenda de proximité : rayon de 35 km autour du gîte, trié par distance.
$agenda_proches = agenda_events(35, 12);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php seo_head([
        'title'       => "Que faire en Aveyron ? Distances et incontournables depuis Sainte-Eulalie-d'Olt",
        'description' => "Conques à 55 km, Viaduc de Millau à 75 km, Aubrac à 25 km, musée Soulages à 50 km : distances réelles, agenda local, activités et bonnes tables autour du gîte Bellevue d'Aveyron.",
        'path'        => 'decouvrir.php',
        'type'        => 'article',
    ]); ?>
    <link rel="icon" type="image/x-icon" href="images/BELLEVUE/logo.ico">

    <!-- Fonts Google — chargement non bloquant -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap"
          rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    </noscript>

    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-decouvrir">


<!-- ══════════════════════════════════════════
     HEADER / NAVIGATION
     Logo cliquable → index.php
     Liens menu → sections de index.php
     ══════════════════════════════════════════ -->
<header id="navbar">
    <nav>
        <!-- Logo — clique → retour accueil index.php -->
        <a href="index.php#accueil" class="logo logo-link" aria-label="Bellevue d'Aveyron — Accueil">
            Bellevue d'Aveyron<span>VILLA 5 ÉTOILES</span>
        </a>

        <div class="menu-toggle" onclick="toggleMenu()" aria-label="Ouvrir le menu" aria-expanded="false">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

        <ul class="nav-links" id="navLinks">
            <li><a href="index.php#accueil"     onclick="toggleMenu()">Accueil</a></li>
            <li><a href="index.php#experience"  onclick="toggleMenu()">La Villa</a></li>
            <li><a href="index.php#services"    onclick="toggleMenu()">Services</a></li>
            <li><a href="index.php#tarifs"      onclick="toggleMenu()">Tarifs</a></li>
            <li><a href="decouvrir.php"         onclick="toggleMenu()" style="color:var(--gold-text);">Visiter l'Aveyron</a></li>
            <li><a href="javascript:void(0)"     onclick="openContactModal(); toggleMenu()">Contact</a></li>
            <li class="menu-phone">
                <a href="tel:+33680907107" style="color:var(--gold-text);font-weight:600;">✆ 06 80 90 71 07</a>
            </li>
            <li><a href="index.php#reservation" class="btn-book-now" onclick="toggleMenu()">Réserver</a></li>
        </ul>
    </nav>
</header>


<!-- ══════════════════════════════════════════
     HERO — photo villa + vallée du Lot
     Image : images/34.jpg (piscine, terrasse, vue)
     ══════════════════════════════════════════ -->
<div class="region-hero">
    <div class="hero-content">
        <span class="hero-eyebrow">★ Gîte Bellevue d'Aveyron · 5 Étoiles ★</span>
        <h1>L'Aveyron<br>à <em>portée de vue</em></h1>
        <p>
            Depuis Sainte-Eulalie-d'Olt — l'un des Plus Beaux Villages de France —
            partez explorer l'Aubrac, les gorges du Lot et les trésors médiévaux
            à quelques minutes de votre villa d'exception.
        </p>
        <div class="hero-cta-group">
            <a href="index.php#reservation" class="btn-gold-hero">Réserver votre séjour</a>
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


<!-- ══════════════════════════════════════════════════════════════
     THÈMES — 4 cartes éditoriales « Choisissez votre Aveyron »
     Chaque carte active l'onglet agenda correspondant
     Images : photos Aveyron authentiques (Wikimedia Commons)
     ══════════════════════════════════════════════════════════════ -->
<section class="themes-section">
    <div class="section-header">
        <span class="subtitle">Votre Carnet de Voyage</span>
        <h2>Choisissez votre Aveyron</h2>
    </div>

    <div class="curator-grid">

        <!-- Nature & Activités — Plateau de l'Aubrac -->
        <a href="#agenda" onclick="activateTab('activites', null); return true;" class="curator-card">
            <?php media_card('theme-nature-activites', 'curator-img'); ?>
            <div class="curator-body">
                <h3>Nature &amp; Activités</h3>
                <p>De l'immensité volcanique de l'Aubrac aux berges boisées du Lot — VTT depuis le gîte, kayak, paddle, randonnées balisées. Nature grandeur nature.</p>
                <span class="curator-btn">Voir les activités →</span>
            </div>
        </a>

        <!-- Agenda & Événements — Sainte-Eulalie-d'Olt vue du château -->
        <a href="#agenda" onclick="activateTab('autour', null); return true;" class="curator-card">
            <?php media_card('theme-agenda-evenements', 'curator-img'); ?>
            <div class="curator-body">
                <h3>Agenda &amp; Événements</h3>
                <p>Marchés nocturnes à Sainte-Eulalie, fêtes de l'Aubrac, concerts de Conques, Trail Aubrac… Le calendrier de votre territoire ne s'arrête jamais.</p>
                <span class="curator-btn">Voir l'agenda →</span>
            </div>
        </a>

        <!-- Conques & Patrimoine — Village de Conques -->
        <a href="#agenda" onclick="activateTab('conques', null); return true;" class="curator-card">
            <?php media_card('theme-conques-patrimoine', 'curator-img'); ?>
            <div class="curator-body">
                <h3>Conques &amp; Patrimoine</h3>
                <p>Abbatiale romane, trésor médiéval, village classé parmi les Plus Beaux de France — Conques-en-Rouergue à 55 km, incontournable de tout séjour aveyronnais.</p>
                <span class="curator-btn">Explorer Conques →</span>
            </div>
        </a>

        <!-- Tables & Gastronomie — Aligot traditionnel -->
        <a href="#agenda" onclick="activateTab('restaurants', null); return true;" class="curator-card">
            <?php media_card('theme-tables-gastronomie', 'curator-img'); ?>
            <div class="curator-body">
                <h3>Tables &amp; Gastronomie</h3>
                <p>Aligot en buron d'Aubrac, bœuf Aubrac, tomme, roquefort, marchés de producteurs en été — les meilleures tables autour de votre villa.</p>
                <span class="curator-btn">Voir les restaurants →</span>
            </div>
        </a>

    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════
     DISTANCES — FLIP CARDS
     3 groupes : à deux pas · à portée · grand Aveyron
     Cliquer sur une carte → flip exclusif (une seule ouverte)
     ══════════════════════════════════════════════════════════════ -->
<section class="distances-section" id="distances">
    <details>
        <summary>
            Bellevue d'Aveyron : tout est proche
            <span class="arrow">↓</span>
        </summary>

        <p style="text-align:center; font-family:'Montserrat',sans-serif; font-size:.78rem; color:rgba(201,168,76,.6); margin:18px 0 0; letter-spacing:.08em;">
            Cliquez sur une destination pour découvrir ses points clés touristiques
        </p>

        <div class="dist-categories">

            <!-- ── Groupe 1 : À deux pas (0–10 km) ── -->
            <div>
                <div class="dist-cat-label">À deux pas · 0 – 10 km</div>
                <div class="dist-cards-row">

                    <div class="flip-card" onclick="flipExclusive(this)">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="fc-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                </div>
                                <div>
                                    <div class="fc-name">Saint-Geniez-d'Olt</div>
                                    <div class="fc-km">2 km · 5 min</div>
                                </div>
                            </div>
                            <div class="flip-card-back">
                                <ul class="fc-points">
                                    <li>Baignade &amp; kayak sur le Lot</li>
                                    <li>Marché hebdomadaire (jeudi matin)</li>
                                    <li>Trail Aubrac — course de montagne</li>
                                    <li>Halles médiévales &amp; vieille ville</li>
                                </ul>
                                <div class="fc-back-hint">Cliquer pour retourner ↺</div>
                            </div>
                        </div>
                    </div>

                    <div class="flip-card" onclick="flipExclusive(this)">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="fc-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10A15.3 15.3 0 0 1 12 2z"/></svg>
                                </div>
                                <div>
                                    <div class="fc-name">Lac de Castelnau</div>
                                    <div class="fc-km">2 km · 5 min</div>
                                </div>
                            </div>
                            <div class="flip-card-back">
                                <ul class="fc-points">
                                    <li>Base nautique — kayak &amp; paddle</li>
                                    <li>Plage aménagée &amp; baignade</li>
                                    <li>Pêche dans le Lot</li>
                                    <li>Sentiers autour du lac</li>
                                </ul>
                                <div class="fc-back-hint">Cliquer pour retourner ↺</div>
                            </div>
                        </div>
                    </div>

                    <div class="flip-card" onclick="flipExclusive(this)">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="fc-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="9" width="18" height="12" rx="1"/><path d="M3 9l9-7 9 7"/></svg>
                                </div>
                                <div>
                                    <div class="fc-name">Saint-Côme-d'Olt ★</div>
                                    <div class="fc-km">5 km · 8 min</div>
                                </div>
                            </div>
                            <div class="flip-card-back">
                                <ul class="fc-points">
                                    <li>Plus Beau Village de France</li>
                                    <li>Clocher flamboyant du XVIe s.</li>
                                    <li>Marchés nocturnes en été</li>
                                    <li>Ruelles médiévales &amp; château</li>
                                </ul>
                                <div class="fc-back-hint">Cliquer pour retourner ↺</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ── Groupe 2 : À portée (10–40 km) ── -->
            <div>
                <div class="dist-cat-label">À portée · 10 – 40 km</div>
                <div class="dist-cards-row">

                    <div class="flip-card" onclick="flipExclusive(this)">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="fc-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4.5 8-11.8A8 8 0 0 0 12 2a8 8 0 0 0-8 8.2c0 7.3 8 11.8 8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <div>
                                    <div class="fc-name">Espalion</div>
                                    <div class="fc-km">12 km · 15 min</div>
                                </div>
                            </div>
                            <div class="flip-card-back">
                                <ul class="fc-points">
                                    <li>Vieux pont roman en grès rouge</li>
                                    <li>Musée Joseph-Vaylet &amp; arts</li>
                                    <li>Musée du Scaphandre (unique)</li>
                                    <li>Kayak &amp; baignade sur le Lot</li>
                                </ul>
                                <div class="fc-back-hint">Cliquer pour retourner ↺</div>
                            </div>
                        </div>
                    </div>

                    <div class="flip-card" onclick="flipExclusive(this)">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="fc-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21V8l9-6 9 6v13"/><path d="M9 21v-6h6v6"/><path d="M3 12h2m14 0h2"/></svg>
                                </div>
                                <div>
                                    <div class="fc-name">Estaing ★</div>
                                    <div class="fc-km">20 km · 25 min</div>
                                </div>
                            </div>
                            <div class="flip-card-back">
                                <ul class="fc-points">
                                    <li>Plus Beau Village de France</li>
                                    <li>Château des Comtes d'Estaing (XIVe)</li>
                                    <li>Pont médiéval sur le Lot</li>
                                    <li>Fête du vin en juillet</li>
                                </ul>
                                <div class="fc-back-hint">Cliquer pour retourner ↺</div>
                            </div>
                        </div>
                    </div>

                    <div class="flip-card" onclick="flipExclusive(this)">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="fc-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 17l4-8 4 4 4-6 4 10"/><path d="M3 21h18"/></svg>
                                </div>
                                <div>
                                    <div class="fc-name">Plateau de l'Aubrac</div>
                                    <div class="fc-km">25 km · 30 min</div>
                                </div>
                            </div>
                            <div class="flip-card-back">
                                <ul class="fc-points">
                                    <li>Transhumance fin mai (spectacle)</li>
                                    <li>Burons &amp; aligot authentique</li>
                                    <li>GR65 Chemin de Saint-Jacques</li>
                                    <li>Faune sauvage &amp; grands espaces</li>
                                </ul>
                                <div class="fc-back-hint">Cliquer pour retourner ↺</div>
                            </div>
                        </div>
                    </div>

                    <div class="flip-card" onclick="flipExclusive(this)">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="fc-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m14.5 9-6 6"/><path d="m11 11-4 4-2.5-2.5a2.12 2.12 0 1 1 3-3L11 11Z"/><path d="m14 14 4-4 2.5 2.5a2.12 2.12 0 1 1-3 3L14 14Z"/></svg>
                                </div>
                                <div>
                                    <div class="fc-name">Laguiole</div>
                                    <div class="fc-km">30 km · 35 min</div>
                                </div>
                            </div>
                            <div class="flip-card-back">
                                <ul class="fc-points">
                                    <li>Coutellerie — visite manufacture</li>
                                    <li>Fromage Laguiole AOP</li>
                                    <li>Restaurant Bras ★★★ Michelin</li>
                                    <li>Ski nordique en hiver</li>
                                </ul>
                                <div class="fc-back-hint">Cliquer pour retourner ↺</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ── Groupe 3 : Grand Aveyron (40–80 km) ── -->
            <div>
                <div class="dist-cat-label">Grand Aveyron · 40 – 80 km</div>
                <div class="dist-cards-row">

                    <div class="flip-card" onclick="flipExclusive(this)">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="fc-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2v20"/><path d="M7 7h10"/><path d="M7 17h10"/></svg>
                                </div>
                                <div>
                                    <div class="fc-name">Conques ★</div>
                                    <div class="fc-km">55 km · 50 min</div>
                                </div>
                            </div>
                            <div class="flip-card-back">
                                <ul class="fc-points">
                                    <li>Abbatiale Sainte-Foy (romane, XIe)</li>
                                    <li>Trésor médiéval exceptionnel</li>
                                    <li>Concerts classiques en abbatiale</li>
                                    <li>Plus Beau Village de France</li>
                                </ul>
                                <div class="fc-back-hint">Cliquer pour retourner ↺</div>
                            </div>
                        </div>
                    </div>

                    <div class="flip-card" onclick="flipExclusive(this)">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="fc-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>
                                </div>
                                <div>
                                    <div class="fc-name">Musée Soulages · Rodez</div>
                                    <div class="fc-km">50 km · 45 min</div>
                                </div>
                            </div>
                            <div class="flip-card-back">
                                <ul class="fc-points">
                                    <li>Collection Pierre Soulages</li>
                                    <li>Cathédrale Notre-Dame de Rodez</li>
                                    <li>Musée Fenaille (mégalithes)</li>
                                    <li>Marchés &amp; vieille ville</li>
                                </ul>
                                <div class="fc-back-hint">Cliquer pour retourner ↺</div>
                            </div>
                        </div>
                    </div>

                    <div class="flip-card" onclick="flipExclusive(this)">
                        <div class="flip-card-inner">
                            <div class="flip-card-front">
                                <div class="fc-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 10v12"/><path d="M20 10v12"/><path d="M2 10h20"/><path d="M4 4v6"/><path d="M20 4v6"/></svg>
                                </div>
                                <div>
                                    <div class="fc-name">Viaduc de Millau</div>
                                    <div class="fc-km">75 km · 1h</div>
                                </div>
                            </div>
                            <div class="flip-card-back">
                                <ul class="fc-points">
                                    <li>Viaduc le plus haut du monde</li>
                                    <li>Gorges du Tarn &amp; randonnées</li>
                                    <li>Parapente &amp; sports aériens</li>
                                    <li>Canoë-kayak en gorges</li>
                                </ul>
                                <div class="fc-back-hint">Cliquer pour retourner ↺</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div><!-- /dist-categories -->
    </details>
</section>


<!-- ══════════════════════════════════════════════════════════════════════
     COUPS DE CŒUR & INCONTOURNABLES
     Sélection curated — 4 catégories issues des fichiers CSV
     Tables / Culture / Nature / Marchés
     ══════════════════════════════════════════════════════════════════════ -->
<section class="coups-section">

    <div class="section-header">
        <span class="subtitle">Sélection Bellevue d'Aveyron</span>
        <h2>Nos Coups de Cœur &amp; Incontournables</h2>
        <p class="section-intro">
            Des adresses durables et des expériences uniques, soigneusement sélectionnées autour du gîte.
            Du buron perché sur l'Aubrac à l'abbatiale de Conques qui résonne de musique l'été,
            voici ce que nous aimons faire découvrir à nos hôtes.
        </p>
    </div>

    <!-- Onglets catégories -->
    <div class="coups-tabs" role="tablist">
        <button class="coups-tab-btn active" data-target="coups-tables" role="tab">
            <span class="tab-em">🍽</span> Tables &amp; Saveurs
        </button>
        <button class="coups-tab-btn" data-target="coups-culture" role="tab">
            <span class="tab-em">🎶</span> Culture &amp; Concerts
        </button>
        <button class="coups-tab-btn" data-target="coups-nature" role="tab">
            <span class="tab-em">🌿</span> Nature &amp; Grand Air
        </button>
        <button class="coups-tab-btn" data-target="coups-artisanat" role="tab">
            <span class="tab-em">🛒</span> Marchés &amp; Artisanat
        </button>
        <button class="coups-tab-btn" data-target="coups-jeunesse" role="tab">
            <span class="tab-em">🎉</span> Jeunesse &amp; Famille
        </button>
    </div>

    <!-- ══ TABLES & SAVEURS ══ -->
    <div class="coups-panel active" id="coups-tables">
        <div class="coups-grid">

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('au-moulin-d-alexandre'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Dans le village</span>
                        <span class="coup-badge">Restaurant</span>
                    </div>
                    <h3>Au Moulin d'Alexandre</h3>
                    <p>Adresse incontournable de Sainte-Eulalie-d'Olt : terrasse agréable, formule du marché et spécialités du terroir aveyronnais. La table de proximité par excellence pour nos hôtes, à quelques minutes à pied du gîte.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Terrasse</span>
                        <span class="coup-tag">Terroir</span>
                        <span class="coup-tag">Proximité</span>
                    </div>
                    <div class="coup-season">À découvrir sur place — vérifier les horaires en saison</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('maison-de-severac'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">~40 km</span>
                        <span class="coup-badge">Gastronomique</span>
                    </div>
                    <h3>Maison de Sévérac</h3>
                    <p>Détour gastronomique très qualitatif à Sévérac d'Aveyron. Cadre intimiste, cuisine créative ancrée dans le terroir, lien fort avec la culture régionale — une recommandation premium pour nos hôtes en quête d'une table d'exception.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Gastronomique</span>
                        <span class="coup-tag">Intimiste</span>
                        <span class="coup-tag">Réservation</span>
                    </div>
                    <div class="coup-season">Ouvert toute l'année — réservation conseillée</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('les-burons-de-l-aubrac'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">25–35 km</span>
                        <span class="coup-badge">Tradition Aubrac</span>
                    </div>
                    <h3>Les Burons de l'Aubrac</h3>
                    <p>Sur le plateau, d'anciennes cabanes de bergers réhabilitées en restaurants d'altitude servent l'aligot comme au premier jour — tomme fraîche, beurre, ail — devant un panorama à couper le souffle. Buron de Camejane, Buron de la Trémouille…</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Aligot</span>
                        <span class="coup-tag">Altitude</span>
                        <span class="coup-tag">Estival</span>
                    </div>
                    <div class="coup-season">Mai à septembre selon les burons</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('restaurant-bras-laguiole'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">30 km</span>
                        <span class="coup-badge">★★★ Michelin</span>
                    </div>
                    <h3>Restaurant Bras — Laguiole</h3>
                    <p>Sébastien Bras a inscrit Laguiole sur la carte gastronomique mondiale depuis le Suquet, bâtisse suspendue au-dessus de l'Aubrac. Cuisine végétale et radicale, vue panoramique sublime — une expérience à vivre au moins une fois dans sa vie.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Gastronomique</span>
                        <span class="coup-tag">Vue panoramique</span>
                        <span class="coup-tag">Réservation</span>
                    </div>
                    <div class="coup-season">Avril à novembre — réservation indispensable</div>
                </div>
            </div>

        </div>
    </div>

    <!-- ══ CULTURE & CONCERTS ══ -->
    <div class="coups-panel" id="coups-culture">
        <div class="coups-grid">

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('festival-en-vallee-d-olt'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Dans la vallée</span>
                        <span class="coup-badge">Festival Été</span>
                    </div>
                    <h3>Festival en Vallée d'Olt</h3>
                    <p>Événement culturel fort de la région : ce festival de musique de chambre résonne à Sainte-Eulalie-d'Olt et Saint-Geniez-d'Olt tout l'été. Des concerts d'une beauté rare dans des cadres patrimoniaux uniques — programme annoncé à deux pas du gîte.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Musique de chambre</span>
                        <span class="coup-tag">Vallée du Lot</span>
                        <span class="coup-tag">Été</span>
                    </div>
                    <div class="coup-season">Saisonnier · Programme estival — Région Occitanie</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('eulalie-d-art'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Dans le village</span>
                        <span class="coup-badge">Pôle artistique</span>
                    </div>
                    <h3>Eulalie d'Art</h3>
                    <p>Dans une ancienne grange du XVIIe siècle, six ateliers-boutiques d'artisans d'art ouvrent leurs portes en visite libre et gratuite. Céramique, textile, bijou, sculpture… Un condensé de créativité locale à découvrir à pied depuis le gîte.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Gratuit</span>
                        <span class="coup-tag">Artisans d'art</span>
                        <span class="coup-tag">Visite libre</span>
                    </div>
                    <div class="coup-season">Permanent · Point accueil tourisme sur place</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('musee-marcel-boudou-et-expos-estivales'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Dans le village</span>
                        <span class="coup-badge">Musée · Expo</span>
                    </div>
                    <h3>Musée Marcel Boudou &amp; Expos Estivales</h3>
                    <p>Ce musée abrite 45 toiles de l'artiste local Marcel Boudou, peintre attaché à la Vallée du Lot. L'été, l'association prolonge l'expérience avec des expositions de rue dans les ruelles médiévales du village — une invitation à l'art en plein air.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Art local</span>
                        <span class="coup-tag">Expo de rue</span>
                        <span class="coup-tag">Patrimoine</span>
                    </div>
                    <div class="coup-season">Permanent + expositions estivales en juillet-août</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('maison-de-severac-art-et-gastronomie'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">~40 km</span>
                        <span class="coup-badge">Culture &amp; Musique</span>
                    </div>
                    <h3>Maison de Sévérac — Art &amp; Gastronomie</h3>
                    <p>Lieu hybride et singulier à Sévérac d'Aveyron, mêlant gastronomie raffinée, événements musicaux et mise en lumière d'artistes régionaux. Un endroit qui incarne l'esprit créatif de l'Aveyron contemporain.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Concerts</span>
                        <span class="coup-tag">Artistes locaux</span>
                        <span class="coup-tag">Gastronomie</span>
                    </div>
                    <div class="coup-season">Permanent · Programmation événementielle variable</div>
                </div>
            </div>

        </div>
    </div>

    <!-- ══ NATURE & GRAND AIR ══ -->
    <div class="coups-panel" id="coups-nature">
        <div class="coups-grid">

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('avenga-canoe-kayak-sur-le-lot'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">2 km</span>
                        <span class="coup-badge">Activité nautique</span>
                    </div>
                    <h3>Avenga — Canoë-Kayak sur le Lot</h3>
                    <p>Balades en canoë-kayak sur le Lot au départ de Saint-Geniez-d'Olt — une activité douce et ressourçante dans un cadre naturel préservé. Idéal pour découvrir les berges à son propre rythme, entre falaises boisées et villages endormis.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Kayak</span>
                        <span class="coup-tag">Rivière Lot</span>
                        <span class="coup-tag">Tout niveau</span>
                    </div>
                    <div class="coup-season">Saisonnier — printemps à automne</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('o-paddle-d-olt'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Dans le village</span>
                        <span class="coup-badge">Base nautique</span>
                    </div>
                    <h3>O'Paddle d'Olt</h3>
                    <p>Directement sur les bords du Lot à Sainte-Eulalie-d'Olt, cette base nautique propose des sessions paddle pour tous les niveaux. Activité premium-friendly dans un cadre idyllique, à quelques minutes à pied du gîte.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Paddle</span>
                        <span class="coup-tag">Bords du Lot</span>
                        <span class="coup-tag">Détente</span>
                    </div>
                    <div class="coup-season">Saisonnier — renseignements sur place</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('balades-au-bord-du-lot-et-village'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">0 km</span>
                        <span class="coup-badge">Promenade</span>
                    </div>
                    <h3>Balades au Bord du Lot &amp; Village</h3>
                    <p>Sainte-Eulalie-d'Olt est un joyau médiéval que l'on explore à pied en quelques heures. Les chemins de bord de Lot offrent une balade douce et apaisante, ponctuée de points de vue sur les méandres dorés de la rivière.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Gratuit</span>
                        <span class="coup-tag">Village médiéval</span>
                        <span class="coup-tag">Famille</span>
                    </div>
                    <div class="coup-season">Permanent — toute l'année</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('l-aubrac-grands-espaces-et-faune'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">25 km</span>
                        <span class="coup-badge">Nature sauvage</span>
                    </div>
                    <h3>L'Aubrac — Grands Espaces &amp; Faune</h3>
                    <p>L'un des derniers espaces sauvages du Massif Central. Vautours fauves en vol, troupeaux de bovins Aubrac, flore endémique… En hiver, les paysages prennent une majesté nordique. Un plateau à couper le souffle, à 30 minutes du gîte.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Vautours</span>
                        <span class="coup-tag">Flore rare</span>
                        <span class="coup-tag">Panoramas</span>
                    </div>
                    <div class="coup-season">Toute l'année · Splendide au lever du soleil</div>
                </div>
            </div>

        </div>
    </div>

    <!-- ══ MARCHÉS & ARTISANAT ══ -->
    <div class="coups-panel" id="coups-artisanat">
        <div class="coups-grid">

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('marche-estival-de-sainte-eulalie-d-olt'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Dans le village</span>
                        <span class="coup-badge">Marché paysan</span>
                    </div>
                    <h3>Marché Estival de Sainte-Eulalie-d'Olt</h3>
                    <p>Chaque lundi matin en été, la place de l'église s'anime avec les producteurs locaux : légumes de saison, miel du terroir, fromages, conserves artisanales… Un rendez-vous convivial organisé par la mairie, à deux pas du gîte.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Chaque lundi</span>
                        <span class="coup-tag">Producteurs</span>
                        <span class="coup-tag">Été</span>
                    </div>
                    <div class="coup-season">Saisonnier — lundi matin en juillet-août</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('de-faire-et-de-savoir'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Dans le village</span>
                        <span class="coup-badge">Boutique collective</span>
                    </div>
                    <h3>De Faire et de Savoir</h3>
                    <p>Boutique collective réunissant artisans et producteurs locaux de la vallée. Un lieu idéal pour rapporter de beaux souvenirs qualitatifs — créations artisanales, produits du terroir aveyronnais — directement issus des savoir-faire du territoire.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Artisanat local</span>
                        <span class="coup-tag">Cadeaux</span>
                        <span class="coup-tag">Terroir</span>
                    </div>
                    <div class="coup-season">Permanent / à confirmer selon saison</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('eulalie-d-art-ateliers-et-creations'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Dans le village</span>
                        <span class="coup-badge">Ateliers d'art</span>
                    </div>
                    <h3>Eulalie d'Art — Ateliers &amp; Créations</h3>
                    <p>Six ateliers-boutiques d'artisans d'art dans une grange du XVIIe siècle — potiers, bijoutiers, tisserands… Visite libre et gratuite avec point accueil tourisme. Un lieu rare qui ancre fortement l'image artisanale de Sainte-Eulalie-d'Olt.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Gratuit</span>
                        <span class="coup-tag">6 ateliers</span>
                        <span class="coup-tag">Artisanat d'art</span>
                    </div>
                    <div class="coup-season">Permanent — toute l'année</div>
                </div>
            </div>

            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('marches-de-saint-geniez-d-olt'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">2 km</span>
                        <span class="coup-badge">Marché traditionnel</span>
                    </div>
                    <h3>Marchés de Saint-Geniez-d'Olt</h3>
                    <p>Deux rendez-vous complémentaires à 2 km : le marché traditionnel du samedi matin et le marché des Producteurs de Pays en saison. Fromageries, maraîchers, charcutiers — l'immersion parfaite dans la vie du territoire.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Samedi matin</span>
                        <span class="coup-tag">Producteurs</span>
                        <span class="coup-tag">Local</span>
                    </div>
                    <div class="coup-season">Régulier — renforcé en été</div>
                </div>
            </div>

        </div>
    </div>

    <!-- ══ JEUNESSE & FAMILLE ══ -->
    <div class="coups-panel" id="coups-jeunesse">
        <div class="coups-grid">

            <!-- 1. Espace jeux du gîte -->
            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('ping-pong-trampoline-velos-et-piscine'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Sur place</span>
                        <span class="coup-badge">Au gîte</span>
                    </div>
                    <h3>Ping-Pong, Trampoline, Vélos &amp; Piscine</h3>
                    <p>Le paradis des petits (et des grands) sans quitter le domaine : table de ping-pong pour les joutes familiales, trampoline pour les sauts sans fin, 11 vélos adultes et enfants pour explorer les chemins alentour, et la piscine chauffée de 4×8 m pour les baignades en toute liberté. Un parc de 5 000 m² qui fait le bonheur de tous dès le lever du soleil.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Inclus</span>
                        <span class="coup-tag">Tous âges</span>
                        <span class="coup-tag">Piscine chauffée</span>
                    </div>
                    <div class="coup-season">Permanent · Piscine de mai à septembre</div>
                </div>
            </div>

            <!-- 2. Jardin des bêtes -->
            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('jardin-des-betes'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">À proximité</span>
                        <span class="coup-badge">Ferme &amp; Jeux</span>
                    </div>
                    <h3>Jardin des Bêtes</h3>
                    <p>Un lieu ludique et attendrissant pensé pour les plus jeunes : rencontres avec les animaux de la ferme, jeux d'attractions originaux et animations en plein air. Un vrai moment de complicité familiale, parfait pour faire découvrir la nature aveyronnaise aux enfants de façon douce et joyeuse.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Animaux</span>
                        <span class="coup-tag">Jeux</span>
                        <span class="coup-tag">Enfants</span>
                    </div>
                    <div class="coup-season">Saisonnier — vérifier les horaires</div>
                </div>
            </div>

            <!-- 3. O'Paddle d'Olt -->
            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('o-paddle-d-olt-canoe-kayak-et-sup'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">~2 km</span>
                        <span class="coup-badge">Nautique</span>
                    </div>
                    <h3>O'Paddle d'Olt — Canoë, Kayak &amp; SUP</h3>
                    <p>Glissez sur les eaux émeraude du Lot en famille ! La base nautique O'Paddle d'Olt propose des sorties en canoë, kayak et Stand Up Paddle accessibles à tous niveaux. Les enfants adorent. Les parents aussi. Le courant doux de la rivière et les rives boisées font de chaque sortie un souvenir inoubliable.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Canoë</span>
                        <span class="coup-tag">Kayak</span>
                        <span class="coup-tag">Stand Up Paddle</span>
                    </div>
                    <div class="coup-season">Saisonnier · Printemps–Été</div>
                </div>
            </div>

            <!-- 4. Maison de la Chouette -->
            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('maison-de-la-chouette-sainte-eulalie-d-o'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Dans le village</span>
                        <span class="coup-badge">Sensibilisation nature</span>
                    </div>
                    <h3>Maison de la Chouette — Sainte-Eulalie-d'Olt</h3>
                    <p>À deux pas du gîte, la Maison de la Chouette éveille la curiosité des enfants pour la faune nocturne et les oiseaux rapaces de l'Aveyron. Expositions interactives, découverte en douceur des espèces locales et ateliers pédagogiques pour les petits naturalistes en herbe — une visite qui réconcilie les enfants avec la nature sauvage.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Pédagogique</span>
                        <span class="coup-tag">Rapaces</span>
                        <span class="coup-tag">Gratuit / Entrée libre</span>
                    </div>
                    <div class="coup-season">Permanent · Toute l'année</div>
                </div>
            </div>

            <!-- 5. Air Globe Fun e-bike -->
            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('air-globe-fun-e-bike'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">Région</span>
                        <span class="coup-badge">Vélo électrique</span>
                    </div>
                    <h3>Air Globe — Fun e-Bike</h3>
                    <p>L'aventure à portée de pédale ! Air Globe propose des sorties guidées et des locations de vélos électriques fun pour explorer la vallée du Lot et les chemins de l'Aubrac sans effort — et avec beaucoup de sourires. Idéal pour les familles souhaitant couvrir plus de terrain, des plus petits (vélos adaptés) aux ados en quête de sensations.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">E-bike</span>
                        <span class="coup-tag">Famille</span>
                        <span class="coup-tag">Balades guidées</span>
                    </div>
                    <div class="coup-season">Saisonnier · Printemps–Automne</div>
                </div>
            </div>

            <!-- 6. Bozouls et son Canyon -->
            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('bozouls-et-son-canyon-emblematique'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">~35 km</span>
                        <span class="coup-badge">Site naturel</span>
                    </div>
                    <h3>Bozouls &amp; son Canyon Emblématique</h3>
                    <p>Le "trou de Bozouls" est l'une des curiosités géologiques les plus spectaculaires d'Aveyron : un canyon circulaire de 400 m de diamètre creusé par la rivière Dourdou sur des milliers d'années. Le village médiéval, perché en presqu'île au-dessus du gouffre, fascine petits et grands. L'accès au belvédère est libre et les vues sont à couper le souffle.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Géologie</span>
                        <span class="coup-tag">Spectaculaire</span>
                        <span class="coup-tag">Accès libre</span>
                    </div>
                    <div class="coup-season">Permanent · Toute l'année</div>
                </div>
            </div>

            <!-- 7. Les Secrets du Trésor de Conques -->
            <div class="coup-card">
                <div class="coup-img-wrap">
                    <?php media_card('les-secrets-du-tresor-de-conques'); ?>
                </div>
                <div class="coup-body">
                    <div class="coup-meta">
                        <span class="coup-distance">55 km</span>
                        <span class="coup-badge">Visite guidée</span>
                    </div>
                    <h3>Les Secrets du Trésor de Conques</h3>
                    <p>Une expérience hors du commun pour petits et grands : des visites guidées ponctuelles qui révèlent les mystères du trésor médiéval de Conques — l'un des plus riches d'Europe — à travers un récit vivant, ludique et fascinant. Les enfants deviennent de véritables explorateurs du Moyen Âge le temps d'une visite inoubliable sous les voûtes de l'abbatiale Sainte-Foy.</p>
                    <div class="coup-tags">
                        <span class="coup-tag">Visites guidées</span>
                        <span class="coup-tag">Moyen Âge</span>
                        <span class="coup-tag">Conques</span>
                    </div>
                    <div class="coup-season">Ponctuel · Renseignements sur place</div>
                </div>
            </div>

        </div>
    </div>

</section>


<!-- ══════════════════════════════════════════════════════════════════════
     SECTION AGENDA — Widget HIT Aveyron via iframes Laetis
     Point de référence unique : SEO_LAT / SEO_LNG (config/seo.php)
     ══════════════════════════════════════════════════════════════════════ -->
<section class="agenda-section" id="agenda">

    <div class="agenda-header-row">
        <div class="section-header">
            <span class="subtitle">Données officielles HIT Aveyron — Triées par proximité</span>
            <h2>Agenda &amp; Bons Plans autour du Gîte</h2>
        </div>
        <p class="agenda-intro-txt">
            Les événements et activités sont affichés en commençant par les plus proches de Sainte-Eulalie-d'Olt,
            puis en s'élargissant progressivement vers Estaing, l'Aubrac, Conques, Rodez et Millau.
            Changez d'onglet pour explorer chaque territoire.
        </p>

        <div class="agenda-tabs" role="tablist" aria-label="Territoires">
            <button class="tab-btn active" role="tab" aria-selected="true"
                    data-tab="autour" onclick="activateTab('autour', this)">
                📍 Autour du Gîte
            </button>
            <button class="tab-btn" role="tab" aria-selected="false"
                    data-tab="activites" onclick="activateTab('activites', this)">
                Activités &amp; Loisirs
            </button>
            <button class="tab-btn" role="tab" aria-selected="false"
                    data-tab="conques" onclick="activateTab('conques', this)">
                Conques &amp; Patrimoine
            </button>
            <button class="tab-btn" role="tab" aria-selected="false"
                    data-tab="restaurants" onclick="activateTab('restaurants', this)">
                Tables &amp; Gastronomie
            </button>
            <button class="tab-btn" role="tab" aria-selected="false"
                    data-tab="rodez" onclick="activateTab('rodez', this)">
                Rodez &amp; Tout l'Aveyron
            </button>
        </div>
    </div>

    <div class="widget-zone">

        <div class="tab-panel active" id="panel-autour" role="tabpanel">
            <?php if (!empty($agenda_proches)): ?>
            <div class="proximite-grid">
                <?php foreach ($agenda_proches as $ev): ?>
                <article class="proximite-card">
                    <div class="proximite-head">
                        <span class="proximite-km"><?php echo seo_e(agenda_distance_label($ev['distance_km'])); ?></span>
                        <span class="proximite-quand"><?php echo seo_e($ev['quand']); ?></span>
                    </div>
                    <h3><?php echo seo_e($ev['titre']); ?></h3>
                    <p class="proximite-lieu"><?php echo seo_e($ev['commune']); ?></p>
                    <p class="proximite-desc"><?php echo seo_e($ev['description']); ?></p>
                </article>
                <?php endforeach; ?>
            </div>
            <p class="proximite-note">
                Rendez-vous situés à moins de 35 km du gîte, du plus proche au plus lointain.
                Horaires et dates à confirmer auprès des organisateurs.
            </p>
            <?php endif; ?>

            <div class="iframe-wrapper">
                <div class="iframe-skeleton" id="skel-autour"></div>
                <iframe title="Agenda officiel HIT Aveyron — vallée du Lot"
                    src="https://widget.laetis.fr/tourisme-aveyron/wagenda?ordre=proximite&lat=<?php echo SEO_LAT; ?>&lng=<?php echo SEO_LNG; ?>&auto=0&nb=12&bgc=%23FFFFFF&txtc=%23050914&thc=%23A07828&mode=diaporama"
                    loading="lazy" onload="hideSkeleton('skel-autour')" allowfullscreen></iframe>
            </div>
        </div>

        <div class="tab-panel" id="panel-activites" role="tabpanel">
            <div class="iframe-wrapper">
                <div class="iframe-skeleton" id="skel-activites"></div>
                <iframe title="Activités et loisirs en Aubrac et Vallée du Lot"
                    src="https://widget.laetis.fr/tourisme-aveyron/wactivites-loisirs?sem_local=aubrac&ordre=proximite&lat=<?php echo SEO_LAT; ?>&lng=<?php echo SEO_LNG; ?>&auto=0&nb=12&bgc=%23FFFFFF&txtc=%23050914&thc=%23A07828&mode=diaporama"
                    loading="lazy" onload="hideSkeleton('skel-activites')" allowfullscreen></iframe>
            </div>
        </div>

        <div class="tab-panel" id="panel-conques" role="tabpanel">
            <div class="iframe-wrapper">
                <div class="iframe-skeleton" id="skel-conques"></div>
                <iframe title="À découvrir autour de Conques-en-Rouergue"
                    src="https://widget.laetis.fr/tourisme-aveyron/wactivites-loisirs?sem_local=conques&ordre=proximite&lat=44.2880&lng=2.3970&auto=0&nb=12&bgc=%23FFFFFF&txtc=%23050914&thc=%23A07828&mode=diaporama"
                    loading="lazy" onload="hideSkeleton('skel-conques')" allowfullscreen></iframe>
            </div>
        </div>

        <div class="tab-panel" id="panel-restaurants" role="tabpanel">
            <div class="iframe-wrapper">
                <div class="iframe-skeleton" id="skel-restaurants"></div>
                <iframe title="Restaurants et gastronomie — Vallée du Lot et Aubrac"
                    src="https://widget.laetis.fr/tourisme-aveyron/wrestaurants?sem_local=aubrac&ordre=proximite&lat=<?php echo SEO_LAT; ?>&lng=<?php echo SEO_LNG; ?>&auto=0&nb=12&bgc=%23FFFFFF&txtc=%23050914&thc=%23A07828&mode=diaporama"
                    loading="lazy" onload="hideSkeleton('skel-restaurants')" allowfullscreen></iframe>
            </div>
        </div>

        <div class="tab-panel" id="panel-rodez" role="tabpanel">
            <div class="iframe-wrapper">
                <div class="iframe-skeleton" id="skel-rodez"></div>
                <iframe title="Découvrir Rodez et tout l'Aveyron"
                    src="https://widget.laetis.fr/tourisme-aveyron/wactivites-loisirs?auto=0&nb=12&bgc=%23FFFFFF&txtc=%23050914&thc=%23A07828&mode=diaporama"
                    loading="lazy" onload="hideSkeleton('skel-rodez')" allowfullscreen></iframe>
            </div>
        </div>

    </div>

    <p style="text-align:center; margin-top:28px; font-family:'Montserrat',sans-serif; font-size:.82rem; color:#999;">
        Données officielles HIT Aveyron ·
        <a href="https://www.tourisme-aveyron.com/fr/evenements/agenda-aveyron"
           target="_blank" rel="noopener" style="color:var(--gold-dark);">Voir tout l'agenda Aveyron</a>
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
    <a href="index.php#reservation" class="btn-gold">Vérifier les disponibilités</a>
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
                <a href="https://www.instagram.com/gitebellevuedaveyron/" target="_blank" rel="noopener" class="social-link">Instagram</a>
                <a href="https://www.facebook.com/gitebellevuedaveyron" target="_blank" rel="noopener" class="social-link">Facebook</a>
            </div>
        </div>
        <div class="footer-col links-col">
            <h3>Explorer</h3>
            <ul>
                <li><a href="index.php#experience">La Villa &amp; L'Histoire</a></li>
                <li><a href="index.php#services">Les Services 5★</a></li>
                <li><a href="index.php#tarifs">Nos Tarifs</a></li>
                <li><a href="index.php#temoignages">Livre d'Or</a></li>
                <li><a href="decouvrir.php">La Région</a></li>
            </ul>
        </div>
        <div class="footer-col contact-col">
            <h3>Nous Trouver</h3>
            <ul class="contact-list">
                <li><span class="icon">📍</span><span>12130 Sainte-Eulalie-d'Olt, Aveyron</span></li>
                <li><span class="icon">📞</span><a href="tel:+33680907107">06 80 90 71 07</a></li>
            </ul>
            <a href="index.php#reservation" class="btn-footer">Réserver maintenant</a>
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


<!-- Script page — chargé en fin de body, après le DOM -->
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

    <script src="js/script.js"></script>
    <script>
    // Extension du hash mapping pour l'onglet Jeunesse
    (function() {
        var hash = window.location.hash;
        if (hash === '#coups-jeunesse') {
            setTimeout(function() {
                var el = document.getElementById('coups-jeunesse');
                if (el) {
                    document.querySelectorAll('.coups-tab-btn').forEach(function(b){ b.classList.remove('active'); });
                    document.querySelectorAll('.coups-panel').forEach(function(p){ p.classList.remove('active'); });
                    var btn = document.querySelector('.coups-tab-btn[data-target="coups-jeunesse"]');
                    if (btn) btn.classList.add('active');
                    el.classList.add('active');
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 400);
        }
    })();
    </script>
<?php
// ── Données structurées : distances et attractions, matière première des
//    réponses générées (« que visiter près de Sainte-Eulalie-d'Olt ? »). ──
seo_jsonld([
    seo_node_website(),
    seo_node_webpage('decouvrir.php', "Découvrir l'Aveyron depuis Bellevue d'Aveyron",
        "Distances routières, sites incontournables, activités et agenda local autour de Sainte-Eulalie-d'Olt."),
    seo_node_breadcrumb([['Accueil', ''], ['Découvrir la région', 'decouvrir.php']]),
    seo_node_itemlist('decouvrir.php#distances', "Sites et villages accessibles depuis Bellevue d'Aveyron", [
        ["Saint-Geniez-d'Olt", "À 2 km (5 min) du gîte : baignade et kayak sur le Lot, marché du jeudi matin, halles médiévales."],
        ["Lac de Castelnau", "À 2 km (5 min) : base nautique kayak et paddle, plage aménagée, pêche et sentiers."],
        ["Saint-Côme-d'Olt", "À 5 km (8 min) : village classé parmi les Plus Beaux Villages de France, sur le chemin de Saint-Jacques."],
        ["Espalion", "À 12 km (15 min) : Pont Vieux, bords du Lot et marchés."],
        ["Estaing", "À 20 km (25 min) : village classé et château surplombant le Lot."],
        ["Plateau de l'Aubrac", "À 25 km (30 min) : grands espaces, burons, faune et randonnées."],
        ["Laguiole", "À 30 km (35 min) : coutellerie, aligot et restaurant Bras."],
        ["Musée Soulages, Rodez", "À 50 km (45 min) : collection Pierre Soulages et expositions temporaires."],
        ["Conques", "À 55 km (50 min) : abbatiale Sainte-Foy, tympan roman, trésor et illuminations nocturnes."],
        ["Viaduc de Millau", "À 75 km (1 h) : plus haut pont à haubans du monde, viaduc de Norman Foster."],
    ]),
]);

// Les rendez-vous de proximité, balisés un par un.
if (!empty($agenda_proches)) {
    seo_jsonld(array_map('agenda_node_event', $agenda_proches));
}
?>
</body>
</html>
