-- Remove colunas de NF-e que saíram do formulário de lançamento
ALTER TABLE `Expense` DROP COLUMN IF EXISTS `modelo`;
ALTER TABLE `Expense` DROP COLUMN IF EXISTS `numeroSerie`;
ALTER TABLE `Expense` DROP COLUMN IF EXISTS `ue`;
ALTER TABLE `Expense` DROP COLUMN IF EXISTS `nfeKey`;
ALTER TABLE `Expense` DROP COLUMN IF EXISTS `nfeValid`;
ALTER TABLE `Expense` DROP COLUMN IF EXISTS `nfeValidatedAt`;
