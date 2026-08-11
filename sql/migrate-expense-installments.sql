-- Parcelamento de despesas (até 2x)
ALTER TABLE `Expense`
  ADD COLUMN IF NOT EXISTS `installmentGroupId` VARCHAR(191) NULL,
  ADD COLUMN IF NOT EXISTS `installmentNumber` INT NULL,
  ADD COLUMN IF NOT EXISTS `installmentCount` INT NULL;

-- MySQL antigo sem IF NOT EXISTS em ADD COLUMN: use Schema::ensure no bootstrap.
