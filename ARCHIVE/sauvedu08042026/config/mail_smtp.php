<?php
// config/mail_smtp.php
require_once __DIR__ . '/mail_config.php';

/**
 * Fonction d'envoi d'email via SMTP (compatible Hostinger/SSL Port 465)
 * Réalisée en pur PHP sans bibliothèque externe (PHPMailer) pour plus de légèreté
 */
function send_smtp_mail($to, $subject, $message_content, $reply_to = "")
{
    $timeout = 10;

    // Connexion sécurisée SSL
    $socket = fsockopen("ssl://" . SMTP_HOST, SMTP_PORT, $errno, $errstr, $timeout);
    if (!$socket)
        return "Erreur connexion : $errstr ($errno)";

    $log = [];
    function read($socket)
    {
        $data = "";
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) == " ")
                break;
        }
        return $data;
    }

    read($socket); // Accueil du serveur

    fputs($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
    read($socket);

    fputs($socket, "AUTH LOGIN\r\n");
    read($socket);

    fputs($socket, base64_encode(SMTP_USER) . "\r\n");
    read($socket);

    fputs($socket, base64_encode(SMTP_PASS) . "\r\n");
    $res = read($socket);
    if (strpos($res, '235') === false)
        return "Erreur authentification : " . $res;

    fputs($socket, "MAIL FROM: <" . SMTP_USER . ">\r\n");
    read($socket);

    // Support de plusieurs destinataires (séparés par des virgules)
    $recipients = explode(',', $to);
    foreach ($recipients as $rec) {
        fputs($socket, "RCPT TO: <" . trim($rec) . ">\r\n");
        read($socket);
    }

    fputs($socket, "DATA\r\n");
    read($socket);

    // En-têtes du mail
    $headers = "To: $to\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
    if ($reply_to)
        $headers .= "Reply-To: $reply_to\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP-SMTP-Bellevue\r\n";
    $headers .= "\r\n";

    fputs($socket, $headers . $message_content . "\r\n.\r\n");
    $res_final = read($socket);

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return (strpos($res_final, '250') !== false);
}
?>