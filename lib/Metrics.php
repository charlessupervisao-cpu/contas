<?php
declare(strict_types=1);

final class Metrics
{
    public static function getCampaign(): ?array
    {
        $pdo = Database::pdo();
        $campaign = $pdo->query('SELECT * FROM `Campaign` ORDER BY createdAt ASC LIMIT 1')->fetch();
        if (!$campaign) {
            return null;
        }
        $stmt = $pdo->prepare(
            'SELECT * FROM `BankAccount` WHERE campaignId = ? AND active = 1 ORDER BY sortOrder ASC, label ASC'
        );
        $stmt->execute([$campaign['id']]);
        $campaign['bankAccounts'] = $stmt->fetchAll();
        return $campaign;
    }

    public static function getDashboardMetrics(): ?array
    {
        $campaign = self::getCampaign();
        if (!$campaign) {
            return null;
        }
        $pdo = Database::pdo();
        $cid = $campaign['id'];

        $revenues = $pdo->prepare('SELECT * FROM `Revenue` WHERE campaignId = ?');
        $revenues->execute([$cid]);
        $revenues = $revenues->fetchAll();

        $expenses = $pdo->prepare("SELECT * FROM `Expense` WHERE campaignId = ? AND status <> 'CANCELADA'");
        $expenses->execute([$cid]);
        $expenses = $expenses->fetchAll();

        $cStmt = $pdo->prepare('SELECT COUNT(*) AS c FROM `CaboEleitoral` WHERE campaignId = ? AND active = 1');
        $cStmt->execute([$cid]);
        $cabos = (int) $cStmt->fetch()['c'];

        $contracts = (int) $pdo->query("SELECT COUNT(*) AS c FROM `Contract` WHERE status = 'ATIVO'")->fetch()['c'];

        $txStmt = $pdo->prepare(
            'SELECT t.* FROM `BankTransaction` t
             INNER JOIN `BankAccount` a ON a.id = t.bankAccountId
             WHERE a.campaignId = ?'
        );
        $txStmt->execute([$cid]);
        $transactions = $txStmt->fetchAll();

        $totalReceitas = array_sum(array_map(
            static fn ($r) => (($r['source'] ?? '') === 'DOADOR_PJ') ? 0.0 : (float) $r['amount'],
            $revenues
        ));
        // Orçamento: inclui futuras (compromisso). Caixa realizado: só pagas/lançadas.
        $totalDespesas = array_sum(array_column($expenses, 'amount'));
        $totalDespesasPagas = array_sum(array_map(
            static fn ($e) => Lancamento::expenseAffectsCash($e['status'] ?? null) ? (float) $e['amount'] : 0.0,
            $expenses
        ));
        $totalDespesasFuturas = array_sum(array_map(
            static fn ($e) => (($e['status'] ?? '') === 'FUTURA') ? (float) $e['amount'] : 0.0,
            $expenses
        ));

        // Contas parceladas e futuras: qualquer FUTURA (sem débito em caixa ainda).
        // Inclui 2x e despesas com vencimento futuro — não são pagas até a baixa.
        $contasPendentesRows = array_values(array_filter(
            $expenses,
            static fn (array $e): bool => ($e['status'] ?? '') === 'FUTURA'
        ));
        $parcelasAVencer = count($contasPendentesRows);
        $totalParcelasAVencer = array_sum(array_column($contasPendentesRows, 'amount'));
        // Card da home: total das contas parceladas + futuras a pagar.
        $totalDespesasParceladas = $totalParcelasAVencer;

        $saldo = $totalReceitas - $totalDespesasPagas;
        $budget = (float) $campaign['totalBudget'];

        // Campanha não recebe doação de pessoa jurídica — PJ fica fora do dashboard.
        $receitasPorFonte = [];
        foreach (REVENUE_SOURCES as $source => $label) {
            if ($source === 'DOADOR_PJ') {
                continue;
            }
            $items = array_values(array_filter($revenues, fn ($r) => $r['source'] === $source));
            $receitasPorFonte[] = [
                'source' => $source,
                'label' => $label,
                'amount' => array_sum(array_column($items, 'amount')),
                'count' => count($items),
            ];
        }

        $despesasPorCategoria = [];
        foreach (Categories::map(false) as $category => $label) {
            $items = array_values(array_filter($expenses, fn ($e) => $e['category'] === $category));
            $despesasPorCategoria[] = [
                'category' => $category,
                'label' => $label,
                'amount' => array_sum(array_column($items, 'amount')),
                'count' => count($items),
                'color' => Categories::color($category),
            ];
        }

        $vehicleFuelAmount = 0.0;
        foreach ($expenses as $e) {
            if ($e['category'] === 'VEICULOS' || $e['category'] === 'COMBUSTIVEIS') {
                $vehicleFuelAmount += (float) $e['amount'];
            }
        }
        $vehicleFuelLimit = $budget * VEHICLE_FUEL_LIMIT_RATIO;

        // Após simplificação do formulário: acompanha presença de Nº NF (sem chave de acesso).
        $nfeValid = count(array_filter($expenses, fn ($e) => trim((string) ($e['numeroNf'] ?? '')) !== ''));
        $nfeInvalid = 0;
        $nfeMissing = count(array_filter($expenses, fn ($e) => trim((string) ($e['numeroNf'] ?? '')) === ''));

        $conciliated = count(array_filter($transactions, fn ($t) => $t['status'] === 'CONCILIADO'));
        $pending = count(array_filter($transactions, fn ($t) => $t['status'] === 'PENDENTE'));
        $divergent = count(array_filter($transactions, fn ($t) => $t['status'] === 'DIVERGENTE'));

        $donors = [];
        foreach ($revenues as $r) {
            if ($r['source'] === 'DOADOR_PF' && $r['donorCpf']) {
                $donors[$r['donorCpf']] = true;
            }
        }

        $bankBalances = array_map(static fn ($a) => [
            'id' => $a['id'],
            'label' => $a['label'],
            'bankName' => $a['bankName'],
            'balance' => (float) $a['balance'],
            'agency' => $a['agency'],
            'accountNumber' => $a['accountNumber'],
        ], $campaign['bankAccounts']);

        return [
            'campaign' => $campaign,
            'totals' => [
                'totalReceitas' => $totalReceitas,
                'totalDespesas' => $totalDespesas,
                'totalDespesasPagas' => $totalDespesasPagas,
                'totalDespesasFuturas' => $totalDespesasFuturas,
                'totalDespesasParceladas' => $totalDespesasParceladas,
                'totalParcelasAVencer' => $totalParcelasAVencer,
                'saldo' => $saldo,
                'orcamento' => $budget,
                'limiteLegal' => (float) $campaign['legalSpendLimit'],
                'percentualGasto' => $budget > 0 ? $totalDespesas / $budget : 0,
                'percentualRecebido' => $budget > 0 ? $totalReceitas / $budget : 0,
            ],
            'counts' => [
                'receitas' => count(array_filter($revenues, static fn ($r) => ($r['source'] ?? '') !== 'DOADOR_PJ')),
                'despesas' => count($expenses),
                'cabos' => $cabos,
                'contratosAtivos' => $contracts,
                'doadores' => count($donors),
                'contas' => count($campaign['bankAccounts']),
                'nfeValid' => $nfeValid,
                'nfeInvalid' => $nfeInvalid,
                'nfeMissing' => $nfeMissing,
                'conciliated' => $conciliated,
                'pending' => $pending,
                'divergent' => $divergent,
                'parcelasAVencer' => $parcelasAVencer,
            ],
            'receitasPorFonte' => self::withShare($receitasPorFonte, $totalReceitas),
            'despesasPorCategoria' => self::withShare($despesasPorCategoria, $totalDespesas),
            'rankingDespesas' => self::rankByAmount($despesasPorCategoria, $totalDespesas),
            'vehicleFuel' => [
                'amount' => $vehicleFuelAmount,
                'limit' => $vehicleFuelLimit,
                'ratio' => $budget > 0 ? $vehicleFuelAmount / $budget : 0,
                'remaining' => max(0, $vehicleFuelLimit - $vehicleFuelAmount),
                'withinLimit' => $vehicleFuelAmount <= $vehicleFuelLimit,
            ],
            'bankBalances' => $bankBalances,
        ];
    }

