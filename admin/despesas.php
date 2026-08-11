<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$user = Auth::requireLogin('despesas');
$activeModule = 'despesas';
$pageTitle = 'Despesas';
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$editId = trim((string) get('id', ''));
$edit = null;
$suppliers = $pdo->query('SELECT id,name FROM `Supplier` WHERE active=1 ORDER BY name')->fetchAll();

if ($editId !== '') {
    $st = $pdo->prepare('SELECT * FROM `Expense` WHERE id=? LIMIT 1');
    $st->execute([$editId]);
    $edit = $st->fetch() ?: null;
}

if (request_method() === 'POST' && $canWrite) {
    Auth::requireMaster();
    $action = (string) post('action', 'save');
    if ($action === 'delete') {
        $r = LancamentoCrud::deleteExpense((string) post('id'), $user['id']);
        flash_set($r['ok'] ? 'ok' : 'danger', $r['ok'] ? 'Despesa excluída (saldo, extrato e vínculos ajustados).' : ($r['error'] ?? 'Erro'));
        redirect('/admin/despesas.php');
    }
    if ($action === 'pay') {
        $r = LancamentoCrud::payFutureExpense((string) post('id'), $user['id']);
        flash_set($r['ok'] ? 'ok' : 'danger', $r['ok'] ? 'Parcela paga: saldo da conta e extrato atualizados.' : ($r['error'] ?? 'Erro'));
        redirect('/admin/despesas.php');
    }
    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        if ($id === '') {
            redirect('/admin/lancamento.php?tipo=DESPESA');
        }
        $r = LancamentoCrud::updateExpense($id, [
            'amount' => post('amount'),
            'date' => post('date'),
            'category' => post('category'),
            'supplierId' => post('supplierId') ?: null,
            'description' => post('description'),
            'naturezaOp' => post('naturezaOp'),
            'dataEmissao' => post('dataEmissao'),
            'numeroNf' => post('numeroNf'),
        ], $user['id']);
        flash_set($r['ok'] ? 'ok' : 'danger', $r['ok'] ? 'Despesa atualizada.' : ($r['error'] ?? 'Erro'));
        redirect($r['ok'] ? '/admin/despesas.php' : '/admin/despesas.php?id=' . rawurlencode($id));
    }
}

$rows = $pdo->query(
    "SELECT e.*, a.label AS accountLabel FROM `Expense` e
     LEFT JOIN `BankAccount` a ON a.id = e.bankAccountId
     WHERE e.status <> 'CANCELADA'
     ORDER BY e.date DESC"
)->fetchAll();

$total = array_sum(array_map(fn ($r) => (float) $r['amount'], $rows));
$totalFuturas = array_sum(array_map(
    static fn ($r) => (($r['status'] ?? '') === 'FUTURA') ? (float) $r['amount'] : 0.0,
    $rows
));
$byCat = [];
foreach (Categories::map(false) as $code => $label) {
    $items = array_values(array_filter($rows, fn ($r) => $r['category'] === $code));
    $byCat[] = [
        'code' => $code,
        'label' => $label,
        'count' => count($items),
        'amount' => array_sum(array_map(fn ($r) => (float) $r['amount'], $items)),
        'color' => Categories::color($code),
    ];
}
$metrics = Metrics::getDashboardMetrics();
$vf = $metrics['vehicleFuel'] ?? null;
$nfeComNumero = count(array_filter($rows, fn ($r) => trim((string) ($r['numeroNf'] ?? '')) !== ''));
$nfeMissing = count($rows) - $nfeComNumero;
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-despesas">

<?php if ($vf): ?>
<div class="panel desp-limit" style="margin-bottom:1rem">
  <div class="desp-limit-head">
    <div>
      <strong>Limite Veículos + Combustíveis = 20% do orçamento</strong>
      <div class="muted"><?= e(money_br($vf['amount'])) ?> de <?= e(money_br($vf['limit'])) ?> · resta <?= e(money_br($vf['remaining'])) ?></div>
    </div>
    <span class="badge <?= $vf['withinLimit'] ? 'badge-ok' : 'badge-danger' ?>"><?= $vf['withinLimit'] ? 'Dentro do limite' : 'Excedido' ?></span>
  </div>
  <div class="progress-track" style="margin-top:.6rem"><div class="progress-fill" style="width:<?= min(100, $vf['limit'] > 0 ? ($vf['amount'] / $vf['limit']) * 100 : 0) ?>%;background:<?= $vf['withinLimit'] ? '' : 'var(--danger)' ?>"></div></div>
</div>
<?php endif; ?>

