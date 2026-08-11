-- CEP no cadastro de cabo eleitoral
ALTER TABLE `CaboEleitoral` ADD COLUMN `zipCode` VARCHAR(191) NULL AFTER `email`;
