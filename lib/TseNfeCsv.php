<?php

declare(strict_types=1);

/**
 * Importa o CSV da aba NF-es do DivulgaCandContas.
 * - Cadastro de fornecedor: só identidade do emitente (sem nº de nota).
 * - Dados da NF-e: vão para o lançamento (Expense).
 */
final class TseNfeCsv
{
    public const DEFAULT_CSV = __DIR__ . '/../data/tse-nfes-2022.csv';
    public const IMPORT_SOURCE = 'TSE_CSV';

    /**
     * @return array{deletedSuppliers:int,deletedNfes:int,suppliers:int,nfes:int,expenses:int,error?:string}
     */
    public static function replaceFromCsv(string $campaignId, ?string $csvPath = null): array
    {
        $csvPath = $csvPath ?: self::DEFAULT_CSV;
        if (!is_file($csvPath)) {
            return [
                'deletedSuppliers' => 0,
                'deletedNfes' => 0,
                'suppliers' => 0,
                'nfes' => 0,
                'expenses' => 0,
                'error' => 'Arquivo CSV não encontrado: ' . $csvPath,
            ];
        }

        $parsed = self::parseCsv($csvPath);
        if (!$parsed['ok']) {
            return [
                'deletedSuppliers' => 0,
                'deletedNfes' => 0,
                'suppliers' => 0,
                'nfes' => 0,
                'expenses' => 0,
                'error' => $parsed['error'] ?? 'CSV inválido.',
            ];
        }

        $pdo = Database::pdo();
        $now = now_sql();
        $pdo->beginTransaction();
        try {
            // Remove apenas lançamentos já importados deste CSV (não mexe em saldos bancários)
            $delExp = $pdo->prepare(
                'DELETE FROM `Expense` WHERE campaignId = ? AND importSource = ?'
            );
            $delExp->execute([$campaignId, self::IMPORT_SOURCE]);
            $deletedNfes = $delExp->rowCount();

            if (self::tableExists($pdo, 'SupplierNfe')) {
                $pdo->prepare('DELETE FROM `SupplierNfe` WHERE campaignId = ?')->execute([$campaignId]);
            }

            $supplierMap = self::upsertSuppliers($pdo, $campaignId, $parsed['emitentes'], $now);
            $expenseCount = self::insertExpenses($pdo, $campaignId, $parsed['rows'], $supplierMap, $now);

            // Totais no cadastro (agregado dos lançamentos, sem guardar nº de NF no fornecedor)
            foreach ($supplierMap as $supplierId) {
                refresh_supplier_nfe_totals($pdo, $supplierId, $campaignId, $now);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'deletedSuppliers' => 0,
                'deletedNfes' => 0,
                'suppliers' => 0,
                'nfes' => 0,
                'expenses' => 0,
                'error' => $e->getMessage(),
            ];
        }

        return [
            'deletedSuppliers' => 0,
            'deletedNfes' => $deletedNfes,
            'suppliers' => count($supplierMap),
            'nfes' => $expenseCount,
            'expenses' => $expenseCount,
        ];
    }

