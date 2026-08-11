<?php
declare(strict_types=1);

const APP_NAME = 'POLLICONTAS';
const ELECTION_YEAR = 2026;
/** Fim da janela do demonstrativo / 1º turno (GO 2026) */
const CAMPAIGN_END_DATE = '2026-10-04';
const APP_DOMAIN = 'pollicontas.synetiq.com.br';
const VEHICLE_FUEL_LIMIT_RATIO = 0.2;
const MAX_BANK_ACCOUNTS = 4;

const ROLES = [
    'MASTER' => 'MASTER',
    'FINANCEIRO' => 'FINANCEIRO',
    'RH' => 'RH',
    'CONSULTA' => 'CONSULTA',
];

const ROLE_LABELS = [
    'MASTER' => 'Master',
    'FINANCEIRO' => 'Financeiro (leitura)',
    'RH' => 'RH (leitura)',
    'CONSULTA' => 'Consulta (leitura)',
];

/** Semente inicial da tabela ExpenseCategory (não usar direto nas telas). */
const DEFAULT_EXPENSE_CATEGORIES = [
    'COMITE' => 'Comitê',
    'GRAFICA' => 'Gráfica',
    'INTERNET' => 'Internet',
    'VEICULOS' => 'Veículos',
    'IMPULSIONAMENTO' => 'Impulsionamento',
    'COMBUSTIVEIS' => 'Combustíveis',
    'CABOS_ELEITORAIS' => 'Cabos Eleitorais',
    'COMUNICACAO' => 'Comunicação',
];

const DEFAULT_EXPENSE_CATEGORY_COLORS = [
    'COMITE' => '#0D9488',
    'GRAFICA' => '#0284C7',
    'INTERNET' => '#4F46E5',
    'VEICULOS' => '#EA580C',
    'IMPULSIONAMENTO' => '#DB2777',
    'COMBUSTIVEIS' => '#CA8A04',
    'CABOS_ELEITORAIS' => '#16A34A',
    'COMUNICACAO' => '#0891B2',
];

/** @deprecated Use Categories::map() — mantido só para compatibilidade. */
const EXPENSE_CATEGORIES = DEFAULT_EXPENSE_CATEGORIES;
/** @deprecated Use Categories::colors() — mantido só para compatibilidade. */
const EXPENSE_CATEGORY_COLORS = DEFAULT_EXPENSE_CATEGORY_COLORS;

/** Semente inicial da tabela Team (mesmo quantitativo das categorias). Nomes em ordem alfabética. */
const DEFAULT_TEAMS = [
    'ANA_PAULA_MENDES' => 'Ana Paula Mendes',
    'BRUNO_CARVALHO' => 'Bruno Carvalho Silva',
    'CARLOS_EDUARDO' => 'Carlos Eduardo Nunes',
    'FERNANDA_LOPES' => 'Fernanda Lopes Vieira',
    'JULIANA_MARTINS' => 'Juliana Martins Rocha',
    'LUCAS_FERREIRA' => 'Lucas Ferreira Costa',
    'PATRICIA_SOUZA' => 'Patricia Souza Almeida',
    'RICARDO_ALVES' => 'Ricardo Alves Pinto',
];

/** Perfis demo dos integrantes (telefone com máscara, cidade e região). */
const DEFAULT_TEAM_PROFILES = [
    'ANA_PAULA_MENDES' => ['phone' => '(62) 98111-1001', 'city' => 'Goiânia', 'cityRegion' => 'Setor Bueno'],
    'BRUNO_CARVALHO' => ['phone' => '(62) 98222-1002', 'city' => 'Aparecida de Goiânia', 'cityRegion' => 'Centro'],
    'CARLOS_EDUARDO' => ['phone' => '(62) 98333-1003', 'city' => 'Anápolis', 'cityRegion' => 'Jundiaí'],
    'FERNANDA_LOPES' => ['phone' => '(64) 98444-1004', 'city' => 'Rio Verde', 'cityRegion' => 'Setor Central'],
    'JULIANA_MARTINS' => ['phone' => '(64) 98555-1005', 'city' => 'Catalão', 'cityRegion' => 'Zona Norte'],
    'LUCAS_FERREIRA' => ['phone' => '(64) 98666-1006', 'city' => 'Itumbiara', 'cityRegion' => 'Centro'],
    'PATRICIA_SOUZA' => ['phone' => '(64) 98777-1007', 'city' => 'Jataí', 'cityRegion' => 'Setor Samuel Graham'],
    'RICARDO_ALVES' => ['phone' => '(61) 98888-1008', 'city' => 'Luziânia', 'cityRegion' => 'Parque Estrela Dalva'],
];

