<?php
declare(strict_types=1);

const APP_NAME = 'CONTAS';
const APP_TAGLINE = 'Prestação de contas para políticos';
/** Build publicada no deploy — use para confirmar se o cPanel está atualizado */
const APP_BUILD = '2026.08.14-contamaisje-gaps';
const ELECTION_YEAR = 2026;
/** Fim da janela do demonstrativo / 1º turno (GO 2026) */
const CAMPAIGN_END_DATE = '2026-10-04';
/** Teto TRE-GO 2026 — Deputado Estadual (R$) */
const DEFAULT_LEGAL_SPEND_LIMIT = 1270629.01;
/** Teto de contratação de militância/rua — Deputado Estadual (TRE-GO) */
const DEFAULT_PERSONNEL_LIMIT = 450;
const APP_DOMAIN = 'contas.synetiq.com.br';
const APP_VENDOR = 'Synetiq';
const APP_VENDOR_URL = 'https://synetiq.com.br';
const APP_VENDOR_TAGLINE = 'Soluções Digitais Inteligentes';
const VEHICLE_FUEL_LIMIT_RATIO = 0.2;
const MAX_BANK_ACCOUNTS = 4;

/** Fontes oficiais das contas bancárias de campanha (Conta+JE §7.5). */
const BANK_RESOURCE_ORIGINS = [
    'DOACOES_CAMPANHA' => 'Doações para Campanha',
    'FUNDO_PARTIDARIO' => 'Fundo Partidário',
    'FEFC' => 'Fundo Especial de Financiamento de Campanha (FEFC)',
];

/**
 * Fontes / tipos de receita alinhados ao Conta+JE §8 (Lei 9.504/97 · Res.-TSE 23.607/2019).
 * Códigos legados (DOADOR_PF, VAQUINHA_ELEITORAL) são migrados em Schema::ensure.
 */
const REVENUE_SOURCES = [
    'RECURSOS_PROPRIOS' => 'Recursos Próprios',
    'RECURSOS_PF' => 'Recursos de Pessoas Físicas',
    'FUNDO_PARTIDARIO' => 'Fundo Partidário',
    'FEFC' => 'Fundo Especial de Financiamento de Campanha (FEFC)',
    'RECURSOS_PARTIDO' => 'Recursos de Partido Político',
    'RECURSOS_OUTROS_CANDIDATOS' => 'Recursos de Outros Candidatos',
    'FCC' => 'Financiamento Coletivo de Campanha (FCC)',
    'RONI' => 'Recursos de Origens Não Identificadas (RONI)',
];

