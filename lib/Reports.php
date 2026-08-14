<?php
declare(strict_types=1);

/**
 * Relatórios de conferência alinhados ao Conta+JE §11 e prazos TRE-GO 2026.
 * CONTAS gera HTML imprimível / CSV para conferência interna —
 * a entrega oficial permanece no Conta+JE.
 */
final class Reports
{
    /** @return list<array{id:string,group:string,label:string,desc:string,export:list<string>}> */
    public static function catalog(): array
    {
        return [
            // Diversos (Conta+JE §11.1)
            [
                'id' => 'qualificacao',
                'group' => 'diversos',
                'label' => 'Qualificação',
                'desc' => 'Dados do prestador, campanha, representantes e contas bancárias.',
                'export' => ['pdf', 'html'],
            ],
            [
                'id' => 'demonstrativo',
                'group' => 'diversos',
                'label' => 'Demonstrativo de Receitas e Despesas',
                'desc' => 'Totais por fonte/categoria, saldo e uso do teto TRE-GO.',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'extrato-contas',
                'group' => 'diversos',
                'label' => 'Extrato por conta bancária',
                'desc' => 'Saldos e movimentações por fonte (Doações / FEFC / Fundo Partidário).',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'fundo-caixa',
                'group' => 'diversos',
                'label' => 'Fundo de Caixa',
                'desc' => 'Constituição/devolução de fundo de caixa (quando lançado no CONTAS).',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'transferencias',
                'group' => 'diversos',
                'label' => 'Transferência entre Contas',
                'desc' => 'Movimentações internas entre contas do prestador.',
                'export' => ['pdf', 'html', 'csv'],
            ],

            // Receitas (Conta+JE §11.2)
            [
                'id' => 'receitas-financeiras',
                'group' => 'receitas',
                'label' => 'Demonstrativo de Receitas Financeiras',
                'desc' => 'Todas as doações e recursos financeiros lançados.',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'receitas-estimaveis',
                'group' => 'receitas',
                'label' => 'Receitas Estimáveis em Dinheiro',
                'desc' => 'Doações com espécie estimável em dinheiro.',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'receitas-fcc',
                'group' => 'receitas',
                'label' => 'Receitas de Financiamento Coletivo (FCC)',
                'desc' => 'Vaquinha / FCC a partir de 15/05/2026 (TRE-GO).',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'receitas-internet',
                'group' => 'receitas',
                'label' => 'Doações pela Internet',
                'desc' => 'Receitas marcadas como doação recebida pela internet.',
                'export' => ['pdf', 'html', 'csv'],
            ],

            // Despesas (Conta+JE §11.3)
            [
                'id' => 'despesas-efetuadas',
                'group' => 'despesas',
                'label' => 'Despesas Efetuadas',
                'desc' => 'Todas as despesas (exceto canceladas).',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'despesas-nao-pagas',
                'group' => 'despesas',
                'label' => 'Despesas Efetuadas e Não Pagas',
                'desc' => 'Compromissos futuros / parcelas a vencer (status FUTURA).',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'despesas-pos-eleicao',
                'group' => 'despesas',
                'label' => 'Despesas Pagas após a Eleição',
                'desc' => 'Despesas com data após ' . date_br(CAMPAIGN_END_DATE) . '.',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'despesas-advogados',
                'group' => 'despesas',
                'label' => 'Despesas com Advogados',
                'desc' => 'Natureza Conta+JE: serviços advocatícios.',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'despesas-contador',
                'group' => 'despesas',
                'label' => 'Despesas com Contador',
                'desc' => 'Natureza Conta+JE: serviços contábeis.',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'despesas-diversas',
                'group' => 'despesas',
                'label' => 'Despesas Diversas a Especificar',
                'desc' => 'Categoria Conta+JE correspondente.',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'despesas-gerador',
                'group' => 'despesas',
                'label' => 'Despesas com Gerador de Energia',
                'desc' => 'Comícios/eventos ou descrição contendo “gerador”.',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'despesas-combustivel',
                'group' => 'despesas',
                'label' => 'Despesas com Combustível / Carreata',
                'desc' => 'Combustíveis e deslocamento (comunicação TRE-GO: 24h).',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'despesas-passagens',
                'group' => 'despesas',
                'label' => 'Despesas com Passagens Aéreas',
                'desc' => 'Natureza Conta+JE: passagens aéreas.',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'doacoes-candidato-partido',
                'group' => 'despesas',
                'label' => 'Doações a Candidato/Partido',
                'desc' => 'Transferências a outras candidaturas ou partidos.',
                'export' => ['pdf', 'html', 'csv'],
            ],

            // Recibos (Conta+JE §11.4)
            [
                'id' => 'recibos',
                'group' => 'recibos',
                'label' => 'Recibos Eleitorais',
                'desc' => 'Numeração emitida nas receitas com recibo.',
                'export' => ['pdf', 'html', 'csv'],
            ],

            // TRE-GO / prazos
            [
                'id' => 'rf-72h',
                'group' => 'trego',
                'label' => 'Relatório Financeiro (72h)',
                'desc' => 'Receitas dos últimos 3 dias — TRE-GO exige RF em até 72h após cada recurso.',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'prestacao-parcial',
                'group' => 'trego',
                'label' => 'Snapshot Prestação Parcial',
                'desc' => 'Conferência para janela TRE-GO 09–13/09/2026 (entrega no Conta+JE).',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'prestacao-final',
                'group' => 'trego',
                'label' => 'Snapshot Prestação Final',
                'desc' => 'Conferência para janela TRE-GO 05/10–03/11/2026 (2º turno até 14/11).',
                'export' => ['pdf', 'html', 'csv'],
            ],
            [
                'id' => 'limites-pessoal',
                'group' => 'trego',
                'label' => 'Limites de gastos e pessoal (GO)',
                'desc' => 'Teto TRE-GO × realizado e militância/rua cadastrada.',
                'export' => ['pdf', 'html'],
            ],
        ];
    }