const REVENUE_SOURCES = [
    'DOADOR_PF' => 'Doador Pessoa Física',
    'DOADOR_PJ' => 'Doador Pessoa Jurídica',
    'FUNDO_PARTIDARIO' => 'Fundo Partidário',
    'VAQUINHA_ELEITORAL' => 'Vaquinha Eleitoral',
];

/** Status de despesa (caixa x compromisso orçamentário). */
const EXPENSE_STATUSES = [
    'PAGA' => 'Paga',
    'LANCADA' => 'Lançada',
    'FUTURA' => 'Futura',
    'CANCELADA' => 'Cancelada',
];

/** Tipo de operação da NF-e no lançamento (opcional). */
const NFE_OPERATION_TYPES = [
    'COMPRA' => 'Compra',
    'SERVICO' => 'Serviço',
];

/** Tipo de atividade do fornecedor (cadastro — não é número de NF). */
const SUPPLIER_ACTIVITY_TYPES = [
    'SERVICO' => 'Serviço',
    'VENDA' => 'Venda',
];

/** Tipos de veículo no cadastro. */
const VEHICLE_TYPES = [
    'AUTOMÓVEL' => 'Automóvel',
    'VAN' => 'Van',
    'ÔNIBUS' => 'Ônibus',
    'CAMINHONETE' => 'Caminhonete',
    'MOTOCICLETA' => 'Motocicleta',
    'BICICLETA' => 'Bicicleta',
];

const REVENUE_SOURCE_COLORS = [
    'DOADOR_PF' => '#0D9488',
    'DOADOR_PJ' => '#7C3AED',
    'FUNDO_PARTIDARIO' => '#0284C7',
    'VAQUINHA_ELEITORAL' => '#CA8A04',
];

/**
 * Menu lateral (ordem fixa):
 * primary → ops → config (acordeão Configurações).
 */
