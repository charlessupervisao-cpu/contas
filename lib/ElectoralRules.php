<?php
declare(strict_types=1);

/**
 * Regras Conta+JE / legislação eleitoral aplicáveis à campanha 2026.
 * Base: Lei 9.504/1997 · Resolução-TSE 23.607/2019 · Manual Conta+JE (TSE).
 * CONTAS não substitui o Conta+JE oficial — prepara e organiza os dados
 * para prestação à Justiça Eleitoral.
 */
final class ElectoralRules
{
    public const LEGAL_BASIS = [
        'Lei nº 9.504/1997 — Art. 17 a 32 (arrecadação, gastos e prestação de contas)',
        'Resolução-TSE nº 23.607/2019 — arrecadação, aplicação de recursos e prestação de contas',
        'Manual Conta+JE (TSE/STI) — registro, validação e entrega da prestação',
    ];

    public const MANUAL_URL = 'https://contas.synetiq.com.br/sistematse.pdf';

    /** Contas bancárias de campanha — fontes oficiais Conta+JE §7.5 */
    public const BANK_ORIGINS = [
        'DOACOES_CAMPANHA' => 'Doações para Campanha',
        'FUNDO_PARTIDARIO' => 'Fundo Partidário',
        'FEFC' => 'Fundo Especial de Financiamento de Campanha (FEFC)',
    ];

    /** Tipos de doação Conta+JE §8 */
    public const DONATION_TYPES = [
        'RECURSOS_PROPRIOS' => 'Recursos Próprios',
        'RECURSOS_PF' => 'Recursos de Pessoas Físicas',
        'RECURSOS_PARTIDO' => 'Recursos de Partido Político',
        'RECURSOS_OUTROS_CANDIDATOS' => 'Recursos de Outros Candidatos',
        'RONI' => 'Recursos de Origens Não Identificadas (RONI)',
    ];

    /** Espécies de recurso Conta+JE §8.11 */
    public const RESOURCE_SPECIES = [
        'BOLETO' => 'Boleto de Cobrança',
        'CARTAO_CREDITO' => 'Cartão de Crédito',
        'CARTAO_DEBITO' => 'Cartão de Débito',
        'CHEQUE' => 'Cheque',
        'PIX' => 'PIX',
        'TRANSFERENCIA' => 'Transferência Eletrônica',
        'ESPECIE' => 'Em Espécie',
        'ESTIMAVEL' => 'Estimável em Dinheiro',
        'OUTROS' => 'Outros títulos de crédito',
    ];

    /** Formas de pagamento Conta+JE §9.3 */
    public const PAYMENT_METHODS = [
        'BOLETO' => 'Boleto bancário',
        'CARTAO_CREDITO' => 'Cartão de crédito',
        'CARTAO_DEBITO' => 'Cartão de débito',
        'CHEQUE' => 'Cheque',
        'DEBITO_CONTA' => 'Débito em conta',
        'ESPECIE' => 'Espécie',
        'PIX' => 'PIX',
        'TRANSFERENCIA' => 'Transferência eletrônica',
    ];

    /** Funções de representantes Conta+JE §7.3 */
    public const REPRESENTATIVE_ROLES = [
        'ADMIN_FINANCEIRO' => 'Administrador(a) financeiro(a)',
        'ADVOGADO' => 'Advogado(a)',
        'CONTABILISTA' => 'Contabilista',
        'PRESIDENTE' => 'Presidente',
        'TESOUREIRO' => 'Tesoureiro(a)',
        'OUTROS' => 'Outros representantes',
    ];

