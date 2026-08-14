<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('receitas');
$activeModule = 'receitas';
$pageTitle = 'Doações recebidas';
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$editId = trim((string) get('id', ''));
$edit = null;

if ($editId !== '') {
    $st = $pdo->prepare('SELECT * FROM `Revenue` WHERE id=? LIMIT 1');
    $st->execute([$editId]);
    $edit = $st->fetch() ?: null;
}

if (request_method() === 'POST' && $canWrite) {
    Auth::requireMaster();
    $action = (string) post('action', 'save');
    if ($action === 'delete') {
        $r = LancamentoCrud::deleteRevenue((string) post('id'), $user['id']);
        flash_set($r['ok'] ? 'ok' : 'danger', $r['ok'] ? 'Receita excluída (saldo e extrato ajustados).' : ($r['error'] ?? 'Erro'));
        redirect('/admin/receitas.php');
    }
    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        if ($id === '') {
            redirect('/admin/lancamento.php?tipo=RECEITA');
        }
        $r = LancamentoCrud::updateRevenue($id, [
            'amount' => post('amount'),
            'date' => post('date'),
            'source' => post('source'),
            'donorName' => post('donorName'),
            'donorCpf' => post('donorCpf'),
            'receiptNumber' => post('receiptNumber'),
            'description' => post('description'),
        ], $user['id']);
        flash_set($r['ok'] ? 'ok' : 'danger', $r['ok'] ? 'Receita atualizada.' : ($r['error'] ?? 'Erro'));
        redirect($r['ok'] ? '/admin/receitas.php' : '/admin/receitas.php?id=' . rawurlencode($id));
    }
}

$rows = $pdo->query(
    'SELECT r.*, a.label AS accountLabel FROM `Revenue` r
     LEFT JOIN `BankAccount` a ON a.id = r.bankAccountId
     ORDER BY r.date DESC'
)->fetchAll();
$total = array_sum(array_map(fn ($r) => (float) $r['amount'], $rows));
$bySource = [];
foreach (REVENUE_SOURCES as $code => $label) {
    $items = array_values(array_filter($rows, fn ($r) => $r['source'] === $code));
    $bySource[] = [
        'code' => $code,
        'label' => $label,
        'count' => count($items),
        'amount' => array_sum(array_map(fn ($r) => (float) $r['amount'], $items)),
        'color' => REVENUE_SOURCE_COLORS[$code],
    ];
}
$donors = [];
foreach ($rows as $r) {
    if ($r['source'] === 'DOADOR_PF' && $r['donorCpf']) {
        $donors[$r['donorCpf']] = true;
    }
}
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-toolbar filter-panel">
  <div class="page-toolbar-main">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Painel</a>
    <div>
      <div class="fin-kicker" style="margin:0">Conta+JE §8 · Doações recebidas</div>
      <strong style="font-size:1.05rem">Receitas da campanha</strong>
    </div>
  </div>
  <div class="page-toolbar-side">
    <div class="muted" style="font-size:.85rem">
      Total <strong><?= e(money_br($total)) ?></strong> · <?= count($rows) ?> · <?= count($donors) ?> doadores PF
    </div>
    <?php if ($canWrite): ?>
      <a class="btn btn-primary" href="<?= e(url_path('admin/lancamento.php?tipo=RECEITA')) ?>">Nova doação</a>
    <?php endif; ?>
    <a class="btn btn-ghost" href="<?= e(url_path('admin/relatorios.php?tipo=receitas-financeiras&formato=oficial')) ?>">Relatório</a>
  </div>
</div>

<div class="grid grid-3" style="margin-bottom:1rem">
  <?php foreach ($bySource as $s): ?>
    <div class="panel kpi">
      <div class="muted"><?= e($s['label']) ?></div>
      <div class="display kpi-amount" style="color:<?= e($s['color']) ?>"><?= e(money_br($s['amount'])) ?></div>
      <div class="muted kpi-meta"><?= (int) $s['count'] ?> lançamentos · <?= $total > 0 ? e(percent_br($s['amount'] / $total)) : '0%' ?> do total</div>
    </div>
  <?php endforeach; ?>
</div>

