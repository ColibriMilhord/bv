<?php
/**
 * config/antispam.php — Protection du formulaire de réservation.
 * ---------------------------------------------------------------------------
 * Le formulaire déclenchait deux envois par soumission : un aux propriétaires
 * et un accusé de réception à l'adresse saisie par le visiteur. Sans garde-fou,
 * un robot pouvait donc faire partir des milliers de messages depuis la boîte
 * du gîte, vers des adresses de son choix — ce qui aboutit à la suspension du
 * compte d'envoi.
 *
 * Quatre barrages, du moins gênant au plus strict :
 *
 *   1. Champ piège (honeypot) — invisible pour un humain, rempli par la
 *      plupart des robots. Coût pour le visiteur : nul.
 *   2. Délai minimal — un formulaire soumis en moins de trois secondes n'a pas
 *      été rempli à la main.
 *   3. Limitation par adresse réseau — un même réseau ne peut pas envoyer plus
 *      de quelques demandes par heure.
 *   4. Contrôle du contenu — une demande bourrée de liens n'est pas une
 *      demande de séjour.
 *
 * Aucun captcha : ils dégradent l'expérience et se contournent. Ces quatre
 * mesures arrêtent l'essentiel du trafic automatisé.
 */

require_once __DIR__ . '/stats.php';   // pour stats_ip() et stats_prefixe_ip()

const ANTISPAM_DELAI_MIN   = 3;    // secondes entre l'affichage et l'envoi
const ANTISPAM_MAX_PAR_H   = 3;    // demandes par heure et par réseau
const ANTISPAM_MAX_PAR_JOUR = 8;   // demandes par jour et par réseau
const ANTISPAM_LIENS_MAX   = 2;    // liens tolérés dans le message

/** Crée la table de suivi des envois. Idempotent. */
function antispam_migrer(?PDO $pdo): bool
{
    if (!$pdo) return false;

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS envois_formulaire (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                envoye_le  DATETIME    NOT NULL,
                prefixe_ip VARCHAR(45) NULL,
                accepte    TINYINT(1)  NOT NULL DEFAULT 1,
                motif      VARCHAR(60) NULL,
                campagne   VARCHAR(120) NULL,
                INDEX idx_prefixe (prefixe_ip),
                INDEX idx_date (envoye_le)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        // Colonne ajoutée après coup sur une table déjà en place : elle
        // rattache une demande à l'annonce qui a amené le visiteur.
        try {
            if (!$pdo->query("SHOW COLUMNS FROM envois_formulaire LIKE 'campagne'")->fetch()) {
                $pdo->exec("ALTER TABLE envois_formulaire ADD COLUMN campagne VARCHAR(120) DEFAULT NULL");
            }
        } catch (PDOException $e) {
            // Sans incidence : le suivi par campagne sera simplement vide.
        }

        return true;
    } catch (PDOException $e) {
        error_log('[bellevue] antispam : création de la table impossible — ' . $e->getMessage());
        return false;
    }
}

/** Champs cachés à insérer dans le formulaire. */
function antispam_champs(): string
{
    $jeton = time();

    return '<input type="text" name="site_web" value="" tabindex="-1" autocomplete="off"'
         . ' aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0">'
         . '<input type="hidden" name="ouvert_a" value="' . $jeton . '">';
}

/**
 * Nettoie une valeur destinée à un en-tête d'e-mail ou au sujet.
 * Les retours à la ligne sont ce qui permet d'injecter des destinataires.
 */
function antispam_nettoyer(string $valeur, int $longueur = 120): string
{
    $valeur = str_replace(["\r", "\n", "\0", "%0a", "%0d"], ' ', $valeur);
    $valeur = preg_replace('/\s+/u', ' ', $valeur);

    return mb_substr(trim($valeur), 0, $longueur);
}

/** Nombre de liens contenus dans un texte. */
function antispam_compter_liens(string $texte): int
{
    return preg_match_all('~(https?://|www\.|\[url|<a\s)~i', $texte);
}

/**
 * Vérifie une soumission.
 *
 * @return array{0:bool, 1:string} acceptée ou non, et le motif du refus
 */