    /**
     * Naturezas de despesa alinhadas ao Conta+JE §9.1 (semente + UI).
     * Códigos estáveis para vínculos e relatórios.
     */
    public const TSE_EXPENSE_NATURES = [
        'SERVICOS_ADVOCATICIOS' => ['label' => 'Serviços advocatícios', 'color' => '#1D4ED8'],
        'SERVICOS_CONTABEIS' => ['label' => 'Serviços contábeis', 'color' => '#0369A1'],
        'PESSOAL_MILITANCIA' => ['label' => 'Pessoal, militância e mobilização', 'color' => '#16A34A'],
        'COMBUSTIVEIS_TRANSPORTE' => ['label' => 'Combustíveis, transporte e deslocamento', 'color' => '#CA8A04'],
        'PUBLICIDADE_GRAFICA' => ['label' => 'Publicidade e materiais impressos', 'color' => '#0284C7'],
        'INTERNET_IMPULSIONAMENTO' => ['label' => 'Internet e impulsionamento', 'color' => '#4F46E5'],
        'LOCACAO_BENS_VEICULOS' => ['label' => 'Locação/cessão de bens e veículos', 'color' => '#EA580C'],
        'COMICIOS_EVENTOS' => ['label' => 'Comícios, eventos, gerador e carro de som', 'color' => '#DB2777'],
        'AGUA_ENERGIA_CORREIOS' => ['label' => 'Água, energia e correspondências', 'color' => '#0D9488'],
        'ENCARGOS_TAXAS' => ['label' => 'Encargos, taxas, impostos e multas', 'color' => '#BE123C'],
        'PASSAGENS_AEREAS' => ['label' => 'Passagens aéreas', 'color' => '#7C3AED'],
        'AQUISICAO_BENS' => ['label' => 'Aquisição/doação de bens móveis ou imóveis', 'color' => '#0891B2'],
        'DOACAO_OUTRAS_CANDIDATURAS' => ['label' => 'Doações a candidatas, candidatos e partidos', 'color' => '#9333EA'],
        'DESPESAS_DIVERSAS' => ['label' => 'Despesas diversas a especificar', 'color' => '#64748B'],
        // legado operacional mantido
        'COMITE' => ['label' => 'Comitê', 'color' => '#0D9488'],
        'GRAFICA' => ['label' => 'Gráfica', 'color' => '#0284C7'],
        'INTERNET' => ['label' => 'Internet', 'color' => '#4F46E5'],
        'VEICULOS' => ['label' => 'Veículos', 'color' => '#EA580C'],
        'IMPULSIONAMENTO' => ['label' => 'Impulsionamento', 'color' => '#DB2777'],
        'COMBUSTIVEIS' => ['label' => 'Combustíveis', 'color' => '#CA8A04'],
        'CABOS_ELEITORAIS' => ['label' => 'Cabos Eleitorais', 'color' => '#16A34A'],
        'COMUNICACAO' => ['label' => 'Comunicação', 'color' => '#0891B2'],
    ];

    /** Mapeamento legado → fonte Conta+JE */
    public const LEGACY_REVENUE_MAP = [
        'DOADOR_PF' => 'RECURSOS_PF',
        'DOADOR_PJ' => 'RECURSOS_PF', // PJ não permitido em estadual; reclassifica para revisão
        'VAQUINHA_ELEITORAL' => 'FCC',
    ];

    public static function bankOriginLabel(?string $code): string
    {
        $code = (string) $code;
        return self::BANK_ORIGINS[$code] ?? ($code !== '' ? $code : '—');
    }

    public static function donationTypeLabel(?string $code): string
    {
        $code = (string) $code;
        if (isset(REVENUE_SOURCES[$code])) {
            return REVENUE_SOURCES[$code];
        }
        return self::DONATION_TYPES[$code] ?? ($code !== '' ? $code : '—');
    }

    public static function speciesLabel(?string $code): string
    {
        $code = (string) $code;
        return self::RESOURCE_SPECIES[$code] ?? ($code !== '' ? $code : '—');
    }

    public static function normalizeRevenueSource(string $source): string
    {
        return self::LEGACY_REVENUE_MAP[$source] ?? $source;
    }

