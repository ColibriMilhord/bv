<?php require_once __DIR__ . '/config/seo.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php seo_head([
        'title'       => "Mentions légales — Bellevue d'Aveyron, gîte 5 étoiles à Sainte-Eulalie-d'Olt",
        'description' => "Éditeur, propriétaire, SIRET, hébergeur et propriété intellectuelle du site du gîte Bellevue d'Aveyron (Sainte-Eulalie-d'Olt, 12130).",
        'path'        => 'mentions.php',
        'type'        => 'article',
    ]); ?>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    
    <style>
        /* --- RÉCUPÉRATION DE VOTRE CHARTE GRAPHIQUE --- */
        :root {
            --gold-gradient: linear-gradient(135deg, #bf953f 0%, #fcf6ba 40%, #b38728 70%, #fbf5b7 100%);
            --gold-text: #c5a059;
            --navy-deep: #050914;
            --navy-light: #121b33;
            --white-soft: #f9f9f9;
        }
        body { margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; background: var(--white-soft); color: #333; line-height: 1.8; }
        
        /* HEADER SIMPLIFIÉ (Pas de menu, juste le retour) */
        header {
            background: var(--navy-deep);
            padding: 20px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(197, 160, 89, 0.3);
        }
        .logo { font-family: 'Cinzel', serif; font-size: 1.2rem; color: white; text-decoration: none; font-weight: 700; }
        .logo span { color: var(--gold-text); }
        
        .btn-return {
            color: var(--gold-text);
            text-decoration: none;
            border: 1px solid var(--gold-text);
            padding: 8px 20px;
            font-size: 0.8rem;
            text-transform: uppercase;
            transition: 0.3s;
        }
        .btn-return:hover { background: var(--gold-text); color: var(--navy-deep); }

        /* CONTENU LÉGAL */
        .legal-container {
            max-width: 900px;
            margin: 60px auto;
            padding: 40px;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-top: 3px solid var(--gold-text);
        }
        h1 { font-family: 'Cinzel', serif; color: var(--navy-deep); font-size: 2.5rem; margin-bottom: 40px; text-align: center; }
        h2 { font-family: 'Cinzel', serif; color: var(--gold-text); font-size: 1.2rem; margin-top: 30px; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        p { margin-bottom: 15px; font-size: 0.95rem; text-align: justify; }
        
        /* FOOTER (Version Simplifiée pour pages légales) */
        footer { background: var(--navy-deep); color: #888; text-align: center; padding: 30px; font-size: 0.8rem; margin-top: 50px; }
        
        @media (max-width: 768px) {
            .legal-container { padding: 20px; margin: 30px 5%; }
            h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

    <header>
        <a href="index.php?skip=1" class="logo">Bellevue d'Aveyron <span>★</span></a>
        <a href="index.php?skip=1" class="btn-return">← Retour au site</a>
    </header>
    
    <div class="legal-container">
        <h1>Mentions Légales</h1>

        <h2>1. Édition du site</h2>
        <p>
            En vertu de l'article 6 de la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique, il est précisé aux utilisateurs du site internet <strong>https://bellevuedaveyron.fr</strong> l'identité des différents intervenants dans le cadre de sa réalisation et de son suivi :
        </p>
        <p>
            <strong>Propriétaire du site :</strong> BELLEVUE D'AVEYRON, Véronique & Daniel LACAN<br>
            <strong>Adresse :</strong> 12130 Sainte-Eulalie-d'Olt, France<br>
            <strong>Contact :</strong> accueil@bellevuedaveyron.com | 06 80 90 71 07<br>
            <strong>SIRET :</strong> 414 548 776 00023
        </p>

        <h2>2. Hébergement</h2>
        <p>
            Le site est hébergé par :<br>
            <strong>Hostinger International Ltd.</strong><br>
            61 Lordou Vironos Street, 6023 Larnaca, Chypre.<br>
            Site web : https://www.hostinger.fr
        </p>

        <h2>3. Propriété intellectuelle</h2>
        <p>
            Véronique et Daniel LACAN sont propriétaires des droits de propriété intellectuelle et détiennent les droits d’usage sur tous les éléments accessibles sur le site internet, notamment les textes, images, graphismes, logos, vidéos, architecture, icônes et sons.
        </p>
        <p>
            Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite, sauf autorisation écrite préalable.
        </p>

        <h2>4. Limitations de responsabilité</h2>
        <p>
            Bellevue d'Aveyron ne pourra être tenu pour responsable des dommages directs et indirects causés au matériel de l’utilisateur, lors de l’accès au site. Le propriétaire s’engage à sécuriser au mieux le site, cependant sa responsabilité ne pourra être mise en cause si des données indésirables sont importées et installées sur son site à son insu.
        </p>
    </div>

    <footer>
        &copy; 2026 Bellevue d'Aveyron - Tous droits réservés.
    </footer>

<?php
seo_jsonld([
    seo_node_website(),
    seo_node_webpage('mentions.php', "Mentions légales", "Éditeur, propriétaire, SIRET, hébergeur et propriété intellectuelle du site du gîte Bellevue d'Aveyron (Sainte-Eulalie-d'Olt, 12130)."),
    seo_node_breadcrumb([['Accueil', ''], ["Mentions légales", 'mentions.php']]),
]);
?>
</body>
</html>