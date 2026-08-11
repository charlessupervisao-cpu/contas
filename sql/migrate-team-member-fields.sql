-- Integrantes da equipe: código numérico automático + telefone, cidade e região
-- Em bases já migradas pelo Schema::ensure(), estas colunas já existem.

ALTER TABLE `Team` ADD COLUMN `memberNumber` INT NULL AFTER `id`;
ALTER TABLE `Team` ADD COLUMN `phone` VARCHAR(32) NULL AFTER `label`;
ALTER TABLE `Team` ADD COLUMN `city` VARCHAR(191) NULL AFTER `phone`;
ALTER TABLE `Team` ADD COLUMN `cityRegion` VARCHAR(191) NULL AFTER `city`;

-- Preencha memberNumber pela ordem atual (ajuste manual se necessário)
UPDATE `Team` t
JOIN (
  SELECT id, ROW_NUMBER() OVER (ORDER BY sortOrder ASC, createdAt ASC, label ASC) AS rn
  FROM `Team`
) x ON x.id = t.id
SET t.memberNumber = x.rn
WHERE t.memberNumber IS NULL;

ALTER TABLE `Team` MODIFY COLUMN `memberNumber` INT NOT NULL;
ALTER TABLE `Team` ADD UNIQUE INDEX `Team_memberNumber_key`(`memberNumber`);