    /**
     * Regras de depósito por origem da conta (estadual).
     * @return array{cpf:bool,cnpj:bool}
     */
    public static function depositFlagsForOrigin(string $origin): array
    {
        return match ($origin) {
            'FUNDO_PARTIDARIO', 'FEFC' => ['cpf' => false, 'cnpj' => true],
            'DOACOES_CAMPANHA' => ['cpf' => true, 'cnpj' => false],
            default => ['cpf' => true, 'cnpj' => true],
        };
    }

    /**
     * Fonte de receita sugerida a partir da origem da conta bancária.
     */
    public static function revenueSourceFromBankOrigin(?string $origin, string $docType = 'CPF'): string
    {
        return match ((string) $origin) {
            'FUNDO_PARTIDARIO' => 'FUNDO_PARTIDARIO',
            'FEFC' => 'FEFC',
            'DOACOES_CAMPANHA' => $docType === 'CNPJ' ? 'RECURSOS_PARTIDO' : 'RECURSOS_PF',
            default => $docType === 'CNPJ' ? 'RECURSOS_PARTIDO' : 'RECURSOS_PF',
        };
    }

    /**
     * Verificação preventiva de inconsistências (estilo Conta+JE §12.1).
     * @return list<array{code:string,level:string,message:string,href:?string}>
     */
    public static function checkInconsistencies(?array $campaign = null): array
    {
        $issues = [];
        try {
            $pdo = Database::pdo();
        } catch (Throwable) {
            return [[
                'code' => 'DB',
                'level' => 'IMPEDITIVA',
                'message' => 'Banco de dados indisponível para verificação.',
                'href' => null,
            ]];
        }

        if ($campaign === null) {
            $campaign = $pdo->query('SELECT * FROM `Campaign` ORDER BY createdAt ASC LIMIT 1')->fetch() ?: null;
        }
        if (!$campaign) {
            $issues[] = [
                'code' => 'CAMPANHA',
                'level' => 'IMPEDITIVA',
                'message' => 'Campanha não cadastrada. Preencha Campanha / foto.',
                'href' => url_path('admin/wizard.php'),
            ];
            return $issues;
        }
        $cid = (string) $campaign['id'];

        foreach (['candidateName', 'candidateNumber', 'party', 'office', 'state'] as $field) {
            if (trim((string) ($campaign[$field] ?? '')) === '') {
                $issues[] = [
                    'code' => 'QUALIFICACAO',
                    'level' => 'IMPEDITIVA',
                    'message' => "Qualificação incompleta: campo {$field} obrigatório.",
                    'href' => url_path('admin/wizard.php'),
                ];
            }
        }
        if (trim((string) ($campaign['cnpjCampaign'] ?? '')) === '') {
            $issues[] = [
                'code' => 'CNPJ',
                'level' => 'NAO_IMPEDITIVA',
                'message' => 'CNPJ de campanha não informado (recomendado para Conta+JE).',
                'href' => url_path('admin/wizard.php'),
            ];
        }
        if (trim((string) ($campaign['addressStreet'] ?? '')) === '' && self::campaignHasAddressColumns($pdo)) {
            $issues[] = [
                'code' => 'ENDERECO',
                'level' => 'IMPEDITIVA',
                'message' => 'Não foi informado um endereço para a Qualificação do prestador.',
                'href' => url_path('admin/wizard.php'),
            ];
        }

        $st = $pdo->prepare('SELECT COUNT(*) AS c FROM `BankAccount` WHERE campaignId=? AND active=1');
        $st->execute([$cid]);
        $accCount = (int) $st->fetch()['c'];
        if ($accCount === 0) {
            $issues[] = [
                'code' => 'CONTAS',
                'level' => 'IMPEDITIVA',
                'message' => 'Nenhuma conta bancária de campanha ativa. Cadastre Fundo Partidário, Doações e/ou FEFC.',
                'href' => url_path('admin/contas.php'),
            ];
        } else {
            $origins = $pdo->prepare('SELECT DISTINCT resourceOrigin FROM `BankAccount` WHERE campaignId=? AND active=1 AND resourceOrigin IS NOT NULL AND resourceOrigin<>\'\'');
            try {
                $origins->execute([$cid]);
                $have = $origins->fetchAll(PDO::FETCH_COLUMN) ?: [];
                foreach (['DOACOES_CAMPANHA', 'FUNDO_PARTIDARIO', 'FEFC'] as $need) {
                    if (!in_array($need, $have, true)) {
                        $issues[] = [
                            'code' => 'FONTE_' . $need,
                            'level' => 'NAO_IMPEDITIVA',
                            'message' => 'Conta bancária com fonte “' . self::bankOriginLabel($need) . '” ainda não cadastrada.',
                            'href' => url_path('admin/contas.php'),
                        ];
                    }
                }
            } catch (Throwable) {
                // coluna ainda não migrada
            }
        }

        if (self::tableExists($pdo, 'Representative')) {
            $roles = $pdo->prepare('SELECT role FROM `Representative` WHERE campaignId=? AND active=1');
            $roles->execute([$cid]);
            $haveRoles = $roles->fetchAll(PDO::FETCH_COLUMN) ?: [];
            if (!in_array('ADVOGADO', $haveRoles, true)) {
                $issues[] = [
                    'code' => 'ADVOGADO',
                    'level' => 'NAO_IMPEDITIVA',
                    'message' => 'Ausência de advogado(a) cadastrado(a) na representação legal.',
                    'href' => url_path('admin/representantes.php'),
                ];
            }
            if (!in_array('CONTABILISTA', $haveRoles, true)) {
                $issues[] = [
                    'code' => 'CONTABILISTA',
                    'level' => 'NAO_IMPEDITIVA',
                    'message' => 'Ausência de contabilista cadastrado(a).',
                    'href' => url_path('admin/representantes.php'),
                ];
            }
            if (!in_array('ADMIN_FINANCEIRO', $haveRoles, true) && !in_array('TESOUREIRO', $haveRoles, true)) {
                $issues[] = [
                    'code' => 'ADMIN_FIN',
                    'level' => 'NAO_IMPEDITIVA',
                    'message' => 'Recomenda-se cadastrar administrador(a) financeiro(a) ou tesoureiro(a).',
                    'href' => url_path('admin/representantes.php'),
                ];
            }
        }

            $mst = $pdo->prepare('SELECT COUNT(*) AS c FROM `AccountMapping` WHERE campaignId=?');
        $mst->execute([$cid]);
        $mapCount = (int) $mst->fetch()['c'];
        if ($mapCount === 0 && $accCount > 0) {
            $issues[] = [
                'code' => 'VINCULOS',
                'level' => 'NAO_IMPEDITIVA',
                'message' => 'Vínculos de contas (categoria/fonte → conta) ainda não configurados.',
                'href' => url_path('admin/vinculos.php'),
            ];
        }

        $sup = $pdo->prepare('SELECT COUNT(*) AS c FROM `Supplier` WHERE campaignId=?');
        $sup->execute([$cid]);
        if ((int) $sup->fetch()['c'] === 0) {
            $issues[] = [
                'code' => 'FORNECEDOR',
                'level' => 'NAO_IMPEDITIVA',
                'message' => 'Nenhum fornecedor cadastrado — necessário para despesas com documento fiscal.',
                'href' => url_path('admin/fornecedores.php'),
            ];
        }

        return $issues;
    }

    private static function campaignHasAddressColumns(PDO $pdo): bool
    {
        try {
            $st = $pdo->query("SHOW COLUMNS FROM `Campaign` LIKE 'addressStreet'");
            return (bool) $st->fetch();
        } catch (Throwable) {
            return false;
        }
    }

    private static function tableExists(PDO $pdo, string $table): bool
    {
        try {
            $st = $pdo->prepare(
                'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1'
            );
            $st->execute([$table]);
            return (bool) $st->fetch();
        } catch (Throwable) {
            return false;
        }
    }
}
