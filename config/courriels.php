<?php
/**
 * config/courriels.php — Les deux messages émis par le formulaire.
 * ---------------------------------------------------------------------------
 * Une demande de séjour produit deux courriers : celui qui part aux
 * propriétaires, fait pour être lu vite et traité, et l'accusé de réception du
 * client, qui est le premier objet à l'image du gîte qu'il reçoit. Les deux
 * sont construits ici, et nulle part ailleurs : la page n'assemble plus de
 * texte à la volée.
 *
 * Chaque message existe en deux versions, produites ensemble :
 *
 *   • « texte »  — lisible partout, y compris sur une montre ou un client en
 *                  mode texte seul. C'est aussi ce que réclament les filtres :
 *                  un message uniquement HTML est tenu pour suspect.
 *   • « html »   — la mise en forme, à l'identité du site.
 *
 * Contraintes propres au courrier électronique, qui expliquent la forme du
 * code ci-dessous : mise en page en tableaux, styles écrits dans les balises,
 * aucune police ni image distante. Les messageries ignorent les feuilles de
 * style externes, la plupart bloquent les images tant que le destinataire ne
 * les autorise pas, et aucune ne connaît les dispositions modernes. Un titre
 * en Georgia qui s'affiche partout vaut mieux qu'un logo qui ne s'affiche
 * nulle part.
 */

require_once __DIR__ . '/seo.php';

// ── Identité visuelle, reprise du site ─────────────────────────────────────
const COURRIEL_NUIT   = '#050914';   // fond d'en-tête
const COURRIEL_OR     = '#c5a059';   // filets et accents
const COURRIEL_CREME  = '#faf8f4';   // fond de page
const COURRIEL_TEXTE  = '#3a3630';
const COURRIEL_DISCRET = '#8a8172';

