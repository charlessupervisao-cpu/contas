-- Notas fiscais (aba NF-es do DivulgaCandContas) vinculadas ao fornecedor/emitente
CREATE TABLE IF NOT EXISTS `SupplierNfe` (
    `id` VARCHAR(191) NOT NULL,
    `campaignId` VARCHAR(191) NOT NULL,
    `supplierId` VARCHAR(191) NULL,
    `cnpjEmitente` VARCHAR(191) NOT NULL,
    `nmEmitente` VARCHAR(191) NOT NULL,
    `naturezaOp` VARCHAR(191) NULL,
    `modelo` VARCHAR(191) NULL,
    `dataEmissao` DATE NULL,
    `numeroNf` VARCHAR(191) NULL,
    `numeroSerie` VARCHAR(191) NULL,
    `valor` DOUBLE NOT NULL DEFAULT 0,
    `ue` VARCHAR(191) NULL,
    `unidadeArrecadadora` VARCHAR(191) NULL,
    `dsUe` VARCHAR(191) NULL,
    `chaveAcesso` VARCHAR(191) NULL,
    `link` TEXT NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    INDEX `SupplierNfe_campaignId_idx`(`campaignId`),
    INDEX `SupplierNfe_supplierId_idx`(`supplierId`),
    INDEX `SupplierNfe_cnpjEmitente_idx`(`cnpjEmitente`),
    INDEX `SupplierNfe_chaveAcesso_idx`(`chaveAcesso`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
