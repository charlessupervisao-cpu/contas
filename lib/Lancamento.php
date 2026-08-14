<?php
declare(strict_types=1);

final class Lancamento
{
    public static function listAccountsOrdered(string $campaignId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM `BankAccount` WHERE campaignId = ? AND active = 1 ORDER BY sortOrder ASC, createdAt ASC'
        );
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }

    public static function getDefaultAccountId(string $campaignId, string $kind, string $code): ?string
    {
        $stmt = Database::pdo()->prepare(
            'SELECT bankAccountId FROM `AccountMapping` WHERE campaignId = ? AND kind = ? AND code = ? LIMIT 1'
        );
        $stmt->execute([$campaignId, $kind, $code]);
        $row = $stmt->fetch();
        return $row['bankAccountId'] ?? null;
    }

    public static function upsertMappings(string $campaignId, array $items): void
    {
        $pdo = Database::pdo();
        foreach ($items as $item) {
            $kind = (string) $item['kind'];
            $code = (string) $item['code'];
            $bankAccountId = $item['bankAccountId'] ?? null;

            if (!$bankAccountId) {
                $del = $pdo->prepare('DELETE FROM `AccountMapping` WHERE campaignId = ? AND kind = ? AND code = ?');
                $del->execute([$campaignId, $kind, $code]);
                continue;
            }

            $sel = $pdo->prepare('SELECT id FROM `AccountMapping` WHERE campaignId = ? AND kind = ? AND code = ? LIMIT 1');
            $sel->execute([$campaignId, $kind, $code]);
            $existing = $sel->fetch();
            $now = now_sql();
            if ($existing) {
                $upd = $pdo->prepare('UPDATE `AccountMapping` SET bankAccountId = ?, updatedAt = ? WHERE id = ?');
                $upd->execute([$bankAccountId, $now, $existing['id']]);
            } else {
                $ins = $pdo->prepare(
                    'INSERT INTO `AccountMapping` (id, campaignId, kind, code, bankAccountId, createdAt, updatedAt) VALUES (?,?,?,?,?,?,?)'
                );
                $ins->execute([cuid(), $campaignId, $kind, $code, $bankAccountId, $now, $now]);
            }
        }
    }

    public static function seedDefaultMappings(string $campaignId, array $accountIds): void
    {
        if (!$accountIds) {
            return;
        }
        $a = static fn (int $i) => $accountIds[min($i, count($accountIds) - 1)];

        $expenseAccountHint = [
            'COMITE' => 0, 'GRAFICA' => 0, 'COMUNICACAO' => 0,
            'INTERNET' => 2, 'IMPULSIONAMENTO' => 2,
            'VEICULOS' => 3, 'COMBUSTIVEIS' => 3, 'CABOS_ELEITORAIS' => 1,
        ];
        $revenueDefaults = [
            'RECURSOS_PF' => 0,
            'RECURSOS_PROPRIOS' => 0,
            'FUNDO_PARTIDARIO' => 1,
            'FEFC' => 2,
            'FCC' => 0,
            'RECURSOS_PARTIDO' => 1,
            'RECURSOS_OUTROS_CANDIDATOS' => 0,
            'RONI' => 0,
        ];

        $items = [];
        foreach (array_keys(Categories::map()) as $code) {
            $idx = $expenseAccountHint[$code] ?? 0;
            $items[] = ['kind' => 'EXPENSE_CATEGORY', 'code' => $code, 'bankAccountId' => $a($idx)];
        }
        foreach ($revenueDefaults as $code => $idx) {
            if (!isset(REVENUE_SOURCES[$code])) {
                continue;
            }
            $items[] = ['kind' => 'REVENUE_SOURCE', 'code' => $code, 'bankAccountId' => $a($idx)];
        }
        self::upsertMappings($campaignId, $items);
    }

    /** @return array{ok:bool,error?:string,data?:array} */
    public static function create(array $input, string $userId): array
    {
        $amountRaw = $input['amount'] ?? 0;
        $amount = is_numeric($amountRaw)
            ? (float) $amountRaw
            : parse_money_input((string) $amountRaw);
        $date = parse_date_input($input['date'] ?? null);
        $bankAccountId = (string) ($input['bankAccountId'] ?? '');
        $kind = (string) ($input['kind'] ?? '');

        // Despesa 2x: total = soma dos valores das parcelas (datas definidas pelo usuário)
        $resolvedParts = null;
        if ($kind === 'DESPESA' && self::normalizeInstallments($input['installments'] ?? 1) === 2) {
            $resolved = self::resolveInstallmentParts($input);
            if (!$resolved['ok']) {
                return ['ok' => false, 'error' => $resolved['error'] ?? 'Parcelas inválidas.'];
            }
            $resolvedParts = $resolved['parts'];
            $amount = (float) $resolved['total'];
            $date = (string) ($resolvedParts[0]['date'] ?? $date);
        }

        if ($amount <= 0) {
            return ['ok' => false, 'error' => 'Valor inválido.'];
        }
        if ($bankAccountId === '') {
            return ['ok' => false, 'error' => 'Selecione a conta bancária onde o lançamento será contabilizado.'];
        }

        $pdo = Database::pdo();
        $campaign = $pdo->query('SELECT * FROM `Campaign` ORDER BY createdAt ASC LIMIT 1')->fetch();
        if (!$campaign) {
            return ['ok' => false, 'error' => 'Campanha não encontrada.'];
        }

        $accStmt = $pdo->prepare('SELECT * FROM `BankAccount` WHERE id = ? AND active = 1 AND campaignId = ? LIMIT 1');
        $accStmt->execute([$bankAccountId, $campaign['id']]);
        $account = $accStmt->fetch();
        if (!$account) {
            return ['ok' => false, 'error' => 'Conta bancária inválida.'];
        }

        if ($kind === 'RECEITA') {
            return self::createReceita($pdo, $campaign, $account, $input, $userId, $amount, $date);
        }
        if ($kind === 'DESPESA') {
            if ($resolvedParts !== null) {
                $input['__resolvedParts'] = $resolvedParts;
            }
            return self::createDespesa($pdo, $campaign, $account, $input, $userId, $amount, $date);
        }
        return ['ok' => false, 'error' => 'Tipo de lançamento inválido.'];
    }

    private static function createReceita(PDO $pdo, array $campaign, array $account, array $input, string $userId, float $amount, string $date): array
    {
        $allowCpf = !empty($account['depositCpf']);
        $allowCnpj = !empty($account['depositCnpj']);
        if (!$allowCpf && !$allowCnpj) {
            return ['ok' => false, 'error' => 'Esta conta não permite depósito de receita. Ajuste em Contas bancárias.'];
        }

        $donorName = trim((string) ($input['donorName'] ?? ''));
        if ($donorName === '') {
            return ['ok' => false, 'error' => 'Nome do doador é obrigatório.'];
        }

        $donorDoc = only_digits((string) ($input['donorCpf'] ?? ''));
        $docType = strtoupper(trim((string) ($input['donorDocType'] ?? '')));
        // Inferência pelo tamanho quando o tipo não veio ou veio inconsistente
        if ($docType !== 'CPF' && $docType !== 'CNPJ') {
            if (strlen($donorDoc) === 14 && $allowCnpj) {
                $docType = 'CNPJ';
            } elseif (strlen($donorDoc) === 11 && $allowCpf) {
                $docType = 'CPF';
            } elseif ($allowCpf && !$allowCnpj) {
                $docType = 'CPF';
            } elseif ($allowCnpj && !$allowCpf) {
                $docType = 'CNPJ';
            } else {
                return ['ok' => false, 'error' => 'Selecione se o doador é CPF ou CNPJ.'];
            }
        }
        if ($docType === 'CPF' && strlen($donorDoc) === 14 && $allowCnpj) {
            $docType = 'CNPJ';
        }
        if ($docType === 'CNPJ' && strlen($donorDoc) === 11 && $allowCpf) {
            $docType = 'CPF';
        }
        if ($docType === 'CPF' && !$allowCpf) {
            return ['ok' => false, 'error' => 'Esta conta não aceita depósito de CPF.'];
        }
        if ($docType === 'CNPJ' && !$allowCnpj) {
            return ['ok' => false, 'error' => 'Esta conta não aceita depósito de CNPJ.'];
        }

        if ($docType === 'CPF') {
            if (!is_valid_cpf($donorDoc)) {
                return ['ok' => false, 'error' => 'CPF do doador inválido. Corrija os dígitos e tente novamente.'];
            }
        } else {
            if (!is_valid_cnpj($donorDoc)) {
                return ['ok' => false, 'error' => 'CNPJ do doador inválido. Corrija os dígitos e tente novamente.'];
            }
        }

        // Fonte Conta+JE: origem da conta bancária tem prioridade; depois vínculos; depois documento.
        $source = self::resolveRevenueSource(
            $pdo,
            (string) $campaign['id'],
            (string) $account['id'],
            (string) ($account['label'] ?? ''),
            $docType,
            (string) ($account['resourceOrigin'] ?? ''),
            (string) ($input['source'] ?? '')
        );
        // Deputado estadual: doação direta de PJ só é válida em contas de Fundo/FEFC/partido.
        if ($source === 'DOADOR_PJ' || ($docType === 'CNPJ' && in_array($source, ['RECURSOS_PF', 'RECURSOS_PROPRIOS'], true))) {
            return [
                'ok' => false,
                'error' => 'Não é permitido receber doação direta de pessoa jurídica nesta conta. Use conta de Fundo Partidário / FEFC ou registre como Recursos de Partido Político.',
            ];
        }

        $donationType = (string) ($input['donationType'] ?? '');
        if ($donationType === '' || !isset(ElectoralRules::DONATION_TYPES[$donationType])) {
            $donationType = match ($source) {
                'RECURSOS_PROPRIOS' => 'RECURSOS_PROPRIOS',
                'RECURSOS_PARTIDO', 'FUNDO_PARTIDARIO', 'FEFC' => 'RECURSOS_PARTIDO',
                'RECURSOS_OUTROS_CANDIDATOS' => 'RECURSOS_OUTROS_CANDIDATOS',
                'RONI' => 'RONI',
                default => 'RECURSOS_PF',
            };
        }
        $resourceSpecies = strtoupper(trim((string) ($input['resourceSpecies'] ?? '')));
        if ($resourceSpecies !== '' && !isset(RESOURCE_SPECIES[$resourceSpecies])) {
            $resourceSpecies = '';
        }
        $resourceOrigin = (string) ($account['resourceOrigin'] ?? '');
        if ($resourceOrigin === '' && in_array($source, ['FUNDO_PARTIDARIO', 'FEFC'], true)) {
            $resourceOrigin = $source;
        }
        $emitReceipt = !empty($input['emitReceipt']) ? 1 : 0;
        $isFcc = !empty($input['isFcc']) || $source === 'FCC' ? 1 : 0;
        $isInternet = !empty($input['isInternet']) ? 1 : 0;
        $isLoan = !empty($input['isLoan']) ? 1 : 0;
        if ($isFcc) {
            $source = 'FCC';
        }

        $description = trim((string) ($input['description'] ?? '')) ?: (REVENUE_SOURCES[$source] ?? 'Receita');
        $now = now_sql();
        $revenueId = cuid();
        $txId = cuid();
        $receiptNumber = $input['receiptNumber'] ?? null;
        if ($emitReceipt && ($receiptNumber === null || trim((string) $receiptNumber) === '')) {
            $receiptNumber = self::nextElectoralReceipt($pdo, (string) $campaign['id']);
        }

        try {
            $pdo->beginTransaction();
            $hasExtra = self::revenueHasElectoralColumns($pdo);
            if ($hasExtra) {
                $pdo->prepare(
                    'INSERT INTO `Revenue` (id, campaignId, source, donationType, resourceOrigin, resourceSpecies, emitReceipt, isFcc, isInternet, isLoan, donorName, donorCpf, amount, date, description, receiptNumber, bankAccountId, createdById, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
                )->execute([
                    $revenueId, $campaign['id'], $source, $donationType, $resourceOrigin ?: null, $resourceSpecies ?: null,
                    $emitReceipt, $isFcc, $isInternet, $isLoan,
                    $donorName, $donorDoc, $amount, $date, $description,
                    $receiptNumber, $account['id'], $userId, $now, $now,
                ]);
            } else {
                $pdo->prepare(
                    'INSERT INTO `Revenue` (id, campaignId, source, donorName, donorCpf, amount, date, description, receiptNumber, bankAccountId, createdById, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
                )->execute([
                    $revenueId, $campaign['id'], $source,
                    $donorName, $donorDoc, $amount, $date, $description,
                    $receiptNumber, $account['id'], $userId, $now, $now,
                ]);
            }

            $pdo->prepare('UPDATE `BankAccount` SET balance = balance + ?, updatedAt = ? WHERE id = ?')
                ->execute([$amount, $now, $account['id']]);

            $pdo->prepare(
                'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, documentRef, status, matchedRevenueId, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $txId, $account['id'], $date, "Crédito — {$description}", $amount, 'CREDITO',
                $receiptNumber, 'PENDENTE', $revenueId, $now, $now,
            ]);

            audit_log(
                $userId,
                'LAUNCH',
                'Revenue',
                $revenueId,
                sprintf('Receita %s R$ %.2f → %s · conciliação %s', REVENUE_SOURCES[$source] ?? $source, $amount, $account['label'], substr($txId, -6))
            );

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'Falha ao gravar receita: ' . $e->getMessage()];
        }

        return ['ok' => true, 'data' => ['kind' => 'RECEITA', 'id' => $revenueId]];
    }

    private static function revenueHasElectoralColumns(PDO $pdo): bool
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        try {
            $st = $pdo->query("SHOW COLUMNS FROM `Revenue` LIKE 'donationType'");
            $cached = (bool) $st->fetch();
        } catch (Throwable) {
            $cached = false;
        }
        return $cached;
    }

    private static function nextElectoralReceipt(PDO $pdo, string $campaignId): string
    {
        try {
            $st = $pdo->prepare(
                "SELECT COUNT(*) AS c FROM `Revenue` WHERE campaignId=? AND emitReceipt=1 AND receiptNumber IS NOT NULL AND receiptNumber<>''"
            );
            $st->execute([$campaignId]);
            $n = (int) $st->fetch()['c'] + 1;
        } catch (Throwable) {
            $st = $pdo->prepare('SELECT COUNT(*) AS c FROM `Revenue` WHERE campaignId=? AND receiptNumber IS NOT NULL AND receiptNumber<>\'\'');
            $st->execute([$campaignId]);
            $n = (int) $st->fetch()['c'] + 1;
        }
        return sprintf('RE-%s-%04d', ELECTION_YEAR, $n);
    }

    /**
     * Classifica a receita para o dashboard (Receitas por fonte) — Conta+JE.
     */
    public static function resolveRevenueSource(
        PDO $pdo,
        string $campaignId,
        string $bankAccountId,
        string $accountLabel,
        string $docType,
        string $resourceOrigin = '',
        string $explicitSource = ''
    ): string {
        if ($explicitSource !== '' && isset(REVENUE_SOURCES[$explicitSource])) {
            return $explicitSource;
        }
        if ($resourceOrigin !== '') {
            return ElectoralRules::revenueSourceFromBankOrigin($resourceOrigin, $docType);
        }
        try {
            $st = $pdo->prepare(
                "SELECT code FROM `AccountMapping`
                 WHERE campaignId = ? AND kind = 'REVENUE_SOURCE' AND bankAccountId = ?"
            );
            $st->execute([$campaignId, $bankAccountId]);
            $codes = $st->fetchAll(PDO::FETCH_COLUMN);
            foreach (['FEFC', 'FUNDO_PARTIDARIO', 'FCC', 'RECURSOS_PARTIDO', 'RECURSOS_PF', 'VAQUINHA_ELEITORAL'] as $prefer) {
                if (in_array($prefer, $codes, true)) {
                    return $prefer === 'VAQUINHA_ELEITORAL' ? 'FCC' : $prefer;
                }
            }
        } catch (Throwable) {
        }

        $label = mb_strtolower($accountLabel);
        if ($label !== '' && (str_contains($label, 'vaquinha') || str_contains($label, 'vakinha') || str_contains($label, 'fcc'))) {
            return 'FCC';
        }
        if ($label !== '' && str_contains($label, 'fefc')) {
            return 'FEFC';
        }
        if ($label !== '' && str_contains($label, 'fundo')) {
            return 'FUNDO_PARTIDARIO';
        }

        return $docType === 'CNPJ' ? 'RECURSOS_PARTIDO' : 'RECURSOS_PF';
    }

    private static function createDespesa(PDO $pdo, array $campaign, array $account, array $input, string $userId, float $amount, string $date): array
    {
        $category = (string) ($input['category'] ?? '');
        if (!Categories::isValid($category, true)) {
            return ['ok' => false, 'error' => 'Categoria inválida.'];
        }
        $description = trim((string) ($input['description'] ?? ''));
        if ($description === '') {
            return ['ok' => false, 'error' => 'Descrição é obrigatória.'];
        }

        // NF-e simplificada (opcional): tipo de operação, data emissão, nº NF
        $naturezaOp = strtoupper(trim((string) ($input['naturezaOp'] ?? '')));
        if ($naturezaOp === 'SERV') {
            $naturezaOp = 'SERVICO';
        }
        if ($naturezaOp === 'VEND') {
            $naturezaOp = 'COMPRA';
        }
        if ($naturezaOp !== '' && !isset(NFE_OPERATION_TYPES[$naturezaOp])) {
            $naturezaOp = '';
        }
        $dataEmissaoRaw = trim((string) ($input['dataEmissao'] ?? ''));
        $numeroNf = trim((string) ($input['numeroNf'] ?? ''));
        $unidadeArrecadadora = trim((string) ($input['unidadeArrecadadora'] ?? ''));
        $dsUe = trim((string) ($input['dsUe'] ?? ''));
        $nfeLink = trim((string) ($input['nfeLink'] ?? ''));

        $dataEmissao = null;
        if ($dataEmissaoRaw !== '') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $dataEmissaoRaw)) {
                $dataEmissao = substr($dataEmissaoRaw, 0, 10);
            } else {
                $dataEmissao = substr(parse_date_input($dataEmissaoRaw), 0, 10);
            }
        }

        $installments = self::normalizeInstallments($input['installments'] ?? 1);
        if ($installments < 1 || $installments > 2) {
            return ['ok' => false, 'error' => 'Parcelamento permitido somente à vista ou em 2 vezes.'];
        }

        if ($category === 'VEICULOS' || $category === 'COMBUSTIVEIS') {
            $sumStmt = $pdo->prepare(
                "SELECT COALESCE(SUM(amount),0) AS total FROM `Expense`
                 WHERE campaignId = ? AND status <> 'CANCELADA' AND category IN ('VEICULOS','COMBUSTIVEIS')"
            );
            $sumStmt->execute([$campaign['id']]);
            // Limite considera o valor total (compromisso), inclusive parcelas futuras
            $used = (float) $sumStmt->fetch()['total'] + $amount;
            $limit = (float) $campaign['totalBudget'] * VEHICLE_FUEL_LIMIT_RATIO;
            if ($used > $limit) {
                return [
                    'ok' => false,
                    'error' => sprintf('Limite de 20%% do orçamento para Veículos+Combustíveis excedido (R$ %.2f).', $limit),
                ];
            }
        }

        $supplierName = trim((string) ($input['supplierName'] ?? ''));
        $supplierDoc = isset($input['supplierDoc']) ? only_digits((string) $input['supplierDoc']) : null;
        $supplierId = $input['supplierId'] ?? null;
        if ($supplierId) {
            $s = $pdo->prepare('SELECT * FROM `Supplier` WHERE id = ? LIMIT 1');
            $s->execute([$supplierId]);
            $supplier = $s->fetch();
            if (!$supplier) {
                return ['ok' => false, 'error' => 'Fornecedor não encontrado.'];
            }
            $supplierName = $supplier['name'];
            $supplierDoc = $supplier['document'] ?: $supplierDoc;
        }
        if ($supplierName === '') {
            return ['ok' => false, 'error' => 'Fornecedor é obrigatório.'];
        }

        if ($installments > 1 && !empty($input['__resolvedParts']) && is_array($input['__resolvedParts'])) {
            $parts = $input['__resolvedParts'];
        } elseif ($installments > 1) {
            $resolved = self::resolveInstallmentParts($input + ['amount' => $amount, 'date' => $date]);
            if (!$resolved['ok']) {
                return ['ok' => false, 'error' => $resolved['error'] ?? 'Parcelas inválidas.'];
            }
            $parts = $resolved['parts'];
            $amount = (float) $resolved['total'];
        } else {
            $parts = [['amount' => $amount, 'date' => $date]];
        }

        $groupId = $installments > 1 ? cuid() : null;
        $now = now_sql();
        $todayYmd = (new DateTimeImmutable('today'))->format('Y-m-d');
        $createdIds = [];
        $anyFuture = false;

        try {
            $pdo->beginTransaction();

            foreach ($parts as $idx => $part) {
                $n = $idx + 1;
                $expenseId = cuid();
                $createdIds[] = $expenseId;
                $partAmount = (float) $part['amount'];
                $partDate = (string) $part['date'];
                $partDesc = $installments > 1
                    ? (rtrim($description) . sprintf(' (parcela %d/%d)', $n, $installments))
                    : $description;
                // Parcelada (2x) ou vencimento após hoje → FUTURA (compromisso, sem débito).
                // Só debita na baixa manual / pagamento no vencimento.
                $partDateYmd = substr($partDate, 0, 10);
                if ($installments > 1 || ($partDateYmd !== '' && $partDateYmd > $todayYmd)) {
                    $status = 'FUTURA';
                    $anyFuture = true;
                } else {
                    $status = 'PAGA';
                }

                $pdo->prepare(
                    'INSERT INTO `Expense` (
                        id, campaignId, category, supplierName, supplierDoc, supplierId, description, amount, date, status,
                        naturezaOp, dataEmissao, numeroNf,
                        unidadeArrecadadora, dsUe, nfeLink, importSource, bankAccountId, caboId, vehicleId,
                        installmentGroupId, installmentNumber, installmentCount,
                        createdById, createdAt, updatedAt
                     ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
                )->execute([
                    $expenseId, $campaign['id'], $category, $supplierName, $supplierDoc ?: null, $supplierId ?: null,
                    $partDesc, $partAmount, $partDate, $status,
                    $naturezaOp !== '' ? $naturezaOp : null,
                    $dataEmissao,
                    $numeroNf !== '' ? $numeroNf : null,
                    $unidadeArrecadadora !== '' ? $unidadeArrecadadora : null,
                    $dsUe !== '' ? $dsUe : null,
                    $nfeLink !== '' ? $nfeLink : null,
                    null,
                    $account['id'],
                    $input['caboId'] ?? null, $input['vehicleId'] ?? null,
                    $groupId, $installments > 1 ? $n : null, $installments > 1 ? $installments : null,
                    $userId, $now, $now,
                ]);

                $txId = cuid();
                $docRef = $numeroNf !== '' ? ('NF ' . $numeroNf) : null;
                if ($status === 'PAGA') {
                    $pdo->prepare('UPDATE `BankAccount` SET balance = balance - ?, updatedAt = ? WHERE id = ?')
                        ->execute([$partAmount, $now, $account['id']]);

                    $pdo->prepare(
                        'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, documentRef, status, matchedExpenseId, notes, createdAt, updatedAt)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
                    )->execute([
                        $txId, $account['id'], $partDate, "Débito — {$partDesc}", -$partAmount, 'DEBITO',
                        $docRef, 'PENDENTE', $expenseId, null, $now, $now,
                    ]);

                    audit_log(
                        $userId,
                        'LAUNCH',
                        'Expense',
                        $expenseId,
                        sprintf('Despesa %s R$ %.2f → %s · conciliação %s', Categories::label($category), $partAmount, $account['label'], substr($txId, -6))
                    );
                } else {
                    // Conta parcelada/futura: pendente de conciliação (a pagar), sem debitar saldo.
                    $txLabel = $installments > 1 ? "Parcela a pagar — {$partDesc}" : "Conta a pagar — {$partDesc}";
                    $pdo->prepare(
                        'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, documentRef, status, matchedExpenseId, notes, createdAt, updatedAt)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
                    )->execute([
                        $txId, $account['id'], $partDate, $txLabel, -$partAmount, 'DEBITO',
                        $docRef, 'PENDENTE', $expenseId,
                        'Conta parcelada/futura — pendente de conciliação; caixa só muda na baixa manual ou no vencimento',
                        $now, $now,
                    ]);

                    audit_log(
                        $userId,
                        'LAUNCH',
                        'Expense',
                        $expenseId,
                        sprintf(
                            'Despesa futura %s R$ %.2f%s · pendente na conciliação %s · sem débito em conta',
                            Categories::label($category),
                            $partAmount,
                            $installments > 1 ? sprintf(' (parcela %d/%d)', $n, $installments) : '',
                            substr($txId, -6)
                        )
                    );
                }
            }

            if ($supplierId) {
                refresh_supplier_nfe_totals($pdo, (string) $supplierId, (string) $campaign['id'], $now);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'Falha ao gravar despesa: ' . $e->getMessage()];
        }

        return [
            'ok' => true,
            'data' => [
                'kind' => 'DESPESA',
                'id' => $createdIds[0] ?? null,
                'ids' => $createdIds,
                'installments' => $installments,
                'total' => $amount,
                'status' => $anyFuture ? 'FUTURA' : 'PAGA',
            ],
        ];
    }

    /** Despesa paga/lançada movimenta caixa; futura só compromete orçamento. */
    public static function expenseAffectsCash(?string $status): bool
    {
        return in_array((string) $status, ['PAGA', 'LANCADA'], true);
    }

    private static function normalizeInstallments(mixed $raw): int
    {
        if (is_bool($raw)) {
            return $raw ? 2 : 1;
        }
        $n = (int) $raw;
        if ($n <= 0) {
            return 1;
        }
        return min(2, $n);
    }

    /**
     * Lê valor/data das 2 parcelas informadas pelo usuário.
     * @return array{ok:bool,error?:string,parts?:list<array{amount:float,date:string}>,total?:float}
     */
    private static function resolveInstallmentParts(array $input): array
    {
        $rawParts = $input['installmentParts'] ?? null;
        $parts = [];
        if (is_array($rawParts) && count($rawParts) >= 2) {
            for ($i = 0; $i < 2; $i++) {
                $row = $rawParts[$i] ?? [];
                $amtRaw = $row['amount'] ?? 0;
                $amt = is_numeric($amtRaw) ? (float) $amtRaw : parse_money_input((string) $amtRaw);
                $dtRaw = trim((string) ($row['date'] ?? ''));
                if ($dtRaw === '') {
                    return ['ok' => false, 'error' => 'Informe a data de pagamento da ' . ($i + 1) . 'ª parcela.'];
                }
                $dt = parse_date_input($dtRaw);
                if ($amt <= 0) {
                    return ['ok' => false, 'error' => 'Informe o valor da ' . ($i + 1) . 'ª parcela.'];
                }
                $parts[] = ['amount' => round($amt, 2), 'date' => substr($dt, 0, 10)];
            }
        } else {
            // Fallback: divide o total e usa data / data+1 mês (compat)
            $amountRaw = $input['amount'] ?? 0;
            $total = is_numeric($amountRaw) ? (float) $amountRaw : parse_money_input((string) $amountRaw);
            if ($total <= 0) {
                return ['ok' => false, 'error' => 'Informe os valores das duas parcelas.'];
            }
            $base = parse_date_input($input['date'] ?? null);
            $cents = (int) round($total * 100);
            $a = intdiv($cents, 2);
            $b = $cents - $a;
            $d2 = $base;
            try {
                $d2 = (new DateTimeImmutable(substr($base, 0, 10)))->modify('+1 month')->format('Y-m-d');
            } catch (Throwable) {
            }
            $parts = [
                ['amount' => round($a / 100, 2), 'date' => substr($base, 0, 10)],
                ['amount' => round($b / 100, 2), 'date' => $d2],
            ];
        }

        $total = round($parts[0]['amount'] + $parts[1]['amount'], 2);
        return ['ok' => true, 'parts' => $parts, 'total' => $total];
    }
}
