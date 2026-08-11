-- Campos novos no emitente para totais da importação TSE / CSV NF-es.
-- Em instalações já migradas, ignore erros de "Duplicate column".
-- A aplicação também aplica isto automaticamente via lib/Schema.php.

ALTER TABLE `Supplier` ADD COLUMN `quantidadeNfes` INT NOT NULL DEFAULT 0;
ALTER TABLE `Supplier` ADD COLUMN `valorTotalNfes` DOUBLE NOT NULL DEFAULT 0;
