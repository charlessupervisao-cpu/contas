<?php

declare(strict_types=1);

/**
 * Importa prestadores / emitentes do DivulgaCandContas (TSE).
 * Referência: rankingFornecedores + NF-es do candidato.
 *
 * @see https://divulgacandcontas.tse.jus.br/divulga/#/candidato/CENTROOESTE/GO/2040602022/90001648401/2022/GO/nfes
 */
final class TseFornecedores
{
    public const SNAPSHOT_PATH = __DIR__ . '/../data/tse-fornecedores-2022.json';

    public const DEFAULT_CANDIDATE = [
        'sqEleicao' => '2040602022',
        'ano' => '2022',
        'sgUe' => 'GO',
        'cargo' => '7',
        'nrPartido' => '44',
        'nrCandidato' => '44321',
        'idCandidato' => '90001648401',
    ];

    /** @return array{ok:bool,fornecedores:list<array>,nfes:list<array>,meta:array,error?:string} */
    public static function fetchLive(?array $candidate = null): array
    {
        $c = array_merge(self::DEFAULT_CANDIDATE, $candidate ?? []);
        $base = 'https://divulgacandcontas.tse.jus.br/divulga/rest/v1';

        $prestacaoUrl = sprintf(
            '%s/prestador/consulta/%s/%s/%s/%s/%s/%s/%s',
            $base,
            rawurlencode($c['sqEleicao']),
            rawurlencode($c['ano']),
            rawurlencode($c['sgUe']),
            rawurlencode($c['cargo']),
            rawurlencode($c['nrPartido']),
            rawurlencode($c['nrCandidato']),
            rawurlencode($c['idCandidato'])
        );

        $prestacao = self::httpJson($prestacaoUrl);
        if (!$prestacao['ok'] || !is_array($prestacao['data']) || empty($prestacao['data']['idPrestador'])) {
            return [
                'ok' => false,
                'fornecedores' => [],
                'nfes' => [],
                'meta' => $c,
                'error' => $prestacao['error'] ?? 'Prestação de contas não encontrada no TSE.',
            ];
        }

        $p = $prestacao['data'];
        $idPrestador = (string) $p['idPrestador'];
        $idEntrega = (string) ($p['idUltimaEntrega'] ?? '');

        $despesasUrl = sprintf(
            '%s/prestador/consulta/despesas/%s/%s/%s',
            $base,
            rawurlencode($c['sqEleicao']),
            rawurlencode($idPrestador),
            rawurlencode($idEntrega)
        );
        $nfesUrl = sprintf(
            '%s/prestador/consulta/nfes/%s/%s/%s',
            $base,
            rawurlencode($c['sqEleicao']),
            rawurlencode($c['ano']),
            rawurlencode($idPrestador)
        );

        $despesas = self::httpJson($despesasUrl);
        $nfes = self::httpJson($nfesUrl);

        $despList = is_array($despesas['data'] ?? null) ? $despesas['data'] : [];
        $nfeList = is_array($nfes['data'] ?? null) ? $nfes['data'] : [];
        $ranking = is_array($p['rankingFornecedores'] ?? null) ? $p['rankingFornecedores'] : [];

        $fornecedores = self::aggregate($despList, $nfeList, $ranking);

        return [
            'ok' => true,
            'fornecedores' => $fornecedores,
            'nfes' => $nfeList,
            'meta' => [
                'source' => 'live',
                'sqEleicao' => $c['sqEleicao'],
                'ano' => $c['ano'],
                'sgUe' => $c['sgUe'],
                'idCandidato' => $c['idCandidato'],
                'idPrestador' => $idPrestador,
                'idUltimaEntrega' => $idEntrega,
                'candidateName' => 'VIRMONDES CRUVINEL',
                'rankingFornecedores' => $ranking,
            ],
        ];
    }

    /** @return array{ok:bool,fornecedores:list<array>,nfes:list<array>,meta:array,error?:string} */
    public static function loadSnapshot(?string $path = null): array
    {
        $path = $path ?: self::SNAPSHOT_PATH;
        if (!is_file($path)) {
            return ['ok' => false, 'fornecedores' => [], 'nfes' => [], 'meta' => [], 'error' => 'Snapshot TSE não encontrado.'];
        }
        $raw = json_decode((string) file_get_contents($path), true);
        if (!is_array($raw)) {
            return ['ok' => false, 'fornecedores' => [], 'nfes' => [], 'meta' => [], 'error' => 'Snapshot TSE inválido.'];
        }
        $list = $raw['fornecedores'] ?? [];
        if (!$list && !empty($raw['nfes'])) {
            $list = self::aggregate([], $raw['nfes'], []);
        }
        return [
            'ok' => true,
            'fornecedores' => is_array($list) ? $list : [],
            'nfes' => is_array($raw['nfes'] ?? null) ? $raw['nfes'] : [],
            'meta' => [
                'source' => 'snapshot',
                'url' => $raw['url'] ?? null,
                'candidate' => $raw['candidate'] ?? null,
                'sqEleicao' => $raw['sqEleicao'] ?? null,
                'ano' => $raw['ano'] ?? null,
                'idPrestador' => $raw['idPrestador'] ?? null,
            ],
        ];
    }