<div class="desp-toolbar">
  <div class="desp-toolbar-main">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Voltar</a>
    <?php if ($canWrite): ?>
      <a class="btn btn-primary" href="<?= e(url_path('admin/lancamento.php?tipo=DESPESA')) ?>">Nova despesa</a>
    <?php endif; ?>
  </div>
  <div class="desp-summary">
    <div class="desp-summary-total">
      Total <strong><?= e(money_br($total)) ?></strong>
      <span class="muted"><?= count($rows) ?> registros<?= $totalFuturas > 0 ? ' · futuras ' . money_br($totalFuturas) : '' ?></span>
    </div>
    <div class="desp-summary-badges">
      <span class="badge badge-ok"><?= $nfeComNumero ?> com Nº NF</span>
      <?php if ($nfeMissing > 0): ?>
        <span class="badge badge-warn"><?= $nfeMissing ?> sem Nº NF</span>
      <?php endif; ?>
      <?php if ($totalFuturas > 0): ?>
        <span class="badge badge-warn">Futuras <?= e(money_br($totalFuturas)) ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="desp-cats grid grid-4" style="margin-bottom:1rem">
  <?php foreach ($byCat as $s): ?>
    <div class="stat-chip">
      <div class="l" style="color:<?= e($s['color']) ?>"><?= e($s['label']) ?></div>
      <div class="n"><?= e(money_br($s['amount'])) ?></div>
      <div class="muted stat-chip-meta"><?= (int) $s['count'] ?> · <?= $total > 0 ? e(percent_br($s['amount'] / $total)) : '0%' ?></div>
    </div>
  <?php endforeach; ?>
</div>

<?php
  $opLabel = static function (?string $code): string {
      $c = strtoupper(trim((string) $code));
      if ($c === 'SERV') {
          $c = 'SERVICO';
      }
      if ($c === 'VEND') {
          $c = 'COMPRA';
      }
      return NFE_OPERATION_TYPES[$c] ?? ($c !== '' ? $c : '—');
  };
?>
<!-- Desktop / tablet largo: tabela -->
<div class="panel table-wrap desp-table-desktop">
<table class="data">
  <thead>
    <tr>
      <th>Data</th>
      <th>Status</th>
      <th>Categoria</th>
      <th>Fornecedor</th>
      <th>Tipo op.</th>
      <th>Nº NF</th>
      <th>Dt emissão</th>
      <th>Conta</th>
      <th>Valor</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="10" class="empty">Nenhuma despesa.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <?php
      $st = (string) ($r['status'] ?? 'PAGA');
      $stLabel = EXPENSE_STATUSES[$st] ?? $st;
      $isFutura = $st === 'FUTURA';
    ?>
    <tr>
      <td><?= e(date_br($r['date'])) ?></td>
      <td>
        <span class="badge <?= $isFutura ? 'badge-warn' : 'badge-ok' ?>"><?= e($stLabel) ?></span>
        <?php if (!empty($r['installmentCount']) && (int) $r['installmentCount'] > 1): ?>
          <div class="muted" style="font-size:.72rem"><?= (int) $r['installmentNumber'] ?>/<?= (int) $r['installmentCount'] ?></div>
        <?php endif; ?>
      </td>
      <td><?= e(Categories::label((string) $r['category'])) ?></td>
      <td>
        <?= e($r['supplierName']) ?>
        <div class="muted" style="font-size:.75rem"><?= e(mb_strimwidth((string) $r['description'], 0, 60, '…')) ?></div>
      </td>
      <td><?= e($opLabel($r['naturezaOp'] ?? null)) ?></td>
      <td><?= e((string) (($r['numeroNf'] ?? '') !== '' ? $r['numeroNf'] : '—')) ?></td>
      <td><?= e(!empty($r['dataEmissao']) ? date_br($r['dataEmissao']) : '—') ?></td>
      <td><?= e($r['accountLabel'] ?: '—') ?></td>
      <td><strong><?= e(money_br($r['amount'])) ?></strong></td>
      <td>
        <?php if ($canWrite): ?>
        <div class="row-actions">
          <?php if ($isFutura): ?>
            <form method="post" onsubmit="return confirm('Confirmar pagamento desta parcela? O valor será debitado da conta.');">
              <input type="hidden" name="action" value="pay">
              <input type="hidden" name="id" value="<?= e($r['id']) ?>">
              <button class="btn btn-secondary" type="submit">Pagar</button>
            </form>
          <?php endif; ?>
          <a class="btn btn-ghost" href="<?= e(url_path('admin/despesas.php?id=' . rawurlencode($r['id']))) ?>#form-despesa">Editar</a>
          <form method="post" onsubmit="return confirm('Excluir despesa e estornar saldo/extrato?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= e($r['id']) ?>">
            <button class="btn btn-danger" type="submit">Excluir</button>
          </form>
        </div>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<!-- Celular / tablet: cards -->