    /** Últimas movimentações (entradas + saídas) para o painel financeiro. */
    public static function getRecentMovements(int $limit = 12): array
    {
        $pdo = Database::pdo();
        $campaign = self::getCampaign();
        if (!$campaign) {
            return [];
        }
        $cid = $campaign['id'];

        $sql = "
            (SELECT r.id, r.date AS movDate, r.createdAt, 'ENTRADA' AS kind, r.source AS code,
                    r.description AS detail, r.donorName AS party, r.amount, a.label AS accountLabel
             FROM `Revenue` r
             LEFT JOIN `BankAccount` a ON a.id = r.bankAccountId
             WHERE r.campaignId = ?)
            UNION ALL
            (SELECT e.id, e.date AS movDate, e.createdAt, 'SAIDA' AS kind, e.category AS code,
                    e.description AS detail, e.supplierName AS party, e.amount, a.label AS accountLabel
             FROM `Expense` e
             LEFT JOIN `BankAccount` a ON a.id = e.bankAccountId
             WHERE e.campaignId = ? AND e.status <> 'CANCELADA')
            ORDER BY movDate DESC, createdAt DESC
            LIMIT " . (int) $limit;

        $st = $pdo->prepare($sql);
        $st->execute([$cid, $cid]);
        return $st->fetchAll();
    }

