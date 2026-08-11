-- Remove slug legado Team.code e migra CaboEleitoral.teamCode → Team.id
-- (também aplicado automaticamente em Schema::ensure no bootstrap)

ALTER TABLE `CaboEleitoral` MODIFY COLUMN `teamCode` VARCHAR(191) NULL;

UPDATE `CaboEleitoral` c
INNER JOIN `Team` t ON t.code = c.teamCode
LEFT JOIN `Team` t2 ON t2.id = c.teamCode
SET c.teamCode = t.id
WHERE c.teamCode IS NOT NULL
  AND c.teamCode <> ''
  AND t2.id IS NULL;

ALTER TABLE `Team` DROP INDEX `Team_code_key`;
ALTER TABLE `Team` DROP COLUMN `code`;
