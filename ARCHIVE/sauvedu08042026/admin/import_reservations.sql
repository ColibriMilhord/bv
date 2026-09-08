-- admin/import_reservations.sql

-- 1. Reservation: Février - Avril (Hiver/Printemps)
INSERT INTO `reservations` (`client_nom`, `client_email`, `date_debut`, `date_fin`, `statut`, `prix_total`, `notes`) 
VALUES ('Client Importé', 'import@exemple.com', '2026-02-01', '2026-04-25', 'validee', 0.00, 'Importé: Hiver/Printemps');

SET @id1 = LAST_INSERT_ID();

-- Remplissage calendrier pour l'ID 1 (Approximation SQL par procédure ou bloc manuel - ici simplifié par bloc)
-- Note: En SQL pur sans procédure stockée, il est difficile de faire une boucle.
-- Voici une méthode générique si vous importez ce fichier :
-- ATTENTION: Cette partie calendrier nécessite souvent un script PHP ou une procédure stockée.
-- Si vous utilisez phpMyAdmin, importez seulement les INSERT INTO reservations ci-dessus, le système se mettra à jour si vous éditez/sauvegardez via l'admin,
-- OU utilisez le script PHP fourni (import_reservations.php) si vous configurez le mot de passe.

-- 2. Reservation: Mai - Août (Été)
INSERT INTO `reservations` (`client_nom`, `client_email`, `date_debut`, `date_fin`, `statut`, `prix_total`, `notes`) 
VALUES ('Client Importé', 'import@exemple.com', '2026-05-23', '2026-08-23', 'validee', 0.00, 'Importé: Été');

-- 3. Reservation: Septembre
INSERT INTO `reservations` (`client_nom`, `client_email`, `date_debut`, `date_fin`, `statut`, `prix_total`, `notes`) 
VALUES ('Client Importé', 'import@exemple.com', '2026-09-05', '2026-09-19', 'validee', 0.00, 'Importé: Septembre');
