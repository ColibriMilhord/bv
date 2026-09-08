<?php
/**
 * config/env.php — Accès aux secrets de l'application.
 * ---------------------------------------------------------------------------
 * Aucun mot de passe ne doit plus apparaître dans un fichier versionné.
 *
 * Trois sources, consultées dans cet ordre :
 *   1. les variables d'environnement du serveur (la méthode la plus sûre :
 *      rien n'est écrit sur le disque) ;
 *   2. le fichier config/secrets.php, exclu de Git par .gitignore ;
 *   3. la valeur par défaut passée à l'appel, sinon null.
 *
 * Mise en place sur un nouvel hébergement :
 *   cp config/secrets.example.php config/secrets.php
 *   puis renseigner les valeurs réelles dans config/secrets.php.
 *
 * Le fichier config/.htaccess interdit par ailleurs tout accès web direct au
 * dossier config/.
 */

/**
 * Valeur d'un secret.
 *
 * @param string $cle     Nom du secret (ex. 'DB_PASS').
 * @param mixed  $defaut  Valeur si le secret est introuvable.
 */
function secret(string $cle, $defaut = null)
{
    static $fichier = null;

    // 1. Variable d'environnement
    $env = getenv($cle);
    if ($env !== false && $env !== '') return $env;
    if (isset($_SERVER[$cle]) && $_SERVER[$cle] !== '') return $_SERVER[$cle];

    // 2. Fichier config/secrets.php (chargé une seule fois)
    if ($fichier === null) {
        $chemin  = __DIR__ . '/secrets.php';
        $charge  = is_file($chemin) ? require $chemin : [];
        $fichier = is_array($charge) ? $charge : [];
    }
    if (array_key_exists($cle, $fichier) && $fichier[$cle] !== '') {
        return $fichier[$cle];
    }

    return $defaut;
}

/** Vrai si le secret est renseigné. */
function secret_exists(string $cle): bool
{
    return secret($cle) !== null;
}
