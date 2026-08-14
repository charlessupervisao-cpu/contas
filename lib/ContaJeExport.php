<?php
declare(strict_types=1);

/**
 * Pacote de envio Conta+JE / TSE — CSVs com nomenclatura oficial §11
 * + comprovantes PDF para conferência/carga no portal Conta+JE.
 */
final class ContaJeExport
{
    /**
     * Gera ZIP em memória com arquivos prontos para o Conta+JE.
     * @return array{ok:bool,zip:?string,filename:string,error?:string,files?:list<string>}
     */
    public static function buildPackage(?array $campaign = null): array
    {
        $campaign = $campaign ?? Metrics::getCampaign();
        if (!$campaign) {
            return ['ok' => false, 'zip' => null, 'filename' => '', 'error' => 'Campanha não cadastrada.'];
        }
        if (!class_exists('ZipArchive')) {
            return ['ok' => false, 'zip' => null, 'filename' => '', 'error' => 'Extensão ZipArchive indisponível no PHP.'];
        }

        $tmp = tempnam(sys_get_temp_dir(), 'contamaisje_');
        if ($tmp === false) {
            return ['ok' => false, 'zip' => null, 'filename' => '', 'error' => 'Falha ao criar arquivo temporário.'];
        }
        $zipPath = $tmp . '.zip';
        @unlink($tmp);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return ['ok' => false, 'zip' => null, 'filename' => '', 'error' => 'Não foi possível criar o ZIP.'];
        }

        $files = [];
        $add = static function (string $name, string $content) use ($zip, &$files): void {
            $zip->addFromString($name, $content);
            $files[] = $name;
        };

        $add('00-LEIA-ME-ENVIO-CONTAJE.txt', self::readme($campaign));
        $add('01-qualificacao.csv', self::csvFromReport('qualificacao', $campaign));
        $add('02-representantes.csv', self::csvRepresentantes($campaign));
        $add('03-contas-bancarias-campanha.csv', self::csvContas($campaign));
        $add('04-doacoes-recebidas.csv', self::csvFromReport('receitas-financeiras', $campaign));
        $add('05-despesas-efetuadas.csv', self::csvFromReport('despesas-efetuadas', $campaign));
        $add('06-despesas-nao-pagas.csv', self::csvFromReport('despesas-nao-pagas', $campaign));
        $add('07-recibos-eleitorais.csv', self::csvFromReport('recibos', $campaign));
        $add('08-demonstrativo-receitas-despesas.csv', self::csvFromReport('demonstrativo', $campaign));
        $add('09-inconsistencias.csv', self::csvInconsistencias($campaign));
        $add('10-doadores-originarios.csv', self::csvOriginDonors($campaign));
        $add('11-fundo-de-caixa.csv', self::csvCashFund($campaign));
        $add('12-transferencias-contas.csv', self::csvTransfers($campaign));

        $proofCount = self::addProofPdfs($zip, $campaign, $files);

        $add('manifesto.json', json_encode([
            'system' => 'CONTAS',
            'vendor' => APP_VENDOR,
            'build' => APP_BUILD,
            'target' => 'Conta+JE / TSE',
            'portal' => ElectoralRules::CONTA_JE_URL,
            'manual' => ElectoralRules::MANUAL_URL,
            'campaign' => [
                'candidateName' => $campaign['candidateName'] ?? null,
                'candidateCpf' => $campaign['candidateCpf'] ?? null,
                'cnpjCampaign' => $campaign['cnpjCampaign'] ?? null,
                'office' => $campaign['office'] ?? null,
                'party' => $campaign['party'] ?? null,
                'electionYear' => $campaign['electionYear'] ?? ELECTION_YEAR,
            ],
            'generatedAt' => date('c'),
            'files' => $files,
            'proofPdfCount' => $proofCount,
            'note' => 'Pacote de conferência/carga auxiliar. A entrega oficial é feita no Conta+JE.',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $zip->close();
        $bin = file_get_contents($zipPath);
        @unlink($zipPath);
        if ($bin === false) {
            return ['ok' => false, 'zip' => null, 'filename' => '', 'error' => 'Falha ao ler ZIP gerado.'];
        }

        $cnpj = only_digits((string) ($campaign['cnpjCampaign'] ?? '')) ?: 'sem-cnpj';
        $filename = 'ContaJE-pacote-envio-' . $cnpj . '-' . date('Ymd-His') . '.zip';
        return ['ok' => true, 'zip' => $bin, 'filename' => $filename, 'files' => $files];
    }

    private static function readme(array $campaign): string
    {
        $name = (string) ($campaign['candidateName'] ?? '');
        $cnpj = (string) ($campaign['cnpjCampaign'] ?? '');
        $when = date('c');
        $build = APP_BUILD;
        return <<<TXT
PACOTE DE ENVIO — CONTAS → Conta+JE / TSE
=========================================
Prestador: {$name}
CNPJ campanha: {$cnpj}
Gerado em: {$when}
Build CONTAS: {$build}

COMO USAR NO PORTAL Conta+JE
1) Acesse https://contamaisje.tse.jus.br/ e autentique-se (gov.br / e-Título).
2) Selecione/crie a prestação de contas da candidatura.
3) Confira Qualificação, Representantes e Contas Bancárias
   com os CSVs 01–03 deste pacote.
4) Lance/confira Doações Recebidas e Despesas Efetuadas
   com os CSVs 04–06 (doadores originários no CSV 10).
