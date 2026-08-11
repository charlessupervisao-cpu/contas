-- Equipes (integrantes) + colunas de cabo/contrato
-- Vínculo CaboEleitoral.teamCode guarda o Team.id (não mais o slug code).
CREATE TABLE IF NOT EXISTS `Team` (
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

INSERT IGNORE INTO `Team` (`id`, `memberNumber`, `label`, `phone`, `city`, `cityRegion`, `active`, `sortOrder`, `createdAt`, `updatedAt`)
VALUES
  (REPLACE(UUID(),'-',''), 1, 'Ana Paula Mendes', '(62) 98111-1001', 'Goiânia', 'Setor Bueno', 1, 0, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 2, 'Bruno Carvalho Silva', '(62) 98222-1002', 'Aparecida de Goiânia', 'Centro', 1, 1, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 3, 'Carlos Eduardo Nunes', '(62) 98333-1003', 'Anápolis', 'Jundiaí', 1, 2, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 4, 'Fernanda Lopes Vieira', '(64) 98444-1004', 'Rio Verde', 'Setor Central', 1, 3, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 5, 'Juliana Martins Rocha', '(64) 98555-1005', 'Catalão', 'Zona Norte', 1, 4, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 6, 'Lucas Ferreira Costa', '(64) 98666-1006', 'Itumbiara', 'Centro', 1, 5, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 7, 'Patricia Souza Almeida', '(64) 98777-1007', 'Jataí', 'Setor Samuel Graham', 1, 6, NOW(3), NOW(3)),
  (REPLACE(UUID(),'-',''), 8, 'Ricardo Alves Pinto', '(61) 98888-1008', 'Luziânia', 'Parque Estrela Dalva', 1, 7, NOW(3), NOW(3));
