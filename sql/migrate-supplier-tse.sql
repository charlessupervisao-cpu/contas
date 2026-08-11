-- Campos de fornecedor alinhados ao DivulgaCandContas / FiscalizaJE (TSE)
ALTER TABLE `Supplier` ADD COLUMN `tradeName` VARCHAR(191) NULL AFTER `name`;
ALTER TABLE `Supplier` ADD COLUMN `documentType` VARCHAR(191) NULL AFTER `tradeName`;
ALTER TABLE `Supplier` ADD COLUMN `contactName` VARCHAR(191) NULL AFTER `phone`;
ALTER TABLE `Supplier` ADD COLUMN `zipCode` VARCHAR(191) NULL AFTER `contactName`;
ALTER TABLE `Supplier` ADD COLUMN `addressNumber` VARCHAR(191) NULL AFTER `address`;
ALTER TABLE `Supplier` ADD COLUMN `addressComplement` VARCHAR(191) NULL AFTER `addressNumber`;
ALTER TABLE `Supplier` ADD COLUMN `neighborhood` VARCHAR(191) NULL AFTER `addressComplement`;
ALTER TABLE `Supplier` ADD COLUMN `stateRegistration` VARCHAR(191) NULL AFTER `state`;
ALTER TABLE `Supplier` ADD COLUMN `municipalRegistration` VARCHAR(191) NULL AFTER `stateRegistration`;
ALTER TABLE `Supplier` ADD INDEX `Supplier_document_idx`(`document`);
