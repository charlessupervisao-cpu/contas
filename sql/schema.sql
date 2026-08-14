-- CONTAS — schema MySQL (cPanel) · contas.synetiq.com.br
-- Charset: utf8mb4
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- CreateTable
CREATE TABLE `User` (
    `id` VARCHAR(191) NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `email` VARCHAR(191) NOT NULL,
    `passwordHash` VARCHAR(191) NOT NULL,
    `role` VARCHAR(191) NOT NULL DEFAULT 'OPERADOR',
    `active` BOOLEAN NOT NULL DEFAULT true,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    UNIQUE INDEX `User_email_key`(`email`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `PasswordResetToken` (
    `id` VARCHAR(191) NOT NULL,
    `userId` VARCHAR(191) NOT NULL,
    `tokenHash` VARCHAR(64) NOT NULL,
    `expiresAt` DATETIME(3) NOT NULL,
    `usedAt` DATETIME(3) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    INDEX `PasswordResetToken_tokenHash_idx`(`tokenHash`),
    INDEX `PasswordResetToken_userId_idx`(`userId`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Campaign` (
    `id` VARCHAR(191) NOT NULL,
    `electionYear` INTEGER NOT NULL DEFAULT 2026,
    `candidateName` VARCHAR(191) NOT NULL,
    `candidateFullName` VARCHAR(191) NOT NULL,
    `candidateNumber` VARCHAR(191) NOT NULL,
    `party` VARCHAR(191) NOT NULL,
    `partyNumber` VARCHAR(191) NOT NULL,
    `cnpjCampaign` VARCHAR(191) NULL,
    `office` VARCHAR(191) NOT NULL DEFAULT 'Deputado Estadual',
    `state` VARCHAR(191) NOT NULL DEFAULT 'GO',
    `region` VARCHAR(191) NOT NULL DEFAULT 'CENTROOESTE',
    `ballotName` VARCHAR(191) NULL,
    `situation` VARCHAR(191) NOT NULL DEFAULT 'Em campanha',
    `reelection` BOOLEAN NOT NULL DEFAULT true,
    `legalSpendLimit` DOUBLE NOT NULL,
    `totalBudget` DOUBLE NOT NULL,
    `birthDate` VARCHAR(191) NULL,
    `gender` VARCHAR(191) NULL,
    `education` VARCHAR(191) NULL,
    `occupation` VARCHAR(191) NULL,
    `nationality` VARCHAR(191) NULL,
    `website` VARCHAR(191) NULL,
    `photoUrl` VARCHAR(191) NULL,
    `electoralTitle` VARCHAR(191) NULL,
    `phone` VARCHAR(64) NULL,
    `email` VARCHAR(191) NULL,
    `addressZip` VARCHAR(16) NULL,
    `addressStreet` VARCHAR(191) NULL,
    `addressNumber` VARCHAR(32) NULL,
    `addressComplement` VARCHAR(191) NULL,
    `addressDistrict` VARCHAR(191) NULL,
    `addressCity` VARCHAR(191) NULL,
    `addressState` VARCHAR(8) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `ExpenseCategory` (
    `id` VARCHAR(191) NOT NULL,
    `code` VARCHAR(64) NOT NULL,
    `label` VARCHAR(191) NOT NULL,
    `color` VARCHAR(32) NOT NULL DEFAULT '#0d9488',
    `active` BOOLEAN NOT NULL DEFAULT true,
    `sortOrder` INTEGER NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    UNIQUE INDEX `ExpenseCategory_code_key`(`code`),
    INDEX `ExpenseCategory_active_sort_idx`(`active`, `sortOrder`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Team` (
    `id` VARCHAR(191) NOT NULL,
    `memberNumber` INT NOT NULL,
    `label` VARCHAR(191) NOT NULL,
    `phone` VARCHAR(32) NULL,
    `city` VARCHAR(191) NULL,
    `cityRegion` VARCHAR(191) NULL,
    `active` BOOLEAN NOT NULL DEFAULT true,
    `sortOrder` INTEGER NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    UNIQUE INDEX `Team_memberNumber_key`(`memberNumber`),
    INDEX `Team_active_label_idx`(`active`, `label`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `BankAccount` (
    `id` VARCHAR(191) NOT NULL,
    `campaignId` VARCHAR(191) NOT NULL,
    `label` VARCHAR(191) NOT NULL,
    `bankName` VARCHAR(191) NOT NULL,
    `bankCode` VARCHAR(191) NOT NULL,
    `agency` VARCHAR(191) NOT NULL,
    `accountNumber` VARCHAR(191) NOT NULL,
    `accountType` VARCHAR(191) NOT NULL DEFAULT 'Corrente',
    `resourceOrigin` VARCHAR(64) NULL,
    `openedAt` DATE NULL,
    `balance` DOUBLE NOT NULL DEFAULT 0,
    `depositCpf` BOOLEAN NOT NULL DEFAULT true,
    `depositCnpj` BOOLEAN NOT NULL DEFAULT true,
    `active` BOOLEAN NOT NULL DEFAULT true,
    `sortOrder` INTEGER NOT NULL DEFAULT 0,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    INDEX `BankAccount_campaignId_idx`(`campaignId`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `AccountMapping` (
    `id` VARCHAR(191) NOT NULL,
    `campaignId` VARCHAR(191) NOT NULL,
    `kind` VARCHAR(191) NOT NULL,
    `code` VARCHAR(191) NOT NULL,
    `bankAccountId` VARCHAR(191) NOT NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    INDEX `AccountMapping_campaignId_idx`(`campaignId`),
    INDEX `AccountMapping_bankAccountId_idx`(`bankAccountId`),
    UNIQUE INDEX `AccountMapping_campaignId_kind_code_key`(`campaignId`, `kind`, `code`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `BankTransaction` (
    `id` VARCHAR(191) NOT NULL,
    `bankAccountId` VARCHAR(191) NOT NULL,
    `date` DATETIME(3) NOT NULL,
    `description` TEXT NOT NULL,
    `amount` DOUBLE NOT NULL,
    `type` VARCHAR(191) NOT NULL,
    `documentRef` VARCHAR(191) NULL,
    `status` VARCHAR(191) NOT NULL DEFAULT 'PENDENTE',
    `matchedExpenseId` VARCHAR(191) NULL,
    `matchedRevenueId` VARCHAR(191) NULL,
    `notes` TEXT NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    INDEX `BankTransaction_bankAccountId_idx`(`bankAccountId`),
    INDEX `BankTransaction_status_idx`(`status`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Revenue` (
    `id` VARCHAR(191) NOT NULL,
    `campaignId` VARCHAR(191) NOT NULL,
    `source` VARCHAR(191) NOT NULL,
    `donorName` VARCHAR(191) NULL,
    `donorCpf` VARCHAR(191) NULL,
    `amount` DOUBLE NOT NULL,
    `date` DATETIME(3) NOT NULL,
    `description` TEXT NULL,
    `receiptNumber` VARCHAR(191) NULL,
    `bankAccountId` VARCHAR(191) NULL,
    `donationType` VARCHAR(64) NULL,
    `resourceOrigin` VARCHAR(64) NULL,
    `resourceSpecies` VARCHAR(64) NULL,
    `emitReceipt` BOOLEAN NOT NULL DEFAULT false,
    `isFcc` BOOLEAN NOT NULL DEFAULT false,
    `isInternet` BOOLEAN NOT NULL DEFAULT false,
    `isLoan` BOOLEAN NOT NULL DEFAULT false,
    `createdById` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    INDEX `Revenue_campaignId_idx`(`campaignId`),
    INDEX `Revenue_source_idx`(`source`),
    INDEX `Revenue_donorCpf_idx`(`donorCpf`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Representative` (
    `id` VARCHAR(191) NOT NULL,
    `campaignId` VARCHAR(191) NOT NULL,
    `role` VARCHAR(64) NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `cpf` VARCHAR(32) NULL,
    `email` VARCHAR(191) NULL,
    `phone` VARCHAR(64) NULL,
    `oabUf` VARCHAR(8) NULL,
    `oabNumber` VARCHAR(64) NULL,
    `crcUf` VARCHAR(8) NULL,
    `crcNumber` VARCHAR(64) NULL,
    `roleOther` VARCHAR(191) NULL,
    `active` BOOLEAN NOT NULL DEFAULT true,
    `notes` TEXT NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    INDEX `Representative_campaignId_idx`(`campaignId`),
    INDEX `Representative_role_idx`(`role`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Expense` (
    `id` VARCHAR(191) NOT NULL,
    `campaignId` VARCHAR(191) NOT NULL,
    `category` VARCHAR(191) NOT NULL,
    `supplierName` VARCHAR(191) NOT NULL,
    `supplierDoc` VARCHAR(191) NULL,
    `supplierId` VARCHAR(191) NULL,
    `description` TEXT NOT NULL,
    `amount` DOUBLE NOT NULL,
    `date` DATETIME(3) NOT NULL,
    `status` VARCHAR(191) NOT NULL DEFAULT 'LANCADA',
    `naturezaOp` VARCHAR(191) NULL,
    `dataEmissao` DATE NULL,
    `numeroNf` VARCHAR(191) NULL,
    `unidadeArrecadadora` VARCHAR(191) NULL,
    `dsUe` VARCHAR(191) NULL,
    `nfeLink` TEXT NULL,
    `importSource` VARCHAR(191) NULL,
    `bankAccountId` VARCHAR(191) NULL,
    `caboId` VARCHAR(191) NULL,
    `vehicleId` VARCHAR(191) NULL,
    `installmentGroupId` VARCHAR(191) NULL,
    `installmentNumber` INT NULL,
    `installmentCount` INT NULL,
    `createdById` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    INDEX `Expense_campaignId_idx`(`campaignId`),
    INDEX `Expense_category_idx`(`category`),
    INDEX `Expense_numeroNf_idx`(`numeroNf`),
    INDEX `Expense_importSource_idx`(`importSource`),
    INDEX `Expense_supplierId_idx`(`supplierId`),
    INDEX `Expense_installmentGroupId_idx`(`installmentGroupId`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Vehicle` (
    `id` VARCHAR(191) NOT NULL,
    `campaignId` VARCHAR(191) NOT NULL,
    `label` VARCHAR(191) NOT NULL,
    `plate` VARCHAR(191) NOT NULL,
    `brand` VARCHAR(191) NULL,
    `model` VARCHAR(191) NULL,
    `year` VARCHAR(191) NULL,
    `type` VARCHAR(191) NOT NULL DEFAULT 'Automóvel',
    `color` VARCHAR(191) NULL,
    `ownerName` VARCHAR(191) NULL,
    `ownerDoc` VARCHAR(191) NULL,
    `notes` TEXT NULL,
    `active` BOOLEAN NOT NULL DEFAULT true,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    INDEX `Vehicle_campaignId_idx`(`campaignId`),
    INDEX `Vehicle_active_idx`(`active`),
    UNIQUE INDEX `Vehicle_campaignId_plate_key`(`campaignId`, `plate`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Supplier` (
    `id` VARCHAR(191) NOT NULL,
    `campaignId` VARCHAR(191) NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `tradeName` VARCHAR(191) NULL,
    `documentType` VARCHAR(191) NULL,
    `document` VARCHAR(191) NULL,
    `email` VARCHAR(191) NULL,
    `phone` VARCHAR(191) NULL,
    `contactName` VARCHAR(191) NULL,
    `zipCode` VARCHAR(191) NULL,
    `address` TEXT NULL,
    `addressNumber` VARCHAR(191) NULL,
    `addressComplement` VARCHAR(191) NULL,
    `neighborhood` VARCHAR(191) NULL,
    `city` VARCHAR(191) NULL,
    `state` VARCHAR(191) NULL DEFAULT 'GO',
    `stateRegistration` VARCHAR(191) NULL,
    `municipalRegistration` VARCHAR(191) NULL,
    `category` VARCHAR(191) NULL,
    `activityType` VARCHAR(191) NULL,
    `birthDate` DATE NULL,
    `quantidadeNfes` INT NOT NULL DEFAULT 0,
    `valorTotalNfes` DOUBLE NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `active` BOOLEAN NOT NULL DEFAULT true,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    INDEX `Supplier_campaignId_idx`(`campaignId`),
    INDEX `Supplier_name_idx`(`name`),
    INDEX `Supplier_document_idx`(`document`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
-- Notas fiscais da aba NF-es do DivulgaCandContas (CSV exportado)
CREATE TABLE `SupplierNfe` (
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

-- CreateTable
CREATE TABLE `BalanceAdjustment` (
    `id` VARCHAR(191) NOT NULL,
    `bankAccountId` VARCHAR(191) NOT NULL,
    `previousBalance` DOUBLE NOT NULL,
    `newBalance` DOUBLE NOT NULL,
    `difference` DOUBLE NOT NULL,
    `date` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `reason` TEXT NOT NULL,
    `notes` TEXT NULL,
    `createdById` VARCHAR(191) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),

    INDEX `BalanceAdjustment_bankAccountId_idx`(`bankAccountId`),
    INDEX `BalanceAdjustment_date_idx`(`date`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `CaboEleitoral` (
    `id` VARCHAR(191) NOT NULL,
    `campaignId` VARCHAR(191) NOT NULL,
    `fullName` VARCHAR(191) NOT NULL,
    `cpf` VARCHAR(191) NOT NULL,
    `rg` VARCHAR(191) NULL,
    `birthDate` VARCHAR(191) NULL,
    `motherName` VARCHAR(191) NULL,
    `phone` VARCHAR(191) NULL,
    `email` VARCHAR(191) NULL,
    `zipCode` VARCHAR(191) NULL,
    `address` TEXT NULL,
    `addressNumber` VARCHAR(191) NULL,
    `city` VARCHAR(191) NULL,
    `state` VARCHAR(191) NULL DEFAULT 'GO',
    `zone` VARCHAR(191) NULL,
    `teamCode` VARCHAR(191) NULL,
    `roleTitle` VARCHAR(191) NOT NULL DEFAULT 'Cabo Eleitoral',
    `active` BOOLEAN NOT NULL DEFAULT true,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    UNIQUE INDEX `CaboEleitoral_cpf_key`(`cpf`),
    INDEX `CaboEleitoral_campaignId_idx`(`campaignId`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `Contract` (
    `id` VARCHAR(191) NOT NULL,
    `caboId` VARCHAR(191) NOT NULL,
    `contractNumber` VARCHAR(191) NOT NULL,
    `startDate` DATETIME(3) NOT NULL,
    `endDate` DATETIME(3) NOT NULL,
    `monthlyValue` DOUBLE NOT NULL,
    `totalValue` DOUBLE NOT NULL,
    `functionDesc` TEXT NOT NULL,
    `status` VARCHAR(191) NOT NULL DEFAULT 'ATIVO',
    `notes` TEXT NULL,
    `pdfPath` VARCHAR(255) NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),

    UNIQUE INDEX `Contract_contractNumber_key`(`contractNumber`),
    INDEX `Contract_caboId_idx`(`caboId`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CreateTable
CREATE TABLE `AuditLog` (
    `id` VARCHAR(191) NOT NULL,
    `userId` VARCHAR(191) NULL,
    `action` VARCHAR(191) NOT NULL,
    `entity` VARCHAR(191) NOT NULL,
    `entityId` VARCHAR(191) NULL,
    `details` TEXT NULL,
    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),

    INDEX `AuditLog_entity_idx`(`entity`),
    INDEX `AuditLog_createdAt_idx`(`createdAt`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- AddForeignKey
ALTER TABLE `BankAccount` ADD CONSTRAINT `BankAccount_campaignId_fkey` FOREIGN KEY (`campaignId`) REFERENCES `Campaign`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Representative` ADD CONSTRAINT `Representative_campaignId_fkey` FOREIGN KEY (`campaignId`) REFERENCES `Campaign`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `AccountMapping` ADD CONSTRAINT `AccountMapping_bankAccountId_fkey` FOREIGN KEY (`bankAccountId`) REFERENCES `BankAccount`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `BankTransaction` ADD CONSTRAINT `BankTransaction_bankAccountId_fkey` FOREIGN KEY (`bankAccountId`) REFERENCES `BankAccount`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Revenue` ADD CONSTRAINT `Revenue_campaignId_fkey` FOREIGN KEY (`campaignId`) REFERENCES `Campaign`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Revenue` ADD CONSTRAINT `Revenue_bankAccountId_fkey` FOREIGN KEY (`bankAccountId`) REFERENCES `BankAccount`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Revenue` ADD CONSTRAINT `Revenue_createdById_fkey` FOREIGN KEY (`createdById`) REFERENCES `User`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Expense` ADD CONSTRAINT `Expense_campaignId_fkey` FOREIGN KEY (`campaignId`) REFERENCES `Campaign`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Expense` ADD CONSTRAINT `Expense_supplierId_fkey` FOREIGN KEY (`supplierId`) REFERENCES `Supplier`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Expense` ADD CONSTRAINT `Expense_bankAccountId_fkey` FOREIGN KEY (`bankAccountId`) REFERENCES `BankAccount`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Expense` ADD CONSTRAINT `Expense_caboId_fkey` FOREIGN KEY (`caboId`) REFERENCES `CaboEleitoral`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Expense` ADD CONSTRAINT `Expense_vehicleId_fkey` FOREIGN KEY (`vehicleId`) REFERENCES `Vehicle`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Expense` ADD CONSTRAINT `Expense_createdById_fkey` FOREIGN KEY (`createdById`) REFERENCES `User`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Vehicle` ADD CONSTRAINT `Vehicle_campaignId_fkey` FOREIGN KEY (`campaignId`) REFERENCES `Campaign`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Supplier` ADD CONSTRAINT `Supplier_campaignId_fkey` FOREIGN KEY (`campaignId`) REFERENCES `Campaign`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `SupplierNfe` ADD CONSTRAINT `SupplierNfe_campaignId_fkey` FOREIGN KEY (`campaignId`) REFERENCES `Campaign`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `SupplierNfe` ADD CONSTRAINT `SupplierNfe_supplierId_fkey` FOREIGN KEY (`supplierId`) REFERENCES `Supplier`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `BalanceAdjustment` ADD CONSTRAINT `BalanceAdjustment_bankAccountId_fkey` FOREIGN KEY (`bankAccountId`) REFERENCES `BankAccount`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `CaboEleitoral` ADD CONSTRAINT `CaboEleitoral_campaignId_fkey` FOREIGN KEY (`campaignId`) REFERENCES `Campaign`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `Contract` ADD CONSTRAINT `Contract_caboId_fkey` FOREIGN KEY (`caboId`) REFERENCES `CaboEleitoral`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `AuditLog` ADD CONSTRAINT `AuditLog_userId_fkey` FOREIGN KEY (`userId`) REFERENCES `User`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE `PasswordResetToken` ADD CONSTRAINT `PasswordResetToken_userId_fkey` FOREIGN KEY (`userId`) REFERENCES `User`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;


SET FOREIGN_KEY_CHECKS = 1;
