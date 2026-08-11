<?php

declare(strict_types=1);

/** Exclusão / atualização de receitas e despesas com estorno de saldo e vínculos. */
final class LancamentoCrud
{
    /** @return array{ok:bool,error?:string} */
    public static function deleteRevenue(string $id, string $userId): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM `Revenue` WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) {
            return ['ok' => false, 'error' => 'Receita não encontrada.'];
        }
        $now = now_sql();
        try {
            $pdo->beginTransaction();
            if (!empty($row['bankAccountId'])) {
                $pdo->prepare('UPDATE `BankAccount` SET balance = balance - ?, updatedAt=? WHERE id=?')
                    ->execute([(float) $row['amount'], $now, $row['bankAccountId']]);
            }
            $pdo->prepare('DELETE FROM `BankTransaction` WHERE matchedRevenueId=?')->execute([$id]);
            $pdo->prepare('DELETE FROM `Revenue` WHERE id=?')->execute([$id]);
            $pdo->commit();
            audit_log($userId, 'DELETE', 'Revenue', $id, 'Excluiu receita ' . money_br((float) $row['amount']) . ' · ' . ($row['description'] ?? ''));
            return ['ok' => true];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array{ok:bool,error?:string} */
    public static function deleteExpense(string $id, string $userId): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM `Expense` WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) {
            return ['ok' => false, 'error' => 'Despesa não encontrada.'];
        }
        $now = now_sql();
        try {
            $pdo->beginTransaction();
            // Só estorna caixa se a despesa já tinha sido paga/lançada (futura nunca debitou)
            if (!empty($row['bankAccountId']) && Lancamento::expenseAffectsCash($row['status'] ?? null)) {
                $pdo->prepare('UPDATE `BankAccount` SET balance = balance + ?, updatedAt=? WHERE id=?')
                    ->execute([(float) $row['amount'], $now, $row['bankAccountId']]);
            }
            $pdo->prepare('DELETE FROM `BankTransaction` WHERE matchedExpenseId=?')->execute([$id]);
            $supplierId = $row['supplierId'] ?? null;
            $campaignId = (string) $row['campaignId'];
            $pdo->prepare('DELETE FROM `Expense` WHERE id=?')->execute([$id]);
            if ($supplierId) {
                refresh_supplier_nfe_totals($pdo, (string) $supplierId, $campaignId, $now);
            }
            $pdo->commit();
            audit_log($userId, 'DELETE', 'Expense', $id, 'Excluiu despesa ' . money_br((float) $row['amount']) . ' · NF ' . ($row['numeroNf'] ?? '—'));
            return ['ok' => true];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Paga despesas FUTURA com vencimento até $throughDate (padrão: hoje).
     * Baixa em lote as despesas FUTURA com vencimento até a data informada.
     *
     * @return array{ok:bool,error?:string,paid?:int,total?:float,ids?:list<string>}
     */
    public static function payDueExpensesThrough(?string $throughDate, string $userId, ?string $campaignId = null): array
    {
        $through = $throughDate ?: (new DateTimeImmutable('today'))->format('Y-m-d');
        $pdo = Database::pdo();
        $sql = "SELECT id FROM `Expense`
                WHERE status = 'FUTURA' AND DATE(`date`) <= ?";
        $params = [$through];
        if ($campaignId) {
            $sql .= ' AND campaignId = ?';
            $params[] = $campaignId;
        }
        $sql .= ' ORDER BY `date` ASC';
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $ids = array_column($st->fetchAll(), 'id');
        $paid = 0;
        $total = 0.0;
        $okIds = [];
        foreach ($ids as $id) {
            $rowSt = $pdo->prepare('SELECT amount FROM `Expense` WHERE id=? LIMIT 1');
            $rowSt->execute([$id]);
            $amount = (float) ($rowSt->fetch()['amount'] ?? 0);
            $res = self::payFutureExpense((string) $id, $userId);
            if (!empty($res['ok'])) {
                $paid++;
                $total += $amount;
                $okIds[] = (string) $id;
            }
        }
        if ($paid === 0 && $ids === []) {
            return ['ok' => true, 'paid' => 0, 'total' => 0.0, 'ids' => []];
        }
        return ['ok' => true, 'paid' => $paid, 'total' => $total, 'ids' => $okIds];
    }

    /**
     * Converte despesa FUTURA em paga: debita conta e atualiza o extrato
     * já pendente na conciliação (ou cria um, se ainda não existir).
     * @return array{ok:bool,error?:string}
     */
    public static function payFutureExpense(string $id, string $userId): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM `Expense` WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) {
            return ['ok' => false, 'error' => 'Despesa não encontrada.'];
        }
        if (($row['status'] ?? '') !== 'FUTURA') {
            return ['ok' => false, 'error' => 'Somente despesas futuras podem ser pagas por esta ação.'];
        }
        $accountId = (string) ($row['bankAccountId'] ?? '');
        if ($accountId === '') {
            return ['ok' => false, 'error' => 'Despesa sem conta bancária vinculada.'];
        }
        $amount = (float) $row['amount'];
        $now = now_sql();
        $desc = (string) ($row['description'] ?? 'Despesa');
        $docRef = !empty($row['numeroNf']) ? ('NF ' . $row['numeroNf']) : null;
        $payDate = substr((string) $row['date'], 0, 10);

        try {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE `Expense` SET status=?, updatedAt=? WHERE id=?')
                ->execute(['PAGA', $now, $id]);
            $pdo->prepare('UPDATE `BankAccount` SET balance = balance - ?, updatedAt=? WHERE id=?')
                ->execute([$amount, $now, $accountId]);

            $txSt = $pdo->prepare('SELECT id FROM `BankTransaction` WHERE matchedExpenseId=? LIMIT 1');
            $txSt->execute([$id]);
            $existingTx = $txSt->fetch();
            if ($existingTx) {
                $txId = (string) $existingTx['id'];
                $pdo->prepare(
                    'UPDATE `BankTransaction`
                     SET bankAccountId=?, date=?, description=?, amount=?, type=?, documentRef=?, status=?, notes=?, updatedAt=?
                     WHERE id=?'
                )->execute([
                    $accountId,
                    $payDate,
                    "Débito — {$desc}",
                    -$amount,
                    'DEBITO',
                    $docRef,
                    'PENDENTE',
                    'Parcela paga — conferir no extrato bancário',
                    $now,
                    $txId,
                ]);
            } else {
                $txId = cuid();
                $pdo->prepare(
                    'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, documentRef, status, matchedExpenseId, notes, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
                )->execute([
                    $txId, $accountId, $payDate, "Débito — {$desc}", -$amount, 'DEBITO',
                    $docRef, 'PENDENTE', $id, 'Parcela paga — conferir no extrato bancário', $now, $now,
                ]);
            }

            $pdo->commit();
            audit_log($userId, 'PAY', 'Expense', $id, 'Pagou despesa futura ' . money_br($amount) . ' · conciliação ' . substr($txId, -6));
            return ['ok' => true];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array{ok:bool,error?:string} */
    public static function updateRevenue(string $id, array $input, string $userId): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM `Revenue` WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) {
            return ['ok' => false, 'error' => 'Receita não encontrada.'];
        }

        $amountRaw = $input['amount'] ?? 0;
        $amount = is_numeric($amountRaw) ? (float) $amountRaw : parse_money_input((string) $amountRaw);
        $date = parse_date_input($input['date'] ?? null);
        $source = (string) ($input['source'] ?? $row['source']);
        if (!isset(REVENUE_SOURCES[$source])) {
            return ['ok' => false, 'error' => 'Fonte inválida.'];
        }
        $donorName = trim((string) ($input['donorName'] ?? ''));
        $donorCpf = only_digits((string) ($input['donorCpf'] ?? ''));
        $receipt = trim((string) ($input['receiptNumber'] ?? ''));
        $description = trim((string) ($input['description'] ?? '')) ?: REVENUE_SOURCES[$source];
        $now = now_sql();

        try {
            $pdo->beginTransaction();
            $oldAmount = (float) $row['amount'];
            $accountId = $row['bankAccountId'];
            if ($accountId && abs($oldAmount - $amount) > 0.0001) {
                $pdo->prepare('UPDATE `BankAccount` SET balance = balance - ? + ?, updatedAt=? WHERE id=?')
                    ->execute([$oldAmount, $amount, $now, $accountId]);
            }
            $pdo->prepare(
                'UPDATE `Revenue` SET source=?, donorName=?, donorCpf=?, amount=?, date=?, description=?, receiptNumber=?, updatedAt=? WHERE id=?'
            )->execute([
                $source, $donorName ?: null, $donorCpf !== '' ? $donorCpf : null, $amount, $date,
                $description, $receipt ?: null, $now, $id,
            ]);
            $pdo->prepare(
                'UPDATE `BankTransaction` SET date=?, description=?, amount=?, updatedAt=? WHERE matchedRevenueId=?'
            )->execute([$date, "Crédito — {$description}", $amount, $now, $id]);
            $pdo->commit();
            audit_log($userId, 'UPDATE', 'Revenue', $id, 'Atualizou receita ' . money_br($amount));
            return ['ok' => true];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array{ok:bool,error?:string} */
    public static function updateExpense(string $id, array $input, string $userId): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM `Expense` WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) {
            return ['ok' => false, 'error' => 'Despesa não encontrada.'];
        }

        $amountRaw = $input['amount'] ?? 0;
        $amount = is_numeric($amountRaw) ? (float) $amountRaw : parse_money_input((string) $amountRaw);
        $date = parse_date_input($input['date'] ?? null);
        $category = (string) ($input['category'] ?? $row['category']);
        if (!Categories::isValid($category, false)) {
            return ['ok' => false, 'error' => 'Categoria inválida.'];
        }
        $description = trim((string) ($input['description'] ?? ''));
        if ($description === '') {
            return ['ok' => false, 'error' => 'Descrição obrigatória.'];
        }
        $supplierId = $input['supplierId'] ?? $row['supplierId'];
        $supplierName = (string) $row['supplierName'];
        $supplierDoc = $row['supplierDoc'];
        if ($supplierId) {
            $s = $pdo->prepare('SELECT name, document FROM `Supplier` WHERE id=? LIMIT 1');
            $s->execute([$supplierId]);
            $sup = $s->fetch();
            if ($sup) {
                $supplierName = $sup['name'];
                $supplierDoc = $sup['document'];
            }
        }
        $now = now_sql();
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
        $naturezaOp = $naturezaOp !== '' ? $naturezaOp : null;
        $numeroNf = trim((string) ($input['numeroNf'] ?? '')) ?: null;
        $dataEmissaoRaw = trim((string) ($input['dataEmissao'] ?? ''));
        $dataEmissao = $dataEmissaoRaw !== ''
            ? (preg_match('/^\d{4}-\d{2}-\d{2}/', $dataEmissaoRaw) ? substr($dataEmissaoRaw, 0, 10) : substr(parse_date_input($dataEmissaoRaw), 0, 10))
            : null;

        try {
            $pdo->beginTransaction();
            $oldAmount = (float) $row['amount'];
            $accountId = $row['bankAccountId'];
            $affectsCash = Lancamento::expenseAffectsCash($row['status'] ?? null);
            if ($accountId && $affectsCash && abs($oldAmount - $amount) > 0.0001) {
                $pdo->prepare('UPDATE `BankAccount` SET balance = balance + ? - ?, updatedAt=? WHERE id=?')
                    ->execute([$oldAmount, $amount, $now, $accountId]);
            }
            $pdo->prepare(
                'UPDATE `Expense` SET category=?, supplierName=?, supplierDoc=?, supplierId=?, description=?, amount=?, date=?,
                 naturezaOp=?, dataEmissao=?, numeroNf=?, updatedAt=?
                 WHERE id=?'
            )->execute([
                $category, $supplierName, $supplierDoc, $supplierId ?: null, $description, $amount, $date,
                $naturezaOp, $dataEmissao, $numeroNf, $now, $id,
            ]);
            // Extrato vinculado: pago/lançado ou parcela futura pendente na conciliação
            $txPrefix = $affectsCash ? 'Débito — ' : 'Parcela a pagar — ';
            $pdo->prepare(
                'UPDATE `BankTransaction` SET date=?, description=?, amount=?, updatedAt=? WHERE matchedExpenseId=?'
            )->execute([$date, $txPrefix . $description, -$amount, $now, $id]);
            if ($supplierId) {
                refresh_supplier_nfe_totals($pdo, (string) $supplierId, (string) $row['campaignId'], $now);
            }
            if (!empty($row['supplierId']) && $row['supplierId'] !== $supplierId) {
                refresh_supplier_nfe_totals($pdo, (string) $row['supplierId'], (string) $row['campaignId'], $now);
            }
            $pdo->commit();
            audit_log($userId, 'UPDATE', 'Expense', $id, 'Atualizou despesa ' . money_br($amount) . ' · NF ' . ($numeroNf ?? '—'));
            return ['ok' => true];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
