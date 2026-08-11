<?php
/**
 * Tela de movimentações — entradas e saídas com vários campos e filtros.
 * Complementa o dashboard (home) com o detalhamento operacional.
 */
require_once dirname(__DIR__) . '/bootstrap.php';
$user = Auth::requireLogin('movimentacoes');
$activeModule = 'movimentacoes';
$pageTitle = 'Movimentações';

$tipo = strtoupper((string) get('tipo', 'TODOS')); // TODOS|ENTRADA|SAIDA
$q = trim((string) get('q', ''));
$de = (string) get('de', '');
$ate = (string) get('ate', '');

$pdo = Database::pdo();
$campaign = Metrics::getCampaign();
$cid = $campaign['id'] ?? '';

$params = [];
$whereRev = ['r.campaignId = ?'];
$whereExp = ["e.campaignId = ?", "e.status <> 'CANCELADA'"];
$paramsRev = [$cid];
$paramsExp = [$cid];

if ($de !== '') {
    $whereRev[] = 'r.date >= ?';
    $whereExp[] = 'e.date >= ?';
    $paramsRev[] = $de . ' 00:00:00';
    $paramsExp[] = $de . ' 00:00:00';
}
if ($ate !== '') {
    $whereRev[] = 'r.date <= ?';
    $whereExp[] = 'e.date <= ?';
    $paramsRev[] = $ate . ' 23:59:59';
    $paramsExp[] = $ate . ' 23:59:59';
}
if ($q !== '') {
    $like = '%' . $q . '%';
    $whereRev[] = '(r.donorName LIKE ? OR r.description LIKE ? OR r.source LIKE ? OR r.donorCpf LIKE ?)';
    $whereExp[] = '(e.supplierName LIKE ? OR e.description LIKE ? OR e.category LIKE ? OR e.numeroNf LIKE ?)';
    array_push($paramsRev, $like, $like, $like, $like);
    array_push($paramsExp, $like, $like, $like, $like);
}

$sqlParts = [];
$params = [];
if ($tipo === 'TODOS' || $tipo === 'ENTRADA') {
    $sqlParts[] = "SELECT r.id, r.date AS movDate, r.createdAt, 'ENTRADA' AS kind, r.source AS code,
        r.description AS detail, r.donorName AS party, r.donorCpf AS extra, r.amount, a.label AS accountLabel,
        NULL AS numeroNf
      FROM `Revenue` r
      LEFT JOIN `BankAccount` a ON a.id = r.bankAccountId
      WHERE " . implode(' AND ', $whereRev);
    $params = array_merge($params, $paramsRev);
}
if ($tipo === 'TODOS' || $tipo === 'SAIDA') {
    if ($sqlParts) {
        // params already have rev; will append exp
    }
    $sqlParts[] = "SELECT e.id, e.date AS movDate, e.createdAt, 'SAIDA' AS kind, e.category AS code,
        e.description AS detail, e.supplierName AS party, e.supplierDoc AS extra, e.amount, a.label AS accountLabel,
        e.numeroNf AS numeroNf
      FROM `Expense` e
      LEFT JOIN `BankAccount` a ON a.id = e.bankAccountId
      WHERE " . implode(' AND ', $whereExp);
    $params = array_merge($params, $paramsExp);
}

$sql = '(' . implode(') UNION ALL (', $sqlParts) . ') ORDER BY movDate DESC, createdAt DESC LIMIT 500';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$totalIn = 0.0;
$totalOut = 0.0;
foreach ($rows as $r) {
    if ($r['kind'] === 'ENTRADA') {
        $totalIn += (float) $r['amount'];
    } else {
        $totalOut += (float) $r['amount'];
    }
}

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="candidate-banner animate-rise">
  <div class="muted" style="font-size:.75rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase">Tela de movimentações</div>
  <div class="display" style="margin:.2rem 0">Entradas e saídas da campanha</div>
  <div class="muted">Filtre por período, tipo ou texto · Atalhos: Receitas (F2) · Despesas (F3)</div>
</div>

<div class="grid grid-4" style="margin-bottom:1rem">
  <div class="dash-kpi in"><div class="label">Entradas (filtro)</div><div class="value"><?= e(money_br($totalIn)) ?></div></div>
  <div class="dash-kpi out"><div class="label">Saídas (filtro)</div><div class="value"><?= e(money_br($totalOut)) ?></div></div>
  <div class="dash-kpi bal"><div class="label">Saldo filtrado</div><div class="value"><?= e(money_br($totalIn - $totalOut)) ?></div></div>
  <div class="dash-kpi limit"><div class="label">Linhas</div><div class="value"><?= count($rows) ?></div><div class="meta">máx. 500 resultados</div></div>
