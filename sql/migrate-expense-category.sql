-- Categorias de despesa (dropdown de lançamento e vínculos)
CREATE TABLE IF NOT EXISTS `ExpenseCategory` (
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

INSERT IGNORE INTO `ExpenseCategory` (`id`, `code`, `label`, `color`, `active`, `sortOrder`, `createdAt`, `updatedAt`)
VALUES
  (REPLACE(UUID(),'-',''), 'COMITE', 'Comitê', '#0D9488', 1, 0, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 'GRAFICA', 'Gráfica', '#0284C7', 1, 1, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 'INTERNET', 'Internet', '#4F46E5', 1, 2, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 'VEICULOS', 'Veículos', '#EA580C', 1, 3, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 'IMPULSIONAMENTO', 'Impulsionamento', '#DB2777', 1, 4, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 'COMBUSTIVEIS', 'Combustíveis', '#CA8A04', 1, 5, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 'CABOS_ELEITORAIS', 'Cabos Eleitorais', '#16A34A', 1, 6, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 'COMUNICACAO', 'Comunicação', '#0891B2', 1, 7, NOW(3), NOW(3));
