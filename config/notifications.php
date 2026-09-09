<?php
/**
 * config/notifications.php — Destinataires des demandes envoyées par le
 * formulaire du site.
 * ---------------------------------------------------------------------------
 * La liste est modifiable depuis l'écran d'administration « Paramètres du
 * Gîte » (colonne `emails_destinataires` de la table `gite_settings`).
 *
 * L'expéditeur, lui, ne change jamais : les messages partent toujours de la
 * boîte SMTP `reservation@` (constantes SMTP_FROM / SMTP_USER, définies dans
 * config/mail_config.php). C'est ce compte qui est authentifié auprès du
 * serveur d'envoi ; utiliser une autre adresse ferait classer les messages en
 * indésirables. Seuls les destinataires sont paramétrables.
 *
 * Si la liste est vide, illisible ou si la base est indisponible, on retombe
 * sur les adresses par défaut : une demande de réservation ne doit jamais se
 * perdre faute de configuration.
 */

/** Nombre maximum de destinataires acceptés. */
const NOTIF_MAX_DESTINATAIRES = 10;

/** Adresses utilisées tant qu'aucune liste n'est enregistrée. */
function notifications_defaut(): array
{
    return ['milhord@gmail.com', 'accueil@bellevuedaveyron.com'];
}

/**
 * Transforme une saisie libre en liste d'adresses valides.
 * Accepte les séparateurs usuels : retour à la ligne, virgule, point-virgule,
 * espace. Les doublons et les adresses invalides sont écartés.
 *
 * @return array{0: string[], 1: string[]} [adresses valides, saisies rejetées]
 */
function notifications_parser(string $saisie): array
{
    $morceaux = preg_split('/[\s,;]+/u', trim($saisie), -1, PREG_SPLIT_NO_EMPTY) ?: [];

    $valides = [];
    $rejets  = [];

    foreach ($morceaux as $morceau) {
        $adresse = filter_var($morceau, FILTER_SANITIZE_EMAIL);
        if ($adresse !== '' && filter_var($adresse, FILTER_VALIDATE_EMAIL)) {
            $cle = mb_strtolower($adresse);
            if (!isset($valides[$cle])) $valides[$cle] = $adresse;
        } else {
            $rejets[] = $morceau;
        }
    }

    return [array_slice(array_values($valides), 0, NOTIF_MAX_DESTINATAIRES), $rejets];
}

/**
 * Destinataires effectifs, à partir de la ligne `gite_settings` déjà chargée.
 * Aucune requête supplémentaire n'est faite ici.
 *
 * @param array|null $settings Ligne de gite_settings, ou null si indisponible.
 */
function notifications_destinataires(?array $settings = null): array
{
    $brut = is_array($settings) ? ($settings['emails_destinataires'] ?? '') : '';

    if (is_string($brut) && trim($brut) !== '') {
        list($valides) = notifications_parser($brut);
        if ($valides) return $valides;
    }

    return notifications_defaut();
}

/** Rendu d'une liste pour l'affichage dans un champ de saisie (une par ligne). */
function notifications_format(array $adresses): string
{
    return implode("\n", $adresses);
}

/**
 * Crée la colonne `emails_destinataires` si elle n'existe pas encore.
 * Idempotent : appelé depuis l'écran d'administration, il ne fait rien une
 * fois la colonne en place. Le site public n'exécute jamais cette fonction.
 */
function notifications_migrer(?PDO $pdo): bool
{
    if (!$pdo) return false;

    try {
        $colonne = $pdo->query(
            "SHOW COLUMNS FROM gite_settings LIKE 'emails_destinataires'"
        )->fetch();

        if (!$colonne) {
            $pdo->exec(
                "ALTER TABLE gite_settings ADD COLUMN emails_destinataires TEXT DEFAULT NULL"
            );
        }
        return true;
    } catch (PDOException $e) {
        error_log('[bellevue] migration emails_destinataires : ' . $e->getMessage());
        return false;
    }
}
