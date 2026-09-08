<?php
// config/mail_smtp.php
if (!defined('SMTP_HOST')) {
    require_once __DIR__ . '/mail_config.php';
}

/**
 * Lecture d'une réponse SMTP — DOIT être déclarée HORS de send_smtp_mail()
 * pour éviter "Cannot redeclare function" lors du 2e appel (accusé client).
 */
function smtp_read($socket) {
    $data = '';
    while ($str = fgets($socket, 515)) {
        $data .= $str;
        if (substr($str, 3, 1) === ' ') break;
    }
    return $data;
}

/**
 * Envoi d'email via SMTP SSL — compatible Hostinger port 465
 *
 * @param  string  $to               Destinataire(s), séparés par des virgules
 * @param  string  $subject          Sujet
 * @param  string  $message_content  Corps en texte brut
 * @param  string  $reply_to         Adresse Reply-To (optionnel)
 * @return true|string               true si succès, message d'erreur sinon
 */
function send_smtp_mail($to, $subject, $message_content, $reply_to = '')
{
    $timeout = 15;

    $socket = @fsockopen('ssl://' . SMTP_HOST, SMTP_PORT, $errno, $errstr, $timeout);
    if (!$socket) {
        return "Connexion impossible à " . SMTP_HOST . ":" . SMTP_PORT . " — $errstr ($errno)";
    }

    smtp_read($socket); // Bannière d'accueil

    $ehlo = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    fputs($socket, "EHLO $ehlo\r\n");
    smtp_read($socket);

    fputs($socket, "AUTH LOGIN\r\n");
    smtp_read($socket);

    fputs($socket, base64_encode(SMTP_USER) . "\r\n");
    smtp_read($socket);

    fputs($socket, base64_encode(SMTP_PASS) . "\r\n");
    $auth_response = smtp_read($socket);
    if (strpos($auth_response, '235') === false) {
        fclose($socket);
        return "Authentification SMTP échouée : " . trim($auth_response);
    }

    fputs($socket, "MAIL FROM: <" . SMTP_USER . ">\r\n");
    smtp_read($socket);

    foreach (explode(',', $to) as $recipient) {
        fputs($socket, "RCPT TO: <" . trim($recipient) . ">\r\n");
        smtp_read($socket);
    }

    fputs($socket, "DATA\r\n");
    smtp_read($socket);

    $headers  = "To: $to\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
    if ($reply_to) {
        $headers .= "Reply-To: $reply_to\r\n";
    }
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP-SMTP-Bellevue\r\n";
    $headers .= "\r\n";

    fputs($socket, $headers . $message_content . "\r\n.\r\n");
    $final_response = smtp_read($socket);

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    if (strpos($final_response, '250') !== false) {
        return true;
    }
    return "Envoi refusé par le serveur : " . trim($final_response);
}
?>