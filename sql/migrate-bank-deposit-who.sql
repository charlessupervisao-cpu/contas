-- Quem pode depositar na conta de receita (CPF / CNPJ)
ALTER TABLE `BankAccount`
  ADD COLUMN `depositCpf` BOOLEAN NOT NULL DEFAULT true AFTER `balance`,
  ADD COLUMN `depositCnpj` BOOLEAN NOT NULL DEFAULT true AFTER `depositCpf`;