    public static function groupLabels(): array
    {
        return [
            'diversos' => 'Diversos (Conta+JE §11.1)',
            'receitas' => 'Receitas (Conta+JE §11.2)',
            'despesas' => 'Despesas (Conta+JE §11.3)',
            'recibos' => 'Recibos eleitorais (Conta+JE §11.4)',
            'trego' => 'Prazos e limites TRE-GO 2026',
        ];
    }

    public static function find(string $id): ?array
    {
        foreach (self::catalog() as $row) {
            if ($row['id'] === $id) {
                return $row;
            }
        }
        return null;
    }

    /** @return array{meta:array,columns:list<string>,rows:list<array>,summary?:array,note?:string} */
    public static function build(string $id, ?array $campaign = null): array
    {
        $campaign = $campaign ?? Metrics::getCampaign();
        if (!$campaign) {
            return [
                'meta' => ['title' => 'Sem campanha', 'generatedAt' => date('c')],
                'columns' => [],
                'rows' => [],
                'note' => 'Cadastre a campanha em Campanha / foto.',
            ];
        }

        $meta = [
            'title' => (self::find($id)['label'] ?? $id),
            'campaign' => (string) ($campaign['candidateName'] ?? ''),
            'office' => (string) ($campaign['office'] ?? ''),
            'party' => trim((string) ($campaign['party'] ?? '') . '/' . (string) ($campaign['partyNumber'] ?? ''), '/'),
            'cnpj' => (string) ($campaign['cnpjCampaign'] ?? ''),
            'generatedAt' => date('c'),
            'build' => defined('APP_BUILD') ? APP_BUILD : '',
        ];

        return match ($id) {
            'qualificacao' => self::reportQualificacao($campaign, $meta),
            'demonstrativo' => self::reportDemonstrativo($campaign, $meta),
            'extrato-contas' => self::reportExtrato($campaign, $meta),
            'fundo-caixa' => self::reportEmpty(
                $meta,
                'Fundo de Caixa',
                'O CONTAS ainda não lança fundo de caixa. Use o Conta+JE (Outras Opções → Fundo de Caixa) para o registro oficial.'
            ),
            'transferencias' => self::reportEmpty(
                $meta,
                'Transferência entre Contas',
                'Registre transferências oficiais no Conta+JE. Aqui confira saldos por conta no extrato.'
            ),
            'receitas-financeiras' => self::reportReceitas($campaign, $meta, null),
            'receitas-estimaveis' => self::reportReceitas($campaign, $meta, 'estimavel'),
            'receitas-fcc' => self::reportReceitas($campaign, $meta, 'fcc'),
            'receitas-internet' => self::reportReceitas($campaign, $meta, 'internet'),
            'despesas-efetuadas' => self::reportDespesas($campaign, $meta, 'todas'),
            'despesas-nao-pagas' => self::reportDespesas($campaign, $meta, 'nao_pagas'),
            'despesas-pos-eleicao' => self::reportDespesas($campaign, $meta, 'pos_eleicao'),
            'despesas-advogados' => self::reportDespesas($campaign, $meta, 'advogados'),
            'despesas-contador' => self::reportDespesas($campaign, $meta, 'contador'),
            'despesas-diversas' => self::reportDespesas($campaign, $meta, 'diversas'),
            'despesas-gerador' => self::reportDespesas($campaign, $meta, 'gerador'),
            'despesas-combustivel' => self::reportDespesas($campaign, $meta, 'combustivel'),
            'despesas-passagens' => self::reportDespesas($campaign, $meta, 'passagens'),
            'doacoes-candidato-partido' => self::reportDespesas($campaign, $meta, 'doacoes'),
            'recibos' => self::reportRecibos($campaign, $meta),
            'rf-72h' => self::reportRf72h($campaign, $meta),
            'prestacao-parcial', 'prestacao-final' => self::reportPrestacaoSnapshot($campaign, $meta, $id),
            'limites-pessoal' => self::reportLimites($campaign, $meta),
            default => self::reportEmpty($meta, 'Relatório', 'Tipo de relatório desconhecido.'),
        };
    }

