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
 * Colonnes qu'une demande d'information laisse vides.
 *
 * Le formulaire accepte deux usages : réserver des dates, ou simplement poser
 * une question. La table, elle, avait été dessinée pour le premier seulement —
 * `date_debut` y était déclarée NOT NULL. Deux demandes d'information ont été
 * refusées par la base avant qu'on s'en aperçoive :
 *
 *     SQLSTATE[23000] : Column 'date_debut' cannot be null
 *
 * Ces colonnes doivent donc accepter l'absence de valeur.
 */
function demandes_colonnes_facultatives(): array
{
    return ['date_debut', 'date_fin', 'prix_total', 'acompte_montant',
            'client_tel', 'client_message', 'option_menage'];
}

/**
 * Met `reservations` en état : colonnes manquantes ajoutées, contraintes
 * NOT NULL levées là où une demande d'information laisse le champ vide.
 *
 * Idempotent : une fois la table en règle, l'appel ne coûte qu'une lecture
 * du schéma.
 *
 * @return bool vrai si la table est utilisable.
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

        // Le type déclaré est relu puis réécrit tel quel : seule la
        // nullabilité change, jamais la nature de la colonne.
        foreach (demandes_colonnes_facultatives() as $nom) {
            $colonne = $pdo->query("SHOW COLUMNS FROM reservations LIKE '$nom'")->fetch();
            if (!$colonne || !isset($colonne['Null'], $colonne['Type'])) continue;
            if (strtoupper((string) $colonne['Null']) !== 'NO') continue;

            $pdo->exec("ALTER TABLE reservations MODIFY `$nom` {$colonne['Type']} NULL DEFAULT NULL");
            error_log("[bellevue] colonne reservations.$nom rendue facultative");
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
        // 23000 : contrainte violée — en pratique, un NOT NULL sur une
        //         colonne qu'une demande d'information laisse vide.
        $etats = [(string) $e->getCode()];
        if (isset($e->errorInfo[0])) $etats[] = (string) $e->errorInfo[0];

        if (!array_intersect($etats, ['42S22', '42S02', '23000'])) {
            error_log('[bellevue] demande non enregistrée : ' . $e->getMessage());
            demandes_consigner_perdue($demande, $e->getMessage());
            return 0;
        }

        if (demandes_migrer($pdo)) {
            try {
                return $ecrire();
            } catch (PDOException $e2) {
                error_log('[bellevue] demande non enregistrée après réparation : ' . $e2->getMessage());
                demandes_consigner_perdue($demande, $e2->getMessage());
                return 0;
            }
        }

        demandes_consigner_perdue($demande, $e->getMessage());
        return 0;
    }
}

/**
 * Dernier filet : quand la base refuse la demande, l'écrire dans un fichier.
 *
 * Deux demandes d'information ont déjà été perdues parce que la base les
 * refusait et que rien d'autre ne les retenait. Une ligne de JSON par demande
 * dans `cache/demandes-perdues.jsonl` coûte peu et garde le nom, l'adresse et
 * le message — de quoi rappeler le client. L'écran des demandes les affiche.
 *
 * Le fichier ne doit jamais être servi par le web : `cache/` est fermé par le
 * .htaccess de la racine.
 */
function demandes_consigner_perdue(array $demande, string $motif): void
{
    $dossier = dirname(__DIR__) . '/cache';
    if (!is_dir($dossier)) @mkdir($dossier, 0755, true);
    if (!is_dir($dossier) || !is_writable($dossier)) {
        error_log('[bellevue] demande perdue, et cache/ non accessible en écriture');
        return;
    }

    $ligne = json_encode(
        ['motif' => $motif, 'quand' => date('c')] + $demande,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($ligne !== false) {
        @file_put_contents($dossier . '/demandes-perdues.jsonl', $ligne . "\n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * Relit les demandes que la base avait refusées, la plus récente en tête.
 * Rend un tableau vide si le fichier n'existe pas — le cas normal.
 */
function demandes_perdues(int $maximum = 50): array
{
    $chemin = dirname(__DIR__) . '/cache/demandes-perdues.jsonl';
    if (!is_readable($chemin)) return [];

    $lignes = @file($chemin, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lignes) return [];

    $demandes = [];
    foreach (array_reverse($lignes) as $ligne) {
        $lue = json_decode($ligne, true);
        if (is_array($lue)) $demandes[] = $lue;
        if (count($demandes) >= $maximum) break;
    }

    return $demandes;
}

/**
 * Vide le fichier des demandes refusées, une fois les clients rappelés.
 *
 * Il contient des coordonnées : le garder au-delà de son utilité n'a pas de
 * raison d'être.
 */
function demandes_perdues_oublier(): bool
{
    $chemin = dirname(__DIR__) . '/cache/demandes-perdues.jsonl';
    return !is_file($chemin) || @unlink($chemin);
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
