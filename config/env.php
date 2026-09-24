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

    // 2. Fichier de secrets (chargé une seule fois)
    //
    // Deux emplacements sont consultés, du plus durable au plus proche :
    //
    //   a) un dossier situé HORS de la racine web, à côté d'elle. C'est le seul
    //      endroit qu'un déploiement Git ne touche pas : il remplace le dossier
    //      du site par le contenu du dépôt, et secrets.php n'y figure pas —
    //      volontairement, puisqu'il contient les mots de passe. Résultat, à
    //      chaque mise en ligne le fichier disparaissait et les envois
    //      s'arrêtaient sans bruit.
    //
    //   b) config/secrets.php, l'emplacement historique, qui reste prioritaire
    //      pour que les installations existantes continuent de fonctionner.
    //
    // Les deux sont fusionnés : ce qui manque à l'un est pris à l'autre.
    if ($fichier === null) {
        $fichier = [];

        foreach ([secret_chemin_externe(), __DIR__ . '/secrets.php'] as $chemin) {
            $fichier = secret_charger($chemin) + $fichier;
        }
    }

    if (array_key_exists($cle, $fichier) && $fichier[$cle] !== '') {
        return $fichier[$cle];
    }

    return $defaut;
}

/**
 * Emplacement du fichier de secrets situé hors de la racine web.
 * Par exemple /home/u424962071/domains/bellevuedaveyron.fr/secrets-bellevue.php
 * quand le site est servi depuis .../public_html/.
 */
function secret_chemin_externe(): string
{
    return dirname(dirname(__DIR__)) . '/secrets-bellevue.php';
}

/** Lit un fichier de secrets. Renvoie un tableau, vide en cas de souci. */
function secret_charger(string $chemin): array
{
    if (is_file($chemin)) {
        // Ce fichier est saisi à la main sur le serveur. Une faute de frappe
        // — le grand classique étant une apostrophe dans une chaîne entre
        // apostrophes — ne doit pas faire tomber tout le site avec une
        // erreur 500 muette. On contrôle donc la syntaxe avant d'inclure.
        $syntaxeValide = true;

        if (function_exists('token_get_all') && defined('TOKEN_PARSE')) {
            try {
                token_get_all((string) file_get_contents($chemin), TOKEN_PARSE);
            } catch (Throwable $e) {
                $syntaxeValide = false;
                error_log('[bellevue] ' . basename($chemin) . ' contient une erreur de syntaxe : '
                    . $e->getMessage());
            }
        }

        if ($syntaxeValide) {
            $charge = require $chemin;

            if (is_array($charge) && $charge !== []) {
                return $charge;
            }

            if (is_array($charge)) {
                // Tableau vide : les valeurs ont sans doute été écrites
                // avant ou après le « return [ … ]; », où elles sont ignorées.
                error_log('[bellevue] ' . basename($chemin) . ' ne contient aucune valeur : '
                    . 'vérifier qu\'elles sont bien à l\'intérieur du « return [ … ]; ».');
            } else {
                // Le fichier existe et se lit, mais ne renvoie rien : les
                // valeurs ont été écrites en dehors du « return [ … ]; ».
                // Sans ce message, la panne serait totalement muette.
                error_log('[bellevue] ' . basename($chemin) . ' ne renvoie pas de tableau : '
                    . 'les valeurs doivent être placées à l\'intérieur du « return [ … ]; ».');
            }
        }
    }

    return [];
}

/** Vrai si le secret est renseigné. */
function secret_exists(string $cle): bool
{
    return secret($cle) !== null;
}
