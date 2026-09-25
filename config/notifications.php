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

// ═══════════════════════════════════════════════════════════════════════════
// Mémoire du dernier envoi
// ───────────────────────────────────────────────────────────────────────────
// Une panne d'envoi est silencieuse par nature : le visiteur voit un message,
// le journal du serveur une ligne que personne ne lit, et les demandes
// s'accumulent sans que le propriétaire soit prévenu. Il a fallu une semaine
// pour s'en apercevoir.
//
// L'issue de chaque notification est donc consignée dans un petit fichier, et
// le tableau de bord affiche un bandeau tant que la dernière a échoué. Un
// fichier plutôt qu'une table : aucune migration, et cela fonctionne même
// quand la base est indisponible.
// ═══════════════════════════════════════════════════════════════════════════

/** Emplacement du témoin. */
function notifications_temoin(): string
{
    return __DIR__ . '/../cache/dernier-envoi.json';
}

/** Consigne l'issue de la dernière notification envoyée aux propriétaires. */
function notifications_marquer(bool $ok, string $detail = ''): void
{
    $chemin = notifications_temoin();
    $dossier = dirname($chemin);

    if (!is_dir($dossier)) @mkdir($dossier, 0755, true);

    @file_put_contents($chemin, json_encode([
        'ok'     => $ok,
        'detail' => mb_substr($detail, 0, 300),
        'quand'  => date('c'),
    ], JSON_UNESCAPED_UNICODE));
}

/**
 * Issue de la dernière notification.
 *
 * @return array{ok:bool, detail:string, quand:string}|null null si rien n'a
 *         encore été tenté, ou si le témoin n'est pas lisible.
 */
function notifications_dernier(): ?array
{
    $chemin = notifications_temoin();
    if (!is_readable($chemin)) return null;

    $donnees = json_decode((string) @file_get_contents($chemin), true);
    if (!is_array($donnees) || !isset($donnees['ok'])) return null;

    return [
        'ok'     => (bool) $donnees['ok'],
        'detail' => (string) ($donnees['detail'] ?? ''),
        'quand'  => (string) ($donnees['quand'] ?? ''),
    ];
}

/** Retient si la demande a été notifiée aux propriétaires. */
function notifications_marquer_demande(?PDO $pdo, int $id, bool $ok): void
{
    if (!$pdo || $id <= 0) return;

    try {
        $pdo->prepare("UPDATE reservations SET notifie = ? WHERE id = ?")
            ->execute([$ok ? 1 : 0, $id]);
    } catch (Throwable $e) {
        // La colonne n'existe pas encore : elle sera créée à la première
        // ouverture de l'écran des demandes, sans rien casser d'ici là.
    }
}