<div class="panel table-wrap m-list-desktop">
<table class="data">
  <thead><tr><th>Data</th><th>Fonte Conta+JE</th><th>Doador / Origem</th><th>CPF</th><th>Espécie</th><th>Recibo</th><th>Conta</th><th>Valor</th><th></th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="9" class="empty">Nenhuma receita.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e(date_br($r['date'])) ?></td>
      <td><?= e(REVENUE_SOURCES[$r['source']] ?? $r['source']) ?></td>
      <td><?= e($r['donorName'] ?: '—') ?><div class="muted" style="font-size:.75rem"><?= e($r['description'] ?: '') ?></div></td>
      <td><?= e($r['donorCpf'] ? format_cpf_cnpj($r['donorCpf']) : '—') ?></td>
      <td><?= e(RESOURCE_SPECIES[$r['resourceSpecies'] ?? ''] ?? ($r['resourceSpecies'] ?? '—')) ?></td>
      <td><?= e((string) ($r['receiptNumber'] ?? '—')) ?></td>
      <td><?= e($r['accountLabel'] ?: '—') ?></td>
      <td><strong><?= e(money_br($r['amount'])) ?></strong></td>
      <td>
        <?php if ($canWrite): ?>
        <div class="row-actions">
          <a class="btn btn-ghost" href="<?= e(url_path('admin/receitas.php?id=' . rawurlencode($r['id']))) ?>#form-receita">Editar</a>
          <form method="post" onsubmit="return confirm('Excluir receita e estornar saldo/extrato?');">
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

<div class="m-cards" aria-label="Lista de receitas">
  <?php if (!$rows): ?><div class="panel empty">Nenhuma receita.</div><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <article class="m-card">
      <header class="m-card-head">
        <div>
          <div class="m-card-kicker"><?= e(date_br($r['date'])) ?> · <?= e(REVENUE_SOURCES[$r['source']] ?? $r['source']) ?></div>
          <div class="m-card-title"><?= e($r['donorName'] ?: 'Sem doador / origem') ?></div>
        </div>
        <div class="m-card-amount in"><?= e(money_br($r['amount'])) ?></div>
      </header>
      <?php if (!empty($r['description'])): ?><p class="m-card-desc"><?= e((string) $r['description']) ?></p><?php endif; ?>
      <dl class="m-card-meta">
        <div><dt>CPF</dt><dd><?= e($r['donorCpf'] ? format_cpf_cnpj($r['donorCpf']) : '—') ?></dd></div>
        <div><dt>Espécie</dt><dd><?= e(RESOURCE_SPECIES[$r['resourceSpecies'] ?? ''] ?? ($r['resourceSpecies'] ?? '—')) ?></dd></div>
        <div><dt>Recibo</dt><dd><?= e((string) ($r['receiptNumber'] ?? '—')) ?></dd></div>
        <div><dt>Conta</dt><dd><?= e($r['accountLabel'] ?: '—') ?></dd></div>
      </dl>
      <?php if ($canWrite): ?>
      <footer class="m-card-actions">
        <a class="btn btn-ghost" href="<?= e(url_path('admin/receitas.php?id=' . rawurlencode($r['id']))) ?>#form-receita">Editar</a>
        <form method="post" onsubmit="return confirm('Excluir receita e estornar saldo/extrato?');">
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
<div class="panel form-card" id="form-receita" style="margin-top:1rem">
  <h3 class="display" style="margin-top:0">Editar receita</h3>
  <form method="post" data-mask-form>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e($edit['id']) ?>">
    <div class="grid grid-2">
      <div class="field"><label class="label">Valor</label><input class="input" name="amount" data-mask="money" value="<?= e(number_format((float) $edit['amount'], 2, ',', '.')) ?>" required></div>
      <div class="field"><label class="label">Data</label><input class="input" type="date" name="date" value="<?= e(substr((string) $edit['date'], 0, 10)) ?>"></div>
    </div>
    <div class="field">
      <label class="label">Fonte</label>
      <select class="select" name="source">
        <?php foreach (REVENUE_SOURCES as $code => $label): ?>
          <option value="<?= e($code) ?>" <?= $edit['source'] === $code ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="grid grid-2">
      <div class="field"><label class="label">Nome do doador</label><input class="input" name="donorName" value="<?= e((string) ($edit['donorName'] ?? '')) ?>"></div>
      <div class="field"><label class="label">CPF do doador</label><input class="input" name="donorCpf" data-mask="cpf" value="<?= e($edit['donorCpf'] ? format_cpf_cnpj($edit['donorCpf']) : '') ?>"></div>
    </div>
    <div class="field"><label class="label">Recibo</label><input class="input" name="receiptNumber" value="<?= e((string) ($edit['receiptNumber'] ?? '')) ?>"></div>
    <div class="field"><label class="label">Descrição</label><textarea class="textarea" name="description"><?= e((string) ($edit['description'] ?? '')) ?></textarea></div>
    <div class="row-actions">
      <button class="btn btn-primary" type="submit">Salvar</button>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/receitas.php')) ?>">Voltar</a>
    </div>
  </form>
</div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
