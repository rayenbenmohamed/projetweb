-- Exécuter APRÈS l'import de database/cc.sql
-- Doctrine (héritage User / Admin / Candidat / Recruiter) exige la colonne discr.

ALTER TABLE `user` ADD COLUMN `discr` VARCHAR(31) NOT NULL DEFAULT 'user' AFTER `role`;

UPDATE `user` SET `discr` = 'candidat' WHERE `role` = 'Candidat';
UPDATE `user` SET `discr` = 'recruiter' WHERE `role` = 'Recruteur';
UPDATE `user` SET `discr` = 'admin' WHERE `role` IN ('Admin', 'ROLE_ADMIN', 'admin');