function antispam_verifier(?PDO $pdo, array $post): array
{
    // 0. Un administrateur connecté teste son propre formulaire.
    //
    // Les barrages de rythme sont faits pour un visiteur, pas pour le
    // propriétaire qui vérifie que ses envois partent : trois essais et il
    // était bloqué une heure, sans comprendre pourquoi. Il reste soumis au
    // contrôle de l'adresse e-mail — une faute de frappe doit être signalée —
    // mais ni au délai minimal ni au quota. L'exemption demande d'être
    // authentifié dans l'espace d'administration : elle n'ouvre rien.
    $administrateur = !empty($_SESSION['admin_id']);

    // 1. Champ piège : un humain ne le voit pas, donc ne le remplit pas.
    if (trim((string) ($post['site_web'] ?? '')) !== '') {
        return [false, 'champ piège rempli'];
    }

    // 2. Délai de remplissage.
    $ouvert = (int) ($post['ouvert_a'] ?? 0);
    if (!$administrateur && $ouvert > 0 && (time() - $ouvert) < ANTISPAM_DELAI_MIN) {
        return [false, 'formulaire envoyé trop vite'];
    }

    // 3. Adresse e-mail réellement valide — et non simplement nettoyée.
    $email = trim((string) ($post['customer_email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'adresse e-mail invalide'];
    }

    // 4. Contenu : un nom qui contient un lien, ou un message qui en contient
    //    plusieurs, ne relève pas d'une demande de séjour.
    $nom = (string) ($post['customer_name'] ?? '');
    if (antispam_compter_liens($nom) > 0) {
        return [false, 'lien dans le nom'];
    }
    if (antispam_compter_liens((string) ($post['customer_message'] ?? '')) > ANTISPAM_LIENS_MAX) {
        return [false, 'trop de liens dans le message'];
    }
    if (mb_strlen(trim($nom)) < 2) {
        return [false, 'nom trop court'];
    }

    // 5. Fréquence par réseau.
    if ($administrateur) return [true, 'essai administrateur'];

    [$autorise, $motif] = antispam_verifier_frequence($pdo);
    if (!$autorise) return [false, $motif];

    return [true, ''];
}

/** Contrôle du nombre de demandes déjà parties depuis le même réseau. */
function antispam_verifier_frequence(?PDO $pdo): array
{
    if (!$pdo) return [true, ''];

    $prefixe = stats_prefixe_ip(stats_ip());
    if ($prefixe === null) return [true, ''];

    try {
        // Les essais du propriétaire sont exclus du décompte : sans cela,
        // trois vérifications de sa part condamneraient l'heure suivante pour
        // les visiteurs venus du même réseau — la box du gîte, par exemple.
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM envois_formulaire
              WHERE prefixe_ip = ? AND accepte = 1 AND envoye_le >= ?
                AND (motif IS NULL OR motif <> 'essai administrateur')"
        );

        $stmt->execute([$prefixe, date('Y-m-d H:i:s', time() - 3600)]);
        if ((int) $stmt->fetchColumn() >= ANTISPAM_MAX_PAR_H) {
            return [false, 'trop de demandes cette heure'];
        }

        $stmt->execute([$prefixe, date('Y-m-d H:i:s', time() - 86400)]);
        if ((int) $stmt->fetchColumn() >= ANTISPAM_MAX_PAR_JOUR) {
            return [false, 'trop de demandes aujourd\'hui'];
        }
    } catch (Throwable $e) {
        // Table absente : on n'empêche pas une demande légitime de partir.
        return [true, ''];
    }

    return [true, ''];
}

/** Consigne une soumission, acceptée ou refusée. */
function antispam_journaliser(?PDO $pdo, bool $accepte, string $motif = ''): void
{
    if (!$pdo) return;

    try {
        // La campagne d'origine n'est notée que pour une demande acceptée :
        // c'est la seule qui compte pour savoir ce que rapporte une annonce.
        $campagne = $accepte && function_exists('stats_campagne_du_visiteur')
            ? stats_campagne_du_visiteur($pdo)
            : null;

        $pdo->prepare(
            "INSERT INTO envois_formulaire (envoye_le, prefixe_ip, accepte, motif, campagne)
             VALUES (?, ?, ?, ?, ?)"
        )->execute([
            date('Y-m-d H:i:s'),
            stats_prefixe_ip(stats_ip()),
            $accepte ? 1 : 0,
            $motif !== '' ? mb_substr($motif, 0, 60) : null,
            $campagne,
        ]);
    } catch (Throwable $e) {
        // Journalisation défaillante : sans incidence sur l'envoi.
    }
}

/**
 * Message affiché au visiteur en cas de refus.
 * Volontairement vague : décrire le barrage aiderait à le contourner.
 */
function antispam_message_refus(string $motif): string
{
    if (strpos($motif, 'trop de demandes') === 0) {
        return "Vous avez déjà envoyé plusieurs demandes récemment. "
             . "Merci de patienter un moment, ou de nous appeler directement au " . SEO_PHONE_HUMAN . ".";
    }

    if ($motif === 'adresse e-mail invalide') {
        return "L'adresse e-mail saisie ne semble pas valide. Merci de la vérifier.";
    }

    return "Votre demande n'a pas pu être envoyée. "
         . "Merci de réessayer, ou de nous joindre au " . SEO_PHONE_HUMAN . ".";
}

/** Statistiques d'abus, pour l'écran d'administration. */
function antispam_bilan(?PDO $pdo, int $jours = 30): array
{
    $vide = ['acceptes' => 0, 'refuses' => 0, 'motifs' => []];
    if (!$pdo) return $vide;

    try {
        $depuis = date('Y-m-d H:i:s', time() - $jours * 86400);

        $stmt = $pdo->prepare(
            "SELECT accepte, COUNT(*) AS n FROM envois_formulaire
              WHERE envoye_le >= ? GROUP BY accepte"
        );
        $stmt->execute([$depuis]);
        $bilan = $vide;
        foreach ($stmt->fetchAll() as $l) {
            $bilan[$l['accepte'] ? 'acceptes' : 'refuses'] = (int) $l['n'];
        }

        $stmt = $pdo->prepare(
            "SELECT motif, COUNT(*) AS n FROM envois_formulaire
              WHERE envoye_le >= ? AND accepte = 0 AND motif IS NOT NULL
           GROUP BY motif ORDER BY n DESC LIMIT 8"
        );
        $stmt->execute([$depuis]);
        $bilan['motifs'] = $stmt->fetchAll();

        return $bilan;
    } catch (Throwable $e) {
        return $vide;
    }
}