    public static function toCsv(array $payload): string
    {
        $cols = $payload['columns'] ?? [];
        $rows = $payload['rows'] ?? [];
        $fh = fopen('php://temp', 'r+');
        if ($fh === false) {
            return '';
        }
        // BOM UTF-8
        fwrite($fh, "\xEF\xBB\xBF");
        if ($cols) {
            fputcsv($fh, $cols, ';');
        }
        foreach ($rows as $row) {
            $line = [];
            foreach ($cols as $c) {
                $line[] = is_array($row) ? (string) ($row[$c] ?? '') : '';
            }
            fputcsv($fh, $line, ';');
        }
        rewind($fh);
        $csv = stream_get_contents($fh) ?: '';
        fclose($fh);
        return $csv;
    }

    private static function reportEmpty(array $meta, string $title, string $note): array
    {
        $meta['title'] = $title;
        return [
            'meta' => $meta,
            'columns' => [],
            'rows' => [],
            'note' => $note,
        ];
    }

    private static function reportQualificacao(array $campaign, array $meta): array
    {
        $pdo = Database::pdo();
        $cid = (string) $campaign['id'];
        $reps = [];
        try {
            $st = $pdo->prepare('SELECT * FROM `Representative` WHERE campaignId=? AND active=1 ORDER BY role ASC, name ASC');
            $st->execute([$cid]);
            $reps = $st->fetchAll() ?: [];
        } catch (Throwable) {
        }
        $accounts = $campaign['bankAccounts'] ?? [];
        $rows = [
            ['Campo' => 'Candidato(a)', 'Valor' => (string) ($campaign['candidateName'] ?? '')],
            ['Campo' => 'Número', 'Valor' => (string) ($campaign['candidateNumber'] ?? '')],
            ['Campo' => 'Cargo', 'Valor' => (string) ($campaign['office'] ?? '')],
            ['Campo' => 'Partido', 'Valor' => trim((string) ($campaign['party'] ?? '') . ' / ' . (string) ($campaign['partyNumber'] ?? ''))],
            ['Campo' => 'UF', 'Valor' => (string) ($campaign['state'] ?? 'GO')],
            ['Campo' => 'Ano', 'Valor' => (string) ($campaign['electionYear'] ?? ELECTION_YEAR)],
            ['Campo' => 'CNPJ campanha', 'Valor' => (string) ($campaign['cnpjCampaign'] ?? '')],
            ['Campo' => 'Limite legal', 'Valor' => money_br((float) ($campaign['legalSpendLimit'] ?? 0))],
            ['Campo' => 'Orçamento', 'Valor' => money_br((float) ($campaign['totalBudget'] ?? 0))],
            ['Campo' => 'E-mail', 'Valor' => (string) ($campaign['email'] ?? '')],
            ['Campo' => 'Telefone', 'Valor' => (string) ($campaign['phone'] ?? '')],
            ['Campo' => 'Endereço', 'Valor' => trim(implode(', ', array_filter([
                (string) ($campaign['addressStreet'] ?? ''),
                (string) ($campaign['addressNumber'] ?? ''),
                (string) ($campaign['addressDistrict'] ?? ''),
                (string) ($campaign['addressCity'] ?? ''),
                (string) ($campaign['addressState'] ?? ''),
                (string) ($campaign['addressZip'] ?? ''),
            ])))],
        ];
        foreach ($reps as $r) {
            $role = REPRESENTATIVE_ROLES[$r['role'] ?? ''] ?? (string) ($r['role'] ?? '');
            $extra = trim(implode(' · ', array_filter([
                (string) ($r['cpf'] ?? ''),
                !empty($r['oabNumber']) ? ('OAB ' . ($r['oabUf'] ?? '') . ' ' . $r['oabNumber']) : '',
                !empty($r['crcNumber']) ? ('CRC ' . ($r['crcUf'] ?? '') . ' ' . $r['crcNumber']) : '',
            ])));
            $rows[] = ['Campo' => 'Representante · ' . $role, 'Valor' => trim(($r['name'] ?? '') . ($extra !== '' ? ' — ' . $extra : ''))];
        }
        foreach ($accounts as $a) {
            $origin = ElectoralRules::bankOriginLabel((string) ($a['resourceOrigin'] ?? '')) ?: '—';
            $rows[] = [
                'Campo' => 'Conta · ' . (string) ($a['label'] ?? ''),
                'Valor' => sprintf(
                    '%s · Ag %s · Cc %s · Fonte %s · Saldo %s',
                    (string) ($a['bankName'] ?? ''),
                    (string) ($a['agency'] ?? ''),
                    (string) ($a['accountNumber'] ?? ''),
                    $origin,
                    money_br((float) ($a['balance'] ?? 0))
                ),
            ];
        }
        return [
            'meta' => $meta,
            'columns' => ['Campo', 'Valor'],
            'rows' => $rows,
            'summary' => [
                'Representantes' => (string) count($reps),
                'Contas ativas' => (string) count($accounts),
            ],
        ];
    }

