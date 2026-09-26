<?php
/**
 * config/mail_smtp.php — Envoi d'un message par SMTP, en SSL implicite.
 * ---------------------------------------------------------------------------
 * Le formulaire du site est la seule source d'envoi. Après la suspension de la
 * boîte par l'hébergeur, ce module a été repris sur trois plans :
 *
 *   • sécurité — destinataires validés, en-têtes purgés de leurs retours à la
 *     ligne, et surtout corps « échappé du point » : une ligne réduite à un
 *     point met fin aux données SMTP, et tout ce qui suit serait interprété
 *     comme des commandes. Le message du visiteur pouvait donc en contenir.
 *   • conformité — un message sans Date ni Message-ID est tenu pour suspect
 *     par la plupart des filtres. Les deux sont désormais émis, et chaque
 *     réponse du serveur est vérifiée au lieu d'être ignorée.
 *   • présentation — un second corps HTML peut accompagner le texte brut, dans
 *     un ensemble multipart/alternative. Le texte reste toujours présent : un
 *     message uniquement HTML est, lui aussi, un signal d'indésirable.
 */

if (!defined('SMTP_HOST')) {
    require_once __DIR__ . '/mail_config.php';
}

/**
 * Lecture d'une réponse SMTP.
 * Déclarée hors de send_smtp_mail() : la page envoie plusieurs messages par
 * soumission, et une fonction imbriquée serait redéclarée au second appel.
 */
function smtp_read($socket)
{
    $data = '';
    while ($str = fgets($socket, 515)) {
        $data .= $str;
        if (substr($str, 3, 1) === ' ') break;
    }
    return $data;
}

/**
 * Dernière réponse du serveur à l'acceptation d'un message.
 *
 * Elle contient l'identifiant de file attribué par l'hébergeur, du type
 * « 250 2.0.0 Ok: queued as 1A2B3C4D ». C'est la seule prise qui reste quand
 * un message est accepté mais n'arrive jamais : muni de cet identifiant,
 * l'assistance de l'hébergeur peut dire ce qu'il est devenu après la remise
 * au relais — filtré, rejeté plus loin, ou distribué.
 */
function smtp_derniere_reponse(?string $valeur = null): string
{
    static $reponse = '';

    if ($valeur !== null) $reponse = trim($valeur);

    return $reponse;
}

/** Le code de réponse attendu est-il celui reçu ? */
function smtp_code_est($reponse, $code)
{
    return strpos(ltrim((string) $reponse), (string) $code) === 0;
}

/**
 * Encodage d'un en-tête contenant des caractères accentués.
 *
 * Un en-tête ne transporte que de l'ASCII : le reste passe en base64 balisé.
 * La RFC 2047 limite chaque mot encodé à 75 caractères, tout compris — une
 * limite qu'un sujet tel que « Demande de réservation — Marie-Hélène
 * Dubreuil-Fontanier (14 nuits) » dépassait largement, en un seul mot de
 * plus de cent caractères. Les filtres le relèvent comme non conforme.
 *
 * Le texte est donc découpé en plusieurs mots encodés, repliés par un retour
 * à la ligne suivi d'une espace. La découpe respecte les frontières des
 * caractères UTF-8 : couper au milieu d'un « é » produirait des losanges.
 */
function smtp_entete_encode($valeur)
{
    $valeur = str_replace(["\r", "\n", "\0"], ' ', (string) $valeur);

    if (preg_match('/^[\x20-\x7E]*$/', $valeur)) return $valeur;

    // « =?UTF-8?B?…?= » coûte 12 caractères d'habillage. En limitant la
    // source à 42 octets, le base64 en fait 56, le mot encodé 68, et la
    // ligne « Subject: … » reste sous les 78 caractères que recommande la
    // RFC 5322 — bien en deçà des 998 qu'elle impose.
    $octets_max = 42;

    $morceaux = [];
    $courant  = '';

    foreach (preg_split('//u', $valeur, -1, PREG_SPLIT_NO_EMPTY) as $caractere) {
        if (strlen($courant) + strlen($caractere) > $octets_max) {
            $morceaux[] = $courant;
            $courant = '';
        }
        $courant .= $caractere;
    }
    if ($courant !== '') $morceaux[] = $courant;

    foreach ($morceaux as &$morceau) {
        $morceau = '=?UTF-8?B?' . base64_encode($morceau) . '?=';
    }
    unset($morceau);

    return implode("\r\n ", $morceaux);
}

/**
 * Nom d'affichage prêt à figurer devant une adresse.
 *
 * Une virgule dans ce nom suffit à tout casser : « From: Bellevue, gîte
 * <reservation@…> » se lit, pour un analyseur, comme DEUX adresses — dont
 * la première, « Bellevue », n'en est pas une. Le message devient non
 * conforme sans que rien ne le laisse voir à l'œil nu. Le point et les
 * deux-points posent le même problème.
 *
 * La RFC 5322 prévoit pour cela la chaîne entre guillemets. Un nom accentué,
 * lui, part en mot encodé — qui n'a pas besoin de guillemets, et n'en veut
 * pas.
 */