    /**
     * @return array{ok:bool,rows?:list<array>,emitentes?:array<string,array>,error?:string}
     */
    public static function parseCsv(string $path): array
    {
        $fh = fopen($path, 'rb');
        if (!$fh) {
            return ['ok' => false, 'error' => 'Não foi possível abrir o CSV.'];
        }

        $header = fgetcsv($fh);
        if (!$header) {
            fclose($fh);
            return ['ok' => false, 'error' => 'CSV sem cabeçalho.'];
        }
        $header = array_map(static fn ($h) => trim((string) $h), $header);

        $map = [
            'cnpjEmitente' => ['CNPJ Emitente', 'cnpjEmitente'],
            'nmEmitente' => ['Nome do Emitente', 'nmEmitente'],
            'naturezaOp' => ['Natureza Op.', 'Natureza Op', 'naturezaOp'],
            'modelo' => ['Modelo', 'modelo'],
            'dataEmissao' => ['Data Emissão', 'Data Emissao', 'dataEmissao'],
            'numeroNf' => ['Nº NF', 'No NF', 'N° NF', 'numeroNf'],
            'numeroSerie' => ['Nº Série', 'Nº Serie', 'No Série', 'numeroSerie'],
            'valor' => ['Valor', 'valor'],
            'ue' => ['UE', 'ue'],
            'unidadeArrecadadora' => ['Unidade Arrecadadora', 'unidadeArrecadadora'],
            'dsUe' => ['Ue', 'dsUe'],
            'chaveAcesso' => ['Chave', 'chaveAcesso', 'chave'],
            'link' => ['Link', 'link'],
        ];
        $idx = [];
        foreach ($map as $key => $aliases) {
            foreach ($aliases as $alias) {
                $pos = array_search($alias, $header, true);
                if ($pos !== false) {
                    $idx[$key] = $pos;
                    break;
                }
            }
        }
        if (!isset($idx['cnpjEmitente'], $idx['nmEmitente'])) {
            fclose($fh);
            return ['ok' => false, 'error' => 'CSV sem colunas CNPJ Emitente / Nome do Emitente.'];
        }

        $rows = [];
        $emitentes = [];
        while (($cols = fgetcsv($fh)) !== false) {
            if (count($cols) === 1 && trim((string) $cols[0]) === '') {
                continue;
            }
            $get = static function (string $k) use ($cols, $idx): string {
                if (!isset($idx[$k])) {
                    return '';
                }
                return trim((string) ($cols[$idx[$k]] ?? ''));
            };

            $digits = only_digits($get('cnpjEmitente'));
            $name = $get('nmEmitente');
            if ($digits === '' || $name === '') {
                continue;
            }

            $valorRaw = str_replace(',', '.', $get('valor'));
            $valor = is_numeric($valorRaw) ? (float) $valorRaw : 0.0;
            $ue = strtoupper($get('ue'));
            $dsUe = $get('dsUe');
            $natureza = strtoupper($get('naturezaOp'));

            $row = [
                'cnpjEmitente' => $digits,
                'nmEmitente' => $name,
                'naturezaOp' => $natureza !== '' ? $natureza : null,
                'modelo' => ($m = $get('modelo')) !== '' ? $m : null,
                'dataEmissao' => self::parseBrDate($get('dataEmissao')),
                'numeroNf' => ($n = $get('numeroNf')) !== '' ? $n : null,
                'numeroSerie' => ($s = trim($get('numeroSerie'))) !== '' ? $s : null,
                'valor' => $valor,
                'ue' => $ue !== '' ? $ue : null,
                'unidadeArrecadadora' => ($u = $get('unidadeArrecadadora')) !== '' ? $u : null,
                'dsUe' => $dsUe !== '' ? $dsUe : null,
                'chaveAcesso' => ($c = $get('chaveAcesso')) !== '' ? $c : null,
                'link' => ($l = $get('link')) !== '' ? $l : null,
            ];
            $rows[] = $row;

            $ek = 'd:' . $digits;
            $activity = null;
            if ($natureza === 'SERV' || $natureza === 'SERVICO' || $natureza === 'SERVIÇO') {
                $activity = 'SERVICO';
            } elseif ($natureza === 'VEND' || $natureza === 'VENDA') {
                $activity = 'VENDA';
            }
            if (!isset($emitentes[$ek])) {
                $emitentes[$ek] = [
                    'document' => $digits,
                    'name' => $name,
                    'city' => ($dsUe !== '' && $ue !== 'BR') ? $dsUe : null,
                    'state' => ($ue !== '' && $ue !== 'BR') ? $ue : null,
                    'activityType' => $activity,
                    'qtd' => 0,
                    'valor' => 0.0,
                ];
            }
            $emitentes[$ek]['qtd']++;
            $emitentes[$ek]['valor'] += $valor;
            if ($ue !== '' && $ue !== 'BR') {
                $emitentes[$ek]['state'] = $ue;
                if ($dsUe !== '') {
                    $emitentes[$ek]['city'] = $dsUe;
                }
            }
            if ($activity && empty($emitentes[$ek]['activityType'])) {
                $emitentes[$ek]['activityType'] = $activity;
            }
            $emitentes[$ek]['name'] = $name;
        }
        fclose($fh);

        if (!$rows) {
            return ['ok' => false, 'error' => 'CSV sem linhas de NF-e.'];
        }

        return ['ok' => true, 'rows' => $rows, 'emitentes' => $emitentes];
    }