    private static function reportDemonstrativo(array $campaign, array $meta): array
    {
        $pdo = Database::pdo();
        $cid = (string) $campaign['id'];
        $rev = $pdo->prepare('SELECT source, SUM(amount) AS total, COUNT(*) AS c FROM `Revenue` WHERE campaignId=? GROUP BY source');
        $rev->execute([$cid]);
        $revRows = $rev->fetchAll() ?: [];
        $exp = $pdo->prepare("SELECT category, SUM(amount) AS total, COUNT(*) AS c FROM `Expense` WHERE campaignId=? AND status<>'CANCELADA' GROUP BY category");
        $exp->execute([$cid]);
        $expRows = $exp->fetchAll() ?: [];

        $totalR = 0.0;
        $totalD = 0.0;
        $rows = [];
        foreach ($revRows as $r) {
            $totalR += (float) $r['total'];
            $rows[] = [
                'Tipo' => 'Receita',
                'Código' => (string) $r['source'],
                'Descrição' => REVENUE_SOURCES[$r['source']] ?? (string) $r['source'],
                'Qtd' => (string) (int) $r['c'],
                'Valor' => money_br((float) $r['total']),
                'ValorRaw' => number_format((float) $r['total'], 2, '.', ''),
            ];
        }
        foreach ($expRows as $r) {
            $totalD += (float) $r['total'];
            $rows[] = [
                'Tipo' => 'Despesa',
                'Código' => (string) $r['category'],
                'Descrição' => Categories::label((string) $r['category']),
                'Qtd' => (string) (int) $r['c'],
                'Valor' => money_br((float) $r['total']),
                'ValorRaw' => number_format((float) $r['total'], 2, '.', ''),
            ];
        }
        $limit = ElectoralRules::spendLimitForOffice((string) ($campaign['office'] ?? DEFAULT_OFFICE))
            ?? (float) ($campaign['legalSpendLimit'] ?? DEFAULT_LEGAL_SPEND_LIMIT);

        return [
            'meta' => $meta,
            'columns' => ['Tipo', 'Código', 'Descrição', 'Qtd', 'Valor'],
            'rows' => $rows,
            'summary' => [
                'Total receitas' => money_br($totalR),
                'Total despesas' => money_br($totalD),
                'Saldo (rec. − desp.)' => money_br($totalR - $totalD),
                'Teto TRE-GO' => money_br((float) $limit),
                'Uso do teto' => $limit > 0 ? percent_br($totalD / $limit) : '—',
            ],
        ];
    }

