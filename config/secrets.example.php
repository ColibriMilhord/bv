<?php
/**
 * config/secrets.example.php — Modèle à recopier, JAMAIS de vraie valeur ici.
 *
 * ⚠ Piège classique : une valeur contenant une apostrophe doit être entre
 *   GUILLEMETS DOUBLES, sinon PHP s'arrête et le site renvoie une erreur 500.
 *       'SMTP_FROM_NAME' => 'Bellevue d'Aveyron',   ← CASSE TOUT
 *       'SMTP_FROM_NAME' => "Bellevue d'Aveyron",   ← correct
 *   Et chaque ligne doit se terminer par une virgule.
 *
 * ⚠ Toutes les valeurs doivent être écrites À L'INTÉRIEUR du « return [ … ]; »
 *   ci-dessous. Placées avant ou après, elles ne sont jamais lues : la base et
 *   les e-mails restent alors inaccessibles, sans message d'erreur visible.
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

    // ── Avis Google (facultatif) ──
    // Sans ces deux valeurs, le site affiche la sélection d'avis de
    // config/avis-secours.php. Avec elles, la note, le compteur et les trois
    // derniers avis sont repris automatiquement de Google.
    // Voir docs/seo-ia/avis-google.md
    'GOOGLE_PLACES_API_KEY' => '',
    'GOOGLE_PLACE_ID'       => '',
];