    /** @param array<string,array> $emitentes @return array<string,string> */
    private static function upsertSuppliers(PDO $pdo, string $campaignId, array $emitentes, string $now): array
    {
        $find = $pdo->prepare(
            'SELECT id FROM `Supplier` WHERE campaignId = ? AND REPLACE(REPLACE(REPLACE(REPLACE(document,".",""),"/",""),"-","")," ","") = ? LIMIT 1'
        );
        $ins = $pdo->prepare(
            'INSERT INTO `Supplier` (
                id, campaignId, name, tradeName, documentType, document, email, phone, contactName,
                zipCode, address, addressNumber, addressComplement, neighborhood, city, state,
                stateRegistration, municipalRegistration, category, activityType, birthDate,
                quantidadeNfes, valorTotalNfes, notes, active, createdAt, updatedAt
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?)'
        );
        $upd = $pdo->prepare(
            'UPDATE `Supplier` SET name=?, city=COALESCE(?, city), state=COALESCE(?, state),
             activityType=COALESCE(?, activityType), quantidadeNfes=?, valorTotalNfes=?, updatedAt=?
             WHERE id=?'
        );

        $map = [];
        foreach ($emitentes as $e) {
            $digits = (string) $e['document'];
            $find->execute([$campaignId, $digits]);
            $existing = $find->fetch();
            $docType = document_type_from_digits($digits) ?? 'CNPJ';
            $document = format_cpf_cnpj($digits);

            if ($existing) {
                $id = (string) $existing['id'];
                $upd->execute([
                    $e['name'], $e['city'], $e['state'], $e['activityType'] ?? null,
                    (int) $e['qtd'], (float) $e['valor'], $now, $id,
                ]);
            } else {
                $id = cuid();
                $ins->execute([
                    $id, $campaignId, $e['name'], null, $docType, $document,
                    null, null, null, null, null, null, null, null,
                    $e['city'], $e['state'], null, null, null,
                    $e['activityType'] ?? null, null,
                    (int) $e['qtd'], (float) $e['valor'],
                    'Cadastro do emitente (importação TSE — NF-e no lançamento).',
                    $now, $now,
                ]);
            }
            $map['d:' . $digits] = $id;
        }
        return $map;
    }

    /**
     * @param list<array> $rows
     * @param array<string,string> $supplierMap
     */
    private static function insertExpenses(PDO $pdo, string $campaignId, array $rows, array $supplierMap, string $now): int
    {
        $ins = $pdo->prepare(
            'INSERT INTO `Expense` (
                id, campaignId, category, supplierName, supplierDoc, supplierId, description, amount, date, status,
                naturezaOp, dataEmissao, numeroNf,
                unidadeArrecadadora, dsUe, nfeLink, importSource, bankAccountId, createdById, createdAt, updatedAt
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $count = 0;
        foreach ($rows as $r) {
            $digits = (string) $r['cnpjEmitente'];
            $supplierId = $supplierMap['d:' . $digits] ?? null;

            $dataEm = $r['dataEmissao'];
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
                'NF ' . ($r['numeroNf'] ?? '') .
                ($natureza !== '' ? " · {$natureza}" : '') .
                ' · DivulgaCandContas'
            );

            $ins->execute([
                cuid(),
                $campaignId,
                'COMITE',
                $r['nmEmitente'],
                format_cpf_cnpj($digits),
                $supplierId,
                $desc !== '' ? $desc : 'Despesa importada (TSE)',
                (float) $r['valor'],
                $date,
                'LANCADA',
                $natureza !== '' ? $natureza : null,
                $dataEm,
                $r['numeroNf'],
                $r['unidadeArrecadadora'],
                $r['dsUe'],
                $r['link'],
                self::IMPORT_SOURCE,
                null,
                null,
                $now,
                $now,
            ]);
            $count++;
        }
        return $count;
    }

    private static function parseBrDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : null;
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
}