    /** Fluxo de caixa mensal (entradas x saídas) — últimos N meses. */
    public static function getMonthlyCashFlow(int $months = 6): array
    {
        $pdo = Database::pdo();
        $campaign = self::getCampaign();
        if (!$campaign) {
            return [];
        }
        $cid = $campaign['id'];
        $rows = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = date('Y-m-01 00:00:00', strtotime("-{$i} months"));
            $end = date('Y-m-t 23:59:59', strtotime("-{$i} months"));
            $label = date('m/Y', strtotime($start));

            $in = $pdo->prepare('SELECT COALESCE(SUM(amount),0) AS t FROM `Revenue` WHERE campaignId=? AND date BETWEEN ? AND ?');
            $in->execute([$cid, $start, $end]);
            $out = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS t FROM `Expense` WHERE campaignId=? AND status<>'CANCELADA' AND date BETWEEN ? AND ?");
            $out->execute([$cid, $start, $end]);

            $entrada = (float) $in->fetch()['t'];
            $saida = (float) $out->fetch()['t'];
            $rows[] = [
                'label' => $label,
                'entrada' => $entrada,
                'saida' => $saida,
                'saldo' => $entrada - $saida,
            ];
        }
        return $rows;
    }

    /**
     * Fluxo de caixa semanal (entradas × saídas) — de $from até $to (padrão: hoje → 04/10).
     * Semanas ISO (segunda → domingo), da semana de $from até a semana que contém $to.
     *
     * @param string|null $from Y-m-d (null = hoje)
     * @param string|null $to   Y-m-d (null = CAMPAIGN_END_DATE)
     */
    public static function getWeeklyCashFlow(?string $from = null, ?string $to = null): array
    {
        $pdo = Database::pdo();
        $campaign = self::getCampaign();
        if (!$campaign) {
            return [];
        }
        $cid = $campaign['id'];

        try {
            $fromDt = new DateTimeImmutable($from ?: 'today');
        } catch (Throwable $e) {
            $fromDt = new DateTimeImmutable('today');
        }
        try {
            $toDt = new DateTimeImmutable($to ?: CAMPAIGN_END_DATE);
        } catch (Throwable $e) {
            $toDt = new DateTimeImmutable(CAMPAIGN_END_DATE);
        }
        if ($toDt < $fromDt) {
            [$fromDt, $toDt] = [$toDt, $fromDt];
        }

        // Segunda-feira da semana que contém $from
        $dow = (int) $fromDt->format('N'); // 1=seg … 7=dom
        $weekStart = $fromDt->modify('-' . ($dow - 1) . ' days');

        $rows = [];
        $startDt = $weekStart;
        $guard = 0;
        while ($startDt <= $toDt && $guard < 24) {
            $guard++;
            $endDt = $startDt->modify('+6 days');
            // Limita a consulta ao fim da janela quando a semana ultrapassa $to
            $queryEnd = $endDt > $toDt ? $toDt : $endDt;

            $start = $startDt->format('Y-m-d') . ' 00:00:00';
            $end = $queryEnd->format('Y-m-d') . ' 23:59:59';
            $label = $startDt->format('d/m') . '–' . $queryEnd->format('d/m');

            $in = $pdo->prepare('SELECT COALESCE(SUM(amount),0) AS t FROM `Revenue` WHERE campaignId=? AND date BETWEEN ? AND ?');
            $in->execute([$cid, $start, $end]);
            $out = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS t FROM `Expense` WHERE campaignId=? AND status<>'CANCELADA' AND date BETWEEN ? AND ?");
            $out->execute([$cid, $start, $end]);

            $entrada = (float) $in->fetch()['t'];
            $saida = (float) $out->fetch()['t'];
            $rows[] = [
                'label' => $label,
                'weekStart' => $startDt->format('Y-m-d'),
                'weekEnd' => $queryEnd->format('Y-m-d'),
                'entrada' => $entrada,
                'saida' => $saida,
                'saldo' => $entrada - $saida,
            ];
            $startDt = $startDt->modify('+1 week');
        }
        return $rows;
    }

    /**
     * Lista contas pendentes (despesas FUTURA — parceladas ou com vencimento futuro).
     * @return list<array<string,mixed>>
     */
    public static function listPendingFutureExpenses(?string $campaignId = null): array
    {
        $pdo = Database::pdo();
        $campaign = self::getCampaign();
        $cid = $campaignId ?: ($campaign['id'] ?? null);
        if (!$cid) {
            return [];
        }
        $st = $pdo->prepare(
            "SELECT e.*, a.label AS accountLabel
             FROM `Expense` e
             LEFT JOIN `BankAccount` a ON a.id = e.bankAccountId
             WHERE e.campaignId = ? AND e.status = 'FUTURA'
             ORDER BY e.date ASC, e.amount DESC"
        );
        $st->execute([$cid]);
        return $st->fetchAll();
    }

    /**
     * Contas FUTURA a vencer hoje e na semana (quadro do dashboard).
     *
     * @return array{
     *   date:string,weekEnd:string,
     *   vencemHoje:list<array<string,mixed>>,vencemSemana:list<array<string,mixed>>,
     *   totalVenceHoje:float,totalVenceSemana:float
     * }
     */
    public static function getUpcomingDues(?string $campaignId = null, ?string $date = null): array
    {
        $day = $date ?: (new DateTimeImmutable('today'))->format('Y-m-d');
        try {
            $dayDt = new DateTimeImmutable($day);
        } catch (Throwable) {
            $dayDt = new DateTimeImmutable('today');
            $day = $dayDt->format('Y-m-d');
        }
        $weekEnd = $dayDt->modify('+6 days')->format('Y-m-d');
        $empty = [
            'date' => $day,
            'weekEnd' => $weekEnd,
            'vencemHoje' => [],
            'vencemSemana' => [],
            'totalVenceHoje' => 0.0,
            'totalVenceSemana' => 0.0,
        ];
        $cid = $campaignId;
        if ($cid === null || $cid === '') {
            $campaign = self::getCampaign();
            $cid = $campaign['id'] ?? null;
        }
        if (!$cid) {
            return $empty;
        }

        $pdo = Database::pdo();
        $dueSql = "SELECT e.*, a.label AS accountLabel
                   FROM `Expense` e
                   LEFT JOIN `BankAccount` a ON a.id = e.bankAccountId
                   WHERE e.campaignId = ?
                     AND e.status = 'FUTURA'
                     AND DATE(e.date) BETWEEN ? AND ?
                   ORDER BY e.date ASC, e.amount DESC";
        $stToday = $pdo->prepare($dueSql);
        $stToday->execute([$cid, $day, $day]);
        $vencemHoje = $stToday->fetchAll();

        $stWeek = $pdo->prepare($dueSql);
        $stWeek->execute([$cid, $day, $weekEnd]);
        $vencemSemana = $stWeek->fetchAll();

        return [
            'date' => $day,
            'weekEnd' => $weekEnd,
            'vencemHoje' => $vencemHoje,
            'vencemSemana' => $vencemSemana,
            'totalVenceHoje' => array_sum(array_column($vencemHoje, 'amount')),
            'totalVenceSemana' => array_sum(array_column($vencemSemana, 'amount')),
        ];
    }

    public static function getDailyActivity(?string $date = null): array
    {
        $day = $date ?: date('Y-m-d');
        $start = $day . ' 00:00:00';
        $end = $day . ' 23:59:59';
        $pdo = Database::pdo();

        $logs = $pdo->prepare(
            "SELECT l.*, u.name AS userName, u.email AS userEmail
             FROM `AuditLog` l
             LEFT JOIN `User` u ON u.id = l.userId
             WHERE l.createdAt BETWEEN ? AND ?
               AND l.action IN ('LAUNCH','CREATE','BALANCE_ADJUST','RECONCILE','UPDATE','DELETE')
             ORDER BY l.createdAt DESC"
        );
        $logs->execute([$start, $end]);
        $logs = $logs->fetchAll();

        $revenues = $pdo->prepare(
            'SELECT r.*, a.label AS accountLabel FROM `Revenue` r
             LEFT JOIN `BankAccount` a ON a.id = r.bankAccountId
             WHERE r.date BETWEEN ? AND ? ORDER BY r.createdAt DESC'
        );
        $revenues->execute([$start, $end]);
        $revenues = $revenues->fetchAll();

        $expenses = $pdo->prepare(
            "SELECT e.*, a.label AS accountLabel FROM `Expense` e
             LEFT JOIN `BankAccount` a ON a.id = e.bankAccountId
             WHERE e.date BETWEEN ? AND ? AND e.status <> 'CANCELADA'
             ORDER BY e.createdAt DESC"
        );
        $expenses->execute([$start, $end]);
        $expenses = $expenses->fetchAll();

        $adjustments = $pdo->prepare(
            'SELECT b.*, a.label AS accountLabel FROM `BalanceAdjustment` b
             LEFT JOIN `BankAccount` a ON a.id = b.bankAccountId
             WHERE b.date BETWEEN ? AND ? ORDER BY b.createdAt DESC'
        );
        $adjustments->execute([$start, $end]);
        $adjustments = $adjustments->fetchAll();

        $pending = $pdo->prepare(
            "SELECT COUNT(*) AS c FROM `BankTransaction` WHERE date BETWEEN ? AND ? AND status = 'PENDENTE'"
        );
        $pending->execute([$start, $end]);
        $pendingTx = (int) $pending->fetch()['c'];

        $totalIn = array_sum(array_column($revenues, 'amount'));
        $totalOut = array_sum(array_column($expenses, 'amount'));

        return [
            'date' => $day,
            'summary' => [
                'receitas' => count($revenues),
                'despesas' => count($expenses),
                'ajustes' => count($adjustments),
                'pendentesConciliacao' => $pendingTx,
                'totalIn' => $totalIn,
                'totalOut' => $totalOut,
                'saldoDia' => $totalIn - $totalOut,
                'eventos' => count($logs),
            ],
            'logs' => $logs,
            'revenues' => $revenues,
            'expenses' => $expenses,
            'adjustments' => $adjustments,
        ];
    }

    /** @param list<array<string,mixed>> $rows */
    private static function withShare(array $rows, float $total): array
    {
        return array_map(static function (array $row) use ($total) {
            $amount = (float) ($row['amount'] ?? 0);
            $row['share'] = $total > 0 ? $amount / $total : 0;
            return $row;
        }, $rows);
    }

    /**
     * Ranking de despesas por valor (maior → menor), com posição e %.
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    public static function rankByAmount(array $rows, float $total): array
    {
        $rows = array_values(array_filter($rows, static fn ($r) => (float) ($r['amount'] ?? 0) > 0));
        usort($rows, static fn ($a, $b) => (float) $b['amount'] <=> (float) $a['amount']);
        $rank = 1;
        foreach ($rows as &$row) {
            $row['rank'] = $rank++;
            $row['share'] = $total > 0 ? ((float) $row['amount'] / $total) : 0;
        }
        unset($row);
        return $rows;
    }
}
