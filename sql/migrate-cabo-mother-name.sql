-- Nome da mãe no cadastro de cabo eleitoral
ALTER TABLE `CaboEleitoral` ADD COLUMN `motherName` VARCHAR(191) NULL AFTER `birthDate`;