function smtp_nom_affichage($nom)
{
    $nom = trim(str_replace(["\r", "\n", "\0"], ' ', (string) $nom));
    if ($nom === '') return '';

    // Hors ASCII : l'encodage RFC 2047 neutralise déjà tout caractère gênant.
    if (!preg_match('/^[\x20-\x7E]*$/', $nom)) {
        return smtp_entete_encode($nom);
    }

    // Caractères « specials » de la RFC 5322 : leur présence impose les
    // guillemets.
    if (preg_match('/[()<>\[\]:;@,."\\\\]/', $nom)) {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $nom) . '"';
    }

    return $nom;
}

/**
 * Corps prêt pour la transmission : retours à la ligne normalisés en CRLF,
 * puis encodage « quoted-printable ». Celui-ci conserve le texte lisible tel
 * quel — contrairement à base64, que certains filtres pénalisent — tout en
 * respectant la limite de longueur de ligne du protocole.
 */
function smtp_corps_encode($texte)
{
    $texte = str_replace(["\r\n", "\r"], "\n", (string) $texte);
    $texte = str_replace("\n", "\r\n", $texte);

    return quoted_printable_encode($texte);
}

/**
 * Construit le message complet — en-têtes et corps — tel qu'il part sur le
 * réseau, en CRLF.
 *
 * Séparée de l'envoi à dessein : un message peut ainsi être vérifié caractère
 * par caractère sans ouvrir de connexion. L'hébergeur ayant mis en cause la
 * conformité des en-têtes, c'était la première chose à rendre observable.
 *
 * @param array $champs destinataires, sujet, texte, html, reply_to, domaine
 * @return string
 */
function smtp_message_construire(array $champs): string
{
    $destinataires = $champs['destinataires'];
    $reply_to      = (string) ($champs['reply_to'] ?? '');
    $html          = (string) ($champs['html'] ?? '');
    $domaine       = (string) ($champs['domaine'] ?? 'bellevuedaveyron.fr');

    $frontiere = 'bva_' . bin2hex(random_bytes(12));

    // Un seul en-tête From, avec une adresse valide : c'est la première chose
    // que vérifient les filtres, et un « From: <> » suffit à faire rejeter le
    // message. Le nom d'affichage est facultatif ; l'adresse ne l'est pas.
    $de = smtp_nom_affichage(SMTP_FROM_NAME);
    $de = $de === '' ? '<' . SMTP_FROM . '>' : $de . ' <' . SMTP_FROM . '>';

    $entetes  = "Date: " . date('r') . "\r\n";
    $entetes .= "Message-ID: <" . bin2hex(random_bytes(12)) . '@' . $domaine . ">\r\n";
    $entetes .= "From: " . $de . "\r\n";
    $entetes .= "To: " . implode(', ', $destinataires) . "\r\n";
    if ($reply_to !== '') {
        $entetes .= "Reply-To: " . $reply_to . "\r\n";
    }
    $entetes .= "Subject: " . smtp_entete_encode((string) $champs['sujet']) . "\r\n";
    $entetes .= "MIME-Version: 1.0\r\n";
    $entetes .= "Auto-Submitted: auto-generated\r\n";

    if ($html !== '') {
        $entetes .= "Content-Type: multipart/alternative; boundary=\"$frontiere\"\r\n\r\n";

        // L'ordre compte : la dernière variante est celle que le logiciel de
        // messagerie affiche s'il sait la lire. Le texte vient donc d'abord.
        $corps  = "--$frontiere\r\n";
        $corps .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $corps .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $corps .= smtp_corps_encode((string) $champs['texte']) . "\r\n";

        $corps .= "--$frontiere\r\n";
        $corps .= "Content-Type: text/html; charset=UTF-8\r\n";
        $corps .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $corps .= smtp_corps_encode($html) . "\r\n";

        $corps .= "--$frontiere--\r\n";
    } else {
        $entetes .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $entetes .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $corps = smtp_corps_encode((string) $champs['texte']) . "\r\n";
    }

    return $entetes . $corps;
}

/**
 * Envoi d'un message.
 *
 * @param  string $to               Destinataire(s), séparés par des virgules
 * @param  string $subject          Sujet
 * @param  string $message_content  Corps en texte brut — toujours requis
 * @param  string $reply_to         Adresse de réponse (facultatif)
 * @param  string $html             Corps HTML (facultatif). Présent, il est
 *                                  proposé en variante du texte brut.
 * @return true|string              true si accepté, message d'erreur sinon
 */