<div class="desp-cards" aria-label="Lista de despesas">
  <?php if (!$rows): ?>
    <div class="panel empty">Nenhuma despesa.</div>
  <?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <?php
      $catLabel = Categories::label((string) $r['category']);
      $catColor = Categories::color((string) $r['category']);
      $st = (string) ($r['status'] ?? 'PAGA');
      $stLabel = EXPENSE_STATUSES[$st] ?? $st;
      $isFutura = $st === 'FUTURA';
    ?>
    <article class="desp-card">
      <header class="desp-card-head">
        <div>
          <div class="desp-card-date"><?= e(date_br($r['date'])) ?></div>
          <div class="desp-card-cat" style="--cat:<?= e($catColor) ?>"><?= e($catLabel) ?></div>
          <span class="badge <?= $isFutura ? 'badge-warn' : 'badge-ok' ?>" style="margin-top:.25rem"><?= e($stLabel) ?><?php if (!empty($r['installmentCount']) && (int) $r['installmentCount'] > 1): ?> · <?= (int) $r['installmentNumber'] ?>/<?= (int) $r['installmentCount'] ?><?php endif; ?></span>
        </div>
        <div class="desp-card-amount"><?= e(money_br($r['amount'])) ?></div>
      </header>
      <div class="desp-card-body">
        <strong class="desp-card-supplier"><?= e((string) ($r['supplierName'] ?: 'Sem fornecedor')) ?></strong>
        <?php if (!empty($r['description'])): ?>
          <p class="desp-card-desc"><?= e((string) $r['description']) ?></p>
        <?php endif; ?>
        <dl class="desp-card-meta">
          <div><dt>Tipo op.</dt><dd><?= e($opLabel($r['naturezaOp'] ?? null)) ?></dd></div>
          <div><dt>Nº NF</dt><dd><?= e((string) (($r['numeroNf'] ?? '') !== '' ? $r['numeroNf'] : '—')) ?></dd></div>
          <div><dt>Dt emissão</dt><dd><?= e(!empty($r['dataEmissao']) ? date_br($r['dataEmissao']) : '—') ?></dd></div>
          <div><dt>Conta</dt><dd><?= e($r['accountLabel'] ?: '—') ?></dd></div>
        </dl>
      </div>
      <?php if ($canWrite): ?>
      <footer class="desp-card-actions">
          <?php if ($isFutura): ?>
            <form method="post" onsubmit="return confirm('Confirmar pagamento desta parcela? O valor será debitado da conta.');">
              <input type="hidden" name="action" value="pay">
              <input type="hidden" name="id" value="<?= e($r['id']) ?>">
              <button class="btn btn-secondary" type="submit">Pagar</button>
            </form>
          <?php endif; ?>
          <a class="btn btn-ghost" href="<?= e(url_path('admin/despesas.php?id=' . rawurlencode($r['id']))) ?>#form-despesa">Editar</a>
          <form method="post" onsubmit="return confirm('Excluir despesa e estornar saldo/extrato?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= e($r['id']) ?>">
            <button class="btn btn-danger" type="submit">Excluir</button>
          </form>
      </footer>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>

<?php if ($canWrite && $edit): ?>
<div class="panel form-card desp-form" id="form-despesa" style="margin-top:1rem">
  <h3 class="display" style="margin-top:0">Editar despesa</h3>
  <form method="post" data-mask-form>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e($edit['id']) ?>">
    <div class="grid grid-2">
      <div class="field"><label class="label">Valor</label><input class="input" name="amount" data-mask="money" value="<?= e(number_format((float) $edit['amount'], 2, ',', '.')) ?>" required></div>
      <div class="field"><label class="label">Data</label><input class="input" type="date" name="date" value="<?= e(substr((string) $edit['date'], 0, 10)) ?>"></div>
    </div>
    <div class="field">
      <label class="label">Categoria</label>
      <select class="select" name="category">
        <?php foreach (Categories::map(false) as $code => $label): ?>
          <option value="<?= e($code) ?>" <?= $edit['category'] === $code ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label class="label">Fornecedor</label>
      <select class="select" name="supplierId">
        <option value="">—</option>
        <?php foreach ($suppliers as $s): ?>
          <option value="<?= e($s['id']) ?>" <?= ($edit['supplierId'] ?? '') === $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php
      $editNat = strtoupper((string) ($edit['naturezaOp'] ?? ''));
      if ($editNat === 'SERV') {
          $editNat = 'SERVICO';
      }
      if ($editNat === 'VEND') {
          $editNat = 'COMPRA';
      }
    ?>
    <div class="field">
      <label class="label">Tipo de operação</label>
      <div class="row-actions deposit-who-options" style="margin:0">
        <label class="deposit-who-option">
          <input type="radio" name="naturezaOp" value="COMPRA" <?= $editNat === 'COMPRA' ? 'checked' : '' ?>>
          <span>Compra</span>
        </label>
        <label class="deposit-who-option">
          <input type="radio" name="naturezaOp" value="SERVICO" <?= $editNat === 'SERVICO' ? 'checked' : '' ?>>
          <span>Serviço</span>
        </label>
      </div>
    </div>
    <div class="grid grid-2">
      <div class="field"><label class="label">Data Emissão</label><input class="input" type="date" name="dataEmissao" value="<?= e(!empty($edit['dataEmissao']) ? substr((string) $edit['dataEmissao'], 0, 10) : '') ?>"></div>
      <div class="field"><label class="label">Nº NF</label><input class="input" name="numeroNf" value="<?= e((string) ($edit['numeroNf'] ?? '')) ?>"></div>
    </div>
    <div class="field"><label class="label">Descrição</label><textarea class="textarea" name="description" required><?= e((string) ($edit['description'] ?? '')) ?></textarea></div>
    <div class="row-actions">
      <button class="btn btn-primary" type="submit">Salvar</button>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/despesas.php')) ?>">Voltar</a>
    </div>
  </form>
</div>
<?php endif; ?>

</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