    private static function reportExtrato(array $campaign, array $meta): array
    {
        $pdo = Database::pdo();
        $rows = [];
        foreach ($campaign['bankAccounts'] ?? [] as $a) {
            $st = $pdo->prepare(
                'SELECT t.date, t.description, t.amount, t.type, t.status, t.documentRef
                 FROM `BankTransaction` t WHERE t.bankAccountId=? ORDER BY t.date DESC, t.createdAt DESC LIMIT 500'
            );
            $st->execute([(string) $a['id']]);
            $txs = $st->fetchAll() ?: [];
            if (!$txs) {
                $rows[] = [
                    'Conta' => (string) ($a['label'] ?? ''),
                    'Fonte' => ElectoralRules::bankOriginLabel((string) ($a['resourceOrigin'] ?? '')) ?: '—',
                    'Data' => '—',
                    'Descrição' => 'Sem lançamentos no extrato (saldo atual: ' . money_br((float) ($a['balance'] ?? 0)) . ')',
                    'Tipo' => '—',
                    'Status' => '—',
                    'Doc' => '—',
                    'Valor' => money_br(0),
                ];
                continue;
            }
            foreach ($txs as $t) {
                $rows[] = [
                    'Conta' => (string) ($a['label'] ?? ''),
                    'Fonte' => ElectoralRules::bankOriginLabel((string) ($a['resourceOrigin'] ?? '')) ?: '—',
                    'Data' => date_br(substr((string) $t['date'], 0, 10)),
                    'Descrição' => (string) ($t['description'] ?? ''),
                    'Tipo' => (string) ($t['type'] ?? ''),
                    'Status' => (string) ($t['status'] ?? ''),
                    'Doc' => (string) ($t['documentRef'] ?? ''),
                    'Valor' => money_br((float) $t['amount']),
                ];
            }
        }
        return [
            'meta' => $meta,
            'columns' => ['Conta', 'Fonte', 'Data', 'Descrição', 'Tipo', 'Status', 'Doc', 'Valor'],
            'rows' => $rows,
        ];
    }

    private static function reportReceitas(array $campaign, array $meta, ?string $filter): array
    {
        $pdo = Database::pdo();
        $cid = (string) $campaign['id'];
        $sql = 'SELECT r.*, a.label AS accountLabel FROM `Revenue` r
                LEFT JOIN `BankAccount` a ON a.id = r.bankAccountId
                WHERE r.campaignId=?';
        $params = [$cid];
        if ($filter === 'fcc') {
            $sql .= " AND (r.isFcc=1 OR r.source='FCC' OR r.source='VAQUINHA_ELEITORAL')";
        } elseif ($filter === 'internet') {
            $sql .= ' AND r.isInternet=1';
        } elseif ($filter === 'estimavel') {
            $sql .= " AND r.resourceSpecies='ESTIMAVEL'";
        }
        $sql .= ' ORDER BY r.date DESC, r.createdAt DESC';
        try {
            $st = $pdo->prepare($sql);
            $st->execute($params);
            $items = $st->fetchAll() ?: [];
        } catch (Throwable) {
            // Colunas Conta+JE podem não existir em banco antigo — Schema::ensure costuma criar
            $st = $pdo->prepare(
                'SELECT r.*, a.label AS accountLabel FROM `Revenue` r
                 LEFT JOIN `BankAccount` a ON a.id = r.bankAccountId
                 WHERE r.campaignId=? ORDER BY r.date DESC'
            );
            $st->execute([$cid]);
            $items = $st->fetchAll() ?: [];
            if ($filter === 'fcc') {
                $items = array_values(array_filter($items, static fn ($r) => in_array($r['source'] ?? '', ['FCC', 'VAQUINHA_ELEITORAL'], true)));
            } elseif ($filter === 'estimavel' || $filter === 'internet') {
                $items = [];
            }
        }

        $rows = [];
        $total = 0.0;
        foreach ($items as $r) {
            $total += (float) $r['amount'];
            $rows[] = [
                'Data' => date_br(substr((string) $r['date'], 0, 10)),
                'Tipo' => REVENUE_SOURCES[$r['source'] ?? ''] ?? (string) ($r['source'] ?? ''),
                'Doador' => (string) ($r['donorName'] ?? ''),
                'CPF/CNPJ' => (string) ($r['donorCpf'] ?? ''),
                'Espécie' => RESOURCE_SPECIES[$r['resourceSpecies'] ?? ''] ?? (string) ($r['resourceSpecies'] ?? '—'),
                'Recibo' => (string) ($r['receiptNumber'] ?? ''),
                'Conta' => (string) ($r['accountLabel'] ?? ''),
                'Descrição' => (string) ($r['description'] ?? ''),
                'Valor' => money_br((float) $r['amount']),
            ];
        }
        return [
            'meta' => $meta,
            'columns' => ['Data', 'Tipo', 'Doador', 'CPF/CNPJ', 'Espécie', 'Recibo', 'Conta', 'Descrição', 'Valor'],
            'rows' => $rows,
            'summary' => ['Quantidade' => (string) count($rows), 'Total' => money_br($total)],
        ];
    }

    private static function reportDespesas(array $campaign, array $meta, string $filter): array
    {
        $pdo = Database::pdo();
        $cid = (string) $campaign['id'];
        $st = $pdo->prepare(
            "SELECT e.*, a.label AS accountLabel FROM `Expense` e
             LEFT JOIN `BankAccount` a ON a.id = e.bankAccountId
             WHERE e.campaignId=? AND e.status<>'CANCELADA'
             ORDER BY e.date DESC, e.createdAt DESC"
        );
        $st->execute([$cid]);
        $items = $st->fetchAll() ?: [];

        $items = array_values(array_filter($items, static function (array $e) use ($filter): bool {
            $cat = (string) ($e['category'] ?? '');
            $desc = mb_strtolower((string) ($e['description'] ?? ''));
            $status = (string) ($e['status'] ?? '');
            $date = substr((string) ($e['date'] ?? ''), 0, 10);
            return match ($filter) {
                'todas' => true,
                'nao_pagas' => $status === 'FUTURA',
                'pos_eleicao' => $date > CAMPAIGN_END_DATE,
                'advogados' => $cat === 'SERVICOS_ADVOCATICIOS',
                'contador' => $cat === 'SERVICOS_CONTABEIS',
                'diversas' => $cat === 'DESPESAS_DIVERSAS',
                'gerador' => $cat === 'COMICIOS_EVENTOS' || str_contains($desc, 'gerador'),
                'combustivel' => in_array($cat, ['COMBUSTIVEIS', 'COMBUSTIVEIS_TRANSPORTE'], true)
                    || str_contains($desc, 'carreata')
                    || str_contains($desc, 'combust'),
                'passagens' => $cat === 'PASSAGENS_AEREAS',
                'doacoes' => $cat === 'DOACAO_OUTRAS_CANDIDATURAS',
                default => true,
            };
        }));

        $rows = [];
        $total = 0.0;
        foreach ($items as $e) {
            $total += (float) $e['amount'];
            $rows[] = [
                'Data' => date_br(substr((string) $e['date'], 0, 10)),
                'Categoria' => Categories::label((string) ($e['category'] ?? '')),
                'Fornecedor' => (string) ($e['supplierName'] ?? ''),
                'Documento' => (string) ($e['supplierDoc'] ?? ''),
                'NF' => (string) ($e['numeroNf'] ?? ''),
                'Status' => (string) ($e['status'] ?? ''),
                'Conta' => (string) ($e['accountLabel'] ?? ''),
                'Descrição' => (string) ($e['description'] ?? ''),
                'Valor' => money_br((float) $e['amount']),
            ];
        }
        return [
            'meta' => $meta,
            'columns' => ['Data', 'Categoria', 'Fornecedor', 'Documento', 'NF', 'Status', 'Conta', 'Descrição', 'Valor'],
            'rows' => $rows,
            'summary' => ['Quantidade' => (string) count($rows), 'Total' => money_br($total)],
        ];
    }

