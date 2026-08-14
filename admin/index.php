<?php
/**
 * Dashboard financeiro — modelo Cobre Fácil:
 * 1) Visão geral (KPIs) → 2) Análises (gráficos) → 3) Detalhes.
 * Ref: https://www.cobrefacil.com.br/blog/dashboard-financeiro
 */
require_once dirname(__DIR__) . '/bootstrap.php';
$user = Auth::requireLogin('dashboard');
$activeModule = 'dashboard';
$pageTitle = 'Dashboard financeiro';
$metrics = null;
$error = null;
try {
    $metrics = Metrics::getDashboardMetrics();
} catch (Throwable $e) {
    $error = $e->getMessage();
}

$cashFlow = [];
$recent = [];
$dues = [
    'date' => date('Y-m-d'),
    'weekEnd' => date('Y-m-d'),
    'vencemHoje' => [],
    'vencemSemana' => [],
    'totalVenceHoje' => 0.0,
    'totalVenceSemana' => 0.0,
];
if ($metrics) {
    // Demonstrativo: de hoje até 04/10 (1º turno) — semanas seg–dom
    $cashFlow = Metrics::getWeeklyCashFlow('today', CAMPAIGN_END_DATE);
    $recent = Metrics::getRecentMovements(12);
    $dues = Metrics::getUpcomingDues((string) ($metrics['campaign']['id'] ?? ''));
}
$maxFlow = 1.0;
foreach ($cashFlow as $cf) {
    $maxFlow = max($maxFlow, (float) $cf['entrada'], (float) $cf['saida']);
}

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<?php if ($error): ?>
  <div class="alert alert-danger">Banco indisponível: <?= e($error) ?>.</div>
<?php elseif (!$metrics): ?>
  <div class="alert alert-warn">Nenhuma campanha encontrada. Cadastre em Configuração → Campanha / foto.</div>