const MODULES = [
    // Bloco principal
    ['id' => 'dashboard', 'label' => 'Dashboard', 'href' => '/admin/index.php', 'roles' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'], 'group' => 'primary', 'shortcut' => 'I'],
    ['id' => 'movimentacoes', 'label' => 'Movimentação', 'href' => '/admin/movimentacoes.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary', 'shortcut' => 'M'],
    ['id' => 'receitas', 'label' => 'Receitas', 'href' => '/admin/receitas.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary', 'shortcut' => 'R', 'fkey' => 'F2'],
    ['id' => 'despesas', 'label' => 'Despesas', 'href' => '/admin/despesas.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary', 'shortcut' => 'D', 'fkey' => 'F3'],
    ['id' => 'contas-pendentes', 'label' => 'Contas pendentes', 'href' => '/admin/contas-pendentes.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary'],
    ['id' => 'lancamento', 'label' => 'Novo lançamento', 'href' => '/admin/lancamento.php', 'roles' => ['MASTER'], 'group' => 'primary', 'shortcut' => 'L'],
    // Bloco operacional
    ['id' => 'conciliacao', 'label' => 'Conciliação', 'href' => '/admin/conciliacao.php', 'roles' => ['MASTER', 'FINANCEIRO'], 'group' => 'ops'],
    ['id' => 'fornecedores', 'label' => 'Fornecedores', 'href' => '/admin/fornecedores.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'ops'],
    // Configurações (acordeão) — Manual primeiro para fácil acesso
    ['id' => 'manual', 'label' => 'MANUAL DE USO', 'href' => '/admin/manual.php', 'roles' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'], 'group' => 'config'],
    ['id' => 'contas', 'label' => 'Contas bancárias', 'href' => '/admin/contas.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'config'],
    ['id' => 'categorias', 'label' => 'Categorias de despesa', 'href' => '/admin/categorias.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'config'],
    ['id' => 'equipes', 'label' => 'Equipes', 'href' => '/admin/equipes.php', 'roles' => ['MASTER', 'RH', 'CONSULTA'], 'group' => 'config'],
    ['id' => 'veiculos', 'label' => 'Veículos', 'href' => '/admin/veiculos.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'config'],
    ['id' => 'cabos', 'label' => 'Cabos / Contratos', 'href' => '/admin/cabos.php', 'roles' => ['MASTER', 'RH', 'CONSULTA'], 'group' => 'config'],
    ['id' => 'vinculos', 'label' => 'Vínculos de contas', 'href' => '/admin/vinculos.php', 'roles' => ['MASTER', 'FINANCEIRO'], 'group' => 'config'],
    ['id' => 'saldos', 'label' => 'Ajuste de saldos', 'href' => '/admin/saldos.php', 'roles' => ['MASTER', 'FINANCEIRO'], 'group' => 'config'],
    ['id' => 'wizard', 'label' => 'Campanha / foto', 'href' => '/admin/wizard.php', 'roles' => ['MASTER'], 'group' => 'config'],
    ['id' => 'usuarios', 'label' => 'Usuários / perfis', 'href' => '/admin/usuarios.php', 'roles' => ['MASTER'], 'group' => 'config'],
    ['id' => 'diario', 'label' => 'Diário do dia', 'href' => '/admin/diario.php', 'roles' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'], 'group' => 'config'],
];

const VIEW_ROLES = [
    'dashboard' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'],
    'movimentacoes' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'receitas' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'despesas' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'contas-pendentes' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'cabos' => ['MASTER', 'RH', 'CONSULTA'],
    'equipes' => ['MASTER', 'RH', 'CONSULTA'],
    'contas' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'categorias' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'conciliacao' => ['MASTER', 'FINANCEIRO'],
    'veiculos' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'fornecedores' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'saldos' => ['MASTER', 'FINANCEIRO'],
    'lancamento' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'vinculos' => ['MASTER', 'FINANCEIRO'],
    'diario' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'],
    'usuarios' => ['MASTER'],
    'wizard' => ['MASTER'],
    'manual' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'],
];

/** Cargo padrão da campanha (GO 2026 — reeleição) */
const DEFAULT_OFFICE = 'Deputado Estadual';
const DEFAULT_STATE = 'GO';

/**
 * Referência DivulgaCandContas (prestação 2022 — Virmondes Cruvinel).
 * Usada para sincronizar fornecedores / NF-es.
 */
const TSE_DIVULGA_CANDIDATO = [
    'sqEleicao' => '2040602022',
    'ano' => '2022',
    'sgUe' => 'GO',
    'regiao' => 'CENTROOESTE',
    'cargo' => '7',
    'nrPartido' => '44',
    'nrCandidato' => '44321',
    'idCandidato' => '90001648401',
    'urlNfes' => 'https://divulgacandcontas.tse.jus.br/divulga/#/candidato/CENTROOESTE/GO/2040602022/90001648401/2022/GO/nfes',
];

/** Perfis e permissões de lançamento */
const PROFILE_MATRIX = [
    'MASTER' => [
        'label' => 'Master',
        'description' => 'Acesso total. Único perfil que altera e lança dados.',
        'canLaunch' => true,
    ],
    'FINANCEIRO' => [
        'label' => 'Financeiro',
        'description' => 'Acessa receitas, despesas, contas e conciliação — sem alterar.',
        'canLaunch' => false,
    ],
    'RH' => [
        'label' => 'Recursos Humanos',
        'description' => 'Acessa cabos e contratos — sem cadastrar ou alterar.',
        'canLaunch' => false,
    ],
    'CONSULTA' => [
        'label' => 'Consulta',
        'description' => 'Somente leitura dos painéis e listagens (não altera nada).',
        'canLaunch' => false,
    ],
];

/**
 * Atalhos numéricos (dashboard) e F-keys para cada situação de lançamento/consulta.
 * Teclas 1–9 no dashboard; F2–F8 em qualquer tela do admin.
 */
const QUICK_ACCESS = [
    ['key' => '1', 'label' => 'Novo lançamento', 'href' => '/admin/lancamento.php', 'roles' => ['MASTER'], 'launch' => true, 'hint' => 'Entrar dados'],
    ['key' => '2', 'label' => 'Receitas (R / F2)', 'href' => '/admin/receitas.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'launch' => false, 'hint' => 'Listagem de receitas'],
    ['key' => '3', 'label' => 'Despesas (D / F3)', 'href' => '/admin/despesas.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'launch' => false, 'hint' => 'Listagem de despesas / NF-e'],
    ['key' => '4', 'label' => 'Movimentação', 'href' => '/admin/movimentacoes.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'launch' => false, 'hint' => 'Todas as entradas e saídas'],
    ['key' => '5', 'label' => 'Diário do dia', 'href' => '/admin/diario.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'launch' => false, 'hint' => 'Atividade do dia'],
    ['key' => '6', 'label' => 'Vínculos de contas', 'href' => '/admin/vinculos.php', 'roles' => ['MASTER'], 'launch' => true, 'hint' => 'Categoria → conta'],
    ['key' => '7', 'label' => 'Ajuste de Saldos', 'href' => '/admin/saldos.php', 'roles' => ['MASTER'], 'launch' => true, 'hint' => 'Saldo bancário'],
    ['key' => '8', 'label' => 'Conciliação', 'href' => '/admin/conciliacao.php', 'roles' => ['MASTER', 'FINANCEIRO'], 'launch' => false, 'hint' => 'Até 4 contas'],
    ['key' => '9', 'label' => 'Cabo + Contrato', 'href' => '/admin/cabos/novo.php', 'roles' => ['MASTER'], 'launch' => true, 'hint' => 'Cadastrar cabo'],
];
