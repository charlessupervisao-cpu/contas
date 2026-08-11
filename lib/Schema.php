<?php

declare(strict_types=1);

/** Migrações leves para instalações já existentes (cPanel). */
final class Schema
{
    private static bool $ensured = false;

    public static function ensure(): void
    {
        if (self::$ensured) {
            return;
        }
        self::$ensured = true;

        try {
            $pdo = Database::pdo();
        } catch (Throwable) {
            return;
        }

        self::ensureSupplierColumns($pdo);
        self::ensureExpenseNfeColumns($pdo);
        self::dropObsoleteExpenseNfeColumns($pdo);
        self::ensureSupplierNfeTable($pdo);
        self::ensureSupplierNfeColumns($pdo);
        self::migrateSupplierNfeIntoExpenses($pdo);
        self::ensureCaboColumns($pdo);
        self::ensureBankAccountDepositColumns($pdo);
        self::ensureExpenseCategoryTable($pdo);
        self::ensureExpenseInstallmentColumns($pdo);
        self::ensureTeamTable($pdo);
        self::ensureContractPdfColumn($pdo);
        self::ensurePasswordResetTable($pdo);
        self::repairRevenueSourceByAccount($pdo);
    }

    /** Parcelamento de despesa (até 2x) — vínculo entre parcelas. */
    private static function ensureExpenseInstallmentColumns(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'Expense')) {
            return;
        }
        if (!self::columnExists($pdo, 'Expense', 'installmentGroupId')) {
            $pdo->exec('ALTER TABLE `Expense` ADD COLUMN `installmentGroupId` VARCHAR(191) NULL AFTER `vehicleId`');
        }
        if (!self::columnExists($pdo, 'Expense', 'installmentNumber')) {
            $pdo->exec('ALTER TABLE `Expense` ADD COLUMN `installmentNumber` INT NULL AFTER `installmentGroupId`');
        }
        if (!self::columnExists($pdo, 'Expense', 'installmentCount')) {
            $pdo->exec('ALTER TABLE `Expense` ADD COLUMN `installmentCount` INT NULL AFTER `installmentNumber`');
        }
        if (!self::indexExists($pdo, 'Expense', 'Expense_installmentGroupId_idx')) {
            $pdo->exec('ALTER TABLE `Expense` ADD INDEX `Expense_installmentGroupId_idx`(`installmentGroupId`)');
        }
    }

    /** Equipes / integrantes (dropdown do cadastro de cabo). */
    private static function ensureTeamTable(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'Team')) {
            $pdo->exec(
                'CREATE TABLE `Team` (
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
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
        }

        self::ensureTeamMemberColumns($pdo);
        self::migrateTeamCodeToId($pdo);

        $count = (int) $pdo->query('SELECT COUNT(*) AS c FROM `Team`')->fetch()['c'];
        if ($count === 0) {
            $now = date('Y-m-d H:i:s');
            $cols = self::columnExists($pdo, 'Team', 'code')
                ? 'id, memberNumber, code, label, phone, city, cityRegion, active, sortOrder, createdAt, updatedAt'
                : 'id, memberNumber, label, phone, city, cityRegion, active, sortOrder, createdAt, updatedAt';
            $placeholders = self::columnExists($pdo, 'Team', 'code')
                ? '(?,?,?,?,?,?,?,1,?,?,?)'
                : '(?,?,?,?,?,?,1,?,?,?)';
            $ins = $pdo->prepare("INSERT INTO `Team` ({$cols}) VALUES {$placeholders}");
            $i = 0;
            $defaults = DEFAULT_TEAMS;
            asort($defaults, SORT_NATURAL | SORT_FLAG_CASE);
            $meta = DEFAULT_TEAM_PROFILES;
            foreach ($defaults as $seedKey => $label) {
                $profile = $meta[$seedKey] ?? [];
                $row = [
                    cuid(),
                    $i + 1,
                ];
                if (self::columnExists($pdo, 'Team', 'code')) {
                    $row[] = $seedKey;
                }
                $row = array_merge($row, [
                    $label,
                    $profile['phone'] ?? null,
                    $profile['city'] ?? null,
                    $profile['cityRegion'] ?? null,
                    $i,
                    $now,
                    $now,
                ]);
                $ins->execute($row);
                $i++;
            }
        }

        // Remove slug legado `code` (vínculo com cabo passou a usar Team.id).
        if (self::columnExists($pdo, 'Team', 'code')) {
            try {
                if (self::indexExists($pdo, 'Team', 'Team_code_key')) {
                    $pdo->exec('ALTER TABLE `Team` DROP INDEX `Team_code_key`');
                }
            } catch (Throwable) {
            }
            try {
                $pdo->exec('ALTER TABLE `Team` DROP COLUMN `code`');
            } catch (Throwable) {
            }
        }

        if (class_exists('Teams')) {
            Teams::resetCache();
        }
    }

    /**
     * Converte CaboEleitoral.teamCode de slug (ANA_PAULA_…) para Team.id.
     * Amplia a coluna para caber o id automático.
     */
    private static function migrateTeamCodeToId(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'CaboEleitoral') || !self::columnExists($pdo, 'CaboEleitoral', 'teamCode')) {
            return;
        }
        try {
            $pdo->exec('ALTER TABLE `CaboEleitoral` MODIFY COLUMN `teamCode` VARCHAR(191) NULL');
        } catch (Throwable) {
        }
        if (!self::tableExists($pdo, 'Team') || !self::columnExists($pdo, 'Team', 'code')) {
            return;
        }
        try {
            $rows = $pdo->query(
                'SELECT c.id AS caboId, t.id AS teamId
                 FROM `CaboEleitoral` c
                 INNER JOIN `Team` t ON t.code = c.teamCode
                 LEFT JOIN `Team` t2 ON t2.id = c.teamCode
                 WHERE c.teamCode IS NOT NULL AND c.teamCode <> \'\' AND t2.id IS NULL'
            )->fetchAll();
            $upd = $pdo->prepare('UPDATE `CaboEleitoral` SET teamCode=? WHERE id=?');
            foreach ($rows as $r) {
                $upd->execute([(string) $r['teamId'], (string) $r['caboId']]);
            }
        } catch (Throwable) {
        }
    }

    /** Campos do cadastro de integrante (código numérico, telefone, cidade, região). */
    private static function ensureTeamMemberColumns(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'Team')) {
            return;
        }
        if (!self::columnExists($pdo, 'Team', 'memberNumber')) {
            $pdo->exec('ALTER TABLE `Team` ADD COLUMN `memberNumber` INT NULL AFTER `id`');
        }
        if (!self::columnExists($pdo, 'Team', 'phone')) {
            $pdo->exec('ALTER TABLE `Team` ADD COLUMN `phone` VARCHAR(32) NULL AFTER `label`');
        }
        if (!self::columnExists($pdo, 'Team', 'city')) {
            $pdo->exec('ALTER TABLE `Team` ADD COLUMN `city` VARCHAR(191) NULL AFTER `phone`');
        }
        if (!self::columnExists($pdo, 'Team', 'cityRegion')) {
            $pdo->exec('ALTER TABLE `Team` ADD COLUMN `cityRegion` VARCHAR(191) NULL AFTER `city`');
        }

        // Preenche número automático em registros antigos
        $missing = $pdo->query(
            'SELECT id FROM `Team` WHERE memberNumber IS NULL ORDER BY sortOrder ASC, createdAt ASC, label ASC'
        )->fetchAll();
        if ($missing) {
            $max = (int) $pdo->query('SELECT COALESCE(MAX(memberNumber), 0) AS m FROM `Team`')->fetch()['m'];
            $upd = $pdo->prepare('UPDATE `Team` SET memberNumber=? WHERE id=?');
            foreach ($missing as $row) {
                $max++;
                $upd->execute([$max, $row['id']]);
            }
        }

        // Garante NOT NULL + unique quando possível
        try {
            $pdo->exec('ALTER TABLE `Team` MODIFY COLUMN `memberNumber` INT NOT NULL');
        } catch (Throwable) {
            // bases legadas podem falhar se ainda houver NULL
        }
        if (!self::indexExists($pdo, 'Team', 'Team_memberNumber_key')) {
            try {
                $pdo->exec('ALTER TABLE `Team` ADD UNIQUE INDEX `Team_memberNumber_key`(`memberNumber`)');
            } catch (Throwable) {
                // ignore se houver duplicidade transitória
            }
        }

        // Preenche telefone/cidade/região vazios a partir do perfil demo (sem sobrescrever edição)
        if (defined('DEFAULT_TEAM_PROFILES') && defined('DEFAULT_TEAMS')) {
            $hasCode = self::columnExists($pdo, 'Team', 'code');
            $updProfile = $pdo->prepare(
                $hasCode
                    ? 'UPDATE `Team` SET phone=COALESCE(phone, ?), city=COALESCE(city, ?), cityRegion=COALESCE(cityRegion, ?)
                       WHERE code=? AND (phone IS NULL OR city IS NULL OR cityRegion IS NULL)'
                    : 'UPDATE `Team` SET phone=COALESCE(phone, ?), city=COALESCE(city, ?), cityRegion=COALESCE(cityRegion, ?)
                       WHERE label=? AND (phone IS NULL OR city IS NULL OR cityRegion IS NULL)'
            );
            foreach (DEFAULT_TEAM_PROFILES as $seedKey => $profile) {
                $match = $hasCode ? $seedKey : (DEFAULT_TEAMS[$seedKey] ?? null);
                if ($match === null || $match === '') {
                    continue;
                }
                $updProfile->execute([
                    $profile['phone'] ?? null,
                    $profile['city'] ?? null,
                    $profile['cityRegion'] ?? null,
                    $match,
                ]);
            }
        }
    }

    private static function ensureContractPdfColumn(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'Contract')) {
            return;
        }
        if (!self::columnExists($pdo, 'Contract', 'pdfPath')) {
            $pdo->exec('ALTER TABLE `Contract` ADD COLUMN `pdfPath` VARCHAR(255) NULL AFTER `notes`');
        }
    }

    /**
     * Receitas gravadas só com DOADOR_PF/PJ (após remoção do campo Fonte)
     * passam a usar Fundo/Vaquinha quando a conta está vinculada a essas fontes.
     */
    private static function repairRevenueSourceByAccount(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'Revenue') || !self::tableExists($pdo, 'AccountMapping')) {
            return;
        }
        try {
            $pdo->exec(
                "UPDATE `Revenue` r
                 INNER JOIN `AccountMapping` m
                   ON m.campaignId = r.campaignId
                  AND m.kind = 'REVENUE_SOURCE'
                  AND m.bankAccountId = r.bankAccountId
                  AND m.code IN ('FUNDO_PARTIDARIO', 'VAQUINHA_ELEITORAL')
                 SET r.source = m.code, r.updatedAt = CURRENT_TIMESTAMP(3)
                 WHERE r.source IN ('DOADOR_PF', 'DOADOR_PJ')
                   AND r.bankAccountId IS NOT NULL"
            );

            // Contas com nome óbvio, mesmo sem vínculo configurado
            if (self::tableExists($pdo, 'BankAccount')) {
                $pdo->exec(
                    "UPDATE `Revenue` r
                     INNER JOIN `BankAccount` a ON a.id = r.bankAccountId
                     SET r.source = 'VAQUINHA_ELEITORAL', r.updatedAt = CURRENT_TIMESTAMP(3)
                     WHERE r.source IN ('DOADOR_PF', 'DOADOR_PJ')
                       AND (LOWER(a.label) LIKE '%vaquinha%' OR LOWER(a.label) LIKE '%vakinha%')"
                );
                $pdo->exec(
                    "UPDATE `Revenue` r
                     INNER JOIN `BankAccount` a ON a.id = r.bankAccountId
                     SET r.source = 'FUNDO_PARTIDARIO', r.updatedAt = CURRENT_TIMESTAMP(3)
                     WHERE r.source IN ('DOADOR_PF', 'DOADOR_PJ')
                       AND LOWER(a.label) LIKE '%fundo%'"
                );
            }
        } catch (Throwable) {
            // não bloqueia o bootstrap
        }
    }

    /** Categorias de despesa (dropdown de lançamento, vínculos, relatórios). */
    private static function ensureExpenseCategoryTable(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'ExpenseCategory')) {
            $pdo->exec(
                'CREATE TABLE `ExpenseCategory` (
                    `id` VARCHAR(191) NOT NULL,
                    `code` VARCHAR(64) NOT NULL,
                    `label` VARCHAR(191) NOT NULL,
                    `color` VARCHAR(32) NOT NULL DEFAULT \'#0d9488\',
                    `active` BOOLEAN NOT NULL DEFAULT true,
                    `sortOrder` INTEGER NOT NULL DEFAULT 0,
                    `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
                    `updatedAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
                    UNIQUE INDEX `ExpenseCategory_code_key`(`code`),
                    INDEX `ExpenseCategory_active_sort_idx`(`active`, `sortOrder`),
                    PRIMARY KEY (`id`)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
        }

        $count = (int) $pdo->query('SELECT COUNT(*) AS c FROM `ExpenseCategory`')->fetch()['c'];
        if ($count > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $ins = $pdo->prepare(
            'INSERT INTO `ExpenseCategory` (id, code, label, color, active, sortOrder, createdAt, updatedAt)
             VALUES (?,?,?,?,1,?,?,?)'
        );
        $i = 0;
        foreach (DEFAULT_EXPENSE_CATEGORIES as $code => $label) {
            $ins->execute([
                cuid(),
                $code,
                $label,
                DEFAULT_EXPENSE_CATEGORY_COLORS[$code] ?? '#0d9488',
                $i++,
                $now,
                $now,
            ]);
        }
        if (class_exists('Categories')) {
            Categories::resetCache();
        }
    }

    /** Contas: quem pode depositar (CPF / CNPJ) em receitas. */
    private static function ensureBankAccountDepositColumns(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'BankAccount')) {
            return;
        }
        if (!self::columnExists($pdo, 'BankAccount', 'depositCpf')) {
            $pdo->exec('ALTER TABLE `BankAccount` ADD COLUMN `depositCpf` BOOLEAN NOT NULL DEFAULT true AFTER `balance`');
        }
        if (!self::columnExists($pdo, 'BankAccount', 'depositCnpj')) {
            $pdo->exec('ALTER TABLE `BankAccount` ADD COLUMN `depositCnpj` BOOLEAN NOT NULL DEFAULT true AFTER `depositCpf`');
        }
    }

    private static function ensurePasswordResetTable(PDO $pdo): void
    {
        if (self::tableExists($pdo, 'PasswordResetToken')) {
            return;
        }
        $pdo->exec(
            'CREATE TABLE `PasswordResetToken` (
                `id` VARCHAR(191) NOT NULL,
                `userId` VARCHAR(191) NOT NULL,
                `tokenHash` VARCHAR(64) NOT NULL,
                `expiresAt` DATETIME(3) NOT NULL,
                `usedAt` DATETIME(3) NULL,
                `createdAt` DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
                INDEX `PasswordResetToken_tokenHash_idx`(`tokenHash`),
                INDEX `PasswordResetToken_userId_idx`(`userId`),
                PRIMARY KEY (`id`),
                CONSTRAINT `PasswordResetToken_userId_fkey`
                  FOREIGN KEY (`userId`) REFERENCES `User`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
    }

    private static function ensureCaboColumns(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'CaboEleitoral')) {
            return;
        }
        if (!self::columnExists($pdo, 'CaboEleitoral', 'motherName')) {
            $pdo->exec('ALTER TABLE `CaboEleitoral` ADD COLUMN `motherName` VARCHAR(191) NULL');
        }
        if (!self::columnExists($pdo, 'CaboEleitoral', 'zipCode')) {
            $pdo->exec('ALTER TABLE `CaboEleitoral` ADD COLUMN `zipCode` VARCHAR(191) NULL');
        }
        if (!self::columnExists($pdo, 'CaboEleitoral', 'addressNumber')) {
            $pdo->exec('ALTER TABLE `CaboEleitoral` ADD COLUMN `addressNumber` VARCHAR(191) NULL AFTER `address`');
        }
        if (!self::columnExists($pdo, 'CaboEleitoral', 'teamCode')) {
            $pdo->exec('ALTER TABLE `CaboEleitoral` ADD COLUMN `teamCode` VARCHAR(64) NULL AFTER `zone`');
        }
    }

    private static function ensureSupplierColumns(PDO $pdo): void
    {
        $cols = [
            'tradeName' => 'VARCHAR(191) NULL',
            'documentType' => 'VARCHAR(191) NULL',
            'contactName' => 'VARCHAR(191) NULL',
            'zipCode' => 'VARCHAR(191) NULL',
            'addressNumber' => 'VARCHAR(191) NULL',
            'addressComplement' => 'VARCHAR(191) NULL',
            'neighborhood' => 'VARCHAR(191) NULL',
            'stateRegistration' => 'VARCHAR(191) NULL',
            'municipalRegistration' => 'VARCHAR(191) NULL',
            'quantidadeNfes' => 'INT NOT NULL DEFAULT 0',
            'valorTotalNfes' => 'DOUBLE NOT NULL DEFAULT 0',
            // Cadastro: tipo de atividade (Serviço/Venda) e data nasc. (CPF)
            'activityType' => 'VARCHAR(191) NULL',
            'birthDate' => 'DATE NULL',
        ];
        foreach ($cols as $name => $def) {
            if (!self::columnExists($pdo, 'Supplier', $name)) {
                $pdo->exec("ALTER TABLE `Supplier` ADD COLUMN `{$name}` {$def}");
            }
        }
        if (!self::indexExists($pdo, 'Supplier', 'Supplier_document_idx')) {
            try {
                $pdo->exec('ALTER TABLE `Supplier` ADD INDEX `Supplier_document_idx`(`document`)');
            } catch (Throwable) {
            }
        }
    }

    /** NF-e fica no lançamento (Expense), não no cadastro de fornecedor. */
    private static function ensureExpenseNfeColumns(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'Expense')) {
            return;
        }
        $cols = [
            'naturezaOp' => 'VARCHAR(191) NULL',
            'dataEmissao' => 'DATE NULL',
            'numeroNf' => 'VARCHAR(191) NULL',
            'unidadeArrecadadora' => 'VARCHAR(191) NULL',
            'dsUe' => 'VARCHAR(191) NULL',
            'nfeLink' => 'TEXT NULL',
            'importSource' => 'VARCHAR(191) NULL',
        ];
        foreach ($cols as $name => $def) {
            if (!self::columnExists($pdo, 'Expense', $name)) {
                $pdo->exec("ALTER TABLE `Expense` ADD COLUMN `{$name}` {$def}");
            }
        }
        if (!self::indexExists($pdo, 'Expense', 'Expense_numeroNf_idx')) {
            try {
                $pdo->exec('ALTER TABLE `Expense` ADD INDEX `Expense_numeroNf_idx`(`numeroNf`)');
            } catch (Throwable) {
            }
        }
        if (!self::indexExists($pdo, 'Expense', 'Expense_importSource_idx')) {
            try {
                $pdo->exec('ALTER TABLE `Expense` ADD INDEX `Expense_importSource_idx`(`importSource`)');
            } catch (Throwable) {
            }
        }
    }

    /** Remove campos de NF-e que saíram do formulário de lançamento. */
    private static function dropObsoleteExpenseNfeColumns(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'Expense')) {
            return;
        }
        foreach (['modelo', 'numeroSerie', 'ue', 'nfeKey', 'nfeValid', 'nfeValidatedAt'] as $col) {
            if (!self::columnExists($pdo, 'Expense', $col)) {
                continue;
            }
            try {
                $pdo->exec("ALTER TABLE `Expense` DROP COLUMN `{$col}`");
            } catch (Throwable) {
                // MySQL antigo / permissões: segue sem bloquear o app
            }
        }
    }

    /**
     * Migra uma vez os dados que estavam em SupplierNfe para Expense (lançamento).
     * Só roda se ainda houver SupplierNfe e nenhum Expense com importSource=TSE_CSV.
     */
    private static function migrateSupplierNfeIntoExpenses(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'SupplierNfe') || !self::columnExists($pdo, 'Expense', 'importSource')) {
            return;
        }
        try {
            $has = (int) $pdo->query(
                "SELECT COUNT(*) AS c FROM `Expense` WHERE importSource = 'TSE_CSV'"
            )->fetch()['c'];
            if ($has > 0) {
                return;
            }
            $countNfe = (int) $pdo->query('SELECT COUNT(*) AS c FROM `SupplierNfe`')->fetch()['c'];
            if ($countNfe === 0) {
                return;
            }

            $rows = $pdo->query('SELECT * FROM `SupplierNfe` ORDER BY dataEmissao ASC, createdAt ASC')->fetchAll();
            $ins = $pdo->prepare(
                'INSERT INTO `Expense` (
                    id, campaignId, category, supplierName, supplierDoc, supplierId, description, amount, date, status,
                    naturezaOp, dataEmissao, numeroNf,
                    unidadeArrecadadora, dsUe, nfeLink, importSource, bankAccountId, createdById, createdAt, updatedAt
                 ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $now = date('Y-m-d H:i:s');
            foreach ($rows as $r) {
                $dataEm = $r['dataEmissao'] ?: null;
                $date = $dataEm ? ($dataEm . ' 12:00:00') : $now;
                $natureza = strtoupper(trim((string) ($r['naturezaOp'] ?? '')));
                if ($natureza === 'SERV' || $natureza === 'SERVICO') {
                    $natureza = 'SERVICO';
                } elseif ($natureza === 'VEND' || $natureza === 'COMPRA') {
                    $natureza = 'COMPRA';
                } else {
                    $natureza = '';
                }
                $desc = trim(
                    'NF-e ' . ($r['numeroNf'] ?? '') .
                    ($natureza !== '' ? " · {$natureza}" : '') .
                    ' · importado DivulgaCandContas'
                );
                $ins->execute([
                    cuid(),
                    $r['campaignId'],
                    'COMITE',
                    $r['nmEmitente'],
                    $r['cnpjEmitente'],
                    $r['supplierId'],
                    $desc !== '' ? $desc : 'NF-e importada (TSE)',
                    (float) $r['valor'],
                    $date,
                    'LANCADA',
                    $natureza !== '' ? $natureza : null,
                    $dataEm,
                    $r['numeroNf'],
                    $r['unidadeArrecadadora'],
                    $r['dsUe'],
                    $r['link'],
                    'TSE_CSV',
                    null,
                    null,
                    $now,
                    $now,
                ]);
            }
            // Limpa espelho antigo — NF-e passa a viver só no lançamento
            $pdo->exec('DELETE FROM `SupplierNfe`');
        } catch (Throwable) {
            // não bloqueia o bootstrap
        }
    }

    private static function ensureSupplierNfeTable(PDO $pdo): void
    {
        if (self::tableExists($pdo, 'SupplierNfe')) {
            return;
        }
        // Mantida só como legado/compat; novos dados vão para Expense
        $pdo->exec(
            "CREATE TABLE `SupplierNfe` (
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
                PRIMARY KEY (`id`)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }

    private static function ensureSupplierNfeColumns(PDO $pdo): void
    {
        if (!self::tableExists($pdo, 'SupplierNfe')) {
            return;
        }
        $cols = [
            'cnpjEmitente' => "VARCHAR(191) NOT NULL DEFAULT ''",
            'nmEmitente' => "VARCHAR(191) NOT NULL DEFAULT ''",
            'naturezaOp' => 'VARCHAR(191) NULL',
            'modelo' => 'VARCHAR(191) NULL',
            'dataEmissao' => 'DATE NULL',
            'numeroNf' => 'VARCHAR(191) NULL',
            'numeroSerie' => 'VARCHAR(191) NULL',
            'valor' => 'DOUBLE NOT NULL DEFAULT 0',
            'ue' => 'VARCHAR(191) NULL',
            'unidadeArrecadadora' => 'VARCHAR(191) NULL',
            'dsUe' => 'VARCHAR(191) NULL',
            'chaveAcesso' => 'VARCHAR(191) NULL',
            'link' => 'TEXT NULL',
        ];
        foreach ($cols as $name => $def) {
            if (!self::columnExists($pdo, 'SupplierNfe', $name)) {
                $pdo->exec("ALTER TABLE `SupplierNfe` ADD COLUMN `{$name}` {$def}");
            }
        }
    }

    private static function tableExists(PDO $pdo, string $table): bool
    {
        $st = $pdo->prepare(
            'SELECT COUNT(*) AS c FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $st->execute([$table]);
        return (int) $st->fetch()['c'] > 0;
    }

    private static function columnExists(PDO $pdo, string $table, string $column): bool
    {
        $st = $pdo->prepare(
            'SELECT COUNT(*) AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $st->execute([$table, $column]);
        return (int) $st->fetch()['c'] > 0;
    }

    private static function indexExists(PDO $pdo, string $table, string $index): bool
    {
        $st = $pdo->prepare(
            'SELECT COUNT(*) AS c FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?'
        );
        $st->execute([$table, $index]);
        return (int) $st->fetch()['c'] > 0;
    }
}
