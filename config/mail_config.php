<?php
/**
 * config/mail_config.php — Paramètres d'envoi SMTP.
 *
 * Les valeurs viennent de config/env.php (variables d'environnement ou
 * config/secrets.php) : ce fichier ne contient plus aucun mot de passe.
 */

require_once __DIR__ . '/env.php';

define('SMTP_HOST',      secret('SMTP_HOST', 'smtp.hostinger.com'));
define('SMTP_PORT',      (int) secret('SMTP_PORT', 465));
define('SMTP_USER',      secret('SMTP_USER', ''));
define('SMTP_PASS',      secret('SMTP_PASS', ''));
define('SMTP_FROM',      secret('SMTP_FROM', secret('SMTP_USER', '')));
define('SMTP_FROM_NAME', secret('SMTP_FROM_NAME', "Bellevue d'Aveyron"));
