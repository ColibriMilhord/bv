<?php
/**
 * config/annonces.php — Bandeau d'information publié depuis l'administration.
 * ---------------------------------------------------------------------------
 * Permet d'annoncer aux visiteurs une dernière disponibilité, une promotion
 * sur une période, une fermeture — sans toucher au code.
 *
 * Principes
 * ---------
 * • Une seule annonce active à la fois : un bandeau qui en cumule plusieurs
 *   n'est plus lu. Activer une annonce désactive automatiquement les autres.
 * • Fenêtre d'affichage facultative : l'annonce apparaît et disparaît toute
 *   seule aux dates indiquées, sans intervention.
 * • Le visiteur peut la refermer. Le choix est mémorisé dans son navigateur,
 *   par annonce : publier un nouveau message le fait réapparaître, sans quoi
 *   la fermeture d'un ancien bandeau masquerait aussi le suivant.
 * • Aucun cookie : la fermeture est gardée en stockage local, ce qui ne
 *   déclenche aucune obligation de consentement.
 */

const ANNONCE_TONS = [
    'info'     => 'Information',
    'promo'    => 'Offre',
    'dispo'    => 'Disponibilité',
    'urgent'   => 'À noter',
];

/** Crée la table si besoin. Idempotent, appelé depuis l'administration. */
function annonces_migrer(?PDO $pdo): bool
{
    if (!$pdo) return false;

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS annonces (
                id           INT AUTO_INCREMENT PRIMARY KEY,
                titre        VARCHAR(120)  NOT NULL,
                message      VARCHAR(400)  NOT NULL,
                ton          VARCHAR(12)   NOT NULL DEFAULT 'info',
                lien_url     VARCHAR(255)  NULL,
                lien_libelle VARCHAR(60)   NULL,
                date_debut   DATE          NULL,
                date_fin     DATE          NULL,
                active       TINYINT(1)    NOT NULL DEFAULT 0,
                maj_le       DATETIME      NOT NULL,
                INDEX idx_active (active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        return true;
    } catch (PDOException $e) {
        error_log('[bellevue] annonces : création de la table impossible — ' . $e->getMessage());
        return false;
    }
}

/**
 * Annonce à afficher au visiteur, ou null.
 * Une annonce active mais hors de sa fenêtre de dates n'est pas affichée.
 */
function annonce_active(?PDO $pdo): ?array
{
    if (!$pdo) return null;

    try {
        $stmt = $pdo->prepare(
            "SELECT * FROM annonces
              WHERE active = 1
                AND (date_debut IS NULL OR date_debut <= ?)
                AND (date_fin   IS NULL OR date_fin   >= ?)
           ORDER BY maj_le DESC LIMIT 1"
        );
        $aujourdhui = date('Y-m-d');
        $stmt->execute([$aujourdhui, $aujourdhui]);
        $ligne = $stmt->fetch();
        return $ligne ?: null;
    } catch (Throwable $e) {
        // Table absente ou base indisponible : pas de bandeau, pas d'erreur.
        return null;
    }
}

/** Toutes les annonces, pour l'écran d'administration. */
function annonces_liste(?PDO $pdo): array
{
    if (!$pdo) return [];

    try {
        return $pdo->query("SELECT * FROM annonces ORDER BY active DESC, maj_le DESC")->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/** Une annonce par son identifiant. */
function annonce_par_id(?PDO $pdo, int $id): ?array
{
    if (!$pdo || $id <= 0) return null;

    try {
        $stmt = $pdo->prepare("SELECT * FROM annonces WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * Crée ou met à jour une annonce.
 *
 * @return array{0:bool, 1:string} succès et message destiné à l'administrateur
 */
function annonce_enregistrer(?PDO $pdo, array $champs): array
{
    if (!$pdo) return [false, 'Base de données indisponible.'];

    $titre   = trim((string) ($champs['titre'] ?? ''));
    $message = trim((string) ($champs['message'] ?? ''));

    if ($titre === '' || $message === '') {
        return [false, 'Le titre et le message sont obligatoires.'];
    }

    $ton = (string) ($champs['ton'] ?? 'info');
    if (!isset(ANNONCE_TONS[$ton])) $ton = 'info';

    $lien = trim((string) ($champs['lien_url'] ?? ''));
    if ($lien !== '' && !preg_match('~^(https?://|/|#)~', $lien)) $lien = '#' . ltrim($lien, '#');

    $debut = annonce_date($champs['date_debut'] ?? '');
    $fin   = annonce_date($champs['date_fin'] ?? '');
    if ($debut && $fin && $fin < $debut) {
        return [false, 'La date de fin précède la date de début.'];
    }

    $active = !empty($champs['active']) ? 1 : 0;
    $id     = (int) ($champs['id'] ?? 0);

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare(
                "UPDATE annonces SET titre = ?, message = ?, ton = ?, lien_url = ?,
                        lien_libelle = ?, date_debut = ?, date_fin = ?, active = ?, maj_le = ?
                  WHERE id = ?"
            );
            $stmt->execute([
                mb_substr($titre, 0, 120), mb_substr($message, 0, 400), $ton,
                $lien ?: null, mb_substr(trim((string) ($champs['lien_libelle'] ?? '')), 0, 60) ?: null,
                $debut, $fin, $active, date('Y-m-d H:i:s'), $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO annonces (titre, message, ton, lien_url, lien_libelle,
                        date_debut, date_fin, active, maj_le)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                mb_substr($titre, 0, 120), mb_substr($message, 0, 400), $ton,
                $lien ?: null, mb_substr(trim((string) ($champs['lien_libelle'] ?? '')), 0, 60) ?: null,
                $debut, $fin, $active, date('Y-m-d H:i:s'),
            ]);
            $id = (int) $pdo->lastInsertId();
        }

        // Une seule annonce visible à la fois.
        if ($active) {
            $pdo->prepare("UPDATE annonces SET active = 0 WHERE id <> ?")->execute([$id]);
        }

        return [true, $active
            ? 'Annonce enregistrée et publiée sur le site.'
            : 'Annonce enregistrée, en brouillon.'];
    } catch (Throwable $e) {
        error_log('[bellevue] annonces : ' . $e->getMessage());
        return [false, "L'enregistrement a échoué. Voir le journal du serveur."];
    }
}

/** Active une annonce et désactive toutes les autres. */
function annonce_activer(?PDO $pdo, int $id): bool
{
    if (!$pdo || $id <= 0) return false;

    try {
        $pdo->exec("UPDATE annonces SET active = 0");
        $stmt = $pdo->prepare("UPDATE annonces SET active = 1, maj_le = ? WHERE id = ?");
        $stmt->execute([date('Y-m-d H:i:s'), $id]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/** Retire l'annonce du site sans la supprimer. */
function annonce_desactiver(?PDO $pdo, int $id): bool
{
    if (!$pdo || $id <= 0) return false;

    try {
        $pdo->prepare("UPDATE annonces SET active = 0 WHERE id = ?")->execute([$id]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/** Supprime définitivement une annonce. */
function annonce_supprimer(?PDO $pdo, int $id): bool
{
    if (!$pdo || $id <= 0) return false;

    try {
        $pdo->prepare("DELETE FROM annonces WHERE id = ?")->execute([$id]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/** Normalise une date de formulaire, ou null. */
function annonce_date($valeur): ?string
{
    $valeur = trim((string) $valeur);
    if ($valeur === '') return null;

    $d = date_create($valeur);
    return $d ? $d->format('Y-m-d') : null;
}

/** Période lisible d'une annonce, pour l'administration. */
function annonce_periode(array $a): string
{
    $debut = $a['date_debut'] ?? null;
    $fin   = $a['date_fin'] ?? null;

    if (!$debut && !$fin) return 'Sans limite de date';
    if ($debut && $fin)   return 'Du ' . date('d/m/Y', strtotime($debut)) . ' au ' . date('d/m/Y', strtotime($fin));
    if ($debut)           return 'À partir du ' . date('d/m/Y', strtotime($debut));

    return "Jusqu'au " . date('d/m/Y', strtotime($fin));
}

/** Vrai si l'annonce est active ET dans sa fenêtre de dates. */
function annonce_est_visible(array $a): bool
{
    if (empty($a['active'])) return false;

    $aujourdhui = date('Y-m-d');
    if (!empty($a['date_debut']) && $a['date_debut'] > $aujourdhui) return false;
    if (!empty($a['date_fin'])   && $a['date_fin']   < $aujourdhui) return false;

    return true;
}

/**
 * Rend le bandeau public. Ne produit rien s'il n'y a rien à annoncer.
 * L'identifiant sert de clé de mémorisation : il change à chaque
 * modification, ce qui refait apparaître le bandeau aux visiteurs.
 */
function annonce_bandeau(?array $annonce): void
{
    if (!$annonce) return;

    $cle = 'annonce-' . (int) $annonce['id'] . '-' . strtotime((string) $annonce['maj_le']);
    $ton = isset(ANNONCE_TONS[$annonce['ton']]) ? $annonce['ton'] : 'info';
    $e   = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

    ?>
    <aside class="annonce annonce--<?php echo $e($ton); ?>" id="annonceBandeau"
           data-annonce="<?php echo $e($cle); ?>" role="status" hidden>
        <div class="annonce-inner">
            <span class="annonce-etiquette"><?php echo $e(ANNONCE_TONS[$ton]); ?></span>
            <p class="annonce-texte">
                <strong><?php echo $e($annonce['titre']); ?></strong>
                <span><?php echo $e($annonce['message']); ?></span>
            </p>
            <?php if (!empty($annonce['lien_url'])): ?>
                <a class="annonce-lien" href="<?php echo $e($annonce['lien_url']); ?>">
                    <?php echo $e($annonce['lien_libelle'] ?: 'En savoir plus'); ?>
                </a>
            <?php endif; ?>
            <button type="button" class="annonce-ok" id="annonceOk">
                <span class="annonce-coche" aria-hidden="true"></span>
                J'ai vu
            </button>
        </div>
    </aside>
    <script>
    /* Affichage différé : le bandeau reste masqué si ce visiteur l'a déjà
       refermé, ce qui évite qu'il apparaisse une fraction de seconde. */
    (function () {
        var bandeau = document.getElementById('annonceBandeau');
        if (!bandeau) return;

        var cle = 'bv-' + bandeau.dataset.annonce;
        var lu  = false;
        try { lu = localStorage.getItem(cle) === '1'; } catch (e) {}
        if (lu) return;

        window.setTimeout(function () {
            bandeau.hidden = false;
            window.requestAnimationFrame(function () { bandeau.classList.add('annonce--visible'); });
        }, 1200);

        document.getElementById('annonceOk').addEventListener('click', function () {
            try { localStorage.setItem(cle, '1'); } catch (e) {}
            bandeau.classList.add('annonce--vue');
            window.setTimeout(function () { bandeau.hidden = true; }, 420);
        });
    })();
    </script>
    <?php
}