const RESOURCE_SPECIES = [
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
const PAYMENT_METHODS = [
    'BOLETO' => 'Boleto bancário',
    'CARTAO_CREDITO' => 'Cartão de crédito',
    'CARTAO_DEBITO' => 'Cartão de débito',
    'CHEQUE' => 'Cheque',
    'DEBITO_CONTA' => 'Débito em conta',
    'ESPECIE' => 'Espécie',
    'PIX' => 'PIX',
    'TRANSFERENCIA' => 'Transferência eletrônica',
];

const REPRESENTATIVE_ROLES = [
    'ADMIN_FINANCEIRO' => 'Administrador(a) financeiro(a)',
    'ADVOGADO' => 'Advogado(a)',
    'CONTABILISTA' => 'Contabilista',
    'PRESIDENTE' => 'Presidente',
    'TESOUREIRO' => 'Tesoureiro(a)',
    'OUTROS' => 'Outros representantes',
];

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

/** Semente inicial da tabela ExpenseCategory (Conta+JE §9.1 + categorias operacionais). */
const DEFAULT_EXPENSE_CATEGORIES = [
    'SERVICOS_ADVOCATICIOS' => 'Serviços advocatícios',
    'SERVICOS_CONTABEIS' => 'Serviços contábeis',
    'PESSOAL_MILITANCIA' => 'Pessoal, militância e mobilização',
    'COMBUSTIVEIS_TRANSPORTE' => 'Combustíveis, transporte e deslocamento',
    'PUBLICIDADE_GRAFICA' => 'Publicidade e materiais impressos',
    'INTERNET_IMPULSIONAMENTO' => 'Internet e impulsionamento',
    'LOCACAO_BENS_VEICULOS' => 'Locação/cessão de bens e veículos',
    'COMICIOS_EVENTOS' => 'Comícios, eventos, gerador e carro de som',
    'AGUA_ENERGIA_CORREIOS' => 'Água, energia e correspondências',
    'ENCARGOS_TAXAS' => 'Encargos, taxas, impostos e multas',
    'PASSAGENS_AEREAS' => 'Passagens aéreas',
    'AQUISICAO_BENS' => 'Aquisição/doação de bens móveis ou imóveis',
    'DOACAO_OUTRAS_CANDIDATURAS' => 'Doações a candidatas, candidatos e partidos',
    'DESPESAS_DIVERSAS' => 'Despesas diversas a especificar',
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
    'SERVICOS_ADVOCATICIOS' => '#1D4ED8',
    'SERVICOS_CONTABEIS' => '#0369A1',
    'PESSOAL_MILITANCIA' => '#16A34A',
    'COMBUSTIVEIS_TRANSPORTE' => '#CA8A04',
    'PUBLICIDADE_GRAFICA' => '#0284C7',
    'INTERNET_IMPULSIONAMENTO' => '#4F46E5',
    'LOCACAO_BENS_VEICULOS' => '#EA580C',
    'COMICIOS_EVENTOS' => '#DB2777',
    'AGUA_ENERGIA_CORREIOS' => '#0D9488',
    'ENCARGOS_TAXAS' => '#BE123C',
    'PASSAGENS_AEREAS' => '#7C3AED',
    'AQUISICAO_BENS' => '#0891B2',
    'DOACAO_OUTRAS_CANDIDATURAS' => '#9333EA',
    'DESPESAS_DIVERSAS' => '#64748B',
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
    'RECURSOS_PROPRIOS' => '#0F766E',
    'RECURSOS_PF' => '#0D9488',
    'FUNDO_PARTIDARIO' => '#0284C7',
    'FEFC' => '#1D4ED8',
    'RECURSOS_PARTIDO' => '#7C3AED',
    'RECURSOS_OUTROS_CANDIDATOS' => '#C026D3',
    'FCC' => '#CA8A04',
    'RONI' => '#BE123C',
    'DOADOR_PF' => '#0D9488',
    'DOADOR_PJ' => '#7C3AED',
    'VAQUINHA_ELEITORAL' => '#CA8A04',
];

/**
 * Menu lateral alinhado ao Conta+JE (TSE):
 * primary (operação) → ops → config (sequência numerada de cadastro).
 *
 * Em Configurações, `setupOrder` define a ordem obrigatória de cadastramento.
 */
const MODULES = [
    ['id' => 'dashboard', 'label' => 'Painel da prestação', 'href' => '/admin/index.php', 'roles' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'], 'group' => 'primary', 'shortcut' => 'I'],
    ['id' => 'lancamento', 'label' => 'Lançar receita / despesa', 'href' => '/admin/lancamento.php', 'roles' => ['MASTER'], 'group' => 'primary', 'shortcut' => 'L'],
    ['id' => 'receitas', 'label' => 'Doações recebidas', 'href' => '/admin/receitas.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary', 'shortcut' => 'R', 'fkey' => 'F2'],
    ['id' => 'despesas', 'label' => 'Despesas efetuadas', 'href' => '/admin/despesas.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary', 'shortcut' => 'D', 'fkey' => 'F3'],
    ['id' => 'movimentacoes', 'label' => 'Movimentação financeira', 'href' => '/admin/movimentacoes.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary', 'shortcut' => 'M'],
    ['id' => 'fundo-caixa', 'label' => 'Fundo de caixa', 'href' => '/admin/fundo-caixa.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary'],
    ['id' => 'transferencias', 'label' => 'Transferência entre contas', 'href' => '/admin/transferencias.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary'],
    ['id' => 'contas-pendentes', 'label' => 'Despesas não pagas', 'href' => '/admin/contas-pendentes.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary'],
    ['id' => 'inconsistencias', 'label' => 'Verificar inconsistências', 'href' => '/admin/inconsistencias.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary'],
    ['id' => 'relatorios', 'label' => 'Relatórios e recibos', 'href' => '/admin/relatorios.php', 'roles' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'], 'group' => 'primary'],
    ['id' => 'entrega', 'label' => 'Entrega ao Conta+JE / TSE', 'href' => '/admin/entrega.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'primary'],
    ['id' => 'base-legal', 'label' => 'Base legal TRE-GO 2026', 'href' => '/admin/base-legal.php', 'roles' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'], 'group' => 'primary'],
    ['id' => 'conciliacao', 'label' => 'Conciliação bancária', 'href' => '/admin/conciliacao.php', 'roles' => ['MASTER', 'FINANCEIRO'], 'group' => 'ops'],

    // Configurações — sequência de cadastramento (obrigatória para o sistema funcionar)
    ['id' => 'wizard', 'label' => '1 · Cadastro do candidato', 'href' => '/admin/wizard.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'config', 'setupOrder' => 1],
    ['id' => 'contas', 'label' => '2 · Cadastro das contas', 'href' => '/admin/contas.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'config', 'setupOrder' => 2],
    ['id' => 'representantes', 'label' => '3 · Representantes legais', 'href' => '/admin/representantes.php', 'roles' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'], 'group' => 'config', 'setupOrder' => 3],
    ['id' => 'categorias', 'label' => '4 · Naturezas de despesa', 'href' => '/admin/categorias.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'config', 'setupOrder' => 4],
    ['id' => 'vinculos', 'label' => '5 · Vínculos de contas', 'href' => '/admin/vinculos.php', 'roles' => ['MASTER', 'FINANCEIRO'], 'group' => 'config', 'setupOrder' => 5],
    ['id' => 'fornecedores', 'label' => '6 · Fornecedores', 'href' => '/admin/fornecedores.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'config', 'setupOrder' => 6],
    ['id' => 'equipes', 'label' => '7 · Equipes', 'href' => '/admin/equipes.php', 'roles' => ['MASTER', 'RH', 'CONSULTA'], 'group' => 'config', 'setupOrder' => 7],
    ['id' => 'veiculos', 'label' => '8 · Veículos', 'href' => '/admin/veiculos.php', 'roles' => ['MASTER', 'FINANCEIRO', 'CONSULTA'], 'group' => 'config', 'setupOrder' => 8],
    ['id' => 'cabos', 'label' => '9 · Militância / contratos', 'href' => '/admin/cabos.php', 'roles' => ['MASTER', 'RH', 'CONSULTA'], 'group' => 'config', 'setupOrder' => 9],
    ['id' => 'saldos', 'label' => '10 · Ajuste de saldos', 'href' => '/admin/saldos.php', 'roles' => ['MASTER', 'FINANCEIRO'], 'group' => 'config', 'setupOrder' => 10],
    ['id' => 'usuarios', 'label' => '11 · Administrar acessos', 'href' => '/admin/usuarios.php', 'roles' => ['MASTER'], 'group' => 'config', 'setupOrder' => 11],
    ['id' => 'diario', 'label' => '12 · Diário do dia', 'href' => '/admin/diario.php', 'roles' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'], 'group' => 'config', 'setupOrder' => 12],
    ['id' => 'manual', 'label' => 'Manual de uso', 'href' => '/admin/manual.php', 'roles' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'], 'group' => 'config', 'setupOrder' => 99],
];

const VIEW_ROLES = [
    'dashboard' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'],
    'movimentacoes' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'fundo-caixa' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'transferencias' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'receitas' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'despesas' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'contas-pendentes' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'inconsistencias' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'relatorios' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'],
    'entrega' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'base-legal' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'],
    'cabos' => ['MASTER', 'RH', 'CONSULTA'],
    'equipes' => ['MASTER', 'RH', 'CONSULTA'],
    'contas' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'representantes' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'],
    'categorias' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'conciliacao' => ['MASTER', 'FINANCEIRO'],
    'veiculos' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'fornecedores' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
    'saldos' => ['MASTER', 'FINANCEIRO'],
    'lancamento' => ['MASTER'],
    'vinculos' => ['MASTER', 'FINANCEIRO'],
    'diario' => ['MASTER', 'FINANCEIRO', 'RH', 'CONSULTA'],
    'usuarios' => ['MASTER'],
    'wizard' => ['MASTER', 'FINANCEIRO', 'CONSULTA'],
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