5) Informe Fundo de caixa e Transferências (CSVs 11–12) no Conta+JE §10.
6) Anexe os PDFs da pasta comprovantes/ nos lançamentos
   correspondentes (receitas, despesas, extratos, fundo, transferências).
7) Gere os Relatórios oficiais no Conta+JE (§11) e compare
   com os CSVs 07–08.
8) Execute Verificar Inconsistências no Conta+JE e no CONTAS
   (CSV 09). Corrija impeditivas.
9) Entregue a prestação no Conta+JE (parcial/final conforme TRE-GO).

IMPORTANTE
- Este ZIP NÃO substitui a entrega eletrônica no Conta+JE.
- Serve para conferência e para acelerar o preenchimento/importação
  dos dados já organizados no CONTAS (contas.synetiq.com.br).
- O CONTAS não autentica no portal Conta+JE (credenciais gov.br
  são pessoais). Use o pacote + o portal oficial.
- Base estadual: hub TRE-GO Prestação de Contas Eleições 2026.

Synetiq — https://synetiq.com.br
TXT;
    }

    private static function csvFromReport(string $id, array $campaign): string
    {
        $payload = Reports::build($id, $campaign);
        return Reports::toCsv($payload);
    }

    private static function csvRepresentantes(array $campaign): string
    {
        $pdo = Database::pdo();
        $rows = [];
        try {
            $st = $pdo->prepare('SELECT * FROM `Representative` WHERE campaignId=? ORDER BY role, name');
            $st->execute([(string) $campaign['id']]);
            foreach ($st->fetchAll() ?: [] as $r) {
                $rows[] = [
                    'Funcao' => REPRESENTATIVE_ROLES[$r['role'] ?? ''] ?? (string) ($r['role'] ?? ''),
                    'Nome' => (string) ($r['name'] ?? ''),
                    'CPF' => (string) ($r['cpf'] ?? ''),
                    'Email' => (string) ($r['email'] ?? ''),
                    'Telefone' => (string) ($r['phone'] ?? ''),
                    'OAB_UF' => (string) ($r['oabUf'] ?? ''),
                    'OAB_Numero' => (string) ($r['oabNumber'] ?? ''),
                    'CRC_UF' => (string) ($r['crcUf'] ?? ''),
                    'CRC_Numero' => (string) ($r['crcNumber'] ?? ''),
                    'Ativo' => !empty($r['active']) ? 'SIM' : 'NAO',
                ];
            }
        } catch (Throwable) {
        }
        return Reports::toCsv([
            'columns' => ['Funcao', 'Nome', 'CPF', 'Email', 'Telefone', 'OAB_UF', 'OAB_Numero', 'CRC_UF', 'CRC_Numero', 'Ativo'],
            'rows' => $rows,
        ]);
    }

    private static function csvContas(array $campaign): string
    {
        $rows = [];
        foreach ($campaign['bankAccounts'] ?? [] as $a) {
            $rows[] = [
                'Tipo_Fonte' => ElectoralRules::bankOriginLabel((string) ($a['resourceOrigin'] ?? '')) ?: '',
                'Codigo_Fonte' => (string) ($a['resourceOrigin'] ?? ''),
                'Rotulo' => (string) ($a['label'] ?? ''),
                'Banco' => (string) ($a['bankName'] ?? ''),
                'Codigo_Banco' => (string) ($a['bankCode'] ?? ''),
                'Agencia' => (string) ($a['agency'] ?? ''),
                'DV_Agencia' => (string) ($a['agencyDv'] ?? ''),
                'Conta' => (string) ($a['accountNumber'] ?? ''),
                'DV_Conta' => (string) ($a['accountDv'] ?? ''),
                'Tipo_Conta' => (string) ($a['accountType'] ?? ''),
                'Data_Abertura' => (string) ($a['openedAt'] ?? ''),
                'RAC' => (string) ($a['racNumber'] ?? ''),
                'Extrato_PDF' => !empty($a['statementPdfPath']) ? 'SIM' : 'NAO',
                'Saldo' => number_format((float) ($a['balance'] ?? 0), 2, '.', ''),
                'Ativa' => !empty($a['active']) ? 'SIM' : 'NAO',
            ];
        }
        return Reports::toCsv([
            'columns' => ['Tipo_Fonte', 'Codigo_Fonte', 'Rotulo', 'Banco', 'Codigo_Banco', 'Agencia', 'DV_Agencia', 'Conta', 'DV_Conta', 'Tipo_Conta', 'Data_Abertura', 'RAC', 'Extrato_PDF', 'Saldo', 'Ativa'],
            'rows' => $rows,
        ]);
    }

    private static function csvInconsistencias(array $campaign): string
    {
        $issues = ElectoralRules::checkInconsistencies($campaign);
        $rows = [];
        foreach ($issues as $i) {
            $rows[] = [
                'Codigo' => (string) ($i['code'] ?? ''),
                'Nivel' => (string) ($i['level'] ?? ''),
                'Mensagem' => (string) ($i['message'] ?? ''),
                'Href' => (string) ($i['href'] ?? ''),
            ];
        }
        return Reports::toCsv([
            'columns' => ['Codigo', 'Nivel', 'Mensagem', 'Href'],
            'rows' => $rows,
        ]);
    }

    private static function csvOriginDonors(array $campaign): string
    {
        $pdo = Database::pdo();
        $rows = [];
        try {
            $st = $pdo->prepare(
                'SELECT o.*, r.date AS revenueDate, r.amount AS revenueAmount, r.donorName AS intermediaryName, r.receiptNumber
                 FROM `RevenueOriginDonor` o
                 LEFT JOIN `Revenue` r ON r.id = o.revenueId
                 WHERE o.campaignId=?
                 ORDER BY r.date DESC, o.createdAt DESC'
            );
            $st->execute([(string) $campaign['id']]);
            foreach ($st->fetchAll() ?: [] as $o) {
                $rows[] = [
                    'Data_Doacao' => (string) ($o['revenueDate'] ?? ''),
                    'Recibo' => (string) ($o['receiptNumber'] ?? ''),
                    'Intermediario' => (string) ($o['intermediaryName'] ?? ''),
                    'CPF_Originario' => (string) ($o['cpf'] ?? ''),
                    'Nome_Originario' => (string) ($o['name'] ?? ''),
                    'Especie' => RESOURCE_SPECIES[$o['resourceSpecies'] ?? ''] ?? (string) ($o['resourceSpecies'] ?? ''),
                    'Valor' => number_format((float) ($o['amount'] ?? 0), 2, '.', ''),
                    'Valor_Doacao' => number_format((float) ($o['revenueAmount'] ?? 0), 2, '.', ''),
                ];
            }
        } catch (Throwable) {
        }
        return Reports::toCsv([
            'columns' => ['Data_Doacao', 'Recibo', 'Intermediario', 'CPF_Originario', 'Nome_Originario', 'Especie', 'Valor', 'Valor_Doacao'],
            'rows' => $rows,
        ]);
    }

    private static function csvCashFund(array $campaign): string
    {
        $pdo = Database::pdo();
        $rows = [];
        $labels = [
            'CONSTITUICAO' => 'Constituição',
            'REPOSICAO' => 'Reposição',
            'PRESTACAO' => 'Prestação / baixa',
        ];
        try {
            $st = $pdo->prepare(
                'SELECT f.*, a.label AS accountLabel FROM `CashFund` f
                 LEFT JOIN `BankAccount` a ON a.id = f.bankAccountId
                 WHERE f.campaignId=? ORDER BY f.date ASC'
            );
            $st->execute([(string) $campaign['id']]);
            foreach ($st->fetchAll() ?: [] as $f) {
                $rows[] = [
                    'Data' => (string) ($f['date'] ?? ''),
                    'Tipo' => $labels[$f['kind'] ?? ''] ?? (string) ($f['kind'] ?? ''),
                    'Conta' => (string) ($f['accountLabel'] ?? ''),
                    'Descricao' => (string) ($f['description'] ?? ''),
                    'Comprovante_PDF' => !empty($f['proofPdfPath']) ? 'SIM' : 'NAO',
                    'Valor' => number_format((float) ($f['amount'] ?? 0), 2, '.', ''),
                ];
            }
        } catch (Throwable) {
        }
        return Reports::toCsv([
            'columns' => ['Data', 'Tipo', 'Conta', 'Descricao', 'Comprovante_PDF', 'Valor'],
            'rows' => $rows,
        ]);
    }

    private static function csvTransfers(array $campaign): string
    {
        $pdo = Database::pdo();
        $rows = [];
        try {
            $st = $pdo->prepare(
                'SELECT t.*, fa.label AS fromLabel, ta.label AS toLabel
                 FROM `AccountTransfer` t
                 LEFT JOIN `BankAccount` fa ON fa.id = t.fromAccountId
                 LEFT JOIN `BankAccount` ta ON ta.id = t.toAccountId
                 WHERE t.campaignId=? ORDER BY t.date ASC'
            );
            $st->execute([(string) $campaign['id']]);
            foreach ($st->fetchAll() ?: [] as $t) {
                $rows[] = [
                    'Data' => (string) ($t['date'] ?? ''),
                    'Origem' => (string) ($t['fromLabel'] ?? ''),
                    'Destino' => (string) ($t['toLabel'] ?? ''),
                    'Descricao' => (string) ($t['description'] ?? ''),
                    'Comprovante_PDF' => !empty($t['proofPdfPath']) ? 'SIM' : 'NAO',
                    'Valor' => number_format((float) ($t['amount'] ?? 0), 2, '.', ''),
                ];
            }
        } catch (Throwable) {
        }
        return Reports::toCsv([
            'columns' => ['Data', 'Origem', 'Destino', 'Descricao', 'Comprovante_PDF', 'Valor'],
            'rows' => $rows,
        ]);
    }

    /**
     * Inclui PDFs salvos em /uploads/comprovantes no ZIP.
     * @param list<string> $files
     */
    private static function addProofPdfs(ZipArchive $zip, array $campaign, array &$files): int
    {
        $pdo = Database::pdo();
        $cid = (string) $campaign['id'];
        $paths = [];

        $collect = static function (?string $webPath) use (&$paths): void {
            if (!$webPath) {
                return;
            }
            $abs = DocumentProofUpload::absolutePath($webPath);
            if ($abs) {
                $paths[$webPath] = $abs;
            }
        };

        try {
            $st = $pdo->prepare('SELECT proofPdfPath FROM `Revenue` WHERE campaignId=? AND proofPdfPath IS NOT NULL AND proofPdfPath<>\'\'');
            $st->execute([$cid]);
            foreach ($st->fetchAll(PDO::FETCH_COLUMN) ?: [] as $p) {
                $collect((string) $p);
            }
        } catch (Throwable) {
        }
        try {
            $st = $pdo->prepare('SELECT proofPdfPath FROM `Expense` WHERE campaignId=? AND proofPdfPath IS NOT NULL AND proofPdfPath<>\'\'');
            $st->execute([$cid]);
            foreach ($st->fetchAll(PDO::FETCH_COLUMN) ?: [] as $p) {
                $collect((string) $p);
            }
        } catch (Throwable) {
        }
        try {
            $st = $pdo->prepare('SELECT statementPdfPath FROM `BankAccount` WHERE campaignId=? AND statementPdfPath IS NOT NULL AND statementPdfPath<>\'\'');
            $st->execute([$cid]);
            foreach ($st->fetchAll(PDO::FETCH_COLUMN) ?: [] as $p) {
                $collect((string) $p);
            }
        } catch (Throwable) {
        }
        try {
            $st = $pdo->prepare('SELECT proofPdfPath FROM `CashFund` WHERE campaignId=? AND proofPdfPath IS NOT NULL AND proofPdfPath<>\'\'');
            $st->execute([$cid]);
            foreach ($st->fetchAll(PDO::FETCH_COLUMN) ?: [] as $p) {
                $collect((string) $p);
            }
        } catch (Throwable) {
        }
        try {
            $st = $pdo->prepare('SELECT proofPdfPath FROM `AccountTransfer` WHERE campaignId=? AND proofPdfPath IS NOT NULL AND proofPdfPath<>\'\'');
            $st->execute([$cid]);
            foreach ($st->fetchAll(PDO::FETCH_COLUMN) ?: [] as $p) {
                $collect((string) $p);
            }
        } catch (Throwable) {
        }

        $count = 0;
        foreach ($paths as $web => $abs) {
            $base = basename($abs);
            $entry = 'comprovantes/' . $base;
            if ($zip->addFile($abs, $entry)) {
                $files[] = $entry;
                $count++;
            }
        }
        return $count;
    }
}