function send_smtp_mail($to, $subject, $message_content, $reply_to = '', $html = '')
{
    $timeout = 15;

    // ── Barrage aux injections d'en-tête ───────────────────────────────────
    // Une adresse contenant un retour à la ligne permettrait d'ajouter des
    // destinataires cachés, voire des commandes SMTP : le formulaire
    // deviendrait un relais à spam. Chaque adresse est donc validée, et toute
    // valeur douteuse fait échouer l'envoi plutôt que de partir malgré tout.
    $destinataires = [];
    foreach (explode(',', (string) $to) as $adresse) {
        $adresse = trim($adresse);
        if ($adresse === '') continue;
        if (!filter_var($adresse, FILTER_VALIDATE_EMAIL)) {
            error_log('[bellevue] envoi refusé : destinataire invalide');
            return "Destinataire invalide.";
        }
        $destinataires[] = $adresse;
    }
    if (!$destinataires) return "Aucun destinataire valide.";

    if ($reply_to !== '' && !filter_var($reply_to, FILTER_VALIDATE_EMAIL)) {
        $reply_to = '';   // adresse douteuse : on l'ignore, l'envoi continue
    }

    $subject = str_replace(["\r", "\n"], ' ', (string) $subject);

    // Sans secrets lisibles, SMTP_FROM est vide et le message partirait avec
    // un « From: <> » — précisément ce que les filtres rejettent comme non
    // conforme. Mieux vaut ne pas envoyer et le dire.
    if (!filter_var(SMTP_FROM, FILTER_VALIDATE_EMAIL)) {
        error_log('[bellevue] envoi refusé : adresse d\'expédition absente ou mal formée');
        return "Adresse d'expédition absente ou mal formée (SMTP_FROM).";
    }

    // Le domaine d'envoi sert au salut SMTP et à l'identifiant du message :
    // il vient de l'adresse d'expédition, jamais de l'en-tête Host, que le
    // visiteur contrôle.
    $domaine = substr(strrchr(SMTP_FROM, '@'), 1);
    if (!$domaine) $domaine = 'bellevuedaveyron.fr';

    $socket = @fsockopen('ssl://' . SMTP_HOST, SMTP_PORT, $errno, $errstr, $timeout);
    if (!$socket) {
        return "Connexion impossible à " . SMTP_HOST . ":" . SMTP_PORT . " — $errstr ($errno)";
    }
    stream_set_timeout($socket, $timeout);

    smtp_read($socket); // Bannière d'accueil

    fputs($socket, "EHLO $domaine\r\n");
    smtp_read($socket);

    fputs($socket, "AUTH LOGIN\r\n");
    smtp_read($socket);

    fputs($socket, base64_encode(SMTP_USER) . "\r\n");
    smtp_read($socket);

    fputs($socket, base64_encode(SMTP_PASS) . "\r\n");
    $auth = smtp_read($socket);
    if (!smtp_code_est($auth, 235)) {
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        return "Authentification SMTP échouée : " . trim($auth);
    }

    // La RFC 5321 écrit « MAIL FROM:<adresse> », sans espace après les
    // deux-points. La plupart des serveurs tolèrent l'espace ; les plus
    // stricts ne le font pas, et rien n'oblige à le courir.
    fputs($socket, "MAIL FROM:<" . SMTP_FROM . ">\r\n");
    $reponse = smtp_read($socket);
    if (!smtp_code_est($reponse, 250)) {
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        return "Expéditeur refusé : " . trim($reponse);
    }

    // Un destinataire refusé était jusqu'ici ignoré, et l'envoi déclaré réussi
    // alors que le message n'allait nulle part.
    $acceptes = 0;
    foreach ($destinataires as $recipient) {
        fputs($socket, "RCPT TO:<" . $recipient . ">\r\n");
        $reponse = smtp_read($socket);
        if (smtp_code_est($reponse, 250) || smtp_code_est($reponse, 251)) {
            $acceptes++;
        } else {
            error_log('[bellevue] destinataire refusé par le serveur — ' . trim($reponse));
        }
    }
    if (!$acceptes) {
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        return "Aucun destinataire accepté par le serveur.";
    }

    fputs($socket, "DATA\r\n");
    $reponse = smtp_read($socket);
    if (!smtp_code_est($reponse, 354)) {
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        return "Le serveur a refusé les données : " . trim($reponse);
    }

    $donnees = smtp_message_construire([
        'destinataires' => $destinataires,
        'sujet'         => $subject,
        'texte'         => $message_content,
        'html'          => $html,
        'reply_to'      => $reply_to,
        'domaine'       => $domaine,
    ]);

    // ── Échappement du point ───────────────────────────────────────────────
    // Une ligne réduite à un point met fin aux données. Sans ce doublement,
    // un message contenant une telle ligne serait tronqué, et la suite prise
    // pour des commandes SMTP.
    $donnees = str_replace("\r\n.", "\r\n..", $donnees);
    if (substr($donnees, 0, 1) === '.') $donnees = '.' . $donnees;

    fputs($socket, $donnees . "\r\n.\r\n");
    $finale = smtp_read($socket);

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    smtp_derniere_reponse($finale);

    if (smtp_code_est($finale, 250)) return true;

    return "Envoi refusé par le serveur : " . trim($finale);
}
