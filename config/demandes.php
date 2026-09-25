<?php
/**
 * Les demandes du formulaire : les écrire, les relire, les effacer.
 *
 * Une leçon coûteuse est à l'origine de ce fichier. L'enregistrement d'une
 * demande vivait dans index.php, dans un try dont le catch était vide : quand
 * la table n'avait pas la colonne attendue, la demande disparaissait sans un
 * mot, le client voyait « Demande reçue », et personne ne s'en apercevait tant
 * que le courriel, lui, partait encore. Le jour où l'envoi s'est arrêté aussi,
 * il n'est rien resté.
 *
 * Ici, la table se répare d'elle-même, et un échec laisse une trace.
 */

/**
 * Colonnes que le site écrit, avec leur définition MySQL.
 *
 * `notifie` mérite un mot : le témoin de notifications_marquer() ne retient
 * que le dernier envoi, tous clients confondus. Cette colonne-ci reste
 * attachée à chaque demande — des mois plus tard, on sait encore laquelle
 * n'a prévenu personne.
 */
function demandes_colonnes(): array
{
    return [
        'client_message' => 'TEXT DEFAULT NULL',
        'notifie'        => 'TINYINT(1) DEFAULT NULL',
    ];
}

/**
 * Ajoute à `reservations` les colonnes manquantes. Idempotent : une fois la
 * table complète, l'appel ne coûte qu'une lecture du schéma.
 *
 * @return bool vrai si la table est utilisable avec toutes ses colonnes.
 */
function demandes_migrer(?PDO $pdo): bool
{
    if (!$pdo) return false;

    try {
        foreach (demandes_colonnes() as $nom => $definition) {
            $existe = $pdo->query("SHOW COLUMNS FROM reservations LIKE '$nom'")->fetch();
            if (!$existe) {
                $pdo->exec("ALTER TABLE reservations ADD COLUMN $nom $definition");
                error_log("[bellevue] colonne reservations.$nom créée");
            }
        }
        return true;
    } catch (PDOException $e) {
        error_log('[bellevue] migration des demandes : ' . $e->getMessage());
        return false;
    }
}

/**
 * Enregistre une demande et rend son identifiant, ou 0 si rien n'a pu être
 * écrit.
 *
 * Si la table est incomplète, la colonne manquante est créée et l'écriture
 * retentée une fois. Au-delà, l'échec est consigné dans le journal du serveur
 * avec son motif : mieux vaut une ligne à lire qu'une demande évanouie.
 */
function demandes_enregistrer(?PDO $pdo, array $demande): int
{
    if (!$pdo) return 0;

    $ecrire = function () use ($pdo, $demande) {
        $pdo->prepare(
            "INSERT INTO reservations
                (client_nom, client_email, client_tel, date_debut, date_fin,
                 prix_total, acompte_montant, option_menage, client_message,
                 statut, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'attente', ?)"
        )->execute([
            $demande['nom'],
            $demande['email'],
            $demande['telephone'],
            $demande['date_debut'],
            $demande['date_fin'],
            $demande['prix_total'],
            $demande['acompte'],
            $demande['option_menage'],
            $demande['message'],
            $demande['recu_le'],
        ]);
        return (int) $pdo->lastInsertId();
    };

    try {
        return $ecrire();
    } catch (PDOException $e) {
        // 42S22 : colonne inconnue. 42S02 : table inconnue.
        $etats = [(string) $e->getCode()];
        if (isset($e->errorInfo[0])) $etats[] = (string) $e->errorInfo[0];

        if (!array_intersect($etats, ['42S22', '42S02'])) {
            error_log('[bellevue] demande non enregistrée : ' . $e->getMessage());
            return 0;
        }

        if (!demandes_migrer($pdo)) return 0;

        try {
            return $ecrire();
        } catch (PDOException $e2) {
            error_log('[bellevue] demande non enregistrée après réparation : ' . $e2->getMessage());
            return 0;
        }
    }
}

/**
 * Efface une demande. Rend vrai si une ligne a bien disparu.
 *
 * Les dates bloquées par une réservation validée sont libérées du même coup :
 * une demande effacée ne doit pas continuer à fermer le calendrier.
 */
function demandes_supprimer(?PDO $pdo, int $id): bool
{
    if (!$pdo || $id <= 0) return false;

    try {
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        $efface = $stmt->rowCount() > 0;

        try {
            $pdo->prepare("DELETE FROM calendrier_dispo WHERE id_reservation = ?")->execute([$id]);
        } catch (PDOException $e) {
            // Pas de table d'occupation sur cette installation : rien à libérer.
        }

        return $efface;
    } catch (PDOException $e) {
        error_log('[bellevue] suppression de la demande ' . $id . ' : ' . $e->getMessage());
        return false;
    }
}