/** Échappement pour le HTML du message. */
function courriel_e($valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

/** Montant à la française : « 2 450 € ». */
function courriel_euros($montant): string
{
    return number_format((float) $montant, 0, ',', "\u{202F}") . "\u{202F}€";
}

/** Date lisible : « samedi 4 juillet 2026 ». */
function courriel_date(?string $iso): string
{
    if (!$iso) return '';

    $d = date_create($iso);
    if (!$d) return '';

    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois  = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
              'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    // « samedi 1er août » : le quantième s'ordonne en français.
    $jour = (int) $d->format('j');

    return $jours[(int) $d->format('w')] . ' ' . ($jour === 1 ? '1er' : $jour) . ' '
         . $mois[(int) $d->format('n')] . ' ' . $d->format('Y');
}

/**
 * Enveloppe commune : en-tête sombre, corps blanc, pied discret.
 *
 * @param string $titre      Titre affiché dans l'en-tête
 * @param string $chapeau    Une ligne sous le titre
 * @param string $contenu    HTML déjà assemblé du corps
 * @param string $preentete  Ligne d'aperçu, visible dans la liste des messages
 *                           et nulle part ailleurs
 */
function courriel_enveloppe(string $titre, string $chapeau, string $contenu, string $preentete = ''): string
{
    $nuit    = COURRIEL_NUIT;
    $or      = COURRIEL_OR;
    $creme   = COURRIEL_CREME;
    $texte   = COURRIEL_TEXTE;
    $discret = COURRIEL_DISCRET;

    $serif = "Georgia, 'Times New Roman', serif";
    $sans  = "'Helvetica Neue', Helvetica, Arial, sans-serif";

    return <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>{$titre}</title>
</head>
<body style="margin:0;padding:0;background:{$creme};-webkit-text-size-adjust:100%;">

<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;font-size:1px;line-height:1px;">{$preentete}</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:{$creme};">
<tr><td align="center" style="padding:28px 12px;">

  <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:100%;background:#ffffff;border-collapse:collapse;">

    <!-- En-tête -->
    <tr>
      <td style="background:{$nuit};padding:30px 34px 26px;text-align:center;">
        <div style="font-family:{$serif};font-size:21px;font-weight:bold;color:#ffffff;letter-spacing:.5px;">Bellevue d'Aveyron</div>
        <div style="font-family:{$sans};font-size:10px;letter-spacing:3.5px;text-transform:uppercase;color:{$or};padding-top:7px;">Villa 5 étoiles</div>
      </td>
    </tr>
    <tr><td style="height:3px;background:{$or};font-size:0;line-height:0;">&nbsp;</td></tr>

    <!-- Titre -->
    <tr>
      <td style="padding:34px 34px 0;text-align:center;">
        <h1 style="margin:0;font-family:{$serif};font-size:24px;font-weight:normal;color:{$nuit};line-height:1.3;">{$titre}</h1>
        <p style="margin:12px 0 0;font-family:{$sans};font-size:14px;line-height:1.6;color:{$discret};">{$chapeau}</p>
      </td>
    </tr>

    <!-- Corps -->
    <tr><td style="padding:26px 34px 34px;font-family:{$sans};font-size:14px;line-height:1.65;color:{$texte};">
{$contenu}
    </td></tr>

    <!-- Pied -->
    <tr>
      <td style="background:{$nuit};padding:24px 34px;text-align:center;font-family:{$sans};font-size:12px;line-height:1.8;color:rgba(255,255,255,.62);">
        12130 Sainte-Eulalie-d'Olt &middot; Aveyron<br />
        <a href="tel:+33680907107" style="color:{$or};text-decoration:none;">06 80 90 71 07</a>
        &nbsp;&middot;&nbsp;
        <a href="https://bellevuedaveyron.fr" style="color:{$or};text-decoration:none;">bellevuedaveyron.fr</a>
      </td>
    </tr>

  </table>

</td></tr>
</table>

</body>
</html>
HTML;
}

/** Une ligne du tableau récapitulatif : intitulé à gauche, valeur à droite. */
function courriel_ligne(string $intitule, string $valeur, bool $accent = false): string
{
    $or      = COURRIEL_OR;
    $nuit    = COURRIEL_NUIT;
    $discret = COURRIEL_DISCRET;
    $sans    = "'Helvetica Neue', Helvetica, Arial, sans-serif";

    $style = $accent
        ? "font-family:{$sans};font-size:16px;font-weight:bold;color:{$or};"
        : "font-family:{$sans};font-size:14px;color:{$nuit};";

    return '<tr>'
         . '<td style="padding:9px 0;border-bottom:1px solid #eeeae2;font-family:' . $sans . ';font-size:12px;'
         . 'letter-spacing:.06em;text-transform:uppercase;color:' . $discret . ';">' . $intitule . '</td>'
         . '<td align="right" style="padding:9px 0;border-bottom:1px solid #eeeae2;' . $style . '">' . $valeur . '</td>'
         . '</tr>';
}

/** Bloc encadré, pour le message laissé par le visiteur. */
function courriel_citation(string $contenu): string
{
    $or = COURRIEL_OR;

    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:18px 0;">'
         . '<tr><td style="background:#faf8f4;border-left:3px solid ' . $or . ';padding:14px 18px;'
         . "font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;line-height:1.65;color:#3a3630;\">"
         . $contenu . '</td></tr></table>';
}

/** Bouton, construit en tableau : les messageries ignorent les boutons CSS. */
function courriel_bouton(string $lien, string $libelle): string
{
    $nuit = COURRIEL_NUIT;
    $or   = COURRIEL_OR;

    return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px auto 6px;">'
         . '<tr><td align="center" style="background:' . $or . ';">'
         . '<a href="' . courriel_e($lien) . '" style="display:inline-block;padding:13px 30px;'
         . "font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;font-weight:bold;"
         . 'letter-spacing:.1em;text-transform:uppercase;color:' . $nuit . ';text-decoration:none;">'
         . courriel_e($libelle) . '</a></td></tr></table>';
}

/**
 * Récapitulatif du séjour, commun aux deux messages.
 * Renvoie les lignes du tableau, ou une chaîne vide sans dates.
 */
function courriel_recap_sejour(array $d, bool $avec_prix): string
{
    if (empty($d['has_dates'])) {
        return courriel_ligne('Dates', 'non précisées');
    }

    $lignes  = courriel_ligne('Arrivée', courriel_e(courriel_date($d['date_debut'] ?? null)));
    $lignes .= courriel_ligne('Départ',  courriel_e(courriel_date($d['date_fin'] ?? null)));
    $lignes .= courriel_ligne('Durée',   (int) ($d['nuits'] ?? 0) . ' nuits');

    if (!empty($d['option_menage'])) {
        $lignes .= courriel_ligne('Ménage de fin de séjour', 'demandé');
    }

    if ($avec_prix && !empty($d['prix_total'])) {
        $lignes .= courriel_ligne('Montant du séjour', courriel_euros($d['prix_total']), true);
        if (!empty($d['acompte'])) {
            $lignes .= courriel_ligne('Acompte à la réservation', courriel_euros($d['acompte']));
        }
    }

    return $lignes;
}

/** Version texte du récapitulatif. */
function courriel_recap_texte(array $d, bool $avec_prix): string
{
    if (empty($d['has_dates'])) {
        return "Dates     : non précisées (demande d'information)\n";
    }

    $t  = "Arrivée   : " . courriel_date($d['date_debut'] ?? null) . "\n";
    $t .= "Départ    : " . courriel_date($d['date_fin'] ?? null) . "\n";
    $t .= "Durée     : " . (int) ($d['nuits'] ?? 0) . " nuits\n";

    if (!empty($d['option_menage'])) {
        $t .= "Ménage    : demandé\n";
    }
    if ($avec_prix && !empty($d['prix_total'])) {
        $t .= "Montant   : " . courriel_euros($d['prix_total']) . "\n";
        if (!empty($d['acompte'])) {
            $t .= "Acompte   : " . courriel_euros($d['acompte']) . "\n";
        }
    }

    return $t;
}

/**
 * Message d'essai, à l'identique de ce qu'un client reçoit.
 * ---------------------------------------------------------------------------
 * Il emprunte la même enveloppe, les mêmes polices et la même structure que
 * l'accusé de réception : c'est la seule façon qu'un service de notation —
 * mail-tester.com, par exemple — juge ce que vos clients reçoivent vraiment,
 * et non un message en texte brut fabriqué pour l'occasion.
 *
 * @return array{sujet:string, texte:string, html:string}
 */
function courriel_essai(string $quand, string $expediteur): array
{
    $sujet = "Essai d'envoi — Bellevue d'Aveyron";

    $texte  = "Message d'essai envoyé depuis l'administration du site.\n\n";
    $texte .= "S'il vous parvient, la chaîne d'envoi fonctionne : le site sait joindre\n";
    $texte .= "le serveur de messagerie, et les demandes de réservation arriveront.\n\n";
    $texte .= "Envoyé le " . $quand . "\n";
    $texte .= "Expéditeur : " . $expediteur . "\n\n";
    $texte .= "Ce message reprend exactement la mise en forme des accusés de réception\n";
    $texte .= "adressés aux clients : ce qui est jugé ici vaut pour eux.\n";

    $sans = "'Helvetica Neue', Helvetica, Arial, sans-serif";

    $contenu  = '<p style="margin:0 0 16px;">Ceci est un <strong>message d\'essai</strong>, '
              . 'envoyé depuis l\'administration du site.</p>';
    $contenu .= '<p style="margin:0 0 20px;">S\'il vous parvient, la chaîne d\'envoi fonctionne : '
              . 'le site sait joindre le serveur de messagerie, et les demandes de réservation '
              . 'arriveront à bon port.</p>';

    $contenu .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">'
              . courriel_ligne('Envoyé le', courriel_e($quand))
              . courriel_ligne('Expéditeur', courriel_e($expediteur))
              . '</table>';

    $contenu .= '<p style="margin:26px 0 0;padding-top:20px;border-top:1px solid #eeeae2;'
              . 'font-family:' . $sans . ';font-size:13px;color:' . COURRIEL_DISCRET . ';">'
              . 'Ce message reprend exactement la mise en forme des accusés de réception '
              . 'adressés aux clients : ce qui est jugé ici vaut pour eux.</p>';

    $html = courriel_enveloppe(
        "Essai d'envoi",
        'Vérification de la chaîne de messagerie du site.',
        $contenu,
        "Message d'essai — la chaîne d'envoi fonctionne."
    );

    return ['sujet' => $sujet, 'texte' => $texte, 'html' => $html];
}

/**
 * Message aux propriétaires.
 *
 * Fait pour être traité, non pour être admiré : les coordonnées d'abord, en
 * gros et cliquables depuis un téléphone, le séjour ensuite, le message du
 * visiteur en dernier. Le bouton ouvre une réponse déjà adressée.
 *
 * @return array{sujet:string, texte:string, html:string}
 */
function courriel_proprietaires(array $d): array
{
    $nom   = (string) ($d['nom'] ?? '');
    $email = (string) ($d['email'] ?? '');
    $tel   = (string) ($d['telephone'] ?? '');
    $note  = trim((string) ($d['message'] ?? ''));
    $dates = !empty($d['has_dates']);

    $sujet = $dates
        ? 'Demande de réservation — ' . $nom . ' (' . (int) ($d['nuits'] ?? 0) . ' nuits)'
        : 'Demande d\'information — ' . $nom;

    // ── Version texte ──
    $texte  = ($dates ? "DEMANDE DE RÉSERVATION" : "DEMANDE D'INFORMATION") . " — bellevuedaveyron.fr\n";
    $texte .= str_repeat('=', 52) . "\n\n";
    $texte .= "CLIENT\n";
    $texte .= "Nom       : " . $nom . "\n";
    $texte .= "E-mail    : " . $email . "\n";
    $texte .= "Téléphone : " . $tel . "\n\n";
    $texte .= "SÉJOUR\n";
    $texte .= courriel_recap_texte($d, true) . "\n";
    $texte .= "MESSAGE\n";
    $texte .= ($note !== '' ? $note : 'Aucun message.') . "\n\n";
    $texte .= str_repeat('=', 52) . "\n";
    $texte .= "Répondre directement à ce message écrit à " . $email . ".\n";
    if (!empty($d['recu_le'])) {
        $texte .= "Demande reçue le " . $d['recu_le'] . ".\n";
    }

    // ── Version HTML ──
    $sans = "'Helvetica Neue', Helvetica, Arial, sans-serif";
    $or   = COURRIEL_OR;

    $contenu  = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">';
    $contenu .= courriel_ligne('Nom', '<strong>' . courriel_e($nom) . '</strong>');
    $contenu .= courriel_ligne('E-mail',
        '<a href="mailto:' . courriel_e($email) . '" style="color:' . $or . ';text-decoration:none;">'
        . courriel_e($email) . '</a>');
    $contenu .= courriel_ligne('Téléphone',
        '<a href="tel:' . courriel_e(preg_replace('/[^0-9+]/', '', $tel)) . '" style="color:' . $or . ';text-decoration:none;">'
        . courriel_e($tel) . '</a>');
    $contenu .= courriel_recap_sejour($d, true);
    $contenu .= '</table>';

    $contenu .= '<p style="margin:24px 0 0;font-family:' . $sans . ';font-size:12px;letter-spacing:.06em;'
              . 'text-transform:uppercase;color:' . COURRIEL_DISCRET . ';">Message du client</p>';
    $contenu .= courriel_citation(
        $note !== '' ? nl2br(courriel_e($note)) : '<em style="color:#8a8172;">Aucun message.</em>'
    );

    $contenu .= courriel_bouton('mailto:' . $email . '?subject=' . rawurlencode('Votre séjour à Bellevue d\'Aveyron'),
                                'Répondre au client');

    if (!empty($d['recu_le'])) {
        $contenu .= '<p style="margin:18px 0 0;text-align:center;font-family:' . $sans . ';font-size:12px;'
                  . 'color:' . COURRIEL_DISCRET . ';">Demande reçue le ' . courriel_e($d['recu_le']) . '</p>';
    }

    $html = courriel_enveloppe(
        $dates ? 'Nouvelle demande de réservation' : 'Nouvelle demande d\'information',
        $dates
            ? 'Reçue par le formulaire du site, en direct et sans commission.'
            : 'Le visiteur n\'a pas précisé de dates.',
        $contenu,
        $nom . ' — ' . ($dates ? (int) ($d['nuits'] ?? 0) . ' nuits' : 'demande d\'information')
    );

    return ['sujet' => $sujet, 'texte' => $texte, 'html' => $html];
}

/**
 * Accusé de réception adressé au client.
 *
 * C'est le premier objet à l'image du gîte qui lui parvient : il confirme,
 * récapitule, et dit ce qui va se passer ensuite. Aucun prix n'y figure tant
 * que les propriétaires n'ont pas confirmé la disponibilité — annoncer un
 * montant avant d'avoir vérifié le calendrier engagerait sur une semaine
 * peut-être déjà louée.
 *
 * @return array{sujet:string, texte:string, html:string}
 */
function courriel_client(array $d): array
{
    $nom   = (string) ($d['nom'] ?? '');
    $dates = !empty($d['has_dates']);

    $sujet = "Nous avons bien reçu votre demande — Bellevue d'Aveyron";

    // ── Version texte ──
    $texte  = "Bonjour " . $nom . ",\n\n";
    $texte .= $dates
        ? "Nous avons bien reçu votre demande de réservation pour la villa Bellevue d'Aveyron, et nous vous en remercions.\n\n"
        : "Nous avons bien reçu votre demande d'information concernant la villa Bellevue d'Aveyron, et nous vous en remercions.\n\n";
    $texte .= "RÉCAPITULATIF\n";
    $texte .= courriel_recap_texte($d, false) . "\n";
    $texte .= "LA SUITE\n";
    $texte .= "Nous vérifions la disponibilité et revenons vers vous sous 24 heures,\n";
    $texte .= "par e-mail ou par téléphone, avec le tarif exact de votre période et\n";
    $texte .= "les modalités de réservation. Vous réservez en direct auprès des\n";
    $texte .= "propriétaires : aucune commission de plateforme.\n\n";
    $texte .= "Une question d'ici là ? Répondez simplement à ce message, ou appelez\n";
    $texte .= "le " . SEO_PHONE_HUMAN . ".\n\n";
    $texte .= "Bien à vous,\n";
    $texte .= "Véronique et Daniel Lacan\n";
    $texte .= "Bellevue d'Aveyron — 12130 Sainte-Eulalie-d'Olt\n";
    $texte .= "https://bellevuedaveyron.fr\n";

    // ── Version HTML ──
    $sans = "'Helvetica Neue', Helvetica, Arial, sans-serif";

    $contenu  = '<p style="margin:0 0 16px;">Bonjour <strong>' . courriel_e($nom) . '</strong>,</p>';
    $contenu .= '<p style="margin:0 0 20px;">'
              . ($dates
                  ? 'Nous avons bien reçu votre demande de réservation pour la villa, et nous vous en remercions.'
                  : 'Nous avons bien reçu votre demande d\'information concernant la villa, et nous vous en remercions.')
              . '</p>';

    $contenu .= '<p style="margin:0 0 6px;font-family:' . $sans . ';font-size:12px;letter-spacing:.06em;'
              . 'text-transform:uppercase;color:' . COURRIEL_DISCRET . ';">Votre demande</p>';
    $contenu .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">'
              . courriel_recap_sejour($d, false)
              . '</table>';

    $contenu .= '<p style="margin:26px 0 6px;font-family:' . $sans . ';font-size:12px;letter-spacing:.06em;'
              . 'text-transform:uppercase;color:' . COURRIEL_DISCRET . ';">La suite</p>';
    $contenu .= '<p style="margin:0 0 14px;">Nous vérifions la disponibilité et revenons vers vous '
              . '<strong>sous 24 heures</strong>, avec le tarif exact de votre période et les modalités '
              . 'de réservation.</p>';
    $contenu .= '<p style="margin:0 0 14px;">Vous réservez en direct auprès des propriétaires : '
              . 'aucune commission de plateforme, et un interlocuteur unique du premier message '
              . 'jusqu\'à la remise des clés.</p>';
    $contenu .= '<p style="margin:0;">Une question d\'ici là ? Répondez simplement à ce message, '
              . 'ou appelez-nous au <strong>' . SEO_PHONE_HUMAN . '</strong>.</p>';

    $contenu .= courriel_bouton('https://bellevuedaveyron.fr/decouvrir.php', 'Préparer votre séjour');

    $contenu .= '<p style="margin:26px 0 0;padding-top:20px;border-top:1px solid #eeeae2;'
              . 'font-family:Georgia,serif;font-style:italic;color:' . COURRIEL_DISCRET . ';">'
              . 'Véronique et Daniel Lacan</p>';

    $html = courriel_enveloppe(
        'Votre demande est bien arrivée',
        'Nous revenons vers vous sous 24 heures.',
        $contenu,
        'Nous avons bien reçu votre demande — réponse sous 24 heures.'
    );

    return ['sujet' => $sujet, 'texte' => $texte, 'html' => $html];
}
