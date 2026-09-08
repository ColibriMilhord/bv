<?php require_once __DIR__ . '/config/seo.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php seo_head([
        'title'       => "Politique de confidentialité — Bellevue d'Aveyron",
        'description' => "Données collectées, finalités, durée de conservation, droits RGPD et cookies pour les demandes de réservation du gîte Bellevue d'Aveyron.",
        'path'        => 'politique.php',
        'type'        => 'article',
    ]); ?>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    
    <style>
        /* Mêmes styles que mentions.php pour la cohérence */
        :root {
            --gold-gradient: linear-gradient(135deg, #bf953f 0%, #fcf6ba 40%, #b38728 70%, #fbf5b7 100%);
            --gold-text: #c5a059;
            --navy-deep: #050914;
            --white-soft: #f9f9f9;
        }
        body { margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; background: var(--white-soft); color: #333; line-height: 1.8; }
        header { background: var(--navy-deep); padding: 20px 5%; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(197, 160, 89, 0.3); }
        .logo { font-family: 'Cinzel', serif; font-size: 1.2rem; color: white; text-decoration: none; font-weight: 700; }
        .logo span { color: var(--gold-text); }
        .btn-return { color: var(--gold-text); text-decoration: none; border: 1px solid var(--gold-text); padding: 8px 20px; font-size: 0.8rem; text-transform: uppercase; transition: 0.3s; }
        .btn-return:hover { background: var(--gold-text); color: var(--navy-deep); }
        .legal-container { max-width: 900px; margin: 60px auto; padding: 40px; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-top: 3px solid var(--gold-text); }
        h1 { font-family: 'Cinzel', serif; color: var(--navy-deep); font-size: 2.5rem; margin-bottom: 40px; text-align: center; }
        h2 { font-family: 'Cinzel', serif; color: var(--gold-text); font-size: 1.2rem; margin-top: 30px; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        p { margin-bottom: 15px; font-size: 0.95rem; text-align: justify; }
        footer { background: var(--navy-deep); color: #888; text-align: center; padding: 30px; font-size: 0.8rem; margin-top: 50px; }
        @media (max-width: 768px) { .legal-container { padding: 20px; margin: 30px 5%; } h1 { font-size: 1.8rem; } }
    </style>
</head>
<body>

    <header>
        <a href="index.php?skip=1" class="logo">Bellevue d'Aveyron <span>★</span></a>
        <a href="index.php?skip=1" class="btn-return">← Retour au site</a>
    </header>
    
    <div class="legal-container">
        <h1>Politique de Confidentialité</h1>
        <p style="text-align: center; font-style: italic; color: #666;">Dernière mise à jour : Février 2026</p>

        <h2>1. Collecte des données</h2>
        <p>
            Dans le cadre du formulaire de réservation et de contact présent sur le site <strong>Bellevue d'Aveyron</strong>, nous collectons les informations suivantes :
        </p>
        <ul>
            <li>Nom et Prénom</li>
            <li>Adresse E-mail</li>
            <li>Numéro de téléphone</li>
            <li>Dates de séjour souhaitées</li>
        </ul>

        <h2>2. Utilisation des données</h2>
        <p>
            Les données collectées sont utilisées uniquement pour :
        </p>
        <ul>
            <li>Gérer votre demande de réservation et établir le contrat de location.</li>
            <li>Vous contacter pour répondre à vos questions.</li>
            <li>Respecter nos obligations légales (fiche de police pour les hôtes étrangers, facturation).</li>
        </ul>
        <p><strong>Vos données ne sont jamais vendues, louées ou cédées à des tiers à des fins commerciales.</strong></p>

        <h2>3. Durée de conservation</h2>
        <p>
            Les données liées aux demandes non abouties sont conservées 1 an maximum. Les données liées aux factures et contrats de location sont conservées durant la durée légale obligatoire (10 ans pour les documents comptables).
        </p>

        <h2>4. Vos droits (RGPD)</h2>
        <p>
            Conformément à la réglementation européenne (RGPD), vous disposez d'un droit d'accès, de rectification et de suppression de vos données personnelles. Pour exercer ce droit, il vous suffit de nous contacter par email à : <strong>accueil@bellevuedaveyron.com</strong>.
        </p>

        <h2>5. Cookies</h2>
        <p>
            Ce site utilise uniquement des cookies techniques nécessaires au bon fonctionnement de la navigation (notamment pour la sécurité du formulaire). Aucun cookie publicitaire ou de traçage tiers n'est installé sans votre consentement explicite.
        </p>
    </div>

    <footer>
        &copy; 2026 Bellevue d'Aveyron - Confidentialité & Respect.
    </footer>

<?php
seo_jsonld([
    seo_node_website(),
    seo_node_webpage('politique.php', "Politique de confidentialité", "Données collectées, finalités, durée de conservation, droits RGPD et cookies pour les demandes de réservation du gîte Bellevue d'Aveyron."),
    seo_node_breadcrumb([['Accueil', ''], ["Politique de confidentialité", 'politique.php']]),
]);
?>
</body>
</html>