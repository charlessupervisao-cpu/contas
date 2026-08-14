<?php
declare(strict_types=1);

final class Demo
{
    public static function ensureDefaultUsers(): array
    {
        $pdo = Database::pdo();
        $hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 10]);
        $defs = [
            ['Master Campanha', 'master@contas.synetiq.com.br', 'MASTER'],
            ['Tesoureiro', 'financeiro@contas.synetiq.com.br', 'FINANCEIRO'],
            ['Coord. RH', 'rh@contas.synetiq.com.br', 'RH'],
            ['Consulta (somente leitura)', 'consulta@contas.synetiq.com.br', 'CONSULTA'],
        ];
        $now = now_sql();
        foreach ($defs as [$name, $email, $role]) {
            $sel = $pdo->prepare('SELECT id FROM `User` WHERE email = ? LIMIT 1');
            $sel->execute([$email]);
            $row = $sel->fetch();
            if ($row) {
                $pdo->prepare('UPDATE `User` SET name=?, role=?, active=1, passwordHash=?, updatedAt=? WHERE id=?')
                    ->execute([$name, $role, $hash, $now, $row['id']]);
            } else {
                $pdo->prepare(
                    'INSERT INTO `User` (id, name, email, passwordHash, role, active, createdAt, updatedAt) VALUES (?,?,?,?,?,1,?,?)'
                )->execute([cuid(), $name, $email, $hash, $role, $now, $now]);
            }
        }
        $stmt = $pdo->prepare('SELECT * FROM `User` WHERE email = ? LIMIT 1');
        $stmt->execute(['master@contas.synetiq.com.br']);
        return $stmt->fetch();
    }

    /** Remove PDFs de contrato referenciados e resíduos demo em /uploads/contracts. */
    private static function purgeContractPdfs(): void
    {
        $pdo = Database::pdo();
        try {
            $rows = $pdo->query('SELECT pdfPath FROM `Contract` WHERE pdfPath IS NOT NULL')->fetchAll();
            foreach ($rows as $r) {
                ContractPdfUpload::deletePrevious($r['pdfPath'] ?? null);
            }
        } catch (Throwable) {
            // tabela pode não existir ainda
        }
        $dir = dirname(__DIR__) . '/uploads/contracts';
        if (is_dir($dir)) {
            foreach (glob($dir . '/contrato-demo-*.pdf') ?: [] as $file) {
                @unlink($file);
            }
        }
    }

    /** Gera um PDF mínimo de contrato para exercitar o campo pdfPath. */
    private static function writeDemoContractPdf(string $contractId): string
    {
        $dir = ContractPdfUpload::uploadDir();
        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $contractId) ?: 'contrato';
        $filename = 'contrato-demo-' . $safeId . '.pdf';
        $abs = $dir . '/' . $filename;
        $pdf = "%PDF-1.4\n"
            . "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
            . "2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n"
            . "3 0 obj<</Type/Page/MediaBox[0 0 300 144]/Parent 2 0 R/Contents 4 0 R"
            . "/Resources<</Font<</F1 5 0 R>>>>>>endobj\n"
            . "4 0 obj<</Length 62>>stream\n"
            . "BT /F1 12 Tf 18 100 Td (Contrato demo CONTAS) Tj ET\n"
            . "endstream\nendobj\n"
            . "5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n"
            . "trailer<</Size 6/Root 1 0 R>>\n%%EOF\n";
        file_put_contents($abs, $pdf);
        @chmod($abs, 0644);
        return '/uploads/contracts/' . $filename;
    }

    public static function clearOperationalData(?string $userId = null): array
    {
        $pdo = Database::pdo();
        $campaign = $pdo->query('SELECT * FROM `Campaign` ORDER BY createdAt ASC LIMIT 1')->fetch() ?: null;

        self::purgeContractPdfs();

        $pdo->exec('DELETE FROM `AuditLog`');
        $pdo->exec('DELETE FROM `BalanceAdjustment`');
        $pdo->exec('DELETE FROM `BankTransaction`');
        $pdo->exec('DELETE FROM `Expense`');
        $pdo->exec('DELETE FROM `Revenue`');
        $pdo->exec('DELETE FROM `Contract`');
        $pdo->exec('DELETE FROM `CaboEleitoral`');
        $pdo->exec('DELETE FROM `Vehicle`');
        try {
            $pdo->exec('DELETE FROM `SupplierNfe`');
        } catch (Throwable) {
            // tabela pode ainda não existir em bases antigas
        }
        $pdo->exec('DELETE FROM `Supplier`');
        $pdo->exec('DELETE FROM `AccountMapping`');
        $pdo->exec('DELETE FROM `BankAccount`');
        try {
            $pdo->exec('DELETE FROM `Representative`');
        } catch (Throwable) {
            // tabela Conta+JE pode ainda não existir
        }

        $now = now_sql();
        if (!$campaign) {
            $id = cuid();
            $pdo->prepare(
                'INSERT INTO `Campaign` (id, electionYear, candidateName, candidateFullName, candidateNumber, party, partyNumber, cnpjCampaign, office, state, region, ballotName, situation, reelection, legalSpendLimit, totalBudget, birthDate, gender, education, occupation, nationality, website, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $id, 2026, 'Virmondes Cruvinel', 'Virmondes Borges Cruvinel Filho', '44321',
                'UNIÃO', '44', '47552932000100', 'Deputado Estadual', 'GO', 'CENTROOESTE',
                'Virmondes Cruvinel', 'Em campanha — Prestação de contas 2026', 1,
                1270629.01, 1200000, '10/03/1980', 'Masculino', 'Superior Completo',
                'Advogado / Deputado Estadual', 'Brasileira Nata / GO-Goiânia',
                'https://virmondes.com.br', $now, $now,
            ]);
            $campaign = ['id' => $id];
        }

        $pdo->prepare(
            'INSERT INTO `AuditLog` (id, userId, action, entity, details, createdAt) VALUES (?,?,?,?,?,?)'
        )->execute([cuid(), $userId, 'CLEAR', 'SYSTEM', 'Dados operacionais limpos — pronto para abastecimento do zero', $now]);

        return ['campaignId' => $campaign['id']];
    }

    /**
     * Apaga só lançamentos financeiros (receitas, despesas — inclusive parcelas FUTURA —,
     * extrato, ajustes, diário e resíduos de SupplierNfe), mantendo cadastros mestres:
     * campanha/foto, contas, vínculos, fornecedores, cabos/contratos, veículos, usuários
     * e o prazo da eleição (constante da app).
     *
     * @return array{ok:bool,error?:string,counts?:array<string,int>}
     */
    public static function clearFinancialLaunches(string $campaignId, ?string $userId = null): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT id FROM `Campaign` WHERE id=? LIMIT 1');
        $st->execute([$campaignId]);
        if (!$st->fetch()) {
            return ['ok' => false, 'error' => 'Campanha não encontrada.'];
        }

        $countsBefore = self::financialLaunchCounts($campaignId);
        $now = now_sql();
        try {
            $pdo->beginTransaction();

            // 1) Extrato e ajustes das contas da campanha
            $pdo->prepare(
                'DELETE bt FROM `BankTransaction` bt
                 INNER JOIN `BankAccount` ba ON ba.id = bt.bankAccountId
                 WHERE ba.campaignId=?'
            )->execute([$campaignId]);
            $pdo->prepare(
                'DELETE adj FROM `BalanceAdjustment` adj
                 INNER JOIN `BankAccount` ba ON ba.id = adj.bankAccountId
                 WHERE ba.campaignId=?'
            )->execute([$campaignId]);

            // 2) Lançamentos (PAGA, LANCADA, FUTURA, CANCELADA e parcelas 2x)
            $pdo->prepare('DELETE FROM `Expense` WHERE campaignId=?')->execute([$campaignId]);
            $pdo->prepare('DELETE FROM `Revenue` WHERE campaignId=?')->execute([$campaignId]);

            // 3) Resíduo legado de NF-e avulsa (se a tabela existir)
            try {
                $pdo->prepare('DELETE FROM `SupplierNfe` WHERE campaignId=?')->execute([$campaignId]);
            } catch (Throwable) {
                // ignore
            }

            // 4) Diário (eventos) — limpa o histórico operacional
            $pdo->exec('DELETE FROM `AuditLog`');

            // 5) Zera saldos das contas (cadastro permanece)
            $pdo->prepare('UPDATE `BankAccount` SET balance=0, updatedAt=? WHERE campaignId=?')
                ->execute([$now, $campaignId]);

            // 6) Totais de NF-e derivados das despesas
            $pdo->prepare(
                'UPDATE `Supplier` SET quantidadeNfes=0, valorTotalNfes=0, updatedAt=? WHERE campaignId=?'
            )->execute([$now, $campaignId]);

            $pdo->prepare(
                'INSERT INTO `AuditLog` (id, userId, action, entity, entityId, details, createdAt)
                 VALUES (?,?,?,?,?,?,?)'
            )->execute([
                cuid(),
                $userId,
                'CLEAR_LAUNCHES',
                'Campaign',
                $campaignId,
                sprintf(
                    'Apagou lançamentos: %d receitas, %d despesas (%d parcelas a vencer), %d extrato, %d ajustes · cadastros e prazo da eleição mantidos · saldos zerados',
                    $countsBefore['receitas'],
                    $countsBefore['despesas'],
                    $countsBefore['parcelas'],
                    $countsBefore['extrato'],
                    $countsBefore['ajustes']
                ),
                $now,
            ]);

            $pdo->commit();
            return ['ok' => true, 'counts' => $countsBefore];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** Contagens atuais usadas na tela de confirmação. */
    public static function financialLaunchCounts(string $campaignId): array
    {
        $pdo = Database::pdo();
        $q = static function (string $sql, array $params = []) use ($pdo): int {
            $s = $pdo->prepare($sql);
            $s->execute($params);
            return (int) $s->fetchColumn();
        };
        return [
            'receitas' => $q('SELECT COUNT(*) FROM `Revenue` WHERE campaignId=?', [$campaignId]),
            'despesas' => $q('SELECT COUNT(*) FROM `Expense` WHERE campaignId=?', [$campaignId]),
            'parcelas' => $q(
                "SELECT COUNT(*) FROM `Expense`
                 WHERE campaignId=? AND status='FUTURA'
                   AND (installmentCount > 1 OR (installmentGroupId IS NOT NULL AND installmentGroupId <> ''))",
                [$campaignId]
            ),
            'extrato' => $q(
                'SELECT COUNT(*) FROM `BankTransaction` bt
                 INNER JOIN `BankAccount` ba ON ba.id = bt.bankAccountId
                 WHERE ba.campaignId=?',
                [$campaignId]
            ),
            'ajustes' => $q(
                'SELECT COUNT(*) FROM `BalanceAdjustment` adj
                 INNER JOIN `BankAccount` ba ON ba.id = adj.bankAccountId
                 WHERE ba.campaignId=?',
                [$campaignId]
            ),
            'diario' => $q('SELECT COUNT(*) FROM `AuditLog`'),
        ];
    }

    public static function loadDemoData(?string $actorUserId = null): array
    {
        $master = self::ensureDefaultUsers();
        self::clearOperationalData($actorUserId ?: $master['id']);
        $pdo = Database::pdo();
        $now = now_sql();
        $today = new DateTimeImmutable('today');
        $todayYmd = $today->format('Y-m-d');
        // Vencimentos para o quadro do dashboard: hoje, esta semana e mais adiante
        $dueToday = $todayYmd;
        $dueWeek = $today->modify('+3 days')->format('Y-m-d');
        $future1 = $today->modify('+20 days')->format('Y-m-d');
        $future2 = $today->modify('+40 days')->format('Y-m-d');

        $campaign = $pdo->query('SELECT * FROM `Campaign` ORDER BY createdAt ASC LIMIT 1')->fetch();
        $pdo->prepare(
            'UPDATE `Campaign` SET electionYear=?, candidateName=?, candidateFullName=?, candidateNumber=?, party=?, partyNumber=?, totalBudget=?, legalSpendLimit=?, situation=?, website=?, reelection=?, updatedAt=? WHERE id=?'
        )->execute([
            2026, 'Virmondes Cruvinel', 'Virmondes Borges Cruvinel Filho', '44321',
            'UNIÃO', '44', 1200000, 1270629.01, 'Em campanha — Prestação de contas 2026',
            'https://virmondes.com.br', 1, $now, $campaign['id'],
        ]);

        // label, banco, código, agência, conta, saldo, sort, depositCpf, depositCnpj, accountType, active, resourceOrigin, openedAt
        $accountsDef = [
            ['Conta Doações para Campanha', 'Banco do Brasil', '001', '3456-7', '12345-6', 285400, 0, 1, 0, 'Corrente', 1, 'DOACOES_CAMPANHA', '2026-07-01'],
            ['Conta Fundo Partidário', 'Caixa Econômica Federal', '104', '1289', '98765-4', 180000, 1, 0, 1, 'Corrente', 1, 'FUNDO_PARTIDARIO', '2026-07-01'],
            ['Conta FEFC', 'Itaú Unibanco', '341', '4521', '55432-1', 150000, 2, 0, 1, 'Corrente', 1, 'FEFC', '2026-07-05'],
            ['Conta Operacional', 'Santander', '033', '2100', '77881-0', 41320, 3, 1, 0, 'Corrente', 1, 'DOACOES_CAMPANHA', '2026-07-15'],
        ];
        $accounts = [];
        $hasOriginCols = false;
        try {
            $hasOriginCols = (bool) $pdo->query("SHOW COLUMNS FROM `BankAccount` LIKE 'resourceOrigin'")->fetch();
        } catch (Throwable) {
        }
        foreach ($accountsDef as $a) {
            $id = cuid();
            if ($hasOriginCols) {
                $pdo->prepare(
                    'INSERT INTO `BankAccount` (id, campaignId, label, bankName, bankCode, agency, accountNumber, accountType, resourceOrigin, openedAt, balance, depositCpf, depositCnpj, active, sortOrder, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
                )->execute([
                    $id, $campaign['id'], $a[0], $a[1], $a[2], $a[3], $a[4], $a[9], $a[11], $a[12],
                    $a[5], $a[7], $a[8], $a[10], $a[6], $now, $now,
                ]);
            } else {
                $pdo->prepare(
                    'INSERT INTO `BankAccount` (id, campaignId, label, bankName, bankCode, agency, accountNumber, accountType, balance, depositCpf, depositCnpj, active, sortOrder, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
                )->execute([$id, $campaign['id'], $a[0], $a[1], $a[2], $a[3], $a[4], $a[9], $a[5], $a[7], $a[8], $a[10], $a[6], $now, $now]);
            }
            $accounts[] = ['id' => $id, 'label' => $a[0], 'active' => (int) $a[10]];
        }
        $activeAccountIds = array_column(array_filter($accounts, static fn ($a) => (int) $a['active'] === 1), 'id');
        Lancamento::seedDefaultMappings($campaign['id'], $activeAccountIds);

        try {
            $repExists = (bool) $pdo->query(
                "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Representative' LIMIT 1"
            )->fetch();
            if ($repExists) {
                $pdo->prepare(
                    'INSERT INTO `Representative` (id, campaignId, role, name, cpf, email, phone, oabUf, oabNumber, crcUf, crcNumber, roleOther, active, notes, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,?,?,?)'
                )->execute([
                    cuid(), $campaign['id'], 'ADVOGADO', 'Helena Marques Advocacia',
                    format_cpf_cnpj(generate_valid_cpf(9101)), 'advogado@campanha2026.go', '(62) 99910-2026',
                    'GO', '34567', null, null, null, 'Representação legal Conta+JE §7.3', $now, $now,
                ]);
                $pdo->prepare(
                    'INSERT INTO `Representative` (id, campaignId, role, name, cpf, email, phone, oabUf, oabNumber, crcUf, crcNumber, roleOther, active, notes, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,?,?,?)'
                )->execute([
                    cuid(), $campaign['id'], 'CONTABILISTA', 'Carlos Eduardo Contabilidade',
                    format_cpf_cnpj(generate_valid_cpf(9102)), 'contabil@campanha2026.go', '(62) 99920-2026',
                    null, null, 'GO', '12345/O', null, 'CRC responsável pela prestação', $now, $now,
                ]);
            }
        } catch (Throwable) {
            // ignore
        }

        // Datas do demonstrativo: hoje → 04/10/2026 (espalhadas por semana)
        $flowDates = [
            '2026-08-08', '2026-08-15', '2026-08-22', '2026-08-29',
            '2026-09-05', '2026-09-12', '2026-09-19', '2026-09-26', '2026-10-03',
        ];
        $revenues = [
            ['FUNDO_PARTIDARIO', 'Diretório Estadual UNIÃO-GO', 280000, '2026-08-08', 'Repasse fundo partidário — parcela 1', $accounts[1]['id'], null, null],
            ['FUNDO_PARTIDARIO', 'Diretório Estadual UNIÃO-GO', 120000, '2026-09-05', 'Repasse fundo partidário — parcela 2', $accounts[1]['id'], null, null],
            ['FEFC', 'Diretório Nacional UNIÃO', 95000, '2026-08-15', 'Repasse FEFC — parcela 1', $accounts[2]['id'], null, null],
            ['FCC', 'Financiamento Coletivo Oficial', 48500, '2026-08-22', 'Arrecadação FCC — ciclo 1', $accounts[0]['id'], null, null],
            ['FCC', 'Financiamento Coletivo Oficial', 31200, '2026-09-19', 'Arrecadação FCC — ciclo 2', $accounts[0]['id'], null, null],
            ['FCC', 'Financiamento Coletivo Oficial', 27800, '2026-10-03', 'Arrecadação FCC — ciclo final', $accounts[0]['id'], null, null],
        ];
        $donorNames = [
            'Ana Paula Mendes', 'Carlos Eduardo Silva', 'Fernanda Rocha Lima', 'José Roberto Alves',
            'Mariana Costa Nunes', 'Paulo Henrique Dias', 'Luciana Martins Souza', 'Ricardo Borges Pinto',
            'Juliana Ferreira', 'André Luiz Cardoso', 'Patrícia Gomes', 'Bruno Teixeira',
        ];
        foreach ($donorNames as $i => $name) {
            $revenues[] = [
                'RECURSOS_PF', $name, 1500 + $i * 850, $flowDates[$i % count($flowDates)], 'Doação pessoa física',
                $accounts[0]['id'], generate_valid_cpf(1000 + $i), 'REC-2026-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
            ];
        }
        $revenueIds = [];
        foreach ($revenues as $r) {
            $id = cuid();
            $revenueIds[] = $id;
            $donorDoc = $r[6];
            if (is_string($donorDoc) && strlen(only_digits($donorDoc)) === 14) {
                $donorDoc = format_cpf_cnpj($donorDoc);
            }
            $pdo->prepare(
                'INSERT INTO `Revenue` (id, campaignId, source, donorName, donorCpf, amount, date, description, receiptNumber, bankAccountId, createdById, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $id, $campaign['id'], $r[0], $r[1], $donorDoc, $r[2], $r[3] . ' 12:00:00', $r[4], $r[7], $r[5], $master['id'], $now, $now,
            ]);
        }

        $teamIds = array_column(Teams::active(), 'id');
        $caboData = [
            ['Marcos Antônio Pereira', 'Goiânia', 'Zona Norte', 'Maria das Dores Pereira', '1985-04-12', '74000-010', 1],
            ['Sueli Aparecida Ramos', 'Aparecida de Goiânia', 'Centro', 'Ana Ramos Silva', '1990-11-03', '74900-000', 1],
            ['Diego Fernandes Lima', 'Anápolis', 'Zona Sul', 'Helena Lima', '1988-07-21', '75000-100', 1],
            ['Camila Rodrigues', 'Rio Verde', 'Centro', 'Rosa Rodrigues', '1992-01-30', '75900-200', 1],
            ['Rafael Souza Melo', 'Catalão', 'Zona Leste', 'Terezinha Melo', '1983-09-08', '75700-300', 1],
            ['Helena Cristina Dias', 'Itumbiara', 'Centro', 'Célia Dias', '1995-05-17', '75500-400', 1],
            ['Gustavo Henrique Alves', 'Jataí', 'Zona Oeste', 'Aparecida Alves', '1987-12-25', '75800-500', 1],
            ['Beatriz Oliveira', 'Luziânia', 'Centro', 'Joana Oliveira', '1991-03-14', '72800-600', 0], // inativo
        ];
        $cabos = [];
        foreach ($caboData as $i => $c) {
            $id = cuid();
            $cabos[] = $id;
            $teamId = ($i === 7) ? null : ($teamIds[$i % max(1, count($teamIds))] ?? null);
            $pdo->prepare(
                'INSERT INTO `CaboEleitoral` (id, campaignId, fullName, cpf, rg, birthDate, motherName, phone, email, zipCode, address, addressNumber, city, state, zone, teamCode, roleTitle, active, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $id, $campaign['id'], $c[0], generate_valid_cpf(2000 + $i),
                'MG-' . (1000000 + $i), $c[4], $c[3],
                sprintf('(62) 9%d-%d', 8000 + $i, 1000 + $i),
                'cabo' . ($i + 1) . '@campanha2026.go',
                $c[5], 'Rua das Palmeiras', (string) (100 + $i), $c[1], 'GO', $c[2], $teamId, 'Cabo Eleitoral',
                (int) $c[6], $now, $now,
            ]);
            $monthly = 2200 + $i * 100;
            $ctId = cuid();
            $status = $i === 7 ? 'ENCERRADO' : 'ATIVO';
            $notes = $i === 0 ? 'Contrato com PDF de demonstração anexado.' : ($i === 7 ? 'Contrato encerrado — cabo inativo.' : null);
            $pdfPath = null;
            if ($i <= 1) {
                $pdfPath = self::writeDemoContractPdf($ctId);
            }
            $pdo->prepare(
                'INSERT INTO `Contract` (id, caboId, contractNumber, startDate, endDate, monthlyValue, totalValue, functionDesc, status, notes, pdfPath, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $ctId, $id, 'CT-2026-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                '2026-07-01 00:00:00', '2026-10-05 00:00:00', $monthly, $monthly * 3,
                'Articulação territorial e mobilização eleitoral por prazo determinado.',
                $status, $notes, $pdfPath, $now, $now,
            ]);
        }

        $vehiclesDef = [
            ['Van itinerância 01', 'QWE1A23', 'Renault', 'Master', '2022', 'VAN', 'Branca', 'Locadora Rápida GO', generate_valid_cnpj(7100), 'Locação com seguro total', 1],
            ['Carro coordenação', 'RTY2B45', 'Volkswagen', 'Virtus', '2023', 'AUTOMÓVEL', 'Prata', 'Auto Loc Goiânia', generate_valid_cnpj(7101), null, 1],
            ['Motocicleta apoio', 'UIO3C67', 'Honda', 'CG 160', '2021', 'MOTOCICLETA', 'Vermelha', 'João Proprietário', generate_valid_cpf(7102), 'Uso eventual em zonas rurais', 0],
        ];
        $vehicleIds = [];
        foreach ($vehiclesDef as $v) {
            $id = cuid();
            $vehicleIds[] = $id;
            $pdo->prepare(
                'INSERT INTO `Vehicle` (id, campaignId, label, plate, brand, model, year, type, color, ownerName, ownerDoc, notes, active, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $id, $campaign['id'], $v[0], $v[1], $v[2], $v[3], $v[4], $v[5], $v[6], $v[7],
                format_cpf_cnpj((string) $v[8]), $v[9], $v[10], $now, $now,
            ]);
        }

        // Fornecedor PF completo (campos pouco comuns: nascimento, endereço, contato, IE/IM)
        $pfSupplierId = cuid();
        $pfDoc = generate_valid_cpf(8801);
        $pdo->prepare(
            'INSERT INTO `Supplier` (
                id, campaignId, name, tradeName, documentType, document, email, phone, contactName,
                zipCode, address, addressNumber, addressComplement, neighborhood, city, state,
                stateRegistration, municipalRegistration, category, activityType, birthDate,
                quantidadeNfes, valorTotalNfes, notes, active, createdAt, updatedAt
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?)'
        )->execute([
            $pfSupplierId, $campaign['id'], 'João Prestador Autônomo', null, 'CPF', format_cpf_cnpj($pfDoc),
            'joao.prestador@email.com', '(62) 99911-2233', 'João Prestador',
            '74015-010', 'Av. Goiás', '1250', 'Sala 3', 'Centro', 'Goiânia', 'GO',
            null, 'IM-908877', 'Serviços gerais', 'SERVICO', '1978-06-15',
            0, 0, 'Fornecedor PF demo com endereço completo', $now, $now,
        ]);

        // Fornecedores + NF-es do CSV oficial da aba NF-es (DivulgaCandContas)
        $csvImport = TseNfeCsv::replaceFromCsv((string) $campaign['id']);
        $supplierMap = [$pfDoc => $pfSupplierId, format_cpf_cnpj($pfDoc) => $pfSupplierId];
        $supRows = $pdo->prepare('SELECT id, document, name FROM `Supplier` WHERE campaignId = ?');
        $supRows->execute([$campaign['id']]);
        foreach ($supRows->fetchAll() as $s) {
            $supplierMap[only_digits((string) $s['document'])] = (string) $s['id'];
            $supplierMap[(string) $s['name']] = (string) $s['id'];
        }

        // Despesas demo vinculadas a prestadores reais do TSE (por CPF/CNPJ)
        // [category, doc, desc, amount, accountIdx, vehicleIdx|null]
        $expenses = [
            ['GRAFICA', '00747303000172', 'Material gráfico — GRAFOPEL (ref. TSE 2022)', 42000, 0, null],
            ['VEICULOS', '32312128000187', 'Locação — SMART LOC (ref. TSE 2022)', 28000, 0, 0],
            ['IMPULSIONAMENTO', '13347016000117', 'Impulsionamento Facebook / Meta (ref. TSE 2022)', 29465, 2, null],
            ['IMPULSIONAMENTO', '06990590000123', 'Google Ads / NFS-e (ref. TSE 2022)', 15500, 2, null],
            ['COMBUSTIVEIS', '01595271000108', 'Abastecimento — Posto Xodó (ref. TSE 2022)', 12400, 3, 1],
            ['VEICULOS', '24095599000152', 'Locação — Premium Tur (ref. TSE 2022)', 9500, 0, 0],
            ['COMUNICACAO', '47413717000129', 'Evento / música — Dreams (ref. TSE 2022)', 12000, 0, null],
            ['COMUNICACAO', '06273582000166', 'Produção — RS Produtos e Serviços (ref. TSE 2022)', 8000, 0, null],
            ['INTERNET', '14707720000104', 'Comunicação visual — Conexão Digital (ref. TSE 2022)', 3200, 3, null],
            ['GRAFICA', '14647750000164', 'Confecção / malharia — TZ (ref. TSE 2022)', 16420, 0, null],
            ['COMITE', '01732140000117', 'Auditoria e consultoria — MW (ref. TSE 2022)', 13910, 0, null],
            ['COMBUSTIVEIS', '01595271000108', 'Combustível caravana interior', 8700, 3, 1],
        ];

        $expenseIds = [];
        foreach ($expenses as $ei => $e) {
            $id = cuid();
            $expenseIds[] = $id;
            $docDigits = only_digits($e[1]);
            $supplierId = $supplierMap[$docDigits] ?? null;
            $supplierName = $docDigits;
            if ($supplierId) {
                $sn = $pdo->prepare('SELECT name FROM `Supplier` WHERE id = ? LIMIT 1');
                $sn->execute([$supplierId]);
                $supplierName = (string) ($sn->fetch()['name'] ?? $docDigits);
            }
            $expDate = $flowDates[$ei % count($flowDates)] . ' 12:00:00';
            $vehicleId = isset($e[5], $vehicleIds[$e[5]]) ? $vehicleIds[$e[5]] : null;
            $pdo->prepare(
                'INSERT INTO `Expense` (id, campaignId, category, supplierName, supplierDoc, supplierId, description, amount, date, status, naturezaOp, dataEmissao, numeroNf, bankAccountId, vehicleId, createdById, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $id, $campaign['id'], $e[0], $supplierName, format_cpf_cnpj($docDigits), $supplierId, $e[2], $e[3],
                $expDate, 'PAGA', 'COMPRA', substr($expDate, 0, 10), (string) (1000 + $ei),
                $accounts[$e[4]]['id'], $vehicleId, $master['id'], $now, $now,
            ]);
        }

        // Despesa com campos raros de NF-e (unidade arrecadadora, UE, link) — sem CSV
        $idRare = cuid();
        $expenseIds[] = $idRare;
        $pdo->prepare(
            'INSERT INTO `Expense` (
                id, campaignId, category, supplierName, supplierDoc, supplierId, description, amount, date, status,
                naturezaOp, dataEmissao, numeroNf, unidadeArrecadadora, dsUe, nfeLink, importSource,
                bankAccountId, createdById, createdAt, updatedAt
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $idRare, $campaign['id'], 'COMITE', 'João Prestador Autônomo', format_cpf_cnpj($pfDoc), $pfSupplierId,
            'Consultoria de campo — NF com campos TSE manuais', 2750,
            '2026-08-22 10:00:00', 'LANCADA', 'SERVICO', '2026-08-22', '7788',
            'SEFAZ-GO', 'Goiânia', 'https://nfe.sefaz.go.gov.br/demo/7788', 'MANUAL_DEMO',
            $accounts[0]['id'], $master['id'], $now, $now,
        ]);

        $idReal = cuid();
        $expenseIds[] = $idReal;
        $natDoc = '54016069000132';
        if (empty($supplierMap[$natDoc])) {
            $natId = cuid();
            $pdo->prepare(
                'INSERT INTO `Supplier` (id, campaignId, name, tradeName, documentType, document, city, state, category, activityType, notes, active, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,1,?,?)'
            )->execute([
                $natId, $campaign['id'], 'Natural Criacoes', 'Natural Criacoes', 'CNPJ', format_cpf_cnpj($natDoc),
                'Goiânia', 'GO', 'Brindes / lembranças', 'VENDA', 'Emitente exemplo de NF', $now, $now,
            ]);
            $supplierMap[$natDoc] = $natId;
        }
        $pdo->prepare(
            'INSERT INTO `Expense` (id, campaignId, category, supplierName, supplierDoc, supplierId, description, amount, date, status, naturezaOp, dataEmissao, numeroNf, bankAccountId, createdById, createdAt, updatedAt)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $idReal, $campaign['id'], 'GRAFICA', 'Natural Criacoes', format_cpf_cnpj($natDoc), $supplierMap[$natDoc] ?? null,
            'CHAVEIRO LEMBRANCA PIRENOPOLIS', 192, '2026-08-15 14:20:00', 'PAGA',
            'COMPRA', '2026-08-15', '40', $accounts[0]['id'], $master['id'], $now, $now,
        ]);

        $idNoKey = cuid();
        $expenseIds[] = $idNoKey;
        $pdo->prepare(
            'INSERT INTO `Expense` (id, campaignId, category, supplierName, description, amount, date, status, naturezaOp, dataEmissao, numeroNf, bankAccountId, createdById, createdAt, updatedAt)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $idNoKey, $campaign['id'], 'COMITE', 'Prestador sem NF',
            'Despesa sem número de NF', 500,
            '2026-08-29 12:00:00', 'PAGA', 'SERVICO', null, null, $accounts[0]['id'], $master['id'], $now, $now,
        ]);

        // Despesa cancelada (não deve entrar em totais de caixa)
        $idCancel = cuid();
        $expenseIds[] = $idCancel;
        $pdo->prepare(
            'INSERT INTO `Expense` (id, campaignId, category, supplierName, description, amount, date, status, naturezaOp, dataEmissao, numeroNf, bankAccountId, createdById, createdAt, updatedAt)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $idCancel, $campaign['id'], 'COMUNICACAO', 'Evento cancelado Ltda',
            'Show cancelado — não contabilizar', 9000,
            '2026-09-05 12:00:00', 'CANCELADA', 'SERVICO', '2026-09-05', '9991',
            $accounts[0]['id'], $master['id'], $now, $now,
        ]);

        // Despesas futuras/parceladas: contabilizam no orçamento (FUTURA),
        // mas o caixa só muda no vencimento ou pagamento manual.
        // Extrato PENDENTE na conciliação desde o lançamento.
        $insertParcelaPendente = static function (
            PDO $pdo,
            string $expenseId,
            string $accountId,
            string $partDate,
            string $desc,
            float $amt,
            string $now
        ): void {
            $pdo->prepare(
                'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, status, matchedExpenseId, notes, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                cuid(), $accountId, $partDate . ' 12:00:00', 'Parcela a pagar — ' . $desc, -$amt, 'DEBITO',
                'PENDENTE', $expenseId,
                'Futura/parcelada: compromisso no orçamento — débito no vencimento ou pagamento manual; depois conciliar extrato',
                $now, $now,
            ]);
        };

        // Grupo A: 1ª vence HOJE (dashboard) · 2ª daqui a 20 dias
        $instGroupA = cuid();
        foreach ([[4500.0, $dueToday], [3200.0, $future1]] as $nIdx => [$amt, $partDate]) {
            $n = $nIdx + 1;
            $id = cuid();
            $expenseIds[] = $id;
            $desc = sprintf('Assessoria jurídica parcelada (parcela %d/2)', $n);
            $pdo->prepare(
                'INSERT INTO `Expense` (
                    id, campaignId, category, supplierName, supplierDoc, supplierId, description, amount, date, status,
                    naturezaOp, dataEmissao, numeroNf, bankAccountId,
                    installmentGroupId, installmentNumber, installmentCount,
                    createdById, createdAt, updatedAt
                 ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $id, $campaign['id'], 'COMITE', 'Natural Criacoes', format_cpf_cnpj($natDoc), $supplierMap[$natDoc] ?? null,
                $desc, $amt, $partDate . ' 12:00:00', 'FUTURA',
                'SERVICO', $partDate, (string) (5100 + $n), $accounts[0]['id'],
                $instGroupA, $n, 2,
                $master['id'], $now, $now,
            ]);
            $insertParcelaPendente($pdo, $id, $accounts[0]['id'], $partDate, $desc, $amt, $now);
        }

        // Grupo B: 1ª nesta semana · 2ª mais à frente
        $instGroupB = cuid();
        foreach ([[2800.0, $dueWeek], [2800.0, $future2]] as $nIdx => [$amt, $partDate]) {
            $n = $nIdx + 1;
            $id = cuid();
            $expenseIds[] = $id;
            $desc = sprintf('Locação van itinerância (parcela %d/2)', $n);
            $pdo->prepare(
                'INSERT INTO `Expense` (
                    id, campaignId, category, supplierName, description, amount, date, status,
                    naturezaOp, dataEmissao, numeroNf, bankAccountId, vehicleId,
                    installmentGroupId, installmentNumber, installmentCount,
                    createdById, createdAt, updatedAt
                 ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $id, $campaign['id'], 'VEICULOS', 'Locadora Rápida GO',
                $desc, $amt, $partDate . ' 09:00:00', 'FUTURA',
                'SERVICO', $partDate, (string) (5200 + $n), $accounts[0]['id'], $vehicleIds[0] ?? null,
                $instGroupB, $n, 2,
                $master['id'], $now, $now,
            ]);
            $insertParcelaPendente($pdo, $id, $accounts[0]['id'], $partDate, $desc, $amt, $now);
        }

        // Despesa futura avulsa (não parcelada) — compromisso orçamentário sem débito
        $idFuturaAvulsa = cuid();
        $expenseIds[] = $idFuturaAvulsa;
        $futAvulsaDate = $dueWeek;
        $futAvulsaDesc = 'Locação de som — evento interior (despesa futura)';
        $pdo->prepare(
            'INSERT INTO `Expense` (
                id, campaignId, category, supplierName, description, amount, date, status,
                naturezaOp, dataEmissao, numeroNf, bankAccountId, createdById, createdAt, updatedAt
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $idFuturaAvulsa, $campaign['id'], 'COMUNICACAO', 'Dreams Eventos',
            $futAvulsaDesc, 6500, $futAvulsaDate . ' 15:00:00', 'FUTURA',
            'SERVICO', $futAvulsaDate, '6100', $accounts[0]['id'], $master['id'], $now, $now,
        ]);
        $insertParcelaPendente($pdo, $idFuturaAvulsa, $accounts[0]['id'], $futAvulsaDate, $futAvulsaDesc, 6500.0, $now);

        foreach ($cabos as $i => $caboId) {
            if ((int) $caboData[$i][6] !== 1) {
                continue; // cabo inativo sem folha
            }
            $id = cuid();
            $expenseIds[] = $id;
            $amount = 2200 + $i * 100;
            $caboDate = $flowDates[($i + 2) % count($flowDates)] . ' 12:00:00';
            $pdo->prepare(
                'INSERT INTO `Expense` (id, campaignId, category, supplierName, description, amount, date, status, naturezaOp, dataEmissao, numeroNf, bankAccountId, caboId, createdById, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $id, $campaign['id'], 'CABOS_ELEITORAIS', 'Folha cabos', 'Pagamento cabo — parcela 1',
                $amount, $caboDate, 'PAGA', 'SERVICO', substr($caboDate, 0, 10), (string) (2000 + $i),
                $accounts[1]['id'], $caboId, $master['id'], $now, $now,
            ]);
        }

        // bank transactions (sample)
        for ($i = 0; $i < min(8, count($revenueIds)); $i++) {
            $r = $revenues[$i];
            $pdo->prepare(
                'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, status, matchedRevenueId, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                cuid(), $r[5], $r[3] . ' 12:00:00', 'Crédito — ' . $r[4], $r[2], 'CREDITO',
                'CONCILIADO', $revenueIds[$i], $now, $now,
            ]);
        }
        for ($i = 0; $i < min(10, count($expenses)); $i++) {
            $e = $expenses[$i];
            $pdo->prepare(
                'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, status, matchedExpenseId, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                cuid(), $accounts[$e[4]]['id'], $flowDates[$i % count($flowDates)] . ' 12:00:00', 'Débito — ' . $e[2], -$e[3], 'DEBITO',
                'CONCILIADO', $expenseIds[$i], $now, $now,
            ]);
        }
        $pdo->prepare(
            'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, status, createdAt, updatedAt)
             VALUES (?,?,?,?,?,?,?,?,?)'
        )->execute([cuid(), $accounts[0]['id'], $now, 'TED pendente — transferência interna', -1500, 'DEBITO', 'PENDENTE', $now, $now]);
        $pdo->prepare(
            'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, status, notes, createdAt, updatedAt)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        )->execute([cuid(), $accounts[3]['id'], $now, 'Tarifa bancária divergente', -45.9, 'DEBITO', 'DIVERGENTE', 'Conferir extrato', $now, $now]);

        $pdo->prepare(
            'INSERT INTO `BalanceAdjustment` (id, bankAccountId, previousBalance, newBalance, difference, date, reason, notes, createdById, createdAt)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            cuid(), $accounts[0]['id'], 280000, 285400, 5400, $now,
            'Ajuste de abertura conforme extrato BB', 'Demo: exercita campo notes do ajuste', $master['id'], $now,
        ]);

        $parcSt = $pdo->prepare("SELECT COUNT(*) AS c FROM `Expense` WHERE campaignId=? AND status='FUTURA'");
        $parcSt->execute([$campaign['id']]);
        $parcelasFuturas = (int) $parcSt->fetch()['c'];

        $pdo->prepare(
            'INSERT INTO `AuditLog` (id, userId, action, entity, details, createdAt) VALUES (?,?,?,?,?,?)'
        )->execute([
            cuid(), $master['id'], 'LOAD_DEMO', 'SYSTEM',
            sprintf(
                'Base demonstrativa carregada · %d parcelas FUTURA · campos raros (PJ, NF-e, PDF contrato, veículo, cancelada)',
                $parcelasFuturas
            ),
            $now,
        ]);

        $supplierCount = (int) $pdo->query(
            'SELECT COUNT(*) AS c FROM `Supplier` WHERE campaignId=' . $pdo->quote($campaign['id'])
        )->fetch()['c'];

        return [
            'users' => 4,
            'accounts' => count(array_filter($accounts, static fn ($a) => (int) $a['active'] === 1)),
            'revenues' => count($revenues),
            'expenses' => count($expenseIds) + (int) ($csvImport['expenses'] ?? 0),
            'cabos' => count($cabos),
            'vehicles' => count($vehicleIds),
            'parcelas' => $parcelasFuturas,
            'suppliers' => $supplierCount,
            'suppliersSource' => !empty($csvImport['error']) ? 'manual+csv-error' : 'tse-csv+manual',
            'contractsWithPdf' => 2,
        ];
    }

    public static function wizardStatus(): array
    {
        $campaign = Metrics::getCampaign();
        $pdo = Database::pdo();
        $cid = $campaign['id'] ?? null;
        $counts = [
            'campanha' => $campaign ? 1 : 0,
            'contas' => $cid ? (int) $pdo->query("SELECT COUNT(*) AS c FROM `BankAccount` WHERE campaignId = " . $pdo->quote($cid) . " AND active = 1")->fetch()['c'] : 0,
            'vinculos' => $cid ? (int) $pdo->query("SELECT COUNT(*) AS c FROM `AccountMapping` WHERE campaignId = " . $pdo->quote($cid))->fetch()['c'] : 0,
            'fornecedores' => $cid ? (int) $pdo->query("SELECT COUNT(*) AS c FROM `Supplier` WHERE campaignId = " . $pdo->quote($cid))->fetch()['c'] : 0,
            'veiculos' => $cid ? (int) $pdo->query("SELECT COUNT(*) AS c FROM `Vehicle` WHERE campaignId = " . $pdo->quote($cid))->fetch()['c'] : 0,
            'cabos' => $cid ? (int) $pdo->query("SELECT COUNT(*) AS c FROM `CaboEleitoral` WHERE campaignId = " . $pdo->quote($cid))->fetch()['c'] : 0,
            'receitas' => $cid ? (int) $pdo->query("SELECT COUNT(*) AS c FROM `Revenue` WHERE campaignId = " . $pdo->quote($cid))->fetch()['c'] : 0,
            'despesas' => $cid ? (int) $pdo->query("SELECT COUNT(*) AS c FROM `Expense` WHERE campaignId = " . $pdo->quote($cid))->fetch()['c'] : 0,
            'representantes' => 0,
            'inconsistencias' => 0,
        ];
        if ($cid) {
            try {
                $counts['representantes'] = (int) $pdo->query("SELECT COUNT(*) AS c FROM `Representative` WHERE campaignId = " . $pdo->quote($cid) . " AND active = 1")->fetch()['c'];
            } catch (Throwable) {
            }
            $issues = ElectoralRules::checkInconsistencies($campaign);
            $imped = count(array_filter($issues, static fn ($i) => ($i['level'] ?? '') === 'IMPEDITIVA'));
            $counts['inconsistencias'] = $imped === 0 ? 1 : 0;
        }
        return $counts;
    }
}
