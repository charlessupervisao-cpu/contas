-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 13/08/2026 às 23:42
-- Versão do servidor: 11.8.8-MariaDB-log
-- Versão do PHP: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `synetiqcombr_contas`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `AccountMapping`
--

CREATE TABLE `AccountMapping` (
  `id` varchar(191) NOT NULL,
  `campaignId` varchar(191) NOT NULL,
  `kind` varchar(191) NOT NULL,
  `code` varchar(191) NOT NULL,
  `bankAccountId` varchar(191) NOT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `AccountMapping`
--

INSERT INTO `AccountMapping` (`id`, `campaignId`, `kind`, `code`, `bankAccountId`, `createdAt`, `updatedAt`) VALUES
('c77cd71834e52c7c50a72e02a17020123', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'SERVICOS_ADVOCATICIOS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c034d5c455ae06a04da2e555915504417', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'SERVICOS_CONTABEIS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd7877cb04f8ef67688d0abef52365631', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'PESSOAL_MILITANCIA', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cf903cf97e795a12151bd7bed03893687', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'COMBUSTIVEIS_TRANSPORTE', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3c7b8cf41659e0cf5d75bccf15379876', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'PUBLICIDADE_GRAFICA', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c6bde938e795ad9142e2653ca04405585', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'INTERNET_IMPULSIONAMENTO', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd28b07d2da244331749d4ab180968071', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'LOCACAO_BENS_VEICULOS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c57c0cfde1967fd6fe6f6532929969450', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'COMICIOS_EVENTOS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cedfe83caea777c746f3388d794892459', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'AGUA_ENERGIA_CORREIOS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c1e18ba00c4726c1f2df89db063444668', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'ENCARGOS_TAXAS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce9d7d19d4f38902773af282991434420', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'PASSAGENS_AEREAS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cc1d0e6a24a021fc9ba1cf41d31837345', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'AQUISICAO_BENS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c5fbdc6af1ff3ad7bd2a1d25304822525', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'DOACAO_OUTRAS_CANDIDATURAS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce8afeeaa119bb889c7931fd339792172', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'DESPESAS_DIVERSAS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce1ab26ad7e17c77b05306fac90183246', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'COMITE', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cda8cd2ada47dadf91c4be96685059850', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'GRAFICA', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c36ee89788042e2b045c6bd8389169497', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'INTERNET', 'ced99a0ff2cf71f46b3ca7d7678924786', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3e0c21da2e0f3ad569bb124e60835364', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'VEICULOS', 'c5196a0b6341a7bff60590c8904238622', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cde294344d9374b17d1cf564b13470152', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'IMPULSIONAMENTO', 'ced99a0ff2cf71f46b3ca7d7678924786', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c526b509d93d9b818a851fbe113075155', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'COMBUSTIVEIS', 'c5196a0b6341a7bff60590c8904238622', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c639e02da4cc889c027be8d8b65752340', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'CABOS_ELEITORAIS', 'c519893f034acc91c3146f2c814458703', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c292f4b28e91b14e6e4564f6267785864', 'c87ede7c132830731acc772e988233493', 'EXPENSE_CATEGORY', 'COMUNICACAO', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce9a8c5d1221ebcbf70731eac58947948', 'c87ede7c132830731acc772e988233493', 'REVENUE_SOURCE', 'RECURSOS_PF', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c29d1b392fec74a4ba987368563206078', 'c87ede7c132830731acc772e988233493', 'REVENUE_SOURCE', 'RECURSOS_PROPRIOS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c39edc12ecc06f14310b02cc985505520', 'c87ede7c132830731acc772e988233493', 'REVENUE_SOURCE', 'FUNDO_PARTIDARIO', 'c519893f034acc91c3146f2c814458703', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c635b2665f4f3936edc5963b440290780', 'c87ede7c132830731acc772e988233493', 'REVENUE_SOURCE', 'FEFC', 'ced99a0ff2cf71f46b3ca7d7678924786', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd5fcd466a67ebb2b33a9600072410450', 'c87ede7c132830731acc772e988233493', 'REVENUE_SOURCE', 'FCC', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c64a47d5d03558277f761c47b88931278', 'c87ede7c132830731acc772e988233493', 'REVENUE_SOURCE', 'RECURSOS_PARTIDO', 'c519893f034acc91c3146f2c814458703', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cfc0841226ba6c1adce218d6241696767', 'c87ede7c132830731acc772e988233493', 'REVENUE_SOURCE', 'RECURSOS_OUTROS_CANDIDATOS', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c66b917d05776a5f04f55670629037156', 'c87ede7c132830731acc772e988233493', 'REVENUE_SOURCE', 'RONI', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `AuditLog`
--

CREATE TABLE `AuditLog` (
  `id` varchar(191) NOT NULL,
  `userId` varchar(191) DEFAULT NULL,
  `action` varchar(191) NOT NULL,
  `entity` varchar(191) NOT NULL,
  `entityId` varchar(191) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `AuditLog`
--

INSERT INTO `AuditLog` (`id`, `userId`, `action`, `entity`, `entityId`, `details`, `createdAt`) VALUES
('cfa73d31cd17de50b8a5e5eb521118518', 'c771a04a175c46f0b7cb2934690291848', 'CLEAR', 'SYSTEM', NULL, 'Dados operacionais limpos — pronto para abastecimento do zero', '2026-08-13 23:14:24.000'),
('c9b91e89239f610d221429c7947546876', 'c771a04a175c46f0b7cb2934690291848', 'LOAD_DEMO', 'SYSTEM', NULL, 'Base demonstrativa carregada · 5 parcelas FUTURA · campos raros (PJ, NF-e, PDF contrato, veículo, cancelada)', '2026-08-13 23:14:24.000'),
('c55c007bbf7594112c0cd3c1108736364', 'c7a81b9bf8cd1f2011e67a36246954319', 'RECONCILE', 'BankTransaction', 'c3ce4108c460858978ded02a458612136', 'Status → CONCILIADO', '2026-08-13 23:33:13.000'),
('cdb7eb9f60b93efaa28ca1cab37788806', 'c7a81b9bf8cd1f2011e67a36246954319', 'RECONCILE', 'BankTransaction', 'c29c2d221f1ed2b9b52cafd9085726939', 'Status → CONCILIADO', '2026-08-13 23:33:16.000'),
('c3c4cb1eaf4c9fea62041da4a15650698', 'c7a81b9bf8cd1f2011e67a36246954319', 'RECONCILE', 'BankTransaction', 'c4658e129403b8ca188e7c68023358961', 'Status → CONCILIADO', '2026-08-13 23:33:19.000'),
('c0db510c46dfeb0f8e7f8250376621296', 'c7a81b9bf8cd1f2011e67a36246954319', 'RECONCILE', 'BankTransaction', 'cece34177f8ac03e736d3e00f44033520', 'Status → CONCILIADO', '2026-08-13 23:33:21.000'),
('c3dfcdd84f85ada12ddbc10db32318438', 'c7a81b9bf8cd1f2011e67a36246954319', 'RECONCILE', 'BankTransaction', 'cef3adb21079d9fae580b6b2f21741175', 'Status → CONCILIADO', '2026-08-13 23:33:25.000'),
('ce1815351316ea257c33bcf6739180193', 'c7a81b9bf8cd1f2011e67a36246954319', 'DELETE', 'BankTransaction', 'ccec9ec339add35a6d9d01deb03801140', 'Excluiu lançamento do extrato: Tarifa bancária divergente', '2026-08-13 23:33:34.000'),
('c225b310c224a9b41a5f2fc5143216314', 'c7a81b9bf8cd1f2011e67a36246954319', 'RECONCILE', 'BankTransaction', 'ca1095a8f739735fe616fa38b96859574', 'Status → CONCILIADO', '2026-08-13 23:33:38.000'),
('c15d297d8a40eedf14fd5471445255879', 'c7a81b9bf8cd1f2011e67a36246954319', 'PAY', 'Expense', 'ce5ea562f0c0d736ad3aa280294129133', 'Pagou despesa futura R$ 4.500,00 · conciliação 741175', '2026-08-13 23:33:49.000'),
('c1c204efdd9fcb5ad7185934b11544815', 'c7a81b9bf8cd1f2011e67a36246954319', 'PAY', 'Expense', 'c678c743f87d83d60208a724857419897', 'Pagou despesa futura R$ 2.800,00 · conciliação 033520', '2026-08-13 23:33:51.000'),
('cd15dd75d0d253f1fb9d853e563336882', 'c7a81b9bf8cd1f2011e67a36246954319', 'PAY', 'Expense', 'c2b82f955076e9d4598085b6998299796', 'Pagou despesa futura R$ 3.200,00 · conciliação 726939', '2026-08-13 23:33:53.000'),
('ca7bcedbb2274c76272f2cf0389516858', 'c7a81b9bf8cd1f2011e67a36246954319', 'PAY', 'Expense', 'c616f863711a5c2740d7e626b69300762', 'Pagou despesa futura R$ 6.500,00 · conciliação 358961', '2026-08-13 23:33:55.000'),
('cf38c3e8a8184f8a1c7cd5a5a30811764', 'c7a81b9bf8cd1f2011e67a36246954319', 'PAY', 'Expense', 'c34c80947da78f924242e84ae35418236', 'Pagou despesa futura R$ 2.800,00 · conciliação 612136', '2026-08-13 23:33:57.000'),
('c61406f1f640721f1a27c641283309816', 'c7a81b9bf8cd1f2011e67a36246954319', 'DELETE', 'BankTransaction', 'c3ce4108c460858978ded02a458612136', 'Excluiu lançamento do extrato: Débito — Locação van itinerância (parcela 2/2)', '2026-08-13 23:34:07.000'),
('c8c971dc6e1fe6739b7bce8d128574326', 'c7a81b9bf8cd1f2011e67a36246954319', 'DELETE', 'BankTransaction', 'c29c2d221f1ed2b9b52cafd9085726939', 'Excluiu lançamento do extrato: Débito — Assessoria jurídica parcelada (parcela 2/2)', '2026-08-13 23:34:10.000'),
('c4658e33983524cad3eb5bff970316538', 'c7a81b9bf8cd1f2011e67a36246954319', 'DELETE', 'BankTransaction', 'cece34177f8ac03e736d3e00f44033520', 'Excluiu lançamento do extrato: Débito — Locação van itinerância (parcela 1/2)', '2026-08-13 23:34:17.000'),
('c4c5db0a45e636802a44e8d7d86887734', 'c7a81b9bf8cd1f2011e67a36246954319', 'DELETE', 'BankTransaction', 'c4658e129403b8ca188e7c68023358961', 'Excluiu lançamento do extrato: Débito — Locação de som — evento interior (despesa futura)', '2026-08-13 23:34:20.000'),
('caa5b6bcd0a5f71e3bcd75efc61596916', 'c7a81b9bf8cd1f2011e67a36246954319', 'DELETE', 'BankTransaction', 'cef3adb21079d9fae580b6b2f21741175', 'Excluiu lançamento do extrato: Débito — Assessoria jurídica parcelada (parcela 1/2)', '2026-08-13 23:34:24.000'),
('c919358da9aa7e049bf1d2a0e89085713', 'c7a81b9bf8cd1f2011e67a36246954319', 'UPDATE', 'Representative', 'cd1374d93b2dd51ec1d473fb490701286', 'Atualizou Helena Marques Advocacia · Administrador(a) financeiro(a)', '2026-08-13 23:36:44.000'),
('c164c031e1ba618e5720df4a611062430', 'c7a81b9bf8cd1f2011e67a36246954319', 'UPDATE', 'Representative', 'cd1374d93b2dd51ec1d473fb490701286', 'Atualizou Helena Marques Advocacia · Advogado(a)', '2026-08-13 23:37:48.000'),
('c8e0e02df6656e842110256aa72255414', 'c7a81b9bf8cd1f2011e67a36246954319', 'UPDATE', 'Representative', 'cf9d3b15aa40353be1473b7d975385147', 'Atualizou Carlos Eduardo Contabilidade · Administrador(a) financeiro(a)', '2026-08-13 23:37:55.000'),
('c7cf850342b5c82a451b5cf1694247273', 'c7a81b9bf8cd1f2011e67a36246954319', 'CREATE', 'Representative', 'c66b817ef7878c8fe99803cbc11240356', 'Cadastrou Charles Benrardo · Contabilista', '2026-08-13 23:38:30.000'),
('c784473ad82abb5fc34fd70c690961255', 'c7a81b9bf8cd1f2011e67a36246954319', 'UPDATE', 'Campaign', 'c87ede7c132830731acc772e988233493', 'Atualizou dados da campanha · CNPJ 47.552.932/0001-00', '2026-08-13 23:40:00.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `BalanceAdjustment`
--

CREATE TABLE `BalanceAdjustment` (
  `id` varchar(191) NOT NULL,
  `bankAccountId` varchar(191) NOT NULL,
  `previousBalance` double NOT NULL,
  `newBalance` double NOT NULL,
  `difference` double NOT NULL,
  `date` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `reason` text NOT NULL,
  `notes` text DEFAULT NULL,
  `createdById` varchar(191) DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `BalanceAdjustment`
--

INSERT INTO `BalanceAdjustment` (`id`, `bankAccountId`, `previousBalance`, `newBalance`, `difference`, `date`, `reason`, `notes`, `createdById`, `createdAt`) VALUES
('cb89027577e60571963c44ecd06465975', 'cfe45658d89de2113001ff90633398322', 280000, 285400, 5400, '2026-08-13 23:14:24.000', 'Ajuste de abertura conforme extrato BB', 'Demo: exercita campo notes do ajuste', 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `BankAccount`
--

CREATE TABLE `BankAccount` (
  `id` varchar(191) NOT NULL,
  `campaignId` varchar(191) NOT NULL,
  `label` varchar(191) NOT NULL,
  `bankName` varchar(191) NOT NULL,
  `bankCode` varchar(191) NOT NULL,
  `agency` varchar(191) NOT NULL,
  `accountNumber` varchar(191) NOT NULL,
  `accountType` varchar(191) NOT NULL DEFAULT 'Corrente',
  `resourceOrigin` varchar(64) DEFAULT NULL,
  `openedAt` date DEFAULT NULL,
  `balance` double NOT NULL DEFAULT 0,
  `depositCpf` tinyint(1) NOT NULL DEFAULT 1,
  `depositCnpj` tinyint(1) NOT NULL DEFAULT 1,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `BankAccount`
--

INSERT INTO `BankAccount` (`id`, `campaignId`, `label`, `bankName`, `bankCode`, `agency`, `accountNumber`, `accountType`, `resourceOrigin`, `openedAt`, `balance`, `depositCpf`, `depositCnpj`, `active`, `sortOrder`, `createdAt`, `updatedAt`) VALUES
('cfe45658d89de2113001ff90633398322', 'c87ede7c132830731acc772e988233493', 'Conta Doações para Campanha', 'Banco do Brasil', '001', '3456-7', '12345-6', 'Corrente', 'DOACOES_CAMPANHA', '2026-07-01', 265600, 1, 0, 1, 0, '2026-08-13 23:14:24.000', '2026-08-13 23:33:57.000'),
('c519893f034acc91c3146f2c814458703', 'c87ede7c132830731acc772e988233493', 'Conta Fundo Partidário', 'Caixa Econômica Federal', '104', '1289', '98765-4', 'Corrente', 'FUNDO_PARTIDARIO', '2026-07-01', 180000, 0, 1, 1, 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ced99a0ff2cf71f46b3ca7d7678924786', 'c87ede7c132830731acc772e988233493', 'Conta FEFC', 'Itaú Unibanco', '341', '4521', '55432-1', 'Corrente', 'FEFC', '2026-07-05', 150000, 0, 1, 1, 2, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c5196a0b6341a7bff60590c8904238622', 'c87ede7c132830731acc772e988233493', 'Conta Operacional', 'Santander', '033', '2100', '77881-0', 'Corrente', 'DOACOES_CAMPANHA', '2026-07-15', 41320, 1, 0, 1, 3, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `BankTransaction`
--

CREATE TABLE `BankTransaction` (
  `id` varchar(191) NOT NULL,
  `bankAccountId` varchar(191) NOT NULL,
  `date` datetime(3) NOT NULL,
  `description` text NOT NULL,
  `amount` double NOT NULL,
  `type` varchar(191) NOT NULL,
  `documentRef` varchar(191) DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'PENDENTE',
  `matchedExpenseId` varchar(191) DEFAULT NULL,
  `matchedRevenueId` varchar(191) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `BankTransaction`
--

INSERT INTO `BankTransaction` (`id`, `bankAccountId`, `date`, `description`, `amount`, `type`, `documentRef`, `status`, `matchedExpenseId`, `matchedRevenueId`, `notes`, `createdAt`, `updatedAt`) VALUES
('cb9982983d0af12cf8d4a17ed04626383', 'c519893f034acc91c3146f2c814458703', '2026-08-08 12:00:00.000', 'Crédito — Repasse fundo partidário — parcela 1', 280000, 'CREDITO', NULL, 'CONCILIADO', NULL, 'c3f3fadc9459992b5692cdf1411758896', NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c85ddc99f60f0a00e4d3ec01893490669', 'c519893f034acc91c3146f2c814458703', '2026-09-05 12:00:00.000', 'Crédito — Repasse fundo partidário — parcela 2', 120000, 'CREDITO', NULL, 'CONCILIADO', NULL, 'c8b5464c26398954df84f57d896329223', NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cf902aa60b35d5413f8c4135b31590731', 'ced99a0ff2cf71f46b3ca7d7678924786', '2026-08-15 12:00:00.000', 'Crédito — Repasse FEFC — parcela 1', 95000, 'CREDITO', NULL, 'CONCILIADO', NULL, 'c4af3e14793c5ca4c99d9a41b16806394', NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c99e5484b2c03e335fe9a58e264302973', 'cfe45658d89de2113001ff90633398322', '2026-08-22 12:00:00.000', 'Crédito — Arrecadação FCC — ciclo 1', 48500, 'CREDITO', NULL, 'CONCILIADO', NULL, 'cf46572f85d0b9afbdd6b528a04803871', NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cef2ec97a118b37286a209fb165395297', 'cfe45658d89de2113001ff90633398322', '2026-09-19 12:00:00.000', 'Crédito — Arrecadação FCC — ciclo 2', 31200, 'CREDITO', NULL, 'CONCILIADO', NULL, 'ce0db753e191f6eb1ea4860a924543673', NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3607d9995d75803ad82cd8c364251978', 'cfe45658d89de2113001ff90633398322', '2026-10-03 12:00:00.000', 'Crédito — Arrecadação FCC — ciclo final', 27800, 'CREDITO', NULL, 'CONCILIADO', NULL, 'cd3b63d9ab651f7c66924242c48478593', NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cab46dc4631539ff08bf0a01028221120', 'cfe45658d89de2113001ff90633398322', '2026-08-08 12:00:00.000', 'Crédito — Doação pessoa física', 1500, 'CREDITO', NULL, 'CONCILIADO', NULL, 'c8041f022dd33d7a1e9ea307a56984255', NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cfe77695593c23b36e2483ac599860277', 'cfe45658d89de2113001ff90633398322', '2026-08-15 12:00:00.000', 'Crédito — Doação pessoa física', 2350, 'CREDITO', NULL, 'CONCILIADO', NULL, 'ca47022176b5e31efb05d376470320661', NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cc5548f4201a0c3f36be41fa936072011', 'cfe45658d89de2113001ff90633398322', '2026-08-08 12:00:00.000', 'Débito — Material gráfico — GRAFOPEL (ref. TSE 2022)', -42000, 'DEBITO', NULL, 'CONCILIADO', 'cc13fcb1a6aaf01f726b04e6925273066', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c9de5b9c7eb539d9cebd222e955691074', 'cfe45658d89de2113001ff90633398322', '2026-08-15 12:00:00.000', 'Débito — Locação — SMART LOC (ref. TSE 2022)', -28000, 'DEBITO', NULL, 'CONCILIADO', 'c75bf3db12496ce9dbfa598ff62555852', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c9a5bf02bf6b163b7dc8e0a0b91868489', 'ced99a0ff2cf71f46b3ca7d7678924786', '2026-08-22 12:00:00.000', 'Débito — Impulsionamento Facebook / Meta (ref. TSE 2022)', -29465, 'DEBITO', NULL, 'CONCILIADO', 'cf81c9c264c778758b43020d720943345', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c8209864a508bb4f46ca276ba45849826', 'ced99a0ff2cf71f46b3ca7d7678924786', '2026-08-29 12:00:00.000', 'Débito — Google Ads / NFS-e (ref. TSE 2022)', -15500, 'DEBITO', NULL, 'CONCILIADO', 'cb5603d7c917c992065a0ed0609640753', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c4faebdd268a0cf28c76eb56882341531', 'c5196a0b6341a7bff60590c8904238622', '2026-09-05 12:00:00.000', 'Débito — Abastecimento — Posto Xodó (ref. TSE 2022)', -12400, 'DEBITO', NULL, 'CONCILIADO', 'c0a4e95e33fd8e602e65cc09120531283', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c76b6b84193dda5fa5b5cb1de83940631', 'cfe45658d89de2113001ff90633398322', '2026-09-12 12:00:00.000', 'Débito — Locação — Premium Tur (ref. TSE 2022)', -9500, 'DEBITO', NULL, 'CONCILIADO', 'c4b552ba6124dd361401d3f8473544287', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0481e111a38da4ae72a3065642613297', 'cfe45658d89de2113001ff90633398322', '2026-09-19 12:00:00.000', 'Débito — Evento / música — Dreams (ref. TSE 2022)', -12000, 'DEBITO', NULL, 'CONCILIADO', 'c3917b997fb943cd01fc6b95072468947', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd4cf05924f4104db6a6dd23b98863862', 'cfe45658d89de2113001ff90633398322', '2026-09-26 12:00:00.000', 'Débito — Produção — RS Produtos e Serviços (ref. TSE 2022)', -8000, 'DEBITO', NULL, 'CONCILIADO', 'c11d4f918c4175b743a9c658f91883595', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb53301a301af3b10393bd61021373603', 'c5196a0b6341a7bff60590c8904238622', '2026-10-03 12:00:00.000', 'Débito — Comunicação visual — Conexão Digital (ref. TSE 2022)', -3200, 'DEBITO', NULL, 'CONCILIADO', 'c44a07c7e6a9fa82fd4c8881e02083032', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd9a56fe362bae2068393b4e265275223', 'cfe45658d89de2113001ff90633398322', '2026-08-08 12:00:00.000', 'Débito — Confecção / malharia — TZ (ref. TSE 2022)', -16420, 'DEBITO', NULL, 'CONCILIADO', 'c82ebfc4840bffd8810a22f3b60615300', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca1095a8f739735fe616fa38b96859574', 'cfe45658d89de2113001ff90633398322', '2026-08-13 23:14:24.000', 'TED pendente — transferência interna', -1500, 'DEBITO', NULL, 'CONCILIADO', NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:33:38.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `CaboEleitoral`
--

CREATE TABLE `CaboEleitoral` (
  `id` varchar(191) NOT NULL,
  `campaignId` varchar(191) NOT NULL,
  `fullName` varchar(191) NOT NULL,
  `cpf` varchar(191) NOT NULL,
  `rg` varchar(191) DEFAULT NULL,
  `birthDate` varchar(191) DEFAULT NULL,
  `motherName` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `zipCode` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `addressNumber` varchar(191) DEFAULT NULL,
  `city` varchar(191) DEFAULT NULL,
  `state` varchar(191) DEFAULT 'GO',
  `zone` varchar(191) DEFAULT NULL,
  `teamCode` varchar(191) DEFAULT NULL,
  `roleTitle` varchar(191) NOT NULL DEFAULT 'Cabo Eleitoral',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `CaboEleitoral`
--

INSERT INTO `CaboEleitoral` (`id`, `campaignId`, `fullName`, `cpf`, `rg`, `birthDate`, `motherName`, `phone`, `email`, `zipCode`, `address`, `addressNumber`, `city`, `state`, `zone`, `teamCode`, `roleTitle`, `active`, `createdAt`, `updatedAt`) VALUES
('cd24a6e387c19b9dc4345101282227116', 'c87ede7c132830731acc772e988233493', 'Marcos Antônio Pereira', '10000200026', 'MG-1000000', '1985-04-12', 'Maria das Dores Pereira', '(62) 98000-1000', 'cabo1@campanha2026.go', '74000-010', 'Rua das Palmeiras', '100', 'Goiânia', 'GO', 'Zona Norte', 'default-1', 'Cabo Eleitoral', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c6b16de78e63e7475854c72ac79229681', 'c87ede7c132830731acc772e988233493', 'Sueli Aparecida Ramos', '10000200107', 'MG-1000001', '1990-11-03', 'Ana Ramos Silva', '(62) 98001-1001', 'cabo2@campanha2026.go', '74900-000', 'Rua das Palmeiras', '101', 'Aparecida de Goiânia', 'GO', 'Centro', 'default-2', 'Cabo Eleitoral', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0ef0178aaa3b4d9c0c8e397885983131', 'c87ede7c132830731acc772e988233493', 'Diego Fernandes Lima', '10000200298', 'MG-1000002', '1988-07-21', 'Helena Lima', '(62) 98002-1002', 'cabo3@campanha2026.go', '75000-100', 'Rua das Palmeiras', '102', 'Anápolis', 'GO', 'Zona Sul', 'default-3', 'Cabo Eleitoral', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c2656562247380fde36f0e77096995078', 'c87ede7c132830731acc772e988233493', 'Camila Rodrigues', '10000200379', 'MG-1000003', '1992-01-30', 'Rosa Rodrigues', '(62) 98003-1003', 'cabo4@campanha2026.go', '75900-200', 'Rua das Palmeiras', '103', 'Rio Verde', 'GO', 'Centro', 'default-4', 'Cabo Eleitoral', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c96e01ddd4aff7b97853cef1186019825', 'c87ede7c132830731acc772e988233493', 'Rafael Souza Melo', '10000200450', 'MG-1000004', '1983-09-08', 'Terezinha Melo', '(62) 98004-1004', 'cabo5@campanha2026.go', '75700-300', 'Rua das Palmeiras', '104', 'Catalão', 'GO', 'Zona Leste', 'default-5', 'Cabo Eleitoral', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca9bf7bcc019667ccd82e344a88613320', 'c87ede7c132830731acc772e988233493', 'Helena Cristina Dias', '10000200530', 'MG-1000005', '1995-05-17', 'Célia Dias', '(62) 98005-1005', 'cabo6@campanha2026.go', '75500-400', 'Rua das Palmeiras', '105', 'Itumbiara', 'GO', 'Centro', 'default-6', 'Cabo Eleitoral', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c75174edec3cb1e3d021913c502342982', 'c87ede7c132830731acc772e988233493', 'Gustavo Henrique Alves', '10000200611', 'MG-1000006', '1987-12-25', 'Aparecida Alves', '(62) 98006-1006', 'cabo7@campanha2026.go', '75800-500', 'Rua das Palmeiras', '106', 'Jataí', 'GO', 'Zona Oeste', 'default-7', 'Cabo Eleitoral', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c6ff1f792787a8a4ba11bc81531309102', 'c87ede7c132830731acc772e988233493', 'Beatriz Oliveira', '10000200700', 'MG-1000007', '1991-03-14', 'Joana Oliveira', '(62) 98007-1007', 'cabo8@campanha2026.go', '72800-600', 'Rua das Palmeiras', '107', 'Luziânia', 'GO', 'Centro', NULL, 'Cabo Eleitoral', 0, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Campaign`
--

CREATE TABLE `Campaign` (
  `id` varchar(191) NOT NULL,
  `electionYear` int(11) NOT NULL DEFAULT 2026,
  `candidateName` varchar(191) NOT NULL,
  `candidateFullName` varchar(191) NOT NULL,
  `candidateNumber` varchar(191) NOT NULL,
  `party` varchar(191) NOT NULL,
  `partyNumber` varchar(191) NOT NULL,
  `cnpjCampaign` varchar(191) DEFAULT NULL,
  `office` varchar(191) NOT NULL DEFAULT 'Deputado Estadual',
  `state` varchar(191) NOT NULL DEFAULT 'GO',
  `region` varchar(191) NOT NULL DEFAULT 'CENTROOESTE',
  `ballotName` varchar(191) DEFAULT NULL,
  `situation` varchar(191) NOT NULL DEFAULT 'Em campanha',
  `reelection` tinyint(1) NOT NULL DEFAULT 1,
  `legalSpendLimit` double NOT NULL,
  `totalBudget` double NOT NULL,
  `birthDate` varchar(191) DEFAULT NULL,
  `gender` varchar(191) DEFAULT NULL,
  `education` varchar(191) DEFAULT NULL,
  `occupation` varchar(191) DEFAULT NULL,
  `nationality` varchar(191) DEFAULT NULL,
  `website` varchar(191) DEFAULT NULL,
  `photoUrl` varchar(191) DEFAULT NULL,
  `electoralTitle` varchar(191) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `addressZip` varchar(16) DEFAULT NULL,
  `addressStreet` varchar(191) DEFAULT NULL,
  `addressNumber` varchar(32) DEFAULT NULL,
  `addressComplement` varchar(191) DEFAULT NULL,
  `addressDistrict` varchar(191) DEFAULT NULL,
  `addressCity` varchar(191) DEFAULT NULL,
  `addressState` varchar(8) DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Campaign`
--

INSERT INTO `Campaign` (`id`, `electionYear`, `candidateName`, `candidateFullName`, `candidateNumber`, `party`, `partyNumber`, `cnpjCampaign`, `office`, `state`, `region`, `ballotName`, `situation`, `reelection`, `legalSpendLimit`, `totalBudget`, `birthDate`, `gender`, `education`, `occupation`, `nationality`, `website`, `photoUrl`, `electoralTitle`, `phone`, `email`, `addressZip`, `addressStreet`, `addressNumber`, `addressComplement`, `addressDistrict`, `addressCity`, `addressState`, `createdAt`, `updatedAt`) VALUES
('c87ede7c132830731acc772e988233493', 2026, 'Virmondes Cruvinel', 'Virmondes Borges Cruvinel Filho', '44321', 'UNIÃO', '44', '47.552.932/0001-00', 'Deputado Estadual', 'GO', 'CENTROOESTE', 'Virmondes Cruvinel', 'Em campanha — Prestação de contas 2026', 1, 1270629.01, 1200000, '10/03/1980', 'Masculino', 'Superior Completo', 'Advogado / Deputado Estadual', 'Brasileira Nata / GO-Goiânia', 'https://virmondes.com.br', NULL, NULL, NULL, NULL, '74610240', 'RUA 260', '380', NULL, 'UNIVERSITARIO', 'GOIANIA', 'GO', '2026-08-13 23:14:24.000', '2026-08-13 23:40:00.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Contract`
--

CREATE TABLE `Contract` (
  `id` varchar(191) NOT NULL,
  `caboId` varchar(191) NOT NULL,
  `contractNumber` varchar(191) NOT NULL,
  `startDate` datetime(3) NOT NULL,
  `endDate` datetime(3) NOT NULL,
  `monthlyValue` double NOT NULL,
  `totalValue` double NOT NULL,
  `functionDesc` text NOT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'ATIVO',
  `notes` text DEFAULT NULL,
  `pdfPath` varchar(255) DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Contract`
--

INSERT INTO `Contract` (`id`, `caboId`, `contractNumber`, `startDate`, `endDate`, `monthlyValue`, `totalValue`, `functionDesc`, `status`, `notes`, `pdfPath`, `createdAt`, `updatedAt`) VALUES
('c61c5b41adb0f5808ece5bdce89196262', 'cd24a6e387c19b9dc4345101282227116', 'CT-2026-0001', '2026-07-01 00:00:00.000', '2026-10-05 00:00:00.000', 2200, 6600, 'Articulação territorial e mobilização eleitoral por prazo determinado.', 'ATIVO', 'Contrato com PDF de demonstração anexado.', '/uploads/contracts/contrato-demo-c61c5b41adb0f5808ece5bdce89196262.pdf', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd16000b995b1b84b39393ab982193503', 'c6b16de78e63e7475854c72ac79229681', 'CT-2026-0002', '2026-07-01 00:00:00.000', '2026-10-05 00:00:00.000', 2300, 6900, 'Articulação territorial e mobilização eleitoral por prazo determinado.', 'ATIVO', NULL, '/uploads/contracts/contrato-demo-cd16000b995b1b84b39393ab982193503.pdf', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c5d705f152043b8e55adf63f361921324', 'c0ef0178aaa3b4d9c0c8e397885983131', 'CT-2026-0003', '2026-07-01 00:00:00.000', '2026-10-05 00:00:00.000', 2400, 7200, 'Articulação territorial e mobilização eleitoral por prazo determinado.', 'ATIVO', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c8a11d4b3604537b762ad9f0220546026', 'c2656562247380fde36f0e77096995078', 'CT-2026-0004', '2026-07-01 00:00:00.000', '2026-10-05 00:00:00.000', 2500, 7500, 'Articulação territorial e mobilização eleitoral por prazo determinado.', 'ATIVO', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cab9368d2987436fa7d6c3c7004351335', 'c96e01ddd4aff7b97853cef1186019825', 'CT-2026-0005', '2026-07-01 00:00:00.000', '2026-10-05 00:00:00.000', 2600, 7800, 'Articulação territorial e mobilização eleitoral por prazo determinado.', 'ATIVO', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c41c059c3cdf70173aa3ec09374480525', 'ca9bf7bcc019667ccd82e344a88613320', 'CT-2026-0006', '2026-07-01 00:00:00.000', '2026-10-05 00:00:00.000', 2700, 8100, 'Articulação territorial e mobilização eleitoral por prazo determinado.', 'ATIVO', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c06b01f778ab395eff7aa1b0f75334953', 'c75174edec3cb1e3d021913c502342982', 'CT-2026-0007', '2026-07-01 00:00:00.000', '2026-10-05 00:00:00.000', 2800, 8400, 'Articulação territorial e mobilização eleitoral por prazo determinado.', 'ATIVO', NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c4a1283062fabf3e68720ae8c10824922', 'c6ff1f792787a8a4ba11bc81531309102', 'CT-2026-0008', '2026-07-01 00:00:00.000', '2026-10-05 00:00:00.000', 2900, 8700, 'Articulação territorial e mobilização eleitoral por prazo determinado.', 'ENCERRADO', 'Contrato encerrado — cabo inativo.', NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Expense`
--

CREATE TABLE `Expense` (
  `id` varchar(191) NOT NULL,
  `campaignId` varchar(191) NOT NULL,
  `category` varchar(191) NOT NULL,
  `supplierName` varchar(191) NOT NULL,
  `supplierDoc` varchar(191) DEFAULT NULL,
  `supplierId` varchar(191) DEFAULT NULL,
  `description` text NOT NULL,
  `amount` double NOT NULL,
  `date` datetime(3) NOT NULL,
  `status` varchar(191) NOT NULL DEFAULT 'LANCADA',
  `naturezaOp` varchar(191) DEFAULT NULL,
  `dataEmissao` date DEFAULT NULL,
  `numeroNf` varchar(191) DEFAULT NULL,
  `unidadeArrecadadora` varchar(191) DEFAULT NULL,
  `dsUe` varchar(191) DEFAULT NULL,
  `nfeLink` text DEFAULT NULL,
  `importSource` varchar(191) DEFAULT NULL,
  `bankAccountId` varchar(191) DEFAULT NULL,
  `caboId` varchar(191) DEFAULT NULL,
  `vehicleId` varchar(191) DEFAULT NULL,
  `installmentGroupId` varchar(191) DEFAULT NULL,
  `installmentNumber` int(11) DEFAULT NULL,
  `installmentCount` int(11) DEFAULT NULL,
  `createdById` varchar(191) DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Expense`
--

INSERT INTO `Expense` (`id`, `campaignId`, `category`, `supplierName`, `supplierDoc`, `supplierId`, `description`, `amount`, `date`, `status`, `naturezaOp`, `dataEmissao`, `numeroNf`, `unidadeArrecadadora`, `dsUe`, `nfeLink`, `importSource`, `bankAccountId`, `caboId`, `vehicleId`, `installmentGroupId`, `installmentNumber`, `installmentCount`, `createdById`, `createdAt`, `updatedAt`) VALUES
('ce31f06a5a9cec7f0bb839f8c55239110', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GOOGLE BRASIL INTERNET LTDA.', '06.990.590/0001-23', 'c050c7ffe7e6422abd1db891731467813', 'NF 19382695 · SERVICO · DivulgaCandContas', 1000, '2022-10-02 12:00:00.000', 'LANCADA', 'SERVICO', '2022-10-02', '19382695', 'PREFEITURA MUNICIPAL DE SÃO PAULO', 'SÃO PAULO', 'https://nfe.prefeitura.sp.gov.br/contribuinte/notaprint.aspx?ccm=33555800&nf=19382695&cod=IBEP2UER', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c442e5925724673d369e1a31e32579255', 'c87ede7c132830731acc772e988233493', 'COMITE', 'FACEBOOK SERVICOS ONLINE DO BRASIL LTDA.', '13.347.016/0001-17', 'c1dc0c9f87a9a68bb664e5aa187936386', 'NF 51249560 · SERVICO · DivulgaCandContas', 21371.42, '2022-10-02 12:00:00.000', 'LANCADA', 'SERVICO', '2022-10-02', '51249560', 'PREFEITURA MUNICIPAL DE SÃO PAULO', 'SÃO PAULO', 'https://nfe.prefeitura.sp.gov.br/contribuinte/notaprint.aspx?ccm=42427630&nf=51249560&cod=47LARMNK', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca1d35f729cb7ad6ef09712fb81695308', 'c87ede7c132830731acc772e988233493', 'COMITE', 'DREAMS - JRS MUSICA, ARTE E ENTRETENIMENTO LTDA', '47.413.717/0001-29', 'ccb189bf8a3309f75ce646b3c41169131', 'NF 21 · SERVICO · DivulgaCandContas', 3000, '2022-09-30 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-30', '21', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce9fac4895ff06653029fdccf25754559', 'c87ede7c132830731acc772e988233493', 'COMITE', 'PREMIUM TUR LOCADORA LTDA', '24.095.599/0001-52', 'cfe9b2456ce048d77c25e3cef93006954', 'NF 159 · SERVICO · DivulgaCandContas', 2000, '2022-09-30 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-30', '159', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c12a649f3568e66f3f233e41825841744', 'c87ede7c132830731acc772e988233493', 'COMITE', '39.571.991 BRUNO GONCALVES SOUZA LACERDA', '39.571.991/0001-06', 'c83110b7657726bd0ce08505a05962963', 'NF 25 · SERVICO · DivulgaCandContas', 500, '2022-09-30 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-30', '25', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c2d2ec151be4b63cd89b7243715965195', 'c87ede7c132830731acc772e988233493', 'COMITE', 'FESTIVITA LOCACAO DE MATERIAL E MOBILIARIO PARA EVENTOS LTDA', '06.788.342/0001-02', 'cb7d5ffd57eb06bb62d9789c489594579', 'NF 15111 · SERVICO · DivulgaCandContas', 112, '2022-09-29 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-29', '15111', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c6e1924bf21b15ab1cdc0634603553075', 'c87ede7c132830731acc772e988233493', 'COMITE', 'RS PRODUTOS E SERVICOS LTDA', '06.273.582/0001-66', 'cf72884724572130755d8b64062040877', 'NF 15007 · SERVICO · DivulgaCandContas', 2000, '2022-09-29 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-29', '15007', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce8bb350aee0ba26c2b0c314255515954', 'c87ede7c132830731acc772e988233493', 'COMITE', 'RS PRODUTOS E SERVICOS LTDA', '06.273.582/0001-66', 'cf72884724572130755d8b64062040877', 'NF 15008 · SERVICO · DivulgaCandContas', 2000, '2022-09-29 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-29', '15008', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c8c86a7fac26a3f7716203fa368750512', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4343 · SERVICO · DivulgaCandContas', 380, '2022-09-28 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-28', '4343', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cf99999d0d925fd40c363353498370248', 'c87ede7c132830731acc772e988233493', 'COMITE', 'ROFE RENTAL E COMERCIO LTDA', '21.135.490/0001-03', 'c48d5c91238c3d9f621ae859896791296', 'NF 376 · SERVICO · DivulgaCandContas', 2200, '2022-09-28 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-28', '376', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c93d15b126fdf22e701999d2d45367706', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 396 · COMPRA · DivulgaCandContas', 2555.9, '2022-09-28 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-28', '396', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cfcbc8fca59bbcc18ab7b1c0c12659325', 'c87ede7c132830731acc772e988233493', 'COMITE', 'PROMARKET PROMOCAO DE EVENTOS E LOGISTICA LTDA', '37.249.018/0001-31', 'cb3251457a3416294ec504c4275512453', 'NF 15771 · SERVICO · DivulgaCandContas', 6500, '2022-09-28 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-28', '15771', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c5285c3961c8aa1cdbc79a37f31340709', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 396 · COMPRA · DivulgaCandContas', 2555.9, '2022-09-28 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-28', '396', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c46afd4eaa790381efa34dd9156392882', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4314 · SERVICO · DivulgaCandContas', 2080, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '4314', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c95b866f32e5e6d3040be27cf76868901', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4310 · SERVICO · DivulgaCandContas', 9748, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '4310', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3d3a8b8e4226bdff34979bd916161994', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4316 · SERVICO · DivulgaCandContas', 7055, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '4316', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c164029fa7fdb373e82f951c106431877', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4328 · SERVICO · DivulgaCandContas', 4576, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '4328', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cc4f4d01982931c75bd06956056085015', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO Z + Z LARANJEIRAS LTDA', '03.311.068/0001-80', 'c598f461ee1f02474de8e56f043004695', 'NF 13888 · COMPRA · DivulgaCandContas', 500, '2022-09-27 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-27', '13888', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c5089f235936927745b458abf20775449', 'c87ede7c132830731acc772e988233493', 'COMITE', 'FERNANDES COMBUSTIVEIS LTDA', '07.832.010/0001-32', 'c4bd014b5c56078f995b633c430406407', 'NF 47 · COMPRA · DivulgaCandContas', 245.03, '2022-09-27 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-27', '47', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c338838e8bd0826f750d4759518612764', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4313 · SERVICO · DivulgaCandContas', 6633, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '4313', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c02244841201066d417f55a4995222724', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 394615 · COMPRA · DivulgaCandContas', 584.67, '2022-09-27 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-27', '394615', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c26e33e66dc58cc890e9554a227880917', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4315 · SERVICO · DivulgaCandContas', 5750, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '4315', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c907a1625aef3fdf99912370146420920', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4318 · SERVICO · DivulgaCandContas', 3510, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '4318', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb6010db388a8f4ce47eebbe086952776', 'c87ede7c132830731acc772e988233493', 'COMITE', 'FERNANDES COMBUSTIVEIS LTDA', '07.832.010/0001-32', 'c4bd014b5c56078f995b633c430406407', 'NF 47 · COMPRA · DivulgaCandContas', 245.03, '2022-09-27 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-27', '47', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca4dfa83ad713b01866b8872216882420', 'c87ede7c132830731acc772e988233493', 'COMITE', 'JC GRAFICA E EDITORA LTDA', '24.578.406/0001-14', 'cde9d46e3b7dfb377e96b8d5266834739', 'NF 3200 · SERVICO · DivulgaCandContas', 1200, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '3200', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd88b8e9cf4ffa853714313d919396170', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO Z + Z LARANJEIRAS LTDA', '03.311.068/0001-80', 'c598f461ee1f02474de8e56f043004695', 'NF 13888 · COMPRA · DivulgaCandContas', 500, '2022-09-27 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-27', '13888', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd8f8d61e5eb27887f2869fc916853944', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4317 · SERVICO · DivulgaCandContas', 12380, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '4317', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c2721fa7bcce6c4622a9234e815046862', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4308 · SERVICO · DivulgaCandContas', 2504, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '4308', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c1a04f97dbf848f2a67a91a6088020040', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4309 · SERVICO · DivulgaCandContas', 14490, '2022-09-27 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-27', '4309', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c9406e73b6455313533fc747059422731', 'c87ede7c132830731acc772e988233493', 'COMITE', 'CONEXAO DIGITAL SOLUTION - COMUNICACAO VISUAL LTDA', '14.707.720/0001-04', 'c0ffe089990027c13677b01c281087071', 'NF 2918 · COMPRA · DivulgaCandContas', 12557.8, '2022-09-26 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-26', '2918', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('caef1597b1ba4526fec9194af80431468', 'c87ede7c132830731acc772e988233493', 'COMITE', 'CONEXAO DIGITAL SOLUTION - COMUNICACAO VISUAL LTDA', '14.707.720/0001-04', 'c0ffe089990027c13677b01c281087071', 'NF 2917 · COMPRA · DivulgaCandContas', 1185, '2022-09-26 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-26', '2917', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c319e096291be0dbe0d8f663d29478826', 'c87ede7c132830731acc772e988233493', 'COMITE', 'CONEXAO DIGITAL SOLUTION - COMUNICACAO VISUAL LTDA', '14.707.720/0001-04', 'c0ffe089990027c13677b01c281087071', 'NF 2917 · COMPRA · DivulgaCandContas', 1185, '2022-09-26 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-26', '2917', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c6bb52986e0f0151ac6349cfb43972757', 'c87ede7c132830731acc772e988233493', 'COMITE', 'CONEXAO DIGITAL SOLUTION - COMUNICACAO VISUAL LTDA', '14.707.720/0001-04', 'c0ffe089990027c13677b01c281087071', 'NF 2918 · COMPRA · DivulgaCandContas', 12557.8, '2022-09-26 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-26', '2918', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3f7ea5556ad79eee1aa411c057475865', 'c87ede7c132830731acc772e988233493', 'COMITE', 'CONEXAO DIGITAL SOLUTION - COMUNICACAO VISUAL LTDA', '14.707.720/0001-04', 'c0ffe089990027c13677b01c281087071', 'NF 2916 · COMPRA · DivulgaCandContas', 1580, '2022-09-26 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-26', '2916', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c4ac14de16a43b3c73a9c3dd388335249', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 394463 · COMPRA · DivulgaCandContas', 190.04, '2022-09-26 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-26', '394463', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c9204f04b52ccd4483c6e9a0d67545014', 'c87ede7c132830731acc772e988233493', 'COMITE', 'TZ - CONFECCAO, MALHARIA E EMBALAGENS LTDA', '14.647.750/0001-64', 'c772b960c79a6e10254e1a1b354706492', 'NF 1472 · COMPRA · DivulgaCandContas', 16420, '2022-09-26 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-26', '1472', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cae2b9c0f8c1600cc68d7a75b63079009', 'c87ede7c132830731acc772e988233493', 'COMITE', 'CONEXAO DIGITAL SOLUTION - COMUNICACAO VISUAL LTDA', '14.707.720/0001-04', 'c0ffe089990027c13677b01c281087071', 'NF 2916 · COMPRA · DivulgaCandContas', 1580, '2022-09-26 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-26', '2916', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c031d8ef0d14d95fcd1e55af931802638', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 394339 · COMPRA · DivulgaCandContas', 190.15, '2022-09-26 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-26', '394339', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cbd86fb1428fa4a18c4100d2738186332', 'c87ede7c132830731acc772e988233493', 'COMITE', 'TZ - CONFECCAO, MALHARIA E EMBALAGENS LTDA', '14.647.750/0001-64', 'c772b960c79a6e10254e1a1b354706492', 'NF 1472 · COMPRA · DivulgaCandContas', 16420, '2022-09-26 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-26', '1472', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0dc8de1e16d093f13683758993812808', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 394177 · COMPRA · DivulgaCandContas', 200.03, '2022-09-24 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-24', '394177', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb4be9e91a485aedf5b22c68617699836', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 394172 · COMPRA · DivulgaCandContas', 190.53, '2022-09-24 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-24', '394172', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca29f9e8b05fb6de787c7176073722477', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 394002 · COMPRA · DivulgaCandContas', 173.83, '2022-09-23 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-23', '394002', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0cad3135b792b8283b0fa04029619769', 'c87ede7c132830731acc772e988233493', 'COMITE', 'ECONOMICA GRAFICA RAPIDA LTDA', '00.110.583/0001-03', 'c4d416e7fbbb72599b215d0b779526772', 'NF 11293 · SERVICO · DivulgaCandContas', 400, '2022-09-23 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-23', '11293', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c7ae16814ca9796f5ecf2a32d29665773', 'c87ede7c132830731acc772e988233493', 'COMITE', 'DREAMS - JRS MUSICA, ARTE E ENTRETENIMENTO LTDA', '47.413.717/0001-29', 'ccb189bf8a3309f75ce646b3c41169131', 'NF 19 · SERVICO · DivulgaCandContas', 1700, '2022-09-23 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-23', '19', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c5248442afb44c743099c785133762443', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFICA E EDITORA E M 5 LTDA', '01.891.479/0001-66', 'c5dce8d67bd4c55ae0bb88b3118552982', 'NF 19957 · SERVICO · DivulgaCandContas', 1320, '2022-09-22 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-22', '19957', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce5926dfb3b6115d634154fca72750402', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 393758 · COMPRA · DivulgaCandContas', 82.32, '2022-09-22 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-22', '393758', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb1a3e2c296c46f71a370563403871520', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 393756 · COMPRA · DivulgaCandContas', 180.27, '2022-09-22 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-22', '393756', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c6ebc65e05150f329d7bdd4ef38102799', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 393759 · COMPRA · DivulgaCandContas', 562.26, '2022-09-22 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-22', '393759', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cbdc84544e0d103ef09b7592637256538', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 383 · COMPRA · DivulgaCandContas', 2119.77, '2022-09-21 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-21', '383', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c268ca58b5f9c0d8b75b6759252230622', 'c87ede7c132830731acc772e988233493', 'COMITE', 'AMS GRAFICA E EDITORA LTDA', '16.851.106/0001-39', 'c3ce6bd4a50fad335aa41ace109266713', 'NF 8516 · SERVICO · DivulgaCandContas', 5635, '2022-09-21 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-21', '8516', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c7f8fbba47ef3bbacba56539491054031', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 383 · COMPRA · DivulgaCandContas', 2119.77, '2022-09-21 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-21', '383', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cdaa750f9ce21f37ec3a725c036383820', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 393660 · COMPRA · DivulgaCandContas', 201.8, '2022-09-21 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-21', '393660', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cac7e7d54079f8bd68a44865a44057656', 'c87ede7c132830731acc772e988233493', 'COMITE', 'MW - AUDITORIA E CONSULTORIA SS LTDA', '01.732.140/0001-17', 'ca7cf70314f91f3ad7e857c5449800065', 'NF 567 · SERVICO · DivulgaCandContas', 6955, '2022-09-20 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-20', '567', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cf87a510360b3f7dffdcffddb68166555', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 393456 · COMPRA · DivulgaCandContas', 189.19, '2022-09-20 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-20', '393456', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c07a7d10402f8ca83ec5e2eee93438792', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 393494 · COMPRA · DivulgaCandContas', 619.99, '2022-09-20 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-20', '393494', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c63020152531e49d18d158eab04930760', 'c87ede7c132830731acc772e988233493', 'COMITE', 'JC GRAFICA E EDITORA LTDA', '24.578.406/0001-14', 'cde9d46e3b7dfb377e96b8d5266834739', 'NF 3173 · SERVICO · DivulgaCandContas', 1950, '2022-09-20 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-20', '3173', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb6565d1a216a5eb5666dea2928653534', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 393256 · COMPRA · DivulgaCandContas', 220.49, '2022-09-19 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-19', '393256', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cca1e8d47ea40d862c107647688516978', 'c87ede7c132830731acc772e988233493', 'COMITE', 'BR BANDEIRAS E COMUNICACAO VISUAL LTDA', '33.867.095/0001-02', 'ca8e2dd917c7e9ac147f720d109604420', 'NF 800 · COMPRA · DivulgaCandContas', 2200, '2022-09-16 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-16', '800', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c21e45bbc6290f10d93ec1b7582870906', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 392910 · COMPRA · DivulgaCandContas', 254.16, '2022-09-16 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-16', '392910', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca6cc42678f82393422db9d7c18561517', 'c87ede7c132830731acc772e988233493', 'COMITE', 'BR BANDEIRAS E COMUNICACAO VISUAL LTDA', '33.867.095/0001-02', 'ca8e2dd917c7e9ac147f720d109604420', 'NF 800 · COMPRA · DivulgaCandContas', 2200, '2022-09-16 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-16', '800', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c321575f2e564d0ecd639fd7c75820763', 'c87ede7c132830731acc772e988233493', 'COMITE', 'AUTO POSTO DRIM LTDA', '01.732.940/0001-38', 'c9596b18eb8fd712b529e94a834578089', 'NF 19691 · COMPRA · DivulgaCandContas', 5983.63, '2022-09-16 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-16', '19691', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca2426461a2a605e38ac9db1071729630', 'c87ede7c132830731acc772e988233493', 'COMITE', 'AUTO POSTO DRIM LTDA', '01.732.940/0001-38', 'c9596b18eb8fd712b529e94a834578089', 'NF 19691 · COMPRA · DivulgaCandContas', 5983.63, '2022-09-16 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-16', '19691', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c811d5e66fc4cfeaf10bb9ca154926435', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 375 · COMPRA · DivulgaCandContas', 1979.8, '2022-09-15 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-15', '375', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c515c587da64340cefac8e37d01390163', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 392785 · COMPRA · DivulgaCandContas', 204.81, '2022-09-15 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-15', '392785', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cbf200dfc74e8ea95363e913968555254', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 375 · COMPRA · DivulgaCandContas', 1979.8, '2022-09-15 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-15', '375', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c10a8e77501963953b123513198572208', 'c87ede7c132830731acc772e988233493', 'COMITE', 'BR BANDEIRAS E COMUNICACAO VISUAL LTDA', '33.867.095/0001-02', 'ca8e2dd917c7e9ac147f720d109604420', 'NF 784 · COMPRA · DivulgaCandContas', 7750, '2022-09-14 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-14', '784', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c8b8c592842606ac2b2ec691662589988', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 392557 · COMPRA · DivulgaCandContas', 213.94, '2022-09-14 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-14', '392557', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce5d669c2bf3bb8723bda14ff66394307', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 392617 · COMPRA · DivulgaCandContas', 210.45, '2022-09-14 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-14', '392617', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cfc459c48abe0eeb4cb86a02836488566', 'c87ede7c132830731acc772e988233493', 'COMITE', 'BR BANDEIRAS E COMUNICACAO VISUAL LTDA', '33.867.095/0001-02', 'ca8e2dd917c7e9ac147f720d109604420', 'NF 784 · COMPRA · DivulgaCandContas', 7750, '2022-09-14 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-14', '784', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c978d990528f386d48ad96b4923085714', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 392556 · COMPRA · DivulgaCandContas', 206.74, '2022-09-14 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-14', '392556', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3bcbd9a47d58587ba5d6c15181434681', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 392352 · COMPRA · DivulgaCandContas', 481.66, '2022-09-13 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-13', '392352', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c69b31ad547ff3f76e70baa6884624539', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 392218 · COMPRA · DivulgaCandContas', 253.04, '2022-09-13 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-13', '392218', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce4c39dbd9b0a94cc6ad9b85087061063', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 392419 · COMPRA · DivulgaCandContas', 391.1, '2022-09-13 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-13', '392419', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c61d1f4d18661a1497c4e4bd438669629', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO Z + Z LARANJEIRAS LTDA', '03.311.068/0001-80', 'c598f461ee1f02474de8e56f043004695', 'NF 13838 · COMPRA · DivulgaCandContas', 500, '2022-09-12 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-12', '13838', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cfffec9d07fd15790b38ab3dd65313611', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO Z + Z LARANJEIRAS LTDA', '03.311.068/0001-80', 'c598f461ee1f02474de8e56f043004695', 'NF 13837 · COMPRA · DivulgaCandContas', 500, '2022-09-12 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-12', '13837', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca15765d84c335db86e35438795672451', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO Z + Z LARANJEIRAS LTDA', '03.311.068/0001-80', 'c598f461ee1f02474de8e56f043004695', 'NF 13837 · COMPRA · DivulgaCandContas', 500, '2022-09-12 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-12', '13837', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c92a918398813f076ea42c25a89899988', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO Z + Z LARANJEIRAS LTDA', '03.311.068/0001-80', 'c598f461ee1f02474de8e56f043004695', 'NF 13838 · COMPRA · DivulgaCandContas', 500, '2022-09-12 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-12', '13838', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb2db5fa39953d01197f52c0e30487093', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 391998 · COMPRA · DivulgaCandContas', 550.36, '2022-09-12 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-12', '391998', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0950bb150d0b85e5b8350fe848403263', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4239 · SERVICO · DivulgaCandContas', 15530, '2022-09-09 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-09', '4239', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cfa6d4b765a869bdafa82ec0b92195488', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'NF 4237 · SERVICO · DivulgaCandContas', 14836, '2022-09-09 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-09', '4237', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cfaa0f7bcc58bfdc0c60e444547190808', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 391555 · COMPRA · DivulgaCandContas', 159.43, '2022-09-09 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-09', '391555', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c27aafd1a9ef617a38495f12b87986409', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 391670 · COMPRA · DivulgaCandContas', 144.21, '2022-09-09 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-09', '391670', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cafaacdf34bfc1148ab07e31b43721753', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 358 · COMPRA · DivulgaCandContas', 257.28, '2022-09-06 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-06', '358', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c09ffde86e5dcd39fc9e5a60925432757', 'c87ede7c132830731acc772e988233493', 'COMITE', 'AUTO POSTO DRIM LTDA', '01.732.940/0001-38', 'c9596b18eb8fd712b529e94a834578089', 'NF 19612 · COMPRA · DivulgaCandContas', 4105.12, '2022-09-06 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-06', '19612', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c251ca992f1803a647109cb1572587832', 'c87ede7c132830731acc772e988233493', 'COMITE', 'MW - AUDITORIA E CONSULTORIA SS LTDA', '01.732.140/0001-17', 'ca7cf70314f91f3ad7e857c5449800065', 'NF 544 · SERVICO · DivulgaCandContas', 6955, '2022-09-06 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-06', '544', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c75340476c6812a87aeb0326706550048', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 358 · COMPRA · DivulgaCandContas', 257.28, '2022-09-06 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-06', '358', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c9e62da118f456c986f87fd7269521913', 'c87ede7c132830731acc772e988233493', 'COMITE', 'AUTO POSTO DRIM LTDA', '01.732.940/0001-38', 'c9596b18eb8fd712b529e94a834578089', 'NF 19612 · COMPRA · DivulgaCandContas', 4105.12, '2022-09-06 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-06', '19612', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cdbe8c0016d08ac50237d317f98101128', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 352 · COMPRA · DivulgaCandContas', 121.56, '2022-09-05 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-05', '352', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c68e66d30a92bd5ac81ddf18568680629', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 352 · COMPRA · DivulgaCandContas', 121.56, '2022-09-05 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-05', '352', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cbf7d44f2a6f36fe08c7bcfcf97476041', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 351 · COMPRA · DivulgaCandContas', 182.53, '2022-09-05 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-05', '351', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c02b63f74d4537ad02493f16595446197', 'c87ede7c132830731acc772e988233493', 'COMITE', 'GRAFICA E EDITORA E M 5 LTDA', '01.891.479/0001-66', 'c5dce8d67bd4c55ae0bb88b3118552982', 'NF 19915 · SERVICO · DivulgaCandContas', 990, '2022-09-05 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-05', '19915', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0c2ec9e2a19fad2c39f1a86e77926727', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 351 · COMPRA · DivulgaCandContas', 182.53, '2022-09-05 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-05', '351', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd8e97b53039c271319a0fc0672297606', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 349 · COMPRA · DivulgaCandContas', 541.1, '2022-09-05 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-05', '349', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce59ce989469d510d27e5e5bf95966198', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 349 · COMPRA · DivulgaCandContas', 541.1, '2022-09-05 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-05', '349', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd56540c3e39551ccbd48e0a854233840', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 348 · COMPRA · DivulgaCandContas', 174.6, '2022-09-04 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-04', '348', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cc70a065ac756369bf5de102282349759', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 347 · COMPRA · DivulgaCandContas', 140.02, '2022-09-04 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-04', '347', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('caea7105091280479ae16d2a962104767', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 348 · COMPRA · DivulgaCandContas', 174.6, '2022-09-04 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-04', '348', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3ee539349fe91910040b143c28522044', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 347 · COMPRA · DivulgaCandContas', 140.02, '2022-09-04 12:00:00.000', 'LANCADA', 'COMPRA', '2022-09-04', '347', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cdde482a12c60f97d579124a248237996', 'c87ede7c132830731acc772e988233493', 'COMITE', 'FACEBOOK SERVICOS ONLINE DO BRASIL LTDA.', '13.347.016/0001-17', 'c1dc0c9f87a9a68bb664e5aa187936386', 'NF 48924438 · SERVICO · DivulgaCandContas', 8093.58, '2022-09-02 12:00:00.000', 'LANCADA', 'SERVICO', '2022-09-02', '48924438', 'PREFEITURA MUNICIPAL DE SÃO PAULO', 'SÃO PAULO', 'https://nfe.prefeitura.sp.gov.br/contribuinte/notaprint.aspx?ccm=42427630&nf=48924438&cod=V8UTLKQK', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0a4143f908617e2155d94adb66279916', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 326 · COMPRA · DivulgaCandContas', 456.61, '2022-08-30 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-30', '326', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3cc473095fba535d84e838d036450531', 'c87ede7c132830731acc772e988233493', 'COMITE', 'SMART LOC LOCACOES E SERVICOS LTDA', '32.312.128/0001-87', 'c6713e60685d3c4f883570bca40631149', 'NF 98 · SERVICO · DivulgaCandContas', 6500, '2022-08-30 12:00:00.000', 'LANCADA', 'SERVICO', '2022-08-30', '98', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ccbc0c707ded83cf714b0377a15555825', 'c87ede7c132830731acc772e988233493', 'COMITE', 'SMART LOC LOCACOES E SERVICOS LTDA', '32.312.128/0001-87', 'c6713e60685d3c4f883570bca40631149', 'NF 100 · SERVICO · DivulgaCandContas', 25400, '2022-08-30 12:00:00.000', 'LANCADA', 'SERVICO', '2022-08-30', '100', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c22f7ade7f3ac6e001e34aa9d40055373', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 326 · COMPRA · DivulgaCandContas', 456.61, '2022-08-30 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-30', '326', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c49177fe769f1e04c6f34539606996086', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 325 · COMPRA · DivulgaCandContas', 127.14, '2022-08-30 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-30', '325', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd5f5572d44f8717217481e1006882609', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 325 · COMPRA · DivulgaCandContas', 127.14, '2022-08-30 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-30', '325', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca9be9a5ec4b6eb29f4fb80e937336494', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 324 · COMPRA · DivulgaCandContas', 174.08, '2022-08-30 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-30', '324', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000');
INSERT INTO `Expense` (`id`, `campaignId`, `category`, `supplierName`, `supplierDoc`, `supplierId`, `description`, `amount`, `date`, `status`, `naturezaOp`, `dataEmissao`, `numeroNf`, `unidadeArrecadadora`, `dsUe`, `nfeLink`, `importSource`, `bankAccountId`, `caboId`, `vehicleId`, `installmentGroupId`, `installmentNumber`, `installmentCount`, `createdById`, `createdAt`, `updatedAt`) VALUES
('cac8d737bda3b08551cd15e6603536452', 'c87ede7c132830731acc772e988233493', 'COMITE', 'SMART LOC LOCACOES E SERVICOS LTDA', '32.312.128/0001-87', 'c6713e60685d3c4f883570bca40631149', 'NF 99 · SERVICO · DivulgaCandContas', 30000, '2022-08-30 12:00:00.000', 'LANCADA', 'SERVICO', '2022-08-30', '99', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cdb03131a517a73a8ce9e036044221148', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 324 · COMPRA · DivulgaCandContas', 174.08, '2022-08-30 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-30', '324', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c959f2aa2f77890e995c711d745195563', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 323 · COMPRA · DivulgaCandContas', 180.15, '2022-08-30 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-30', '323', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('caa651f972ddd0dc13c8998b526330241', 'c87ede7c132830731acc772e988233493', 'COMITE', 'MUDATO INOVACAO E TECNOLOGIA LTDA', '05.703.562/0001-15', 'c7936ef20a02d73092cf5928352666981', 'NF 1809 · SERVICO · DivulgaCandContas', 480, '2022-08-30 12:00:00.000', 'LANCADA', 'SERVICO', '2022-08-30', '1809', 'PREFEITURA MUNICIPAL DE GOIÂNIA', 'GOIÂNIA', 'WWW.GOIANIA.GO.GOV.BR', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c9d1cc6fa92635296dbd056a514003187', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 323 · COMPRA · DivulgaCandContas', 180.15, '2022-08-30 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-30', '323', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c40d7bc387997310d9f8373c083247382', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 322 · COMPRA · DivulgaCandContas', 1525.23, '2022-08-29 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-29', '322', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0ec556e34ba52c97e78e48d602605911', 'c87ede7c132830731acc772e988233493', 'COMITE', 'SOLIDA COMUNICACAO VISUAL LTDA', '19.863.667/0001-46', 'cd1aa022a20301745d1f574c834813552', 'NF 4197 · COMPRA · DivulgaCandContas', 3950, '2022-08-29 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-29', '4197', 'RFB_TSE', 'BRASIL', 'https://www.nfe.fazenda.gov.br/portal/principal.aspx', 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c1ad030d66a94f2bbc225eb8937982857', 'c87ede7c132830731acc772e988233493', 'COMITE', 'SOLIDA COMUNICACAO VISUAL LTDA', '19.863.667/0001-46', 'cd1aa022a20301745d1f574c834813552', 'NF 4197 · COMPRA · DivulgaCandContas', 3950, '2022-08-29 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-29', '4197', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c4008d53cae3de70e90b8f24025167912', 'c87ede7c132830731acc772e988233493', 'COMITE', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'NF 322 · COMPRA · DivulgaCandContas', 1525.23, '2022-08-29 12:00:00.000', 'LANCADA', 'COMPRA', '2022-08-29', '322', 'SECRETARIA DE ESTADO DA FAZENDA DE GOIÁS', 'GOIÁS', NULL, 'TSE_CSV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cc13fcb1a6aaf01f726b04e6925273066', 'c87ede7c132830731acc772e988233493', 'GRAFICA', 'GRAFOPEL GRAFICA E EDITORA LTDA', '00.747.303/0001-72', 'c0c7966565129516ffd4b41fe75912300', 'Material gráfico — GRAFOPEL (ref. TSE 2022)', 42000, '2026-08-08 12:00:00.000', 'PAGA', 'COMPRA', '2026-08-08', '1000', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c75bf3db12496ce9dbfa598ff62555852', 'c87ede7c132830731acc772e988233493', 'VEICULOS', 'SMART LOC LOCACOES E SERVICOS LTDA', '32.312.128/0001-87', 'c6713e60685d3c4f883570bca40631149', 'Locação — SMART LOC (ref. TSE 2022)', 28000, '2026-08-15 12:00:00.000', 'PAGA', 'COMPRA', '2026-08-15', '1001', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, 'c0d554137969bb823e07a717b16663725', NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cf81c9c264c778758b43020d720943345', 'c87ede7c132830731acc772e988233493', 'IMPULSIONAMENTO', 'FACEBOOK SERVICOS ONLINE DO BRASIL LTDA.', '13.347.016/0001-17', 'c1dc0c9f87a9a68bb664e5aa187936386', 'Impulsionamento Facebook / Meta (ref. TSE 2022)', 29465, '2026-08-22 12:00:00.000', 'PAGA', 'COMPRA', '2026-08-22', '1002', NULL, NULL, NULL, NULL, 'ced99a0ff2cf71f46b3ca7d7678924786', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb5603d7c917c992065a0ed0609640753', 'c87ede7c132830731acc772e988233493', 'IMPULSIONAMENTO', 'GOOGLE BRASIL INTERNET LTDA.', '06.990.590/0001-23', 'c050c7ffe7e6422abd1db891731467813', 'Google Ads / NFS-e (ref. TSE 2022)', 15500, '2026-08-29 12:00:00.000', 'PAGA', 'COMPRA', '2026-08-29', '1003', NULL, NULL, NULL, NULL, 'ced99a0ff2cf71f46b3ca7d7678924786', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0a4e95e33fd8e602e65cc09120531283', 'c87ede7c132830731acc772e988233493', 'COMBUSTIVEIS', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'Abastecimento — Posto Xodó (ref. TSE 2022)', 12400, '2026-09-05 12:00:00.000', 'PAGA', 'COMPRA', '2026-09-05', '1004', NULL, NULL, NULL, NULL, 'c5196a0b6341a7bff60590c8904238622', NULL, 'cc8a4ede14a49a89ba904a6e859011822', NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c4b552ba6124dd361401d3f8473544287', 'c87ede7c132830731acc772e988233493', 'VEICULOS', 'PREMIUM TUR LOCADORA LTDA', '24.095.599/0001-52', 'cfe9b2456ce048d77c25e3cef93006954', 'Locação — Premium Tur (ref. TSE 2022)', 9500, '2026-09-12 12:00:00.000', 'PAGA', 'COMPRA', '2026-09-12', '1005', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, 'c0d554137969bb823e07a717b16663725', NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3917b997fb943cd01fc6b95072468947', 'c87ede7c132830731acc772e988233493', 'COMUNICACAO', 'DREAMS - JRS MUSICA, ARTE E ENTRETENIMENTO LTDA', '47.413.717/0001-29', 'ccb189bf8a3309f75ce646b3c41169131', 'Evento / música — Dreams (ref. TSE 2022)', 12000, '2026-09-19 12:00:00.000', 'PAGA', 'COMPRA', '2026-09-19', '1006', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c11d4f918c4175b743a9c658f91883595', 'c87ede7c132830731acc772e988233493', 'COMUNICACAO', 'RS PRODUTOS E SERVICOS LTDA', '06.273.582/0001-66', 'cf72884724572130755d8b64062040877', 'Produção — RS Produtos e Serviços (ref. TSE 2022)', 8000, '2026-09-26 12:00:00.000', 'PAGA', 'COMPRA', '2026-09-26', '1007', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c44a07c7e6a9fa82fd4c8881e02083032', 'c87ede7c132830731acc772e988233493', 'INTERNET', 'CONEXAO DIGITAL SOLUTION - COMUNICACAO VISUAL LTDA', '14.707.720/0001-04', 'c0ffe089990027c13677b01c281087071', 'Comunicação visual — Conexão Digital (ref. TSE 2022)', 3200, '2026-10-03 12:00:00.000', 'PAGA', 'COMPRA', '2026-10-03', '1008', NULL, NULL, NULL, NULL, 'c5196a0b6341a7bff60590c8904238622', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c82ebfc4840bffd8810a22f3b60615300', 'c87ede7c132830731acc772e988233493', 'GRAFICA', 'TZ - CONFECCAO, MALHARIA E EMBALAGENS LTDA', '14.647.750/0001-64', 'c772b960c79a6e10254e1a1b354706492', 'Confecção / malharia — TZ (ref. TSE 2022)', 16420, '2026-08-08 12:00:00.000', 'PAGA', 'COMPRA', '2026-08-08', '1009', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c313f383149c50b6591023dc004148937', 'c87ede7c132830731acc772e988233493', 'COMITE', 'MW - AUDITORIA E CONSULTORIA SS LTDA', '01.732.140/0001-17', 'ca7cf70314f91f3ad7e857c5449800065', 'Auditoria e consultoria — MW (ref. TSE 2022)', 13910, '2026-08-15 12:00:00.000', 'PAGA', 'COMPRA', '2026-08-15', '1010', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3cd62e59e7de3d661ebb403601997413', 'c87ede7c132830731acc772e988233493', 'COMBUSTIVEIS', 'POSTO XODO LTDA', '01.595.271/0001-08', 'cd3074489a99eafed871f031b88978954', 'Combustível caravana interior', 8700, '2026-08-22 12:00:00.000', 'PAGA', 'COMPRA', '2026-08-22', '1011', NULL, NULL, NULL, NULL, 'c5196a0b6341a7bff60590c8904238622', NULL, 'cc8a4ede14a49a89ba904a6e859011822', NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cab3eb3f0862ce1fc8e60e2ce84410071', 'c87ede7c132830731acc772e988233493', 'COMITE', 'João Prestador Autônomo', '100.008.801-40', 'cc7c64b4f8557bdb3de36bbc564534343', 'Consultoria de campo — NF com campos TSE manuais', 2750, '2026-08-22 10:00:00.000', 'LANCADA', 'SERVICO', '2026-08-22', '7788', 'SEFAZ-GO', 'Goiânia', 'https://nfe.sefaz.go.gov.br/demo/7788', 'MANUAL_DEMO', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb52f972b424a960313ee288140347704', 'c87ede7c132830731acc772e988233493', 'GRAFICA', 'Natural Criacoes', '54.016.069/0001-32', 'c30c2394760f48851f5b928c240158403', 'CHAVEIRO LEMBRANCA PIRENOPOLIS', 192, '2026-08-15 14:20:00.000', 'PAGA', 'COMPRA', '2026-08-15', '40', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c321bab1364515e6c71b511ca25726917', 'c87ede7c132830731acc772e988233493', 'COMITE', 'Prestador sem NF', NULL, NULL, 'Despesa sem número de NF', 500, '2026-08-29 12:00:00.000', 'PAGA', 'SERVICO', NULL, NULL, NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ccea1a221ab29e7637966699b52296430', 'c87ede7c132830731acc772e988233493', 'COMUNICACAO', 'Evento cancelado Ltda', NULL, NULL, 'Show cancelado — não contabilizar', 9000, '2026-09-05 12:00:00.000', 'CANCELADA', 'SERVICO', '2026-09-05', '9991', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce5ea562f0c0d736ad3aa280294129133', 'c87ede7c132830731acc772e988233493', 'COMITE', 'Natural Criacoes', '54.016.069/0001-32', 'c30c2394760f48851f5b928c240158403', 'Assessoria jurídica parcelada (parcela 1/2)', 4500, '2026-08-13 12:00:00.000', 'PAGA', 'SERVICO', '2026-08-13', '5101', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, 'c64626fc4027a5a394fd73f7b12320013', 1, 2, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:33:49.000'),
('c2b82f955076e9d4598085b6998299796', 'c87ede7c132830731acc772e988233493', 'COMITE', 'Natural Criacoes', '54.016.069/0001-32', 'c30c2394760f48851f5b928c240158403', 'Assessoria jurídica parcelada (parcela 2/2)', 3200, '2026-09-02 12:00:00.000', 'PAGA', 'SERVICO', '2026-09-02', '5102', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, 'c64626fc4027a5a394fd73f7b12320013', 2, 2, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:33:53.000'),
('c678c743f87d83d60208a724857419897', 'c87ede7c132830731acc772e988233493', 'VEICULOS', 'Locadora Rápida GO', NULL, NULL, 'Locação van itinerância (parcela 1/2)', 2800, '2026-08-16 09:00:00.000', 'PAGA', 'SERVICO', '2026-08-16', '5201', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, 'c0d554137969bb823e07a717b16663725', 'c5ff59207927b9902e2478f5c99848529', 1, 2, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:33:51.000'),
('c34c80947da78f924242e84ae35418236', 'c87ede7c132830731acc772e988233493', 'VEICULOS', 'Locadora Rápida GO', NULL, NULL, 'Locação van itinerância (parcela 2/2)', 2800, '2026-09-22 09:00:00.000', 'PAGA', 'SERVICO', '2026-09-22', '5202', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, 'c0d554137969bb823e07a717b16663725', 'c5ff59207927b9902e2478f5c99848529', 2, 2, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:33:57.000'),
('c616f863711a5c2740d7e626b69300762', 'c87ede7c132830731acc772e988233493', 'COMUNICACAO', 'Dreams Eventos', NULL, NULL, 'Locação de som — evento interior (despesa futura)', 6500, '2026-08-16 15:00:00.000', 'PAGA', 'SERVICO', '2026-08-16', '6100', NULL, NULL, NULL, NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:33:55.000'),
('c7cd34169907d5417554d4a1e13401096', 'c87ede7c132830731acc772e988233493', 'CABOS_ELEITORAIS', 'Folha cabos', NULL, NULL, 'Pagamento cabo — parcela 1', 2200, '2026-08-22 12:00:00.000', 'PAGA', 'SERVICO', '2026-08-22', '2000', NULL, NULL, NULL, NULL, 'c519893f034acc91c3146f2c814458703', 'cd24a6e387c19b9dc4345101282227116', NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca1d416ffe2d3585b0ff0524082678094', 'c87ede7c132830731acc772e988233493', 'CABOS_ELEITORAIS', 'Folha cabos', NULL, NULL, 'Pagamento cabo — parcela 1', 2300, '2026-08-29 12:00:00.000', 'PAGA', 'SERVICO', '2026-08-29', '2001', NULL, NULL, NULL, NULL, 'c519893f034acc91c3146f2c814458703', 'c6b16de78e63e7475854c72ac79229681', NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c96955e3b32b8d84fbe0428d199207431', 'c87ede7c132830731acc772e988233493', 'CABOS_ELEITORAIS', 'Folha cabos', NULL, NULL, 'Pagamento cabo — parcela 1', 2400, '2026-09-05 12:00:00.000', 'PAGA', 'SERVICO', '2026-09-05', '2002', NULL, NULL, NULL, NULL, 'c519893f034acc91c3146f2c814458703', 'c0ef0178aaa3b4d9c0c8e397885983131', NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c31044913e74e6d476b1484ba23608601', 'c87ede7c132830731acc772e988233493', 'CABOS_ELEITORAIS', 'Folha cabos', NULL, NULL, 'Pagamento cabo — parcela 1', 2500, '2026-09-12 12:00:00.000', 'PAGA', 'SERVICO', '2026-09-12', '2003', NULL, NULL, NULL, NULL, 'c519893f034acc91c3146f2c814458703', 'c2656562247380fde36f0e77096995078', NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c03e4f1412eee892427f5af1336291158', 'c87ede7c132830731acc772e988233493', 'CABOS_ELEITORAIS', 'Folha cabos', NULL, NULL, 'Pagamento cabo — parcela 1', 2600, '2026-09-19 12:00:00.000', 'PAGA', 'SERVICO', '2026-09-19', '2004', NULL, NULL, NULL, NULL, 'c519893f034acc91c3146f2c814458703', 'c96e01ddd4aff7b97853cef1186019825', NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0eb9b44840f68e2af96276e851319664', 'c87ede7c132830731acc772e988233493', 'CABOS_ELEITORAIS', 'Folha cabos', NULL, NULL, 'Pagamento cabo — parcela 1', 2700, '2026-09-26 12:00:00.000', 'PAGA', 'SERVICO', '2026-09-26', '2005', NULL, NULL, NULL, NULL, 'c519893f034acc91c3146f2c814458703', 'ca9bf7bcc019667ccd82e344a88613320', NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb4311c748dabc2ce89690a3198290718', 'c87ede7c132830731acc772e988233493', 'CABOS_ELEITORAIS', 'Folha cabos', NULL, NULL, 'Pagamento cabo — parcela 1', 2800, '2026-10-03 12:00:00.000', 'PAGA', 'SERVICO', '2026-10-03', '2006', NULL, NULL, NULL, NULL, 'c519893f034acc91c3146f2c814458703', 'c75174edec3cb1e3d021913c502342982', NULL, NULL, NULL, NULL, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ExpenseCategory`
--

CREATE TABLE `ExpenseCategory` (
  `id` varchar(191) NOT NULL,
  `code` varchar(64) NOT NULL,
  `label` varchar(191) NOT NULL,
  `color` varchar(32) NOT NULL DEFAULT '#0d9488',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `ExpenseCategory`
--

INSERT INTO `ExpenseCategory` (`id`, `code`, `label`, `color`, `active`, `sortOrder`, `createdAt`, `updatedAt`) VALUES
('cafd2d1380896710804746be135818442', 'SERVICOS_ADVOCATICIOS', 'Serviços advocatícios', '#1D4ED8', 1, 0, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c67bffa8b669d730bc18c04cc76337392', 'SERVICOS_CONTABEIS', 'Serviços contábeis', '#0369A1', 1, 1, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c30cbaba21fa9afa26bb9fa1679672245', 'PESSOAL_MILITANCIA', 'Pessoal, militância e mobilização', '#16A34A', 1, 2, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c148ea6df1c053fefd328080349762336', 'COMBUSTIVEIS_TRANSPORTE', 'Combustíveis, transporte e deslocamento', '#CA8A04', 1, 3, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c8a9e7aee1f45a713507c6f1803824556', 'PUBLICIDADE_GRAFICA', 'Publicidade e materiais impressos', '#0284C7', 1, 4, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c9ea3d157f7ae80279e8e943d94744144', 'INTERNET_IMPULSIONAMENTO', 'Internet e impulsionamento', '#4F46E5', 1, 5, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('cc0760a6c6d1d4faed62f86d537734373', 'LOCACAO_BENS_VEICULOS', 'Locação/cessão de bens e veículos', '#EA580C', 1, 6, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c52dc3d42892d0f4c64c725a510817895', 'COMICIOS_EVENTOS', 'Comícios, eventos, gerador e carro de som', '#DB2777', 1, 7, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('cb530009b8fb0af975522414963064963', 'AGUA_ENERGIA_CORREIOS', 'Água, energia e correspondências', '#0D9488', 1, 8, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c7d20b127841ee7b17190dfa726357896', 'ENCARGOS_TAXAS', 'Encargos, taxas, impostos e multas', '#BE123C', 1, 9, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c187efa840c0b9079b0b49bd215689322', 'PASSAGENS_AEREAS', 'Passagens aéreas', '#7C3AED', 1, 10, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('cd7717e16ca4298348097f6d140603138', 'AQUISICAO_BENS', 'Aquisição/doação de bens móveis ou imóveis', '#0891B2', 1, 11, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c175c98d76b4180cba6196f6e51304574', 'DOACAO_OUTRAS_CANDIDATURAS', 'Doações a candidatas, candidatos e partidos', '#9333EA', 1, 12, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('cef96cc35d130f92db6bd9d1836054329', 'DESPESAS_DIVERSAS', 'Despesas diversas a especificar', '#64748B', 1, 13, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c9fadcd70b1ae2b67be30e9d596534786', 'COMITE', 'Comitê', '#0D9488', 1, 14, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c2213236ceb64cf834266b2b497627375', 'GRAFICA', 'Gráfica', '#0284C7', 1, 15, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('cf0908876c47e04dcc2adb83966785362', 'INTERNET', 'Internet', '#4F46E5', 1, 16, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('cccd48946d28df7cf91b1a90524809905', 'VEICULOS', 'Veículos', '#EA580C', 1, 17, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c7f98cfab259b2c3f5708a80874477464', 'IMPULSIONAMENTO', 'Impulsionamento', '#DB2777', 1, 18, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c5d0efbb070b08eb2f21257b636657661', 'COMBUSTIVEIS', 'Combustíveis', '#CA8A04', 1, 19, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('ce75211e4b7988acf9bb2246189332924', 'CABOS_ELEITORAIS', 'Cabos Eleitorais', '#16A34A', 1, 20, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('ce220f2d93abceefa5c42afbb44741170', 'COMUNICACAO', 'Comunicação', '#0891B2', 1, 21, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `PasswordResetToken`
--

CREATE TABLE `PasswordResetToken` (
  `id` varchar(191) NOT NULL,
  `userId` varchar(191) NOT NULL,
  `tokenHash` varchar(64) NOT NULL,
  `expiresAt` datetime(3) NOT NULL,
  `usedAt` datetime(3) DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `Representative`
--

CREATE TABLE `Representative` (
  `id` varchar(191) NOT NULL,
  `campaignId` varchar(191) NOT NULL,
  `role` varchar(64) NOT NULL,
  `name` varchar(191) NOT NULL,
  `cpf` varchar(32) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `oabUf` varchar(8) DEFAULT NULL,
  `oabNumber` varchar(64) DEFAULT NULL,
  `crcUf` varchar(8) DEFAULT NULL,
  `crcNumber` varchar(64) DEFAULT NULL,
  `roleOther` varchar(191) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Representative`
--

INSERT INTO `Representative` (`id`, `campaignId`, `role`, `name`, `cpf`, `email`, `phone`, `oabUf`, `oabNumber`, `crcUf`, `crcNumber`, `roleOther`, `active`, `notes`, `createdAt`, `updatedAt`) VALUES
('cd1374d93b2dd51ec1d473fb490701286', 'c87ede7c132830731acc772e988233493', 'ADVOGADO', 'Helena Marques Advocacia', '100.009.101-55', 'advogado@campanha2026.go', '(62) 99910-2026', 'GO', '34567', NULL, NULL, NULL, 1, 'Representação legal Conta+JE §7.3', '2026-08-13 23:14:24.000', '2026-08-13 23:37:48.000'),
('cf9d3b15aa40353be1473b7d975385147', 'c87ede7c132830731acc772e988233493', 'ADMIN_FINANCEIRO', 'Carlos Eduardo Contabilidade', '100.009.102-36', 'contabil@campanha2026.go', '(62) 99920-2026', NULL, NULL, 'GO', '12345/O', NULL, 1, 'CRC responsável pela prestação', '2026-08-13 23:14:24.000', '2026-08-13 23:37:55.000'),
('c66b817ef7878c8fe99803cbc11240356', 'c87ede7c132830731acc772e988233493', 'CONTABILISTA', 'Charles Benrardo', NULL, NULL, NULL, 'GO', NULL, 'GO', '56654', NULL, 1, NULL, '2026-08-13 23:38:30.000', '2026-08-13 23:38:30.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Revenue`
--

CREATE TABLE `Revenue` (
  `id` varchar(191) NOT NULL,
  `campaignId` varchar(191) NOT NULL,
  `source` varchar(191) NOT NULL,
  `donorName` varchar(191) DEFAULT NULL,
  `donorCpf` varchar(191) DEFAULT NULL,
  `amount` double NOT NULL,
  `date` datetime(3) NOT NULL,
  `description` text DEFAULT NULL,
  `receiptNumber` varchar(191) DEFAULT NULL,
  `bankAccountId` varchar(191) DEFAULT NULL,
  `donationType` varchar(64) DEFAULT NULL,
  `resourceOrigin` varchar(64) DEFAULT NULL,
  `resourceSpecies` varchar(64) DEFAULT NULL,
  `emitReceipt` tinyint(1) NOT NULL DEFAULT 0,
  `isFcc` tinyint(1) NOT NULL DEFAULT 0,
  `isInternet` tinyint(1) NOT NULL DEFAULT 0,
  `isLoan` tinyint(1) NOT NULL DEFAULT 0,
  `createdById` varchar(191) DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Revenue`
--

INSERT INTO `Revenue` (`id`, `campaignId`, `source`, `donorName`, `donorCpf`, `amount`, `date`, `description`, `receiptNumber`, `bankAccountId`, `donationType`, `resourceOrigin`, `resourceSpecies`, `emitReceipt`, `isFcc`, `isInternet`, `isLoan`, `createdById`, `createdAt`, `updatedAt`) VALUES
('c3f3fadc9459992b5692cdf1411758896', 'c87ede7c132830731acc772e988233493', 'FUNDO_PARTIDARIO', 'Diretório Estadual UNIÃO-GO', NULL, 280000, '2026-08-08 12:00:00.000', 'Repasse fundo partidário — parcela 1', NULL, 'c519893f034acc91c3146f2c814458703', 'RECURSOS_PARTIDO', 'FUNDO_PARTIDARIO', NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.779'),
('c8b5464c26398954df84f57d896329223', 'c87ede7c132830731acc772e988233493', 'FUNDO_PARTIDARIO', 'Diretório Estadual UNIÃO-GO', NULL, 120000, '2026-09-05 12:00:00.000', 'Repasse fundo partidário — parcela 2', NULL, 'c519893f034acc91c3146f2c814458703', 'RECURSOS_PARTIDO', 'FUNDO_PARTIDARIO', NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.779'),
('c4af3e14793c5ca4c99d9a41b16806394', 'c87ede7c132830731acc772e988233493', 'FEFC', 'Diretório Nacional UNIÃO', NULL, 95000, '2026-08-15 12:00:00.000', 'Repasse FEFC — parcela 1', NULL, 'ced99a0ff2cf71f46b3ca7d7678924786', 'RECURSOS_PARTIDO', 'FEFC', NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.779'),
('cf46572f85d0b9afbdd6b528a04803871', 'c87ede7c132830731acc772e988233493', 'FCC', 'Financiamento Coletivo Oficial', NULL, 48500, '2026-08-22 12:00:00.000', 'Arrecadação FCC — ciclo 1', NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ce0db753e191f6eb1ea4860a924543673', 'c87ede7c132830731acc772e988233493', 'FCC', 'Financiamento Coletivo Oficial', NULL, 31200, '2026-09-19 12:00:00.000', 'Arrecadação FCC — ciclo 2', NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd3b63d9ab651f7c66924242c48478593', 'c87ede7c132830731acc772e988233493', 'FCC', 'Financiamento Coletivo Oficial', NULL, 27800, '2026-10-03 12:00:00.000', 'Arrecadação FCC — ciclo final', NULL, 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c8041f022dd33d7a1e9ea307a56984255', 'c87ede7c132830731acc772e988233493', 'FCC', 'Ana Paula Mendes', '10000100072', 1500, '2026-08-08 12:00:00.000', 'Doação pessoa física', 'REC-2026-0001', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('ca47022176b5e31efb05d376470320661', 'c87ede7c132830731acc772e988233493', 'FCC', 'Carlos Eduardo Silva', '10000100153', 2350, '2026-08-15 12:00:00.000', 'Doação pessoa física', 'REC-2026-0002', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('cf03fd26b2c196f51cfc2dca150061378', 'c87ede7c132830731acc772e988233493', 'FCC', 'Fernanda Rocha Lima', '10000100234', 3200, '2026-08-22 12:00:00.000', 'Doação pessoa física', 'REC-2026-0003', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('c056db6db9af09d370dd787ce63200459', 'c87ede7c132830731acc772e988233493', 'FCC', 'José Roberto Alves', '10000100315', 4050, '2026-08-29 12:00:00.000', 'Doação pessoa física', 'REC-2026-0004', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('c6406dc991723d6d60d5b327093705017', 'c87ede7c132830731acc772e988233493', 'FCC', 'Mariana Costa Nunes', '10000100404', 4900, '2026-09-05 12:00:00.000', 'Doação pessoa física', 'REC-2026-0005', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('c348c6dab5b697e0026d243e215779526', 'c87ede7c132830731acc772e988233493', 'FCC', 'Paulo Henrique Dias', '10000100587', 5750, '2026-09-12 12:00:00.000', 'Doação pessoa física', 'REC-2026-0006', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('cbc29acacc9cd6db94f0a9a6797033687', 'c87ede7c132830731acc772e988233493', 'FCC', 'Luciana Martins Souza', '10000100668', 6600, '2026-09-19 12:00:00.000', 'Doação pessoa física', 'REC-2026-0007', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('c4fb7b451f8beabb9ef5445dc82961998', 'c87ede7c132830731acc772e988233493', 'FCC', 'Ricardo Borges Pinto', '10000100749', 7450, '2026-09-26 12:00:00.000', 'Doação pessoa física', 'REC-2026-0008', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('c38fb9dc59b025903086546d303659514', 'c87ede7c132830731acc772e988233493', 'FCC', 'Juliana Ferreira', '10000100820', 8300, '2026-10-03 12:00:00.000', 'Doação pessoa física', 'REC-2026-0009', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('c83edb5c6632f000c55575a5115385356', 'c87ede7c132830731acc772e988233493', 'FCC', 'André Luiz Cardoso', '10000100900', 9150, '2026-08-08 12:00:00.000', 'Doação pessoa física', 'REC-2026-0010', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('cdd56aab6f96a66359a49873053959228', 'c87ede7c132830731acc772e988233493', 'FCC', 'Patrícia Gomes', '10000101044', 10000, '2026-08-15 12:00:00.000', 'Doação pessoa física', 'REC-2026-0011', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777'),
('c72ca42a9307b44f32af39d2e19259414', 'c87ede7c132830731acc772e988233493', 'FCC', 'Bruno Teixeira', '10000101125', 10850, '2026-08-22 12:00:00.000', 'Doação pessoa física', 'REC-2026-0012', 'cfe45658d89de2113001ff90633398322', NULL, NULL, NULL, 0, 0, 0, 0, 'c771a04a175c46f0b7cb2934690291848', '2026-08-13 23:14:24.000', '2026-08-13 23:14:27.777');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Supplier`
--

CREATE TABLE `Supplier` (
  `id` varchar(191) NOT NULL,
  `campaignId` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `tradeName` varchar(191) DEFAULT NULL,
  `documentType` varchar(191) DEFAULT NULL,
  `document` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `contactName` varchar(191) DEFAULT NULL,
  `zipCode` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `addressNumber` varchar(191) DEFAULT NULL,
  `addressComplement` varchar(191) DEFAULT NULL,
  `neighborhood` varchar(191) DEFAULT NULL,
  `city` varchar(191) DEFAULT NULL,
  `state` varchar(191) DEFAULT 'GO',
  `stateRegistration` varchar(191) DEFAULT NULL,
  `municipalRegistration` varchar(191) DEFAULT NULL,
  `category` varchar(191) DEFAULT NULL,
  `activityType` varchar(191) DEFAULT NULL,
  `birthDate` date DEFAULT NULL,
  `quantidadeNfes` int(11) NOT NULL DEFAULT 0,
  `valorTotalNfes` double NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Supplier`
--

INSERT INTO `Supplier` (`id`, `campaignId`, `name`, `tradeName`, `documentType`, `document`, `email`, `phone`, `contactName`, `zipCode`, `address`, `addressNumber`, `addressComplement`, `neighborhood`, `city`, `state`, `stateRegistration`, `municipalRegistration`, `category`, `activityType`, `birthDate`, `quantidadeNfes`, `valorTotalNfes`, `notes`, `active`, `createdAt`, `updatedAt`) VALUES
('cc7c64b4f8557bdb3de36bbc564534343', 'c87ede7c132830731acc772e988233493', 'João Prestador Autônomo', NULL, 'CPF', '100.008.801-40', 'joao.prestador@email.com', '(62) 99911-2233', 'João Prestador', '74015-010', 'Av. Goiás', '1250', 'Sala 3', 'Centro', 'Goiânia', 'GO', NULL, 'IM-908877', 'Serviços gerais', 'SERVICO', '1978-06-15', 0, 0, 'Fornecedor PF demo com endereço completo', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c050c7ffe7e6422abd1db891731467813', 'c87ede7c132830731acc772e988233493', 'GOOGLE BRASIL INTERNET LTDA.', NULL, 'CNPJ', '06.990.590/0001-23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SÃO PAULO', 'SP', NULL, NULL, NULL, 'SERVICO', NULL, 1, 1000, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c1dc0c9f87a9a68bb664e5aa187936386', 'c87ede7c132830731acc772e988233493', 'FACEBOOK SERVICOS ONLINE DO BRASIL LTDA.', NULL, 'CNPJ', '13.347.016/0001-17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SÃO PAULO', 'SP', NULL, NULL, NULL, 'SERVICO', NULL, 2, 29465, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ccb189bf8a3309f75ce646b3c41169131', 'c87ede7c132830731acc772e988233493', 'DREAMS - JRS MUSICA, ARTE E ENTRETENIMENTO LTDA', NULL, 'CNPJ', '47.413.717/0001-29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 2, 4700, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cfe9b2456ce048d77c25e3cef93006954', 'c87ede7c132830731acc772e988233493', 'PREMIUM TUR LOCADORA LTDA', NULL, 'CNPJ', '24.095.599/0001-52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 1, 2000, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c83110b7657726bd0ce08505a05962963', 'c87ede7c132830731acc772e988233493', '39.571.991 BRUNO GONCALVES SOUZA LACERDA', NULL, 'CNPJ', '39.571.991/0001-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 1, 500, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb7d5ffd57eb06bb62d9789c489594579', 'c87ede7c132830731acc772e988233493', 'FESTIVITA LOCACAO DE MATERIAL E MOBILIARIO PARA EVENTOS LTDA', NULL, 'CNPJ', '06.788.342/0001-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 1, 112, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cf72884724572130755d8b64062040877', 'c87ede7c132830731acc772e988233493', 'RS PRODUTOS E SERVICOS LTDA', NULL, 'CNPJ', '06.273.582/0001-66', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 2, 4000, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0c7966565129516ffd4b41fe75912300', 'c87ede7c132830731acc772e988233493', 'GRAFOPEL GRAFICA E EDITORA LTDA', NULL, 'CNPJ', '00.747.303/0001-72', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 13, 99472, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c48d5c91238c3d9f621ae859896791296', 'c87ede7c132830731acc772e988233493', 'ROFE RENTAL E COMERCIO LTDA', NULL, 'CNPJ', '21.135.490/0001-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 1, 2200, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd3074489a99eafed871f031b88978954', 'c87ede7c132830731acc772e988233493', 'POSTO XODO LTDA', NULL, 'CNPJ', '01.595.271/0001-08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÁS', 'GO', NULL, NULL, NULL, 'VENDA', NULL, 52, 27727.01, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cb3251457a3416294ec504c4275512453', 'c87ede7c132830731acc772e988233493', 'PROMARKET PROMOCAO DE EVENTOS E LOGISTICA LTDA', NULL, 'CNPJ', '37.249.018/0001-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 1, 6500, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c598f461ee1f02474de8e56f043004695', 'c87ede7c132830731acc772e988233493', 'POSTO Z + Z LARANJEIRAS LTDA', NULL, 'CNPJ', '03.311.068/0001-80', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÁS', 'GO', NULL, NULL, NULL, 'VENDA', NULL, 6, 3000, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c4bd014b5c56078f995b633c430406407', 'c87ede7c132830731acc772e988233493', 'FERNANDES COMBUSTIVEIS LTDA', NULL, 'CNPJ', '07.832.010/0001-32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÁS', 'GO', NULL, NULL, NULL, 'VENDA', NULL, 2, 490.06, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cde9d46e3b7dfb377e96b8d5266834739', 'c87ede7c132830731acc772e988233493', 'JC GRAFICA E EDITORA LTDA', NULL, 'CNPJ', '24.578.406/0001-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 2, 3150, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0ffe089990027c13677b01c281087071', 'c87ede7c132830731acc772e988233493', 'CONEXAO DIGITAL SOLUTION - COMUNICACAO VISUAL LTDA', NULL, 'CNPJ', '14.707.720/0001-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÁS', 'GO', NULL, NULL, NULL, 'VENDA', NULL, 6, 30645.6, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c772b960c79a6e10254e1a1b354706492', 'c87ede7c132830731acc772e988233493', 'TZ - CONFECCAO, MALHARIA E EMBALAGENS LTDA', NULL, 'CNPJ', '14.647.750/0001-64', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÁS', 'GO', NULL, NULL, NULL, 'VENDA', NULL, 2, 32840, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c4d416e7fbbb72599b215d0b779526772', 'c87ede7c132830731acc772e988233493', 'ECONOMICA GRAFICA RAPIDA LTDA', NULL, 'CNPJ', '00.110.583/0001-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 1, 400, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c5dce8d67bd4c55ae0bb88b3118552982', 'c87ede7c132830731acc772e988233493', 'GRAFICA E EDITORA E M 5 LTDA', NULL, 'CNPJ', '01.891.479/0001-66', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 2, 2310, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c3ce6bd4a50fad335aa41ace109266713', 'c87ede7c132830731acc772e988233493', 'AMS GRAFICA E EDITORA LTDA', NULL, 'CNPJ', '16.851.106/0001-39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 1, 5635, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca7cf70314f91f3ad7e857c5449800065', 'c87ede7c132830731acc772e988233493', 'MW - AUDITORIA E CONSULTORIA SS LTDA', NULL, 'CNPJ', '01.732.140/0001-17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 2, 13910, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('ca8e2dd917c7e9ac147f720d109604420', 'c87ede7c132830731acc772e988233493', 'BR BANDEIRAS E COMUNICACAO VISUAL LTDA', NULL, 'CNPJ', '33.867.095/0001-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÁS', 'GO', NULL, NULL, NULL, 'VENDA', NULL, 4, 19900, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c9596b18eb8fd712b529e94a834578089', 'c87ede7c132830731acc772e988233493', 'AUTO POSTO DRIM LTDA', NULL, 'CNPJ', '01.732.940/0001-38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÁS', 'GO', NULL, NULL, NULL, 'VENDA', NULL, 4, 20177.5, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c6713e60685d3c4f883570bca40631149', 'c87ede7c132830731acc772e988233493', 'SMART LOC LOCACOES E SERVICOS LTDA', NULL, 'CNPJ', '32.312.128/0001-87', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 3, 61900, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c7936ef20a02d73092cf5928352666981', 'c87ede7c132830731acc772e988233493', 'MUDATO INOVACAO E TECNOLOGIA LTDA', NULL, 'CNPJ', '05.703.562/0001-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÂNIA', 'GO', NULL, NULL, NULL, 'SERVICO', NULL, 1, 480, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cd1aa022a20301745d1f574c834813552', 'c87ede7c132830731acc772e988233493', 'SOLIDA COMUNICACAO VISUAL LTDA', NULL, 'CNPJ', '19.863.667/0001-46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'GOIÁS', 'GO', NULL, NULL, NULL, 'VENDA', NULL, 2, 7900, 'Cadastro do emitente (importação TSE — NF-e no lançamento).', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c30c2394760f48851f5b928c240158403', 'c87ede7c132830731acc772e988233493', 'Natural Criacoes', 'Natural Criacoes', 'CNPJ', '54.016.069/0001-32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Goiânia', 'GO', NULL, NULL, 'Brindes / lembranças', 'VENDA', NULL, 0, 0, 'Emitente exemplo de NF', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `SupplierNfe`
--

CREATE TABLE `SupplierNfe` (
  `id` varchar(191) NOT NULL,
  `campaignId` varchar(191) NOT NULL,
  `supplierId` varchar(191) DEFAULT NULL,
  `cnpjEmitente` varchar(191) NOT NULL,
  `nmEmitente` varchar(191) NOT NULL,
  `naturezaOp` varchar(191) DEFAULT NULL,
  `modelo` varchar(191) DEFAULT NULL,
  `dataEmissao` date DEFAULT NULL,
  `numeroNf` varchar(191) DEFAULT NULL,
  `numeroSerie` varchar(191) DEFAULT NULL,
  `valor` double NOT NULL DEFAULT 0,
  `ue` varchar(191) DEFAULT NULL,
  `unidadeArrecadadora` varchar(191) DEFAULT NULL,
  `dsUe` varchar(191) DEFAULT NULL,
  `chaveAcesso` varchar(191) DEFAULT NULL,
  `link` text DEFAULT NULL,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `Team`
--

CREATE TABLE `Team` (
  `id` varchar(191) NOT NULL,
  `memberNumber` int(11) NOT NULL,
  `label` varchar(191) NOT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `city` varchar(191) DEFAULT NULL,
  `cityRegion` varchar(191) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Team`
--

INSERT INTO `Team` (`id`, `memberNumber`, `label`, `phone`, `city`, `cityRegion`, `active`, `sortOrder`, `createdAt`, `updatedAt`) VALUES
('ce9e7203ce1bfeb7b8cf67d7b43903128', 1, 'Ana Paula Mendes', '(62) 98111-1001', 'Goiânia', 'Setor Bueno', 1, 0, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c24bede44b090e4cf738d2ca341911724', 2, 'Bruno Carvalho Silva', '(62) 98222-1002', 'Aparecida de Goiânia', 'Centro', 1, 1, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c0eefabe11840a52f67ba2b6a58400915', 3, 'Carlos Eduardo Nunes', '(62) 98333-1003', 'Anápolis', 'Jundiaí', 1, 2, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c367bf1704b6f4f13bd72e59e31056075', 4, 'Fernanda Lopes Vieira', '(64) 98444-1004', 'Rio Verde', 'Setor Central', 1, 3, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('cf93dad3a2b6320d7b6b07fd246944468', 5, 'Juliana Martins Rocha', '(64) 98555-1005', 'Catalão', 'Zona Norte', 1, 4, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c6d0d10a3fe61ffddb0df865b28811029', 6, 'Lucas Ferreira Costa', '(64) 98666-1006', 'Itumbiara', 'Centro', 1, 5, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('cae9f0a460ff4d9021b3c6f2d91158943', 7, 'Patricia Souza Almeida', '(64) 98777-1007', 'Jataí', 'Setor Samuel Graham', 1, 6, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000'),
('c6859487c4a5a3455562ba65093123295', 8, 'Ricardo Alves Pinto', '(61) 98888-1008', 'Luziânia', 'Parque Estrela Dalva', 1, 7, '2026-08-13 23:14:27.000', '2026-08-13 23:14:27.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `User`
--

CREATE TABLE `User` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `passwordHash` varchar(191) NOT NULL,
  `role` varchar(191) NOT NULL DEFAULT 'OPERADOR',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `User`
--

INSERT INTO `User` (`id`, `name`, `email`, `passwordHash`, `role`, `active`, `createdAt`, `updatedAt`) VALUES
('c771a04a175c46f0b7cb2934690291848', 'Master Campanha', 'master@contas.synetiq.com.br', '$2y$10$38g2ZRXvrZnlbyliv1mCtuu8ms.xSqRsmQY2s0xiStDu1Tqry1E36', 'MASTER', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c7dabaa6ad2fb2f5afbb3f7e011212207', 'Tesoureiro', 'financeiro@contas.synetiq.com.br', '$2y$10$38g2ZRXvrZnlbyliv1mCtuu8ms.xSqRsmQY2s0xiStDu1Tqry1E36', 'FINANCEIRO', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c0cdb3716c36148e45dde12e215980110', 'Coord. RH', 'rh@contas.synetiq.com.br', '$2y$10$38g2ZRXvrZnlbyliv1mCtuu8ms.xSqRsmQY2s0xiStDu1Tqry1E36', 'RH', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('c6b1c716e8b98406e965cf81d48787949', 'Consulta (somente leitura)', 'consulta@contas.synetiq.com.br', '$2y$10$38g2ZRXvrZnlbyliv1mCtuu8ms.xSqRsmQY2s0xiStDu1Tqry1E36', 'CONSULTA', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000');

-- --------------------------------------------------------

--
-- Estrutura para tabela `Vehicle`
--

CREATE TABLE `Vehicle` (
  `id` varchar(191) NOT NULL,
  `campaignId` varchar(191) NOT NULL,
  `label` varchar(191) NOT NULL,
  `plate` varchar(191) NOT NULL,
  `brand` varchar(191) DEFAULT NULL,
  `model` varchar(191) DEFAULT NULL,
  `year` varchar(191) DEFAULT NULL,
  `type` varchar(191) NOT NULL DEFAULT 'Automóvel',
  `color` varchar(191) DEFAULT NULL,
  `ownerName` varchar(191) DEFAULT NULL,
  `ownerDoc` varchar(191) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `createdAt` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updatedAt` datetime(3) NOT NULL DEFAULT current_timestamp(3) ON UPDATE current_timestamp(3)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `Vehicle`
--

INSERT INTO `Vehicle` (`id`, `campaignId`, `label`, `plate`, `brand`, `model`, `year`, `type`, `color`, `ownerName`, `ownerDoc`, `notes`, `active`, `createdAt`, `updatedAt`) VALUES
('c0d554137969bb823e07a717b16663725', 'c87ede7c132830731acc772e988233493', 'Van itinerância 01', 'QWE1A23', 'Renault', 'Master', '2022', 'VAN', 'Branca', 'Locadora Rápida GO', '10.000.012/0700-38', 'Locação com seguro total', 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cc8a4ede14a49a89ba904a6e859011822', 'c87ede7c132830731acc772e988233493', 'Carro coordenação', 'RTY2B45', 'Volkswagen', 'Virtus', '2023', 'AUTOMÓVEL', 'Prata', 'Auto Loc Goiânia', '10.000.012/0717-86', NULL, 1, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000'),
('cdee50c11cbec35cb028f320205861714', 'c87ede7c132830731acc772e988233493', 'Motocicleta apoio', 'UIO3C67', 'Honda', 'CG 160', '2021', 'MOTOCICLETA', 'Vermelha', 'João Proprietário', '100.007.102-29', 'Uso eventual em zonas rurais', 0, '2026-08-13 23:14:24.000', '2026-08-13 23:14:24.000');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `AccountMapping`
--
ALTER TABLE `AccountMapping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `AccountMapping_campaignId_kind_code_key` (`campaignId`,`kind`,`code`) USING HASH,
  ADD KEY `AccountMapping_campaignId_idx` (`campaignId`),
  ADD KEY `AccountMapping_bankAccountId_idx` (`bankAccountId`);

--
-- Índices de tabela `AuditLog`
--
ALTER TABLE `AuditLog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `AuditLog_entity_idx` (`entity`),
  ADD KEY `AuditLog_createdAt_idx` (`createdAt`),
  ADD KEY `AuditLog_userId_fkey` (`userId`);

--
-- Índices de tabela `BalanceAdjustment`
--
ALTER TABLE `BalanceAdjustment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `BalanceAdjustment_bankAccountId_idx` (`bankAccountId`),
  ADD KEY `BalanceAdjustment_date_idx` (`date`);

--
-- Índices de tabela `BankAccount`
--
ALTER TABLE `BankAccount`
  ADD PRIMARY KEY (`id`),
  ADD KEY `BankAccount_campaignId_idx` (`campaignId`);

--
-- Índices de tabela `BankTransaction`
--
ALTER TABLE `BankTransaction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `BankTransaction_bankAccountId_idx` (`bankAccountId`),
  ADD KEY `BankTransaction_status_idx` (`status`);

--
-- Índices de tabela `CaboEleitoral`
--
ALTER TABLE `CaboEleitoral`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `CaboEleitoral_cpf_key` (`cpf`),
  ADD KEY `CaboEleitoral_campaignId_idx` (`campaignId`);

--
-- Índices de tabela `Campaign`
--
ALTER TABLE `Campaign`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `Contract`
--
ALTER TABLE `Contract`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Contract_contractNumber_key` (`contractNumber`),
  ADD KEY `Contract_caboId_idx` (`caboId`);

--
-- Índices de tabela `Expense`
--
ALTER TABLE `Expense`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Expense_campaignId_idx` (`campaignId`),
  ADD KEY `Expense_category_idx` (`category`),
  ADD KEY `Expense_numeroNf_idx` (`numeroNf`),
  ADD KEY `Expense_importSource_idx` (`importSource`),
  ADD KEY `Expense_supplierId_idx` (`supplierId`),
  ADD KEY `Expense_installmentGroupId_idx` (`installmentGroupId`),
  ADD KEY `Expense_bankAccountId_fkey` (`bankAccountId`),
  ADD KEY `Expense_caboId_fkey` (`caboId`),
  ADD KEY `Expense_vehicleId_fkey` (`vehicleId`),
  ADD KEY `Expense_createdById_fkey` (`createdById`);

--
-- Índices de tabela `ExpenseCategory`
--
ALTER TABLE `ExpenseCategory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ExpenseCategory_code_key` (`code`),
  ADD KEY `ExpenseCategory_active_sort_idx` (`active`,`sortOrder`);

--
-- Índices de tabela `PasswordResetToken`
--
ALTER TABLE `PasswordResetToken`
  ADD PRIMARY KEY (`id`),
  ADD KEY `PasswordResetToken_tokenHash_idx` (`tokenHash`),
  ADD KEY `PasswordResetToken_userId_idx` (`userId`);

--
-- Índices de tabela `Representative`
--
ALTER TABLE `Representative`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Representative_campaignId_idx` (`campaignId`),
  ADD KEY `Representative_role_idx` (`role`);

--
-- Índices de tabela `Revenue`
--
ALTER TABLE `Revenue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Revenue_campaignId_idx` (`campaignId`),
  ADD KEY `Revenue_source_idx` (`source`),
  ADD KEY `Revenue_donorCpf_idx` (`donorCpf`),
  ADD KEY `Revenue_bankAccountId_fkey` (`bankAccountId`),
  ADD KEY `Revenue_createdById_fkey` (`createdById`);

--
-- Índices de tabela `Supplier`
--
ALTER TABLE `Supplier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Supplier_campaignId_idx` (`campaignId`),
  ADD KEY `Supplier_name_idx` (`name`),
  ADD KEY `Supplier_document_idx` (`document`);

--
-- Índices de tabela `SupplierNfe`
--
ALTER TABLE `SupplierNfe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `SupplierNfe_campaignId_idx` (`campaignId`),
  ADD KEY `SupplierNfe_supplierId_idx` (`supplierId`),
  ADD KEY `SupplierNfe_cnpjEmitente_idx` (`cnpjEmitente`),
  ADD KEY `SupplierNfe_chaveAcesso_idx` (`chaveAcesso`);

--
-- Índices de tabela `Team`
--
ALTER TABLE `Team`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Team_memberNumber_key` (`memberNumber`),
  ADD KEY `Team_active_label_idx` (`active`,`label`);

--
-- Índices de tabela `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `User_email_key` (`email`);

--
-- Índices de tabela `Vehicle`
--
ALTER TABLE `Vehicle`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Vehicle_campaignId_plate_key` (`campaignId`,`plate`) USING HASH,
  ADD KEY `Vehicle_campaignId_idx` (`campaignId`),
  ADD KEY `Vehicle_active_idx` (`active`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