    private static function reportRecibos(array $campaign, array $meta): array
    {
        $pdo = Database::pdo();
        $cid = (string) $campaign['id'];
        try {
            $st = $pdo->prepare(
                "SELECT date, donorName, donorCpf, amount, receiptNumber, source, emitReceipt
                 FROM `Revenue`
                 WHERE campaignId=? AND receiptNumber IS NOT NULL AND receiptNumber<>''
                 ORDER BY receiptNumber ASC, date ASC"
            );
            $st->execute([$cid]);
            $items = $st->fetchAll() ?: [];
        } catch (Throwable) {
            $items = [];
        }
        $rows = [];
        $total = 0.0;
        foreach ($items as $r) {
            $total += (float) $r['amount'];
            $rows[] = [
                'Recibo' => (string) ($r['receiptNumber'] ?? ''),
                'Data' => date_br(substr((string) $r['date'], 0, 10)),
                'Doador' => (string) ($r['donorName'] ?? ''),
                'CPF' => (string) ($r['donorCpf'] ?? ''),
                'Tipo' => REVENUE_SOURCES[$r['source'] ?? ''] ?? (string) ($r['source'] ?? ''),
                'Valor' => money_br((float) $r['amount']),
            ];
        }
        return [
            'meta' => $meta,
            'columns' => ['Recibo', 'Data', 'Doador', 'CPF', 'Tipo', 'Valor'],
            'rows' => $rows,
            'summary' => ['Recibos' => (string) count($rows), 'Total' => money_br($total)],
            'note' => count($rows) === 0
                ? 'Nenhum recibo emitido. Ao lançar receita, ative “Emissão de recibo eleitoral”.'
                : null,
        ];
    }

    private static function reportRf72h(array $campaign, array $meta): array
    {
        $pdo = Database::pdo();
        $cid = (string) $campaign['id'];
        $since = (new DateTimeImmutable('today'))->modify('-2 days')->format('Y-m-d');
        $st = $pdo->prepare(
            'SELECT r.*, a.label AS accountLabel FROM `Revenue` r
             LEFT JOIN `BankAccount` a ON a.id = r.bankAccountId
             WHERE r.campaignId=? AND r.date >= ?
             ORDER BY r.date DESC'
        );
        $st->execute([$cid, $since . ' 00:00:00']);
        $items = $st->fetchAll() ?: [];
        $rows = [];
        $total = 0.0;
        foreach ($items as $r) {
            $total += (float) $r['amount'];
            $deadline = (new DateTimeImmutable(substr((string) $r['date'], 0, 10)))->modify('+3 days')->format('Y-m-d');
            $rows[] = [
                'Recebido em' => date_br(substr((string) $r['date'], 0, 10)),
                'Entregar RF até' => date_br($deadline),
                'Doador' => (string) ($r['donorName'] ?? ''),
                'Tipo' => REVENUE_SOURCES[$r['source'] ?? ''] ?? (string) ($r['source'] ?? ''),
                'Conta' => (string) ($r['accountLabel'] ?? ''),
                'Descrição' => (string) ($r['description'] ?? ''),
                'Valor' => money_br((float) $r['amount']),
            ];
        }
        $hours = (int) (ElectoralRules::treGoCatalog()['deadlines']['financialReportHoursAfterReceipt'] ?? 72);
        return [
            'meta' => $meta,
            'columns' => ['Recebido em', 'Entregar RF até', 'Doador', 'Tipo', 'Conta', 'Descrição', 'Valor'],
            'rows' => $rows,
            'summary' => [
                'Receitas (últimos 3 dias)' => (string) count($rows),
                'Total' => money_br($total),
                'Prazo TRE-GO' => $hours . 'h após o recebimento',
            ],
            'note' => 'O relatório financeiro oficial é entregue no Conta+JE. Este snapshot ajuda a não perder o prazo de ' . $hours . 'h.',
        ];
    }