</div>

<div class="panel dash-panel-3d filter-panel" style="margin-bottom:1rem">
  <form method="get" class="grid grid-4" style="align-items:end">
    <div class="field" style="margin:0">
      <label class="label">Tipo</label>
      <select class="select" name="tipo">
        <option value="TODOS" <?= $tipo === 'TODOS' ? 'selected' : '' ?>>Todos</option>
        <option value="ENTRADA" <?= $tipo === 'ENTRADA' ? 'selected' : '' ?>>Só entradas</option>
        <option value="SAIDA" <?= $tipo === 'SAIDA' ? 'selected' : '' ?>>Só saídas</option>
      </select>
    </div>
    <div class="field" style="margin:0">
      <label class="label">De</label>
      <input class="input" type="date" name="de" value="<?= e($de) ?>">
    </div>
    <div class="field" style="margin:0">
      <label class="label">Até</label>
      <input class="input" type="date" name="ate" value="<?= e($ate) ?>">
    </div>
    <div class="field" style="margin:0">
      <label class="label">Busca</label>
      <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="fornecedor, doador, NF-e, descrição…">
    </div>
    <div class="row-actions" style="grid-column:1/-1">
      <button class="btn btn-primary" type="submit">Filtrar</button>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/movimentacoes.php')) ?>">Limpar</a>
      <?php if (can_launch($user['role'])): ?>
        <a class="btn btn-secondary" href="<?= e(url_path('admin/lancamento.php?tipo=RECEITA')) ?>">+ Entrada</a>
        <a class="btn btn-secondary" href="<?= e(url_path('admin/lancamento.php?tipo=DESPESA')) ?>">+ Saída</a>
      <?php endif; ?>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Dashboard</a>
    </div>
  </form>
</div>

<div class="panel table-wrap dash-table-wrap m-list-desktop">
<table class="data">
  <thead>
    <tr>
      <th>Data</th>
      <th>Tipo</th>
      <th>Classificação</th>
      <th>Parte</th>
      <th>Descrição</th>
      <th>Documento / NF-e</th>
      <th>Conta</th>
      <th>Valor</th>
    </tr>
  </thead>
  <tbody>
  <?php if (!$rows): ?>
    <tr><td colspan="8" class="empty">Nenhuma movimentação neste filtro.</td></tr>
  <?php endif; ?>
  <?php foreach ($rows as $m): ?>
    <?php
      $isIn = $m['kind'] === 'ENTRADA';
      $codeLabel = $isIn
        ? (REVENUE_SOURCES[$m['code']] ?? $m['code'])
        : Categories::label((string) $m['code']);
    ?>
    <tr>
      <td><?= e(date_br($m['movDate'])) ?></td>
      <td><span class="badge <?= $isIn ? 'badge-ok' : 'badge-danger' ?>"><?= $isIn ? 'Entrada' : 'Saída' ?></span></td>
      <td><?= e($codeLabel) ?></td>
      <td><?= e($m['party'] ?: '—') ?></td>
      <td><?= e(mb_strimwidth((string) ($m['detail'] ?? ''), 0, 70, '…')) ?></td>
      <td>
        <?php if ($isIn): ?>
          <?= e($m['extra'] ?: '—') ?>
        <?php else: ?>
          <?= e((string) (($m['numeroNf'] ?? '') !== '' ? ('NF ' . $m['numeroNf']) : '—')) ?>
        <?php endif; ?>
      </td>
      <td><?= e($m['accountLabel'] ?: '—') ?></td>
      <td class="<?= $isIn ? 'mov-in' : 'mov-out' ?>"><?= $isIn ? '+' : '−' ?><?= e(money_br($m['amount'])) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="m-cards" aria-label="Lista de movimentações">
  <?php if (!$rows): ?><div class="panel empty">Nenhuma movimentação neste filtro.</div><?php endif; ?>
  <?php foreach ($rows as $m): ?>
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
        <div style="grid-column:1/-1"><dt>Documento / NF</dt><dd>
          <?php if ($isIn): ?>
            <?= e($m['extra'] ?: '—') ?>
          <?php else: ?>
            <?= e((string) (($m['numeroNf'] ?? '') !== '' ? ('NF ' . $m['numeroNf']) : '—')) ?>
          <?php endif; ?>
        </dd></div>
      </dl>
    </article>
  <?php endforeach; ?>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