    /**
     * Upsert de fornecedores na campanha. Retorna mapa documentDigits => supplierId.
     *
     * @param list<array> $fornecedores
     * @return array{imported:int,updated:int,map:array<string,string>}
     */
    public static function importIntoCampaign(string $campaignId, array $fornecedores, ?string $notesPrefix = null): array
    {
        $pdo = Database::pdo();
        $now = now_sql();
        $notesPrefix = $notesPrefix ?? 'Importado do DivulgaCandContas/TSE (prestadores e emitentes de NF-e)';
        $imported = 0;
        $updated = 0;
        $map = [];

        $find = $pdo->prepare(
            'SELECT id FROM `Supplier` WHERE campaignId = ? AND REPLACE(REPLACE(REPLACE(REPLACE(document, ".", ""), "/", ""), "-", ""), " ", "") = ? LIMIT 1'
        );
        $insert = $pdo->prepare(
            'INSERT INTO `Supplier` (
                id, campaignId, name, tradeName, documentType, document, email, phone, contactName,
                zipCode, address, addressNumber, addressComplement, neighborhood, city, state,
                stateRegistration, municipalRegistration, category, notes, active, createdAt, updatedAt
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?)'
        );
        $update = $pdo->prepare(
            'UPDATE `Supplier` SET name=?, documentType=?, document=?, city=?, state=?, category=?, notes=?, active=1, updatedAt=?
             WHERE id=?'
        );

        foreach ($fornecedores as $f) {
            $digits = only_digits((string) ($f['document'] ?? $f['cpfCnpj'] ?? ''));
            $name = trim((string) ($f['name'] ?? $f['nome'] ?? ''));
            if ($digits === '' || $name === '') {
                continue;
            }
            $docType = document_type_from_digits($digits) ?? 'CNPJ';
            $document = format_cpf_cnpj($digits);
            $city = isset($f['city']) && $f['city'] !== '' ? (string) $f['city'] : null;
            $state = isset($f['state']) && $f['state'] !== '' ? strtoupper((string) $f['state']) : null;
            $category = isset($f['category']) && $f['category'] !== '' ? (string) $f['category'] : null;
            $qtd = (int) ($f['expenseCount'] ?? $f['qntd'] ?? $f['qtd'] ?? 0);
            $valor = (float) ($f['expenseAmount'] ?? $f['valor'] ?? 0);
            $nfeQtd = (int) ($f['nfeCount'] ?? 0);
            $notes = $notesPrefix
                . ($qtd ? " · {$qtd} despesa(s)" : '')
                . ($valor > 0 ? ' · R$ ' . number_format($valor, 2, ',', '.') : '')
                . ($nfeQtd ? " · {$nfeQtd} NF-e" : '');

            $find->execute([$campaignId, $digits]);
            $existing = $find->fetch();
            if ($existing) {
                $update->execute([$name, $docType, $document, $city, $state, $category, $notes, $now, $existing['id']]);
                $map[$digits] = (string) $existing['id'];
                $map[$name] = (string) $existing['id'];
                $updated++;
            } else {
                $id = cuid();
                $insert->execute([
                    $id, $campaignId, $name, null, $docType, $document,
                    null, null, null, null, null, null, null, null,
                    $city, $state, null, null, $category, $notes, $now, $now,
                ]);
                $map[$digits] = $id;
                $map[$name] = $id;
                $imported++;
            }
        }

