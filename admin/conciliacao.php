<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('conciliacao');
$activeModule = 'conciliacao';
$pageTitle = 'Conciliação';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$accounts = $campaign ? Lancamento::listAccountsOrdered($campaign['id']) : [];
/** Filtro da listagem: '' = todas as contas */
$filterAccountId = (string) get('accountId', '');
if ($filterAccountId === 'all') {
    $filterAccountId = '';
}
$showAllAccounts = $filterAccountId === '';
/** Conta padrão do formulário de nova movimentação */
$formAccountId = $showAllAccounts ? (string) ($accounts[0]['id'] ?? '') : $filterAccountId;
$editId = trim((string) get('id', ''));
$edit = null;

$conciliacaoUrl = static function (string $filterId, string $editTxId = '') : string {
    $q = ['accountId' => $filterId === '' ? 'all' : $filterId];
    if ($editTxId !== '') {
        $q['id'] = $editTxId;
    }
    return '/admin/conciliacao.php?' . http_build_query($q);
};

if ($editId !== '') {
    $st = $pdo->prepare(
        'SELECT t.* FROM `BankTransaction` t
         INNER JOIN `BankAccount` a ON a.id=t.bankAccountId
         WHERE t.id=? AND a.campaignId=? LIMIT 1'
    );
    $st->execute([$editId, $campaign['id'] ?? '']);
    $edit = $st->fetch() ?: null;
    if ($edit) {
        $formAccountId = (string) $edit['bankAccountId'];
    }
}

if (request_method() === 'POST' && $canWrite) {
    Auth::requireMaster();
    $action = (string) post('action', 'status');
    $now = now_sql();
    $postedFilter = (string) post('accountId', $filterAccountId);
    if ($postedFilter === 'all') {
        $postedFilter = '';
    }
    $back = $conciliacaoUrl($postedFilter);

    if ($action === 'delete') {
        $id = (string) post('id');
        $st = $pdo->prepare('SELECT description, amount FROM `BankTransaction` WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM `BankTransaction` WHERE id=?')->execute([$id]);
            audit_log($user['id'], 'DELETE', 'BankTransaction', $id, 'Excluiu lançamento do extrato: ' . $row['description']);
            flash_set('ok', 'Movimentação excluída.');
        }
        redirect($back);
    }

    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        $status = (string) post('status', 'PENDENTE');
        if (!in_array($status, ['PENDENTE', 'CONCILIADO', 'DIVERGENTE'], true)) {
            $status = 'PENDENTE';
        }
        $description = trim((string) post('description', ''));
        $notes = trim((string) post('notes', ''));
        $amount = parse_money_input((string) post('amount', '0'));
        $date = parse_date_input((string) post('date'));
        $bankAccountId = (string) post('bankAccountId', $formAccountId);

        if ($description === '') {
            flash_set('danger', 'Descrição obrigatória.');
            redirect($back);
        }
        if ($bankAccountId === '') {
            flash_set('danger', 'Selecione a conta da movimentação.');
            redirect($back);
        }

        if ($id !== '') {
            $pdo->prepare(
                'UPDATE `BankTransaction` SET bankAccountId=?, date=?, description=?, amount=?, status=?, notes=?, updatedAt=? WHERE id=?'
            )->execute([$bankAccountId, $date, $description, $amount, $status, $notes ?: null, $now, $id]);
            audit_log($user['id'], 'UPDATE', 'BankTransaction', $id, "Editou conciliação → {$status} · {$description}");
            flash_set('ok', 'Movimentação atualizada.');
        } else {
            $type = $amount >= 0 ? 'CREDITO' : 'DEBITO';
            $newId = cuid();
            $pdo->prepare(
                'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, status, notes, createdAt, updatedAt)
                 VALUES (?,?,?,?,?,?,?,?,?,?)'
            )->execute([$newId, $bankAccountId, $date, $description, $amount, $type, $status, $notes ?: null, $now, $now]);
            audit_log($user['id'], 'CREATE', 'BankTransaction', $newId, "Criou movimentação na conciliação · {$status}");
            flash_set('ok', 'Movimentação cadastrada.');
        }
        redirect($back);
    }

    if ($action === 'status') {
        $id = (string) post('id');
        $status = (string) post('status');
        if (!in_array($status, ['PENDENTE', 'CONCILIADO', 'DIVERGENTE'], true)) {
            flash_set('danger', 'Status inválido.');
            redirect($back);
        }
        $pdo->prepare('UPDATE `BankTransaction` SET status=?, updatedAt=? WHERE id=?')->execute([$status, $now, $id]);
        audit_log($user['id'], 'RECONCILE', 'BankTransaction', $id, 'Status → ' . $status);
        flash_set('ok', 'Status: ' . $status);
        redirect($back);
    }
}

$params = [];
$sql = 'SELECT t.*, a.label AS accountLabel, e.status AS expenseStatus, e.installmentCount
        FROM `BankTransaction` t
        INNER JOIN `BankAccount` a ON a.id=t.bankAccountId
        LEFT JOIN `Expense` e ON e.id = t.matchedExpenseId
        WHERE a.campaignId=?';
