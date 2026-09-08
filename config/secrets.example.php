<?php
/**
 * config/secrets.example.php — Modèle à recopier, JAMAIS de vraie valeur ici.
 *
 * Sur le serveur :
 *   cp config/secrets.example.php config/secrets.php
 *   puis renseigner les valeurs réelles dans config/secrets.php.
 *
 * config/secrets.php est exclu de Git (.gitignore) : il ne quitte jamais le
 * serveur. Les mêmes clés peuvent aussi être définies en variables
 * d'environnement, qui ont la priorité.
 */

return [
    // ── Base de données ──
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'nom_de_la_base',
    'DB_USER' => 'utilisateur',
    'DB_PASS' => 'mot_de_passe',

    // ── Envoi des e-mails (SMTP) ──
    'SMTP_HOST'      => 'smtp.hostinger.com',
    'SMTP_PORT'      => 465,
    'SMTP_USER'      => 'reservation@exemple.fr',
    'SMTP_PASS'      => 'mot_de_passe_de_la_boite',
    'SMTP_FROM'      => 'reservation@exemple.fr',
    'SMTP_FROM_NAME' => "Nom affiché de l'expéditeur",
];