        return ['imported' => $imported, 'updated' => $updated, 'map' => $map];
    }

    /**
     * Tenta API ao vivo; se falhar, usa snapshot embutido.
     *
     * @return array{imported:int,updated:int,map:array<string,string>,source:string,count:int,error?:string,deleted?:int,relinked?:int}
     */
    public static function syncCampaign(string $campaignId, bool $preferLive = true): array
    {
        $bundle = self::loadBundle($preferLive);
        if (!$bundle['ok']) {
            return [
                'imported' => 0,
                'updated' => 0,
                'map' => [],
                'source' => 'none',
                'count' => 0,
                'error' => $bundle['error'] ?? 'Sem dados TSE.',
            ];
        }
        $result = self::importIntoCampaign($campaignId, $bundle['fornecedores']);
        $result['source'] = (string) ($bundle['meta']['source'] ?? 'unknown');
        $result['count'] = count($bundle['fornecedores']);
        return $result;
    }

    /**
     * Apaga todos os fornecedores da campanha e recria o cadastro completo a partir do TSE
     * (despesas + NF-es + ranking), com CPF/CNPJ formatado.
     *
     * @return array{imported:int,updated:int,deleted:int,relinked:int,map:array<string,string>,source:string,count:int,error?:string}
     */
    public static function replaceFromTse(string $campaignId, bool $preferLive = true): array
    {
        $bundle = self::loadBundle($preferLive);
        if (!$bundle['ok']) {
            return [
                'imported' => 0,
                'updated' => 0,
                'deleted' => 0,
                'relinked' => 0,
                'map' => [],
                'source' => 'none',
                'count' => 0,
                'error' => $bundle['error'] ?? 'Sem dados TSE.',
            ];
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            // Desvincula despesas antes de apagar (FK SET NULL também cobriria, mas fica explícito)
            $pdo->prepare('UPDATE `Expense` SET supplierId = NULL WHERE campaignId = ?')->execute([$campaignId]);

            $del = $pdo->prepare('DELETE FROM `Supplier` WHERE campaignId = ?');
            $del->execute([$campaignId]);
            $deleted = $del->rowCount();

            $result = self::importIntoCampaign(
                $campaignId,
                $bundle['fornecedores'],
                'Cadastro completo DivulgaCandContas/TSE — prestadores e emitentes de NF-e (CNPJ/CPF oficiais)'
            );
            $relinked = self::relinkExpenses($campaignId, $result['map']);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'imported' => 0,
                'updated' => 0,
                'deleted' => 0,
                'relinked' => 0,
                'map' => [],
                'source' => 'none',
                'count' => 0,
                'error' => $e->getMessage(),
            ];
        }

        // Atualiza snapshot local com o pacote usado
        self::saveSnapshot($bundle);

        $result['deleted'] = $deleted;
        $result['relinked'] = $relinked;
        $result['source'] = (string) ($bundle['meta']['source'] ?? 'unknown');
        $result['count'] = count($bundle['fornecedores']);
        return $result;
    }

    /** @return array{ok:bool,fornecedores:list<array>,nfes:list<array>,meta:array,error?:string} */
    private static function loadBundle(bool $preferLive): array
    {
        $bundle = $preferLive ? self::fetchLive() : self::loadSnapshot();
        if (!$bundle['ok']) {
            $bundle = self::loadSnapshot();
        }
        return $bundle;
    }

    /**
     * Religa despesas ao fornecedor pelo CPF/CNPJ (supplierDoc) ou nome.
     *
     * @param array<string,string> $map
     */
    public static function relinkExpenses(string $campaignId, array $map): int
    {
        $pdo = Database::pdo();
        $rows = $pdo->prepare('SELECT id, supplierName, supplierDoc FROM `Expense` WHERE campaignId = ?');
        $rows->execute([$campaignId]);
        $upd = $pdo->prepare('UPDATE `Expense` SET supplierId = ?, supplierDoc = COALESCE(NULLIF(supplierDoc, ""), ?), supplierName = ? WHERE id = ?');
        $linked = 0;

        while ($e = $rows->fetch()) {
            $digits = only_digits((string) ($e['supplierDoc'] ?? ''));
            $name = trim((string) ($e['supplierName'] ?? ''));
            $supplierId = null;
            if ($digits !== '' && isset($map[$digits])) {
                $supplierId = $map[$digits];
            } elseif ($name !== '' && isset($map[$name])) {
                $supplierId = $map[$name];
            }
            if (!$supplierId) {
                continue;
            }
            $st = $pdo->prepare('SELECT name, document FROM `Supplier` WHERE id = ? LIMIT 1');
            $st->execute([$supplierId]);
            $s = $st->fetch();
            if (!$s) {
                continue;
            }
            $upd->execute([$supplierId, $s['document'], $s['name'], $e['id']]);
            $linked++;
        }
        return $linked;
    }

    /** @param array{fornecedores:list<array>,nfes:list<array>,meta:array} $bundle */
    public static function saveSnapshot(array $bundle): void
    {
        $dir = dirname(self::SNAPSHOT_PATH);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $payload = [
            'source' => 'DivulgaCandContas TSE',
            'candidate' => $bundle['meta']['candidateName'] ?? $bundle['meta']['candidate'] ?? 'VIRMONDES CRUVINEL',
            'sqEleicao' => $bundle['meta']['sqEleicao'] ?? self::DEFAULT_CANDIDATE['sqEleicao'],
            'ano' => $bundle['meta']['ano'] ?? self::DEFAULT_CANDIDATE['ano'],
            'sgUe' => $bundle['meta']['sgUe'] ?? self::DEFAULT_CANDIDATE['sgUe'],
            'cargo' => self::DEFAULT_CANDIDATE['cargo'],
            'nrPartido' => self::DEFAULT_CANDIDATE['nrPartido'],
            'nrCandidato' => self::DEFAULT_CANDIDATE['nrCandidato'],
            'idCandidato' => $bundle['meta']['idCandidato'] ?? self::DEFAULT_CANDIDATE['idCandidato'],
            'idPrestador' => $bundle['meta']['idPrestador'] ?? null,
            'idUltimaEntrega' => $bundle['meta']['idUltimaEntrega'] ?? null,
            'url' => 'https://divulgacandcontas.tse.jus.br/divulga/#/candidato/CENTROOESTE/GO/2040602022/90001648401/2022/GO/nfes',
            'exportedAt' => date('c'),
            'fornecedores' => $bundle['fornecedores'],
            'nfes' => $bundle['nfes'],
        ];
        @file_put_contents(
            self::SNAPSHOT_PATH,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    /**
     * @param list<array> $despesas
     * @param list<array> $nfes
     * @param list<array> $ranking
     * @return list<array>
     */
    public static function aggregate(array $despesas, array $nfes, array $ranking = []): array
    {
        /** @var array<string, array> $agg */
        $agg = [];

        foreach ($despesas as $d) {
            $digits = only_digits((string) ($d['cpfCnpjFornecedor'] ?? ''));
            if ($digits === '') {
                continue;
            }
            if (!isset($agg[$digits])) {
                $agg[$digits] = [
                    'document' => $digits,
                    'name' => '',
                    'city' => null,
                    'state' => null,
                    'expenseCount' => 0,
                    'expenseAmount' => 0.0,
                    'nfeCount' => 0,
                    'category' => null,
                    '_tipos' => [],
                ];
            }
            $agg[$digits]['name'] = (string) ($d['nomeFornecedor'] ?? $agg[$digits]['name']);
            $agg[$digits]['expenseCount']++;
            $agg[$digits]['expenseAmount'] += (float) ($d['valor'] ?? 0);
            $tipo = trim((string) ($d['tipoDespesa'] ?? ''));
            if ($tipo !== '') {
                $agg[$digits]['_tipos'][$tipo] = true;
            }
        }

        foreach ($nfes as $n) {
            $digits = only_digits((string) ($n['cnpjEmitente'] ?? ''));
            if ($digits === '') {
                continue;
            }
            if (!isset($agg[$digits])) {
                $agg[$digits] = [
                    'document' => $digits,
                    'name' => '',
                    'city' => null,
                    'state' => null,
                    'expenseCount' => 0,
                    'expenseAmount' => 0.0,
                    'nfeCount' => 0,
                    'category' => null,
                    '_tipos' => [],
                ];
            }
            $agg[$digits]['name'] = (string) ($n['nmEmitente'] ?? $agg[$digits]['name']);
            $agg[$digits]['nfeCount']++;
            if (!empty($n['dsUe'])) {
                $agg[$digits]['city'] = (string) $n['dsUe'];
            }
            if (!empty($n['ue'])) {
                $agg[$digits]['state'] = strtoupper((string) $n['ue']);
            }
        }

        foreach ($ranking as $r) {
            $digits = only_digits((string) ($r['cpfCnpj'] ?? ''));
            if ($digits === '' || isset($agg[$digits])) {
                continue;
            }
            $agg[$digits] = [
                'document' => $digits,
                'name' => (string) ($r['nome'] ?? ''),
                'city' => null,
                'state' => null,
                'expenseCount' => (int) ($r['qntd'] ?? $r['qtd'] ?? 0),
                'expenseAmount' => (float) ($r['valor'] ?? 0),
                'nfeCount' => 0,
                'category' => null,
                '_tipos' => [],
            ];
        }

        $out = [];
        foreach ($agg as $row) {
            $tipos = array_keys($row['_tipos'] ?? []);
            unset($row['_tipos']);
            $row['expenseAmount'] = round((float) $row['expenseAmount'], 2);
            $row['category'] = $tipos[0] ?? $row['category'];
            if ($row['name'] !== '') {
                $out[] = $row;
            }
        }

        usort($out, static fn ($a, $b) => ($b['expenseAmount'] <=> $a['expenseAmount']));
        return $out;
    }

    /** @return array{ok:bool,data?:mixed,error?:string} */
    private static function httpJson(string $url): array
    {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\nUser-Agent: CONTAS/1.0 (+https://contas.synetiq.com.br)\r\n",
                'timeout' => 45,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false || trim($body) === '') {
            return ['ok' => false, 'error' => 'Falha ao consultar TSE: ' . $url];
        }
        $data = json_decode($body, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return ['ok' => false, 'error' => 'JSON inválido do TSE.'];
        }
        return ['ok' => true, 'data' => $data];
    }
}