<?php else: ?>
  <?php
    $t = $metrics['totals'];
    $c = $metrics['counts'];
    $vf = $metrics['vehicleFuel'];
    $camp = $metrics['campaign'];
    $saldoBancos = array_sum(array_column($metrics['bankBalances'], 'balance'));
    $ranking = $metrics['rankingDespesas'] ?? [];
    $maxRank = max(1, ...array_map(static fn ($r) => (float) $r['amount'], $ranking ?: [['amount' => 0]]));
    $chartH = 170;
    $chartPad = 30;
    $nBars = max(1, count($cashFlow));
    $slot = 100 / $nBars;

    // Comparativo semana atual × semana anterior (fluxo)
    $semAtual = $cashFlow[count($cashFlow) - 1] ?? ['entrada' => 0, 'saida' => 0, 'saldo' => 0, 'label' => '—'];
    $semAnt = $cashFlow[count($cashFlow) - 2] ?? ['entrada' => 0, 'saida' => 0, 'saldo' => 0, 'label' => '—'];
    $deltaReceita = (float) $semAnt['entrada'] > 0
      ? (((float) $semAtual['entrada'] - (float) $semAnt['entrada']) / (float) $semAnt['entrada'])
      : ((float) $semAtual['entrada'] > 0 ? 1 : 0);
    $deltaDespesa = (float) $semAnt['saida'] > 0
      ? (((float) $semAtual['saida'] - (float) $semAnt['saida']) / (float) $semAnt['saida'])
      : ((float) $semAtual['saida'] > 0 ? 1 : 0);
    $margem = (float) $t['totalReceitas'] > 0 ? ((float) $t['saldo'] / (float) $t['totalReceitas']) : 0;

    // Donut receitas
    $donut = [];
    $angle = -90.0;
    foreach ($metrics['receitasPorFonte'] as $row) {
        $share = (float) ($row['share'] ?? 0);
        $sweep = $share * 360;
        $donut[] = [
            'label' => $row['label'],
            'color' => REVENUE_SOURCE_COLORS[$row['source']] ?? '#0f766e',
            'amount' => (float) $row['amount'],
            'share' => $share,
            'start' => $angle,
            'sweep' => max(0.01, $sweep),
        ];
        $angle += $sweep;
    }
    $photoUrl = !empty($camp['photoUrl']) ? url_path(ltrim((string) $camp['photoUrl'], '/')) : '';

    // Dias até a eleição (04/10/2026) — recalcula todo dia pelo fuso da app
    $electionDay = new DateTimeImmutable(CAMPAIGN_END_DATE);
    $todayDay = new DateTimeImmutable('today');
    $daysToElection = (int) $todayDay->diff($electionDay)->format('%r%a');
  ?>

  <?php if (!can_launch($user['role'])): ?>
    <div class="alert alert-info animate-fade">Modo <strong>somente leitura</strong> — consulta dos dados lançados.</div>
  <?php endif; ?>

  <!-- Identidade compacta -->
  <header class="fin-identity animate-rise">
    <div class="fin-identity-main">
      <div class="candidate-photo-frame compact">
        <?php if ($photoUrl): ?>
          <img src="<?= e($photoUrl) ?>" alt="Foto de <?= e($camp['candidateName']) ?>" width="72" height="96">
        <?php else: ?>
          <span class="photo-placeholder">Sem foto</span>
        <?php endif; ?>
      </div>
      <div>
        <div class="fin-kicker">Dashboard financeiro · saúde da campanha</div>
        <h1 class="fin-title"><?= e($camp['candidateName']) ?> <span><?= e($camp['candidateNumber']) ?></span></h1>
        <p class="fin-sub">
          <?= e($camp['office'] ?: DEFAULT_OFFICE) ?> · <?= e($camp['party']) ?>/<?= e($camp['partyNumber']) ?>
          · <?= e($camp['state'] ?: 'GO') ?> <?= e((string) ($camp['electionYear'] ?? ELECTION_YEAR)) ?>
          <?php if (!empty($camp['cnpjCampaign'])): ?>
            · CNPJ <?= e(preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', only_digits((string) $camp['cnpjCampaign']))) ?>
          <?php endif; ?>
        </p>
      </div>
    </div>
    <div class="fin-identity-side">
      <div class="fin-pill">Orçamento <strong><?= e(money_br($t['orcamento'])) ?></strong></div>
      <div class="fin-pill">Limite legal <strong><?= e(money_br($t['limiteLegal'])) ?></strong></div>
      <div class="fin-pill fin-pill-countdown" title="Eleição em <?= e(date_br(CAMPAIGN_END_DATE)) ?>">
        Faltam para eleição
        <strong>
          <?php if ($daysToElection > 1): ?>
            <?= (int) $daysToElection ?> dias
          <?php elseif ($daysToElection === 1): ?>
            1 dia
          <?php elseif ($daysToElection === 0): ?>
            É hoje
          <?php else: ?>
            Encerrada
          <?php endif; ?>
        </strong>
      </div>
    </div>
  </header>

  <?php
    $issueCount = 0;
    $impedCount = 0;
    try {
        if (class_exists('ElectoralRules')) {
            $dashIssues = ElectoralRules::checkInconsistencies($camp);
            $issueCount = count($dashIssues);
            foreach ($dashIssues as $di) {
                if (($di['level'] ?? '') === 'IMPEDITIVA') {
                    $impedCount++;
                }
            }
        }
    } catch (Throwable) {
        $dashIssues = [];
    }
    $tetoGo = class_exists('ElectoralRules')
        ? ElectoralRules::spendLimitForOffice((string) ($camp['office'] ?? DEFAULT_OFFICE))
        : DEFAULT_LEGAL_SPEND_LIMIT;
  ?>
  <div class="panel animate-rise" style="margin:0 0 1rem;border-left:4px solid var(--accent, #0f766e)">
    <div class="fin-kicker" style="margin:0">Conformidade · Conta+JE / TRE-GO 2026 · build <?= e(APP_BUILD) ?></div>
    <div style="display:flex;flex-wrap:wrap;gap:.75rem 1.25rem;align-items:flex-start;justify-content:space-between;margin-top:.35rem">
      <div style="max-width:36rem;line-height:1.45">
        <strong>Base oficial Goiás</strong> aplicada aos limites, contas e prazos.
        Teto do cargo: <strong><?= e(money_br((float) ($tetoGo ?? DEFAULT_LEGAL_SPEND_LIMIT))) ?></strong>.
        <?php if ($impedCount > 0): ?>
          <span class="badge badge-danger" style="margin-left:.35rem"><?= (int) $impedCount ?> impeditiva(s)</span>
        <?php elseif ($issueCount > 0): ?>
          <span class="badge badge-warn" style="margin-left:.35rem"><?= (int) $issueCount ?> pendência(s)</span>
        <?php else: ?>
          <span class="badge badge-ok" style="margin-left:.35rem">sem impeditivas</span>
        <?php endif; ?>
      </div>
      <div class="row-actions" style="gap:.4rem;flex-wrap:wrap">
        <a class="btn btn-primary" href="<?= e(url_path('admin/entrega.php')) ?>">Entrega Conta+JE / TSE</a>
        <a class="btn btn-secondary" href="<?= e(url_path('admin/relatorios.php')) ?>">Relatórios</a>
        <a class="btn btn-secondary" href="<?= e(url_path('admin/inconsistencias.php')) ?>">Inconsistências</a>
        <a class="btn btn-ghost" href="<?= e(url_path('admin/base-legal.php')) ?>">Base legal TRE-GO</a>
      </div>
    </div>
  </div>

  <!-- 1. VISÃO GERAL — 5 a 8 indicadores -->
  <div class="fin-zone">
    <div class="fin-zone-ribbon"><span>1</span> Visão geral · saúde financeira</div>
    <section class="fin-kpi-grid animate-rise">
      <article class="fin-kpi in">
        <div class="fin-kpi-label">Faturamento / Receitas</div>
        <div class="fin-kpi-value"><?= e(money_br($t['totalReceitas'])) ?></div>
        <div class="fin-kpi-meta">
          <?= (int) $c['receitas'] ?> entradas
          <span class="delta <?= $deltaReceita >= 0 ? 'up' : 'down' ?>">
            <?= $deltaReceita >= 0 ? '▲' : '▼' ?> <?= e(percent_br(abs($deltaReceita))) ?> vs sem. <?= e($semAnt['label']) ?>
          </span>
        </div>
      </article>
      <article class="fin-kpi out">
        <div class="fin-kpi-label">Despesas / Saídas</div>
        <div class="fin-kpi-value"><?= e(money_br($t['totalDespesas'])) ?></div>
        <div class="fin-kpi-meta">
          <?= (int) $c['despesas'] ?> saídas
          <span class="delta <?= $deltaDespesa <= 0 ? 'up' : 'down' ?>">
            <?= $deltaDespesa >= 0 ? '▲' : '▼' ?> <?= e(percent_br(abs($deltaDespesa))) ?> vs sem. <?= e($semAnt['label']) ?>
          </span>
        </div>
      </article>
      <article class="fin-kpi bal">
        <div class="fin-kpi-label">Saldo líquido</div>
        <div class="fin-kpi-value"><?= e(money_br($t['saldo'])) ?></div>
        <div class="fin-kpi-meta">Entradas − saídas · margem <?= e(percent_br($margem)) ?></div>
      </article>
      <article class="fin-kpi cash">
        <div class="fin-kpi-label">Caixa (contas)</div>
        <div class="fin-kpi-value"><?= e(money_br($saldoBancos)) ?></div>
        <div class="fin-kpi-meta"><?= (int) $c['contas'] ?>/<?= (int) MAX_BANK_ACCOUNTS ?> contas ativas</div>
      </article>
      <article class="fin-kpi warn">
        <div class="fin-kpi-label">Orçamento consumido</div>
        <div class="fin-kpi-value"><?= e(percent_br($t['percentualGasto'])) ?></div>
        <div class="progress-track" style="margin-top:.45rem"><div class="progress-fill" style="width:<?= min(100, $t['percentualGasto'] * 100) ?>%"></div></div>
        <div class="fin-kpi-meta">de <?= e(money_br($t['orcamento'])) ?></div>
      </article>
      <article class="fin-kpi <?= $vf['withinLimit'] ? 'warn' : 'out' ?>">
        <div class="fin-kpi-label">Veículos + combustíveis</div>
        <div class="fin-kpi-value"><?= e(money_br($vf['amount'])) ?></div>
        <div class="progress-track" style="margin-top:.45rem"><div class="progress-fill" style="width:<?= min(100, $vf['limit'] > 0 ? ($vf['amount'] / $vf['limit']) * 100 : 0) ?>%;background:<?= $vf['withinLimit'] ? 'linear-gradient(90deg,#f59e0b,#fbbf24)' : 'linear-gradient(90deg,#dc2626,#f87171)' ?>"></div></div>
        <div class="fin-kpi-meta">Teto 20% · <?= e(money_br($vf['limit'])) ?></div>
      </article>
      <a class="fin-kpi <?= (int) ($c['parcelasAVencer'] ?? 0) > 0 ? 'warn' : 'bal' ?>" href="<?= e(url_path('admin/contas-pendentes.php')) ?>" style="text-decoration:none;color:inherit">
        <div class="fin-kpi-label">Contas parceladas e futuras</div>
        <div class="fin-kpi-value"><?= e(money_br((float) ($t['totalParcelasAVencer'] ?? $t['totalDespesasParceladas'] ?? $t['totalDespesasFuturas'] ?? 0))) ?></div>
        <div class="fin-kpi-meta">
          <?php
            $nParc = (int) ($c['parcelasAVencer'] ?? 0);
            echo $nParc === 1
              ? '1 conta pendente de conciliação · baixa manual'
              : ($nParc . ' contas pendentes de conciliação · baixa manual');
          ?>
        </div>
      </a>
      <article class="fin-kpi bal">
        <div class="fin-kpi-label">Saldo da semana <?= e($semAtual['label']) ?></div>
        <div class="fin-kpi-value"><?= e(money_br($semAtual['saldo'])) ?></div>
        <div class="fin-kpi-meta">+<?= e(money_br($semAtual['entrada'])) ?> / −<?= e(money_br($semAtual['saida'])) ?></div>
      </article>
    </section>
  </div>

  <!-- 2. ANÁLISES COMPARATIVAS -->
  <div class="fin-zone">
    <div class="fin-zone-ribbon"><span>2</span> Análises comparativas</div>
    <section class="grid grid-2" style="margin-bottom:1rem">
      <div class="panel dash-panel-3d tone-flow">
        <h2 class="dash-section-title">Fluxo de caixa — evolução semanal</h2>
        <p class="muted" style="margin:-.35rem 0 .75rem;font-size:.82rem">De hoje até <?= e(date_br(CAMPAIGN_END_DATE)) ?> · barras por semana (seg–dom): entradas (verde) × saídas (vermelho).</p>
        <div class="col-chart" role="img" aria-label="Gráfico de barras do fluxo de caixa semanal">
          <svg viewBox="0 0 100 <?= $chartH ?>" preserveAspectRatio="none" class="col-chart-svg">
            <?php foreach ($cashFlow as $i => $cf):
              $hIn = $maxFlow > 0 ? (($cf['entrada'] / $maxFlow) * ($chartH - $chartPad - 8)) : 0;
              $hOut = $maxFlow > 0 ? (($cf['saida'] / $maxFlow) * ($chartH - $chartPad - 8)) : 0;
              $x = $i * $slot + $slot * 0.18;
              $bw = $slot * 0.28;
              $base = $chartH - $chartPad;
            ?>
              <rect class="col-in" x="<?= $x ?>" y="<?= $base - $hIn ?>" width="<?= $bw ?>" height="<?= max(0.4, $hIn) ?>" rx="0.8"></rect>
              <rect class="col-out" x="<?= $x + $bw + 1.2 ?>" y="<?= $base - $hOut ?>" width="<?= $bw ?>" height="<?= max(0.4, $hOut) ?>" rx="0.8"></rect>
            <?php endforeach; ?>
            <line x1="0" y1="<?= $chartH - $chartPad ?>" x2="100" y2="<?= $chartH - $chartPad ?>" class="col-axis"></line>
          </svg>
          <div class="col-chart-labels" style="grid-template-columns:repeat(<?= (int) count($cashFlow) ?>,minmax(0,1fr))">
            <?php foreach ($cashFlow as $cf): ?><span title="<?= e(($cf['weekStart'] ?? '') . ' a ' . ($cf['weekEnd'] ?? '')) ?>"><?= e($cf['label']) ?></span><?php endforeach; ?>
          </div>
        </div>
        <div class="chart-legend">
          <span><i class="lg-in"></i> Entradas</span>
          <span><i class="lg-out"></i> Saídas</span>
        </div>
      </div>

      <div class="panel dash-panel-3d tone-src">
        <h2 class="dash-section-title">Receitas por fonte (proporção)</h2>
        <p class="muted" style="margin:-.35rem 0 .75rem;font-size:.82rem">Distribuição do faturamento — Doador PF, Fundo e Vaquinha (sem doação de pessoa jurídica).</p>
        <div class="donut-wrap">
          <svg class="donut-svg" viewBox="0 0 42 42" aria-hidden="true">
            <?php
              $r = 15.5; $cLen = 2 * M_PI * $r; $offset = 0;
              foreach ($donut as $slice):
                $len = max(0.01, $slice['share'] * $cLen);
            ?>
              <circle cx="21" cy="21" r="<?= $r ?>" fill="none"
                stroke="<?= e($slice['color']) ?>" stroke-width="7"
                stroke-dasharray="<?= $len ?> <?= max(0.01, $cLen - $len) ?>"
                stroke-dashoffset="<?= -$offset ?>"
                transform="rotate(-90 21 21)"></circle>
            <?php $offset += $len; endforeach; ?>
            <circle cx="21" cy="21" r="10.5" fill="#fff"></circle>
            <text x="21" y="20.5" text-anchor="middle" class="donut-center-label">Receitas</text>
            <text x="21" y="25.2" text-anchor="middle" class="donut-center-val"><?= e(percent_br($t['percentualRecebido'], 0)) ?></text>
          </svg>
          <div class="pie-legend" style="flex:1;margin:0">
            <?php foreach ($metrics['receitasPorFonte'] as $row): ?>
              <div class="pie-legend-item">
                <span style="display:flex;align-items:center;gap:.45rem">
                  <span class="pie-dot" style="background:<?= e(REVENUE_SOURCE_COLORS[$row['source']] ?? '#0f766e') ?>"></span>
                  <?= e($row['label']) ?>
                </span>
                <strong><?= e(money_br($row['amount'])) ?> · <?= e(percent_br((float) ($row['share'] ?? 0))) ?></strong>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="panel dash-panel-3d tone-cat" style="margin-bottom:1rem">
      <div class="row-actions" style="justify-content:space-between;margin-bottom:.35rem">
        <div>
          <h2 class="dash-section-title" style="margin:0">Ranking de despesas por categoria</h2>
          <p class="muted" style="margin:.25rem 0 0;font-size:.82rem">Onde o dinheiro está saindo — maior para menor.</p>
        </div>
        <a class="btn btn-secondary" href="<?= e(url_path('admin/despesas.php')) ?>">Ver despesas</a>
      </div>
      <?php if (!$ranking): ?>
        <div class="empty">Nenhuma despesa lançada ainda.</div>
      <?php else: ?>
        <div class="rank-list">
          <?php foreach ($ranking as $row): ?>
            <div class="rank-row">
              <div class="rank-pos">#<?= (int) $row['rank'] ?></div>
              <div class="rank-body">
                <div class="rank-head">
                  <strong><?= e($row['label']) ?></strong>
                  <span class="muted"><?= (int) $row['count'] ?> lanç. · <?= e(percent_br((float) $row['share'])) ?></span>
                  <strong class="rank-val"><?= e(money_br($row['amount'])) ?></strong>
                </div>
                <div class="bar-track rank-bar"><div class="bar-fill" style="width:<?= ($row['amount'] / $maxRank) * 100 ?>%;background:<?= e($row['color']) ?>"></div></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>

  <!-- 3. DETALHES -->
  <div class="fin-zone">
    <div class="fin-zone-ribbon"><span>3</span> Detalhes e atenção</div>

    <section class="grid grid-3" style="margin-bottom:1rem">
      <div class="fin-attention <?= (int) $c['pending'] > 0 ? 'is-alert' : 'is-ok' ?>">
        <div class="l">Pendências de conciliação</div>
        <div class="n"><?= (int) $c['pending'] ?></div>
        <a href="<?= e(url_path('admin/conciliacao.php')) ?>">Abrir conciliação →</a>
      </div>
      <div class="fin-attention <?= (int) $c['nfeMissing'] > 0 ? 'is-alert' : 'is-ok' ?>">
        <div class="l">Despesas sem Nº NF</div>
        <div class="n"><?= (int) $c['nfeMissing'] ?></div>
        <a href="<?= e(url_path('admin/despesas.php')) ?>">Revisar despesas →</a>
      </div>
      <div class="fin-attention <?= $vf['withinLimit'] ? 'is-ok' : 'is-alert' ?>">
        <div class="l">Limite 20% veículos+combustível</div>
        <div class="n"><?= e(percent_br($vf['ratio'])) ?></div>
        <span class="muted" style="font-size:.8rem"><?= $vf['withinLimit'] ? 'Dentro do teto' : 'Acima do teto legal' ?></span>
      </div>
    </section>

    <?php
      $weekLabel = date_br($dues['date']) . ' → ' . date_br($dues['weekEnd']);
    ?>
    <section class="grid grid-2" style="margin-bottom:1rem" id="vencimentos">
      <div class="panel dash-panel-3d">
        <h2 class="dash-section-title">Vence hoje · <?= e(money_br((float) $dues['totalVenceHoje'])) ?></h2>
        <?php if (!$dues['vencemHoje']): ?>
          <div class="empty">Nada a vencer hoje.</div>
        <?php else: ?>
          <ul class="daily-due-list">
            <?php foreach ($dues['vencemHoje'] as $e): ?>
              <li>
                <strong><?= e(money_br((float) $e['amount'])) ?></strong>
                <span><?= e(mb_strimwidth((string) $e['description'], 0, 72, '…')) ?></span>
                <span class="muted"><?= e((string) ($e['accountLabel'] ?: '—')) ?><?php if (!empty($e['installmentCount'])): ?> · parc. <?= (int) $e['installmentNumber'] ?>/<?= (int) $e['installmentCount'] ?><?php endif; ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
      <div class="panel dash-panel-3d">
        <h2 class="dash-section-title">Vence na semana · <?= e($weekLabel) ?></h2>
        <p class="muted" style="margin:-.25rem 0 .65rem;font-size:.82rem">Total <?= e(money_br((float) $dues['totalVenceSemana'])) ?></p>
        <?php if (!$dues['vencemSemana']): ?>
          <div class="empty">Nada a vencer nesta semana.</div>
        <?php else: ?>
          <ul class="daily-due-list">
            <?php foreach ($dues['vencemSemana'] as $e): ?>
              <li>
                <strong><?= e(date_br($e['date'])) ?> · <?= e(money_br((float) $e['amount'])) ?></strong>
                <span><?= e(mb_strimwidth((string) $e['description'], 0, 64, '…')) ?></span>
                <span class="muted"><?= e((string) ($e['accountLabel'] ?: '—')) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </section>

    <section class="grid grid-2" style="margin-bottom:1rem">
      <div class="panel dash-panel-3d tone-bank">
        <div class="row-actions" style="justify-content:space-between">
          <h2 class="dash-section-title" style="margin:0">Saldos das contas</h2>
          <a href="<?= e(url_path('admin/contas.php')) ?>">Contas →</a>
        </div>
        <div class="stack" style="margin-top:.85rem">
          <?php foreach ($metrics['bankBalances'] as $i => $b): ?>
            <div class="bank-3d">
              <div>
                <div class="muted" style="font-size:.72rem">Conta <?= $i + 1 ?></div>
                <strong><?= e($b['label']) ?></strong>
                <div class="muted" style="font-size:.78rem"><?= e($b['bankName']) ?> · <?= e($b['agency']) ?></div>
              </div>
              <div class="bal"><?= e(money_br($b['balance'])) ?></div>
            </div>
          <?php endforeach; ?>
          <?php if (!$metrics['bankBalances']): ?><div class="empty">Nenhuma conta.</div><?php endif; ?>
        </div>
      </div>
      <div class="panel dash-panel-3d">
        <h2 class="dash-section-title">Indicadores operacionais</h2>
        <div class="stat-strip" style="grid-template-columns:repeat(2,minmax(0,1fr));margin:0">
          <div class="stat-chip tone-green"><div class="l">Doadores PF</div><div class="n"><?= (int) $c['doadores'] ?></div></div>
          <div class="stat-chip tone-blue"><div class="l">Cabos</div><div class="n"><?= (int) $c['cabos'] ?></div></div>
          <div class="stat-chip tone-teal"><div class="l">Contratos</div><div class="n"><?= (int) $c['contratosAtivos'] ?></div></div>
          <div class="stat-chip tone-cyan"><div class="l">Com Nº NF</div><div class="n"><?= (int) $c['nfeValid'] ?></div></div>
        </div>
      </div>
    </section>

    <section class="panel dash-table-wrap">
      <div class="row-actions" style="justify-content:space-between;margin-bottom:.75rem">
        <div>
          <h2 class="dash-section-title" style="margin:0">Últimas movimentações</h2>
          <p class="muted" style="margin:.2rem 0 0;font-size:.82rem">Detalhe do que entrou e saiu recentemente.</p>
        </div>
        <a class="btn btn-secondary" href="<?= e(url_path('admin/movimentacoes.php')) ?>">Tela completa</a>
      </div>
      <div class="table-wrap m-list-desktop">
      <table class="data">
        <thead>
          <tr><th>Data</th><th>Tipo</th><th>Origem / Destino</th><th>Detalhe</th><th>Conta</th><th>Valor</th></tr>
        </thead>
        <tbody>
        <?php if (!$recent): ?>
          <tr><td colspan="6" class="empty">Sem movimentações lançadas.</td></tr>
        <?php endif; ?>
        <?php foreach ($recent as $m): ?>
          <?php
            $isIn = $m['kind'] === 'ENTRADA';
            $codeLabel = $isIn
              ? (REVENUE_SOURCES[$m['code']] ?? $m['code'])
              : Categories::label((string) $m['code']);
          ?>
          <tr>
            <td><?= e(date_br($m['movDate'])) ?></td>
            <td><span class="badge <?= $isIn ? 'badge-ok' : 'badge-danger' ?>"><?= $isIn ? 'Entrada' : 'Saída' ?></span></td>
            <td><?= e($m['party'] ?: $codeLabel) ?><div class="muted" style="font-size:.72rem"><?= e($codeLabel) ?></div></td>
            <td><?= e(mb_strimwidth((string) ($m['detail'] ?? ''), 0, 48, '…')) ?></td>
            <td><?= e($m['accountLabel'] ?: '—') ?></td>
            <td class="<?= $isIn ? 'mov-in' : 'mov-out' ?>"><?= $isIn ? '+' : '−' ?><?= e(money_br($m['amount'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>

      <div class="m-cards" aria-label="Últimas movimentações">
        <?php if (!$recent): ?><div class="empty">Sem movimentações lançadas.</div><?php endif; ?>
        <?php foreach ($recent as $m): ?>
          <?php
            $isIn = $m['kind'] === 'ENTRADA';
            $codeLabel = $isIn
              ? (REVENUE_SOURCES[$m['code']] ?? $m['code'])
              : Categories::label((string) $m['code']);
          ?>
          <article class="m-card">
            <header class="m-card-head">
              <div>
                <div class="m-card-kicker">
                  <?= e(date_br($m['movDate'])) ?> ·
                  <span class="badge <?= $isIn ? 'badge-ok' : 'badge-danger' ?>"><?= $isIn ? 'Entrada' : 'Saída' ?></span>
                </div>
                <div class="m-card-title"><?= e($m['party'] ?: $codeLabel) ?></div>
              </div>
              <div class="m-card-amount <?= $isIn ? 'in' : 'out' ?>"><?= $isIn ? '+' : '−' ?><?= e(money_br($m['amount'])) ?></div>
            </header>
            <?php if (!empty($m['detail'])): ?><p class="m-card-desc"><?= e((string) $m['detail']) ?></p><?php endif; ?>
            <dl class="m-card-meta">
              <div><dt>Classificação</dt><dd><?= e($codeLabel) ?></dd></div>
              <div><dt>Conta</dt><dd><?= e($m['accountLabel'] ?: '—') ?></dd></div>
            </dl>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