    private static function reportPrestacaoSnapshot(array $campaign, array $meta, string $id): array
    {
        $demo = self::reportDemonstrativo($campaign, $meta);
        $deadlines = ElectoralRules::treGoCatalog()['deadlines'] ?? [];
        if ($id === 'prestacao-parcial') {
            $win = $deadlines['partialAccounts'] ?? ['start' => '2026-09-09', 'end' => '2026-09-13'];
            $label = 'Prestação PARCIAL';
        } else {
            $win = $deadlines['finalAccounts'] ?? ['start' => '2026-10-05', 'end' => '2026-11-03'];
            $label = 'Prestação FINAL';
        }
        $meta['title'] = $label . ' — snapshot de conferência';
        $issues = ElectoralRules::checkInconsistencies($campaign);
        $imped = count(array_filter($issues, static fn ($i) => ($i['level'] ?? '') === 'IMPEDITIVA'));
        $demo['meta'] = $meta;
        $demo['summary'] = array_merge($demo['summary'] ?? [], [
            'Janela TRE-GO' => ($win['start'] ?? '') . ' a ' . ($win['end'] ?? ''),
            'Inconsistências impeditivas' => (string) $imped,
            'Entrega oficial' => 'Conta+JE',
        ]);
        $demo['note'] = 'Use este relatório para conferir dados antes de enviar a ' . $label . ' no Conta+JE. Corrija impeditivas em Verificar inconsistências.';
        return $demo;
    }

    private static function reportLimites(array $campaign, array $meta): array
    {
        $pdo = Database::pdo();
        $cid = (string) $campaign['id'];
        $office = (string) ($campaign['office'] ?? DEFAULT_OFFICE);
        $spendLimit = ElectoralRules::spendLimitForOffice($office) ?? DEFAULT_LEGAL_SPEND_LIMIT;
        $persLimit = ElectoralRules::personnelLimitForOffice($office) ?? DEFAULT_PERSONNEL_LIMIT;
        $exp = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS t FROM `Expense` WHERE campaignId=? AND status<>'CANCELADA'");
        $exp->execute([$cid]);
        $spent = (float) ($exp->fetch()['t'] ?? 0);
        $cab = 0;
        try {
            $c = $pdo->prepare('SELECT COUNT(*) AS c FROM `CaboEleitoral` WHERE campaignId=?');
            $c->execute([$cid]);
            $cab = (int) ($c->fetch()['c'] ?? 0);
        } catch (Throwable) {
        }
        $rows = [
            ['Indicador' => 'Cargo', 'Valor' => $office],
            ['Indicador' => 'Teto de gastos TRE-GO', 'Valor' => money_br((float) $spendLimit)],
            ['Indicador' => 'Limite informado na campanha', 'Valor' => money_br((float) ($campaign['legalSpendLimit'] ?? 0))],
            ['Indicador' => 'Despesas lançadas', 'Valor' => money_br($spent)],
            ['Indicador' => 'Saldo do teto', 'Valor' => money_br((float) $spendLimit - $spent)],
            ['Indicador' => 'Teto militância/rua TRE-GO', 'Valor' => (string) $persLimit],
            ['Indicador' => 'Cabos/contratos cadastrados', 'Valor' => (string) $cab],
            ['Indicador' => 'Vagas restantes (pessoal)', 'Valor' => (string) max(0, (int) $persLimit - $cab)],
        ];
        return [
            'meta' => $meta,
            'columns' => ['Indicador', 'Valor'],
            'rows' => $rows,
            'note' => 'Fonte: hub TRE-GO Prestação de Contas Eleições 2026 (limites GO).',
        ];
    }
}