$params[] = $campaign['id'] ?? '';
if (!$showAllAccounts) {
    $sql .= ' AND t.bankAccountId=?';
    $params[] = $filterAccountId;
}
$sql .= ' ORDER BY t.date DESC, a.sortOrder ASC LIMIT 300';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$summary = ['PENDENTE' => 0, 'CONCILIADO' => 0, 'DIVERGENTE' => 0];
$parcelasPendentes = 0;
foreach ($rows as $r) {
    $summary[$r['status']] = ($summary[$r['status']] ?? 0) + 1;
    if (($r['expenseStatus'] ?? '') === 'FUTURA') {
        $parcelasPendentes++;
    }
}
$filterParam = $showAllAccounts ? 'all' : $filterAccountId;
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-toolbar filter-panel">
  <div class="page-toolbar-main">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Voltar</a>
    <form method="get" class="row-actions" style="flex:1 1 220px">
      <select class="select" name="accountId" onchange="this.form.submit()" style="max-width:100%;width:100%" aria-label="Filtrar por conta">
        <option value="all" <?= $showAllAccounts ? 'selected' : '' ?>>Todas as contas</option>
        <?php foreach ($accounts as $i => $a): ?>
          <option value="<?= e($a['id']) ?>" <?= $filterAccountId === $a['id'] ? 'selected' : '' ?>>Conta <?= $i + 1 ?> — <?= e($a['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <div class="page-toolbar-side">
    <span class="badge badge-warn">Pend. <?= (int) $summary['PENDENTE'] ?></span>
    <?php if ($parcelasPendentes > 0): ?>
      <a class="badge badge-warn" href="<?= e(url_path('admin/contas-pendentes.php')) ?>" title="Contas parceladas e futuras — baixa manual">Contas a pagar <?= (int) $parcelasPendentes ?></a>
    <?php endif; ?>
    <span class="badge badge-ok">OK <?= (int) $summary['CONCILIADO'] ?></span>
    <span class="badge badge-danger">Div. <?= (int) $summary['DIVERGENTE'] ?></span>
    <?php if ($canWrite): ?>
      <a class="btn btn-primary" href="#form-conciliacao"><?= $edit ? 'Editar' : 'Novo lançamento' ?></a>
    <?php endif; ?>
  </div>
</div>

<div class="panel table-wrap m-list-desktop">
<table class="data">
  <thead><tr><th>Data</th><?php if ($showAllAccounts): ?><th>Conta</th><?php endif; ?><th>Descrição</th><th>Valor</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="<?= $showAllAccounts ? 6 : 5 ?>" class="empty">Nenhuma movimentação.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <?php $isParcelaAPagar = ($r['expenseStatus'] ?? '') === 'FUTURA'; ?>
    <tr>
      <td><?= e(date_br($r['date'])) ?></td>
      <?php if ($showAllAccounts): ?><td><?= e((string) ($r['accountLabel'] ?? '—')) ?></td><?php endif; ?>
      <td>
        <?= e($r['description']) ?>
        <?php if ($isParcelaAPagar): ?>
          <div><span class="badge badge-warn">A pagar · baixa manual</span></div>
        <?php endif; ?>
        <?php if (!empty($r['notes'])): ?><div class="muted" style="font-size:.75rem"><?= e($r['notes']) ?></div><?php endif; ?>
      </td>
      <td><strong><?= e(money_br($r['amount'])) ?></strong></td>
      <td><span class="badge <?= $r['status'] === 'CONCILIADO' ? 'badge-ok' : ($r['status'] === 'DIVERGENTE' ? 'badge-danger' : 'badge-warn') ?>"><?= e($r['status'] === 'CONCILIADO' ? 'OK' : ($r['status'] === 'DIVERGENTE' ? 'DIV' : 'PEND')) ?></span></td>
      <td>
        <?php if ($canWrite): ?>
        <div class="row-actions">
          <form method="post"><input type="hidden" name="action" value="status"><input type="hidden" name="accountId" value="<?= e($filterParam) ?>"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn btn-secondary" name="status" value="CONCILIADO" type="submit">OK</button></form>
          <form method="post"><input type="hidden" name="action" value="status"><input type="hidden" name="accountId" value="<?= e($filterParam) ?>"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn btn-secondary" name="status" value="DIVERGENTE" type="submit">Div</button></form>
          <form method="post"><input type="hidden" name="action" value="status"><input type="hidden" name="accountId" value="<?= e($filterParam) ?>"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn btn-ghost" name="status" value="PENDENTE" type="submit">Pend</button></form>
          <a class="btn btn-ghost" href="<?= e(url_path($conciliacaoUrl($filterAccountId, (string) $r['id']))) ?>#form-conciliacao">Editar</a>
          <form method="post" onsubmit="return confirm('Excluir esta movimentação do extrato?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="accountId" value="<?= e($filterParam) ?>">
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

<div class="m-cards" aria-label="Lista de conciliação">
  <?php if (!$rows): ?><div class="panel empty">Nenhuma movimentação.</div><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <?php
      $amt = (float) $r['amount'];
      $isIn = $amt >= 0;
      $stLabel = $r['status'] === 'CONCILIADO' ? 'OK' : ($r['status'] === 'DIVERGENTE' ? 'DIV' : 'PEND');
      $stBadge = $r['status'] === 'CONCILIADO' ? 'badge-ok' : ($r['status'] === 'DIVERGENTE' ? 'badge-danger' : 'badge-warn');
      $isParcelaAPagar = ($r['expenseStatus'] ?? '') === 'FUTURA';
    ?>
    <article class="m-card">
      <header class="m-card-head">
        <div>
          <div class="m-card-kicker">
            <?= e(date_br($r['date'])) ?> ·
            <span class="badge <?= $stBadge ?>"><?= e($stLabel) ?></span>
            <?php if ($isParcelaAPagar): ?>
              <span class="badge badge-warn">A pagar</span>
            <?php endif; ?>
          </div>
          <div class="m-card-title"><?= e($r['description']) ?></div>
        </div>
        <div class="m-card-amount <?= $isIn ? 'in' : 'out' ?>"><?= e(money_br($r['amount'])) ?></div>
      </header>
      <?php if (!empty($r['notes'])): ?><p class="m-card-desc"><?= e((string) $r['notes']) ?></p><?php endif; ?>
      <dl class="m-card-meta">
        <div><dt>Status</dt><dd><span class="badge <?= $stBadge ?>"><?= e($stLabel) ?></span></dd></div>
        <div><dt>Conta</dt><dd><?= e($r['accountLabel'] ?: '—') ?></dd></div>
      </dl>
      <?php if ($canWrite): ?>
      <footer class="m-card-actions">
        <form method="post"><input type="hidden" name="action" value="status"><input type="hidden" name="accountId" value="<?= e($filterParam) ?>"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn btn-secondary" name="status" value="CONCILIADO" type="submit">OK</button></form>
        <form method="post"><input type="hidden" name="action" value="status"><input type="hidden" name="accountId" value="<?= e($filterParam) ?>"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn btn-secondary" name="status" value="DIVERGENTE" type="submit">Div</button></form>
        <form method="post"><input type="hidden" name="action" value="status"><input type="hidden" name="accountId" value="<?= e($filterParam) ?>"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn btn-ghost" name="status" value="PENDENTE" type="submit">Pend</button></form>
        <a class="btn btn-ghost" href="<?= e(url_path($conciliacaoUrl($filterAccountId, (string) $r['id']))) ?>#form-conciliacao">Editar</a>
        <form method="post" onsubmit="return confirm('Excluir esta movimentação do extrato?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="accountId" value="<?= e($filterParam) ?>">
          <input type="hidden" name="id" value="<?= e($r['id']) ?>">
          <button class="btn btn-danger" type="submit">Excluir</button>
        </form>
      </footer>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>

<?php if ($canWrite): ?>
<div class="panel form-card" id="form-conciliacao" style="margin-top:1rem">
  <h3 class="display" style="margin-top:0"><?= $edit ? 'Editar movimentação' : 'Nova movimentação' ?></h3>
  <form method="post" data-mask-form>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="accountId" value="<?= e($filterParam) ?>">
    <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
    <div class="field">
      <label class="label">Conta</label>
      <select class="select" name="bankAccountId" required>
        <?php foreach ($accounts as $i => $a): ?>
          <option value="<?= e($a['id']) ?>" <?= (($edit['bankAccountId'] ?? $formAccountId) === $a['id']) ? 'selected' : '' ?>>Conta <?= $i + 1 ?> — <?= e($a['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="grid grid-2">
      <div class="field"><label class="label">Data</label><input class="input" type="date" name="date" value="<?= e($edit ? substr((string) $edit['date'], 0, 10) : date('Y-m-d')) ?>"></div>
      <div class="field"><label class="label">Valor</label><input class="input" name="amount" data-mask="money" inputmode="decimal" value="<?= e($edit ? number_format((float) $edit['amount'], 2, ',', '.') : '') ?>" required></div>
    </div>
    <div class="field"><label class="label">Descrição</label><input class="input" name="description" value="<?= e((string) ($edit['description'] ?? '')) ?>" required></div>
    <div class="field">
      <label class="label">Status</label>
      <?php $st = (string) ($edit['status'] ?? 'PENDENTE'); ?>
      <select class="select" name="status">
        <option value="PENDENTE" <?= $st === 'PENDENTE' ? 'selected' : '' ?>>PEND (Pendente)</option>
        <option value="CONCILIADO" <?= $st === 'CONCILIADO' ? 'selected' : '' ?>>OK (Conciliado)</option>
        <option value="DIVERGENTE" <?= $st === 'DIVERGENTE' ? 'selected' : '' ?>>DIV (Divergente)</option>
      </select>
    </div>
    <div class="field"><label class="label">Observações</label><textarea class="textarea" name="notes"><?= e((string) ($edit['notes'] ?? '')) ?></textarea></div>
    <div class="row-actions">
      <button class="btn btn-primary" type="submit">Salvar</button>
      <a class="btn btn-ghost" href="<?= e(url_path($conciliacaoUrl($filterAccountId))) ?>">Voltar</a>
    </div>
  </form>
</div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
