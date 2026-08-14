<?php
/**
 * Manual de Uso — configurações iniciais e operação básica.
 */
require_once dirname(__DIR__) . '/bootstrap.php';
$user = Auth::requireLogin('manual');
$activeModule = 'manual';
$pageTitle = 'Manual de Uso';

$img = static function (string $file): string {
    return asset('assets/img/manual/' . ltrim($file, '/'));
};

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-manual animate-rise">
  <header class="manual-hero panel">
    <div>
      <div class="manual-kicker">Guia rápido · CONTAS</div>
      <h2 class="display" style="margin:.2rem 0 .45rem">Manual de configurações iniciais</h2>
      <p class="muted" style="margin:0;max-width:48rem;line-height:1.5">
        Siga estes passos na ordem para o sistema funcionar do zero. Em cada etapa: <strong>onde clicar</strong>,
        <strong>o que preencher</strong> e <strong>o que acontece</strong>. Só o perfil <strong>Master</strong> altera cadastros e lançamentos.
      </p>
    </div>
    <div class="manual-hero-meta">
      <div class="stat-chip tone-blue"><div class="l">Prazo eleição</div><div class="n" style="font-size:1rem"><?= e(date_br(CAMPAIGN_END_DATE)) ?></div></div>
      <a class="btn btn-primary" href="<?= e(url_path('admin/wizard.php')) ?>">Começar pela Campanha</a>
    </div>
  </header>

  <nav class="manual-toc panel" aria-label="Índice do manual">
    <strong>Índice</strong>
    <ol>
      <li><a href="#passo-1">Entrar no sistema</a></li>
      <li><a href="#passo-2">Campanha e foto do deputado</a></li>
      <li><a href="#passo-3">Contas bancárias</a></li>
      <li><a href="#passo-4">Vínculos de contas</a></li>
      <li><a href="#passo-5">Fornecedores</a></li>
      <li><a href="#passo-6">Ajuste de saldos</a></li>
      <li><a href="#passo-7">Primeiro lançamento</a></li>
      <li><a href="#passo-8">Receitas e Despesas</a></li>
      <li><a href="#passo-9">Usuários e perfis</a></li>
      <li><a href="#extras">Opcionais · veículos, cabos, limpeza</a></li>
      <li><a href="#mobile">Menu no celular / tablet</a></li>
      <li><a href="#instalar-app">Instalar como aplicativo</a></li>
    </ol>
  </nav>

  <!-- 1 -->
  <section class="manual-step panel" id="passo-1">
    <div class="manual-step-head">
      <span class="manual-num">1</span>
      <div>
        <h3>Entrar no sistema</h3>
        <p class="muted">Acesso inicial com e-mail e senha cadastrados.</p>
      </div>
    </div>
    <figure class="manual-shot">
      <img src="<?= e($img('01-login.jpg')) ?>" alt="Tela de login do CONTAS" width="1360" height="860" loading="lazy">
      <figcaption>Tela de login</figcaption>
    </figure>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Onde clicar / preencher</strong>
        <ul>
          <li>Campo <em>E-mail</em> → digite o e-mail do usuário</li>
          <li>Campo <em>Senha</em> → digite a senha (ícone do olho mostra/oculta)</li>
          <li>Botão <em>Entrar no dashboard</em></li>
          <li>Link <em>Esqueci a senha</em> se precisar redefinir por e-mail</li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>O que acontece</strong>
        <ul>
          <li>O sistema valida o login e abre o <strong>Dashboard</strong></li>
          <li>Se a senha estiver errada, aparece aviso vermelho</li>
          <li>Somente <strong>Master</strong> pode alterar dados financeiros</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- 2 -->
  <section class="manual-step panel" id="passo-2">
    <div class="manual-step-head">
      <span class="manual-num">2</span>
      <div>
        <h3>Campanha e foto do deputado</h3>
        <p class="muted">Obrigatório. Define nome, número, partido, orçamento e a foto da home.</p>
      </div>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/wizard.php')) ?>">Abrir tela</a>
    </div>
    <figure class="manual-shot">
      <img src="<?= e($img('03-campanha.jpg')) ?>" alt="Tela Campanha / foto" width="1360" height="860" loading="lazy">
      <figcaption>Menu lateral → Configurações → <strong>Campanha / foto</strong></figcaption>
    </figure>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Onde clicar / preencher</strong>
        <ul>
          <li>No menu: <em>Configurações</em> → <em>Campanha / foto</em></li>
          <li>Envie a <em>Foto do deputado</em> (retrato 3∶4)</li>
          <li>Preencha nome de urna, nome completo, CNPJ, número, partido</li>
          <li>Informe <em>Orçamento</em> e <em>Limite legal</em></li>
          <li>Clique em <em>Salvar</em></li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>O que acontece</strong>
        <ul>
          <li>Os dados passam a aparecer no Dashboard</li>
          <li>A foto fica no cartão de identidade da campanha</li>
          <li>A contagem <em>Faltam para eleição</em> continua até <?= e(date_br(CAMPAIGN_END_DATE)) ?></li>
        </ul>
      </div>
    </div>
  </section>

  <!-- 3 -->
  <section class="manual-step panel" id="passo-3">
    <div class="manual-step-head">
      <span class="manual-num">3</span>
      <div>
        <h3>Contas bancárias</h3>
        <p class="muted">Obrigatório. Sem conta não dá para lançar receita nem despesa.</p>
      </div>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/contas.php')) ?>">Abrir tela</a>
    </div>
    <figure class="manual-shot">
      <img src="<?= e($img('04-contas.jpg')) ?>" alt="Tela Contas bancárias" width="1360" height="860" loading="lazy">
      <figcaption>Configurações → <strong>Contas bancárias</strong></figcaption>
    </figure>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Onde clicar / preencher</strong>
        <ul>
          <li>Preencha o formulário <em>Nova conta</em> (nome, banco, agência, conta)</li>
          <li>Informe o saldo inicial (se já souber)</li>
          <li>Clique em <em>Salvar</em> / cadastrar</li>
          <li>Repita para cada conta da campanha (principal, fundo, operacional…)</li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>O que acontece</strong>
        <ul>
          <li>A conta aparece na lista e fica disponível nos lançamentos</li>
          <li>O saldo será atualizado a cada receita/despesa</li>
          <li>Você poderá vincular categorias a essas contas no passo seguinte</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- 4 -->
  <section class="manual-step panel" id="passo-4">
    <div class="manual-step-head">
      <span class="manual-num">4</span>
      <div>
        <h3>Vínculos de contas</h3>
        <p class="muted">Obrigatório para o lançamento automático escolher a conta certa.</p>
      </div>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/vinculos.php')) ?>">Abrir tela</a>
    </div>
    <figure class="manual-shot">
      <img src="<?= e($img('05-vinculos.jpg')) ?>" alt="Tela Vínculos de contas" width="1360" height="860" loading="lazy">
      <figcaption>Configurações → <strong>Vínculos de contas</strong></figcaption>
    </figure>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Onde clicar / preencher</strong>
        <ul>
          <li>Para cada <em>fonte de receita</em> (ex.: Fundo partidário), escolha a conta</li>
          <li>Para cada <em>categoria de despesa</em> (ex.: Combustíveis), escolha a conta</li>
          <li>Salve os vínculos</li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>O que acontece</strong>
        <ul>
          <li>No lançamento, se você não escolher conta, o sistema usa o vínculo padrão</li>
          <li>Evita erro de crédito/débito na conta errada</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- 5 -->
  <section class="manual-step panel" id="passo-5">
    <div class="manual-step-head">
      <span class="manual-num">5</span>
      <div>
        <h3>Fornecedores</h3>
        <p class="muted">Obrigatório para despesas (NF-e / prestadores).</p>
      </div>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/fornecedores.php')) ?>">Abrir tela</a>
    </div>
    <figure class="manual-shot">
      <img src="<?= e($img('06-fornecedores.jpg')) ?>" alt="Tela Fornecedores" width="1360" height="860" loading="lazy">
      <figcaption>Menu → <strong>Fornecedores</strong> (bloco operacional)</figcaption>
    </figure>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Onde clicar / preencher</strong>
        <ul>
          <li>Cadastre nome, CPF/CNPJ e dados do prestador</li>
          <li>Ou importe a base / NF-es quando disponível</li>
          <li>Salve o fornecedor</li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>O que acontece</strong>
        <ul>
          <li>O fornecedor aparece no select de <em>Novo lançamento → Despesa</em></li>
          <li>Totais de NF-e do fornecedor são atualizados pelas despesas</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- 6 -->
  <section class="manual-step panel" id="passo-6">
    <div class="manual-step-head">
      <span class="manual-num">6</span>
      <div>
        <h3>Ajuste de saldos</h3>
        <p class="muted">Use se o saldo da conta no sistema precisar bater com o extrato real.</p>
      </div>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/saldos.php')) ?>">Abrir tela</a>
    </div>
    <figure class="manual-shot">
      <img src="<?= e($img('07-saldos.jpg')) ?>" alt="Tela Ajuste de saldos" width="1360" height="860" loading="lazy">
      <figcaption>Configurações → <strong>Ajuste de saldos</strong></figcaption>
    </figure>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Onde clicar / preencher</strong>
        <ul>
          <li>Escolha a conta</li>
          <li>Informe o novo saldo e o motivo</li>
          <li>Confirme o ajuste</li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>O que acontece</strong>
        <ul>
          <li>O saldo da conta é atualizado</li>
          <li>O histórico do ajuste fica registrado</li>
          <li>Útil após limpar lançamentos ou no início da campanha</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- 7 -->
  <section class="manual-step panel" id="passo-7">
    <div class="manual-step-head">
      <span class="manual-num">7</span>
      <div>
        <h3>Primeiro lançamento (receita ou despesa)</h3>
        <p class="muted">Só Master. É o coração do sistema.</p>
      </div>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/lancamento.php')) ?>">Abrir tela</a>
    </div>
    <figure class="manual-shot">
      <img src="<?= e($img('08-lancamento.jpg')) ?>" alt="Tela Novo lançamento" width="1360" height="860" loading="lazy">
      <figcaption>Menu → <strong>Novo lançamento</strong> (atalho L no celular)</figcaption>
    </figure>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Onde clicar / preencher</strong>
        <ul>
          <li>Marque <em>Receita</em> ou <em>Despesa</em></li>
          <li>Preencha valor, data e conta (ou deixe o vínculo padrão)</li>
          <li><strong>Receita:</strong> fonte, doador/CPF se PF, recibo</li>
          <li><strong>Despesa:</strong> categoria, fornecedor, dados da NF-e e chave (44 dígitos)</li>
          <li>Clique em <em>Registrar lançamento</em></li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>O que acontece</strong>
        <ul>
          <li>Receita <strong>soma</strong> no saldo da conta; despesa <strong>subtrai</strong></li>
          <li>Aparece em Receitas ou Despesas e no Dashboard</li>
          <li>Gera linha no extrato (conciliação)</li>
          <li>Atualiza KPIs, fluxo de caixa e diário do dia</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- 8 -->
  <section class="manual-step panel" id="passo-8">
    <div class="manual-step-head">
      <span class="manual-num">8</span>
      <div>
        <h3>Consultar, editar ou excluir Receitas e Despesas</h3>
        <p class="muted">Listas para acompanhamento diário.</p>
      </div>
    </div>
    <div class="manual-shots-2">
      <figure class="manual-shot">
        <img src="<?= e($img('09-receitas.jpg')) ?>" alt="Tela Receitas" width="1360" height="860" loading="lazy">
        <figcaption>Menu → <strong>Receitas</strong></figcaption>
      </figure>
      <figure class="manual-shot">
        <img src="<?= e($img('10-despesas.jpg')) ?>" alt="Tela Despesas" width="1360" height="860" loading="lazy">
        <figcaption>Menu → <strong>Despesas</strong></figcaption>
      </figure>
    </div>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Onde clicar</strong>
        <ul>
          <li><em>Nova receita</em> / <em>Nova despesa</em> → abre o lançamento</li>
          <li><em>Editar</em> → altera valor, data, NF-e etc.</li>
          <li><em>Excluir</em> → remove o lançamento e estorna o saldo</li>
          <li>Em despesas com chave: <em>Ver nota</em> abre o espelho da NF-e</li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>O que acontece</strong>
        <ul>
          <li>Dashboard e saldos são recalculados</li>
          <li>Exclusão também remove o vínculo no extrato</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- 9 -->
  <section class="manual-step panel" id="passo-9">
    <div class="manual-step-head">
      <span class="manual-num">9</span>
      <div>
        <h3>Usuários e perfis</h3>
        <p class="muted">Cadastre a equipe com o perfil certo.</p>
      </div>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/usuarios.php')) ?>">Abrir tela</a>
    </div>
    <figure class="manual-shot">
      <img src="<?= e($img('11-usuarios.jpg')) ?>" alt="Tela Usuários" width="1360" height="860" loading="lazy">
      <figcaption>Configurações → <strong>Usuários / perfis</strong></figcaption>
    </figure>
    <div class="manual-roles">
      <div class="manual-role"><strong>Master</strong><span>Configura tudo e lança receita/despesa</span></div>
      <div class="manual-role"><strong>Financeiro</strong><span>Consulta e opera conciliação/saldos (sem lançar como Master)</span></div>
      <div class="manual-role"><strong>RH</strong><span>Cuida de cabos/contratos</span></div>
      <div class="manual-role"><strong>Consulta</strong><span>Somente leitura</span></div>
    </div>
  </section>

  <!-- extras -->
  <section class="manual-step panel" id="extras">
    <div class="manual-step-head">
      <span class="manual-num">+</span>
      <div>
        <h3>Opcionais · veículos, cabos e limpeza</h3>
        <p class="muted">Não bloqueiam o início, mas completam a prestação.</p>
      </div>
    </div>
    <div class="manual-shots-2">
      <figure class="manual-shot">
        <img src="<?= e($img('12-veiculos.jpg')) ?>" alt="Tela Veículos" width="1360" height="860" loading="lazy">
        <figcaption>Configurações → <strong>Veículos</strong> — frota / locação</figcaption>
      </figure>
      <figure class="manual-shot">
        <img src="<?= e($img('13-cabos.jpg')) ?>" alt="Tela Cabos" width="1360" height="860" loading="lazy">
        <figcaption>Configurações → <strong>Cabos / Contratos</strong></figcaption>
      </figure>
    </div>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Apagar só lançamentos (Master)</strong>
        <ul>
          <li>Vá em <em>Campanha / foto</em> → seção vermelha no final</li>
          <li>Marque a confirmação e digite <code>APAGAR</code></li>
          <li><strong>Remove</strong> receitas, despesas, extrato, ajustes e diário</li>
          <li><strong>Mantém</strong> campanha, foto, contas, vínculos, fornecedores, cabos, veículos e a contagem até a eleição</li>
          <li>Saldos das contas ficam zerados — ajuste em <em>Saldos</em> depois</li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>Dashboard</strong>
        <ul>
          <li>Mostra saúde financeira, fluxo até <?= e(date_br(CAMPAIGN_END_DATE)) ?> e KPIs</li>
          <li>O card amarelo/azul mostra quantos dias faltam para a eleição</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- mobile -->
  <section class="manual-step panel" id="mobile">
    <div class="manual-step-head">
      <span class="manual-num">📱</span>
      <div>
        <h3>Menu no celular e tablet</h3>
        <p class="muted">Atalhos na barra inferior + Configurações na engrenagem.</p>
      </div>
    </div>
    <div class="manual-shots-2">
      <figure class="manual-shot">
        <img src="<?= e($img('15-menu-config.jpg')) ?>" alt="Menu Configurações no desktop" width="1360" height="860" loading="lazy">
        <figcaption>Desktop: acordeão <strong>Configurações</strong> no menu lateral</figcaption>
      </figure>
      <figure class="manual-shot manual-shot-mobile">
        <img src="<?= e($img('14-menu-mobile-config.jpg')) ?>" alt="Menu Config no celular" width="390" height="844" loading="lazy">
        <figcaption>Celular: toque em <strong>Config</strong> (engrenagem) → opções + logo + Sair</figcaption>
      </figure>
    </div>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Barra inferior</strong>
        <ul>
          <li><em>Config</em> abre o painel (máx. 70% da largura)</li>
          <li><em>I</em> Início · <em>R</em> Receitas · <em>D</em> Despesas · <em>L</em> Lançar · <em>↑</em> Topo</li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>O que acontece</strong>
        <ul>
          <li>No painel Config você acessa contas, vínculos, campanha, usuários etc.</li>
          <li><em>Sair</em> encerra a sessão</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="panel" id="instalar-app">
    <h3 class="display" style="margin-top:0">Instalar como aplicativo (PWA)</h3>
    <p class="muted" style="line-height:1.45">
      O CONTAS pode ser instalado na tela inicial do celular ou como app no computador.
      Funciona em tela cheia (sem barra do navegador) e abre mais rápido.
    </p>
    <div class="manual-actions">
      <div class="manual-action">
        <strong>Android / Chrome</strong>
        <ul>
          <li>Abra o site no Chrome</li>
          <li>Toque no banner <em>Instalar app</em>, ou no menu ⋮ → <em>Instalar app</em> / <em>Adicionar à tela inicial</em></li>
        </ul>
      </div>
      <div class="manual-action">
        <strong>iPhone / iPad (Safari)</strong>
        <ul>
          <li>Abra no Safari</li>
          <li>Toque em <em>Compartilhar</em> → <em>Adicionar à Tela de Início</em></li>
        </ul>
      </div>
    </div>
  </section>

  <section class="panel manual-checklist">
    <h3 class="display" style="margin-top:0">Checklist mínimo Conta+JE / TSE 2026</h3>
    <ol class="manual-check">
      <li>Login Master</li>
      <li>Campanha + foto + endereço/contatos (qualificação)</li>
      <li>Contas bancárias com fonte: Doações, Fundo Partidário e/ou FEFC</li>
      <li>Representantes legais (advogado OAB + contabilista CRC)</li>
      <li>Vínculos categoria/fonte → conta</li>
      <li>Fornecedores para despesas com documento fiscal</li>
      <li>Receitas com tipo, espécie e recibo eleitoral quando couber</li>
      <li>Verificar inconsistências (impeditivas zeradas)</li>
    </ol>
    <div class="row-actions" style="margin-top:1rem">
      <a class="btn btn-primary" href="<?= e(url_path('admin/wizard.php')) ?>">Ir para Campanha / foto</a>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/inconsistencias.php')) ?>">Ver inconsistências</a>
      <a class="btn btn-ghost" href="<?= e(ElectoralRules::MANUAL_URL) ?>" target="_blank" rel="noopener">Manual Conta+JE (PDF)</a>
    </div>
  </section>

  <section class="panel">
    <h3 class="display" style="margin-top:0">Base legal 2026</h3>
    <ul class="manual-check" style="list-style:disc">
      <?php foreach (ElectoralRules::LEGAL_BASIS as $item): ?>
        <li><?= e($item) ?></li>
      <?php endforeach; ?>
    </ul>
    <p class="muted" style="line-height:1.45">
      O CONTAS organiza a movimentação interna da campanha alinhada ao Conta+JE.
      A <strong>entrega oficial</strong> à Justiça Eleitoral continua sendo feita no sistema Conta+JE do TSE.
    </p>
  </section>

  <footer class="panel" style="text-align:center">
    <p class="muted" style="margin:0 0 .65rem;font-size:.85rem">
      <?= e(APP_NAME) ?> — <?= e(APP_TAGLINE) ?>. Sistema independente desenvolvido por
      <a href="<?= e(APP_VENDOR_URL) ?>" target="_blank" rel="noopener noreferrer"><strong><?= e(APP_VENDOR) ?></strong></a>.
    </p>
    <a href="<?= e(APP_VENDOR_URL) ?>" target="_blank" rel="noopener noreferrer" title="<?= e(APP_VENDOR . ' — ' . APP_VENDOR_TAGLINE) ?>">
      <img src="<?= e(asset('assets/img/synetiq-wordmark-sm.png')) ?>" alt="<?= e(APP_VENDOR) ?>" width="160" height="52" loading="lazy">
    </a>
  </footer>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
