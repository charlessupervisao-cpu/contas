<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('saldos');
$activeModule = 'saldos';
$pageTitle = 'Ajuste de Saldos';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$accounts = $campaign ? Lancamento::listAccountsOrdered($campaign['id']) : [];
$editId = trim((string) get('id', ''));
$edit = null;

if ($editId !== '') {
    $st = $pdo->prepare(
        'SELECT b.*, a.label AS accountLabel, a.campaignId FROM `BalanceAdjustment` b
         LEFT JOIN `BankAccount` a ON a.id = b.bankAccountId WHERE b.id=? LIMIT 1'
    );
    $st->execute([$editId]);
    $edit = $st->fetch() ?: null;
    if ($edit && $campaign && ($edit['campaignId'] ?? '') !== $campaign['id']) {
        $edit = null;
    }
}

if (request_method() === 'POST' && $canWrite) {
    Auth::requireMaster();
    $action = (string) post('action', 'save');
    $now = now_sql();

    if ($action === 'delete') {
        $id = (string) post('id');
        $st = $pdo->prepare('SELECT * FROM `BalanceAdjustment` WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $adj = $st->fetch();
        if ($adj) {
            try {
                $pdo->beginTransaction();
                // Restaura saldo anterior
                $pdo->prepare('UPDATE `BankAccount` SET balance=?, updatedAt=? WHERE id=?')
                    ->execute([(float) $adj['previousBalance'], $now, $adj['bankAccountId']]);
                $pdo->prepare(
                    "DELETE FROM `BankTransaction` WHERE bankAccountId=? AND description LIKE ? AND ABS(amount - ?) < 0.001"
                )->execute([$adj['bankAccountId'], 'Ajuste de saldo%', (float) $adj['difference']]);
                $pdo->prepare('DELETE FROM `BalanceAdjustment` WHERE id=?')->execute([$id]);
                $pdo->commit();
                audit_log($user['id'], 'DELETE', 'BalanceAdjustment', $id, 'Excluiu ajuste e restaurou saldo anterior · diff ' . money_br((float) $adj['difference']));
                flash_set('ok', 'Ajuste excluído; saldo restaurado e vínculo do extrato removido.');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                flash_set('danger', $e->getMessage());
            }
        }
        redirect('/admin/saldos.php');
    }

    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        $bankAccountId = (string) post('bankAccountId');
        $newBalance = parse_money_input((string) post('newBalance'));
        $reason = trim((string) post('reason', 'Ajuste manual de saldo'));
        $notes = trim((string) post('notes', ''));
        $date = parse_date_input((string) post('date'));

        $acc = null;
        foreach ($accounts as $a) {
            if ($a['id'] === $bankAccountId) {
                $acc = $a;
            }
        }
        if (!$acc) {
            flash_set('danger', 'Conta inválida.');
            redirect('/admin/saldos.php');
        }

        try {
            $pdo->beginTransaction();
            if ($id !== '') {
                $st = $pdo->prepare('SELECT * FROM `BalanceAdjustment` WHERE id=? LIMIT 1');
                $st->execute([$id]);
                $old = $st->fetch();
                if (!$old) {
                    throw new RuntimeException('Ajuste não encontrado.');
                }
                // Desfaz ajuste antigo
                $pdo->prepare('UPDATE `BankAccount` SET balance=?, updatedAt=? WHERE id=?')
                    ->execute([(float) $old['previousBalance'], $now, $old['bankAccountId']]);
                $pdo->prepare(
                    "DELETE FROM `BankTransaction` WHERE bankAccountId=? AND description LIKE ? AND ABS(amount - ?) < 0.001"
                )->execute([$old['bankAccountId'], 'Ajuste de saldo%', (float) $old['difference']]);

                $prev = (float) $old['previousBalance'];
                if ($old['bankAccountId'] !== $bankAccountId) {
                    $prevStmt = $pdo->prepare('SELECT balance FROM `BankAccount` WHERE id=?');
                    $prevStmt->execute([$bankAccountId]);
                    $prev = (float) $prevStmt->fetch()['balance'];
                }
                $diff = $newBalance - $prev;
                $pdo->prepare(
                    'UPDATE `BalanceAdjustment` SET bankAccountId=?, previousBalance=?, newBalance=?, difference=?, date=?, reason=?, notes=? WHERE id=?'
                )->execute([$bankAccountId, $prev, $newBalance, $diff, $date, $reason, $notes ?: null, $id]);
                $pdo->prepare('UPDATE `BankAccount` SET balance=?, updatedAt=? WHERE id=?')
                    ->execute([$newBalance, $now, $bankAccountId]);
                $pdo->prepare(
                    'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, status, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,?)'
                )->execute([
                    cuid(), $bankAccountId, $date, 'Ajuste de saldo — ' . $reason, $diff,
                    $diff >= 0 ? 'AJUSTE_CREDITO' : 'AJUSTE_DEBITO', 'CONCILIADO', $now, $now,
                ]);
                audit_log($user['id'], 'UPDATE', 'BalanceAdjustment', $id, sprintf('%s: %.2f → %.2f', $acc['label'], $prev, $newBalance));
                flash_set('ok', 'Ajuste atualizado.');
            } else {
                $prev = (float) $acc['balance'];
                $diff = $newBalance - $prev;
                $adjId = cuid();
                $pdo->prepare('UPDATE `BankAccount` SET balance=?, updatedAt=? WHERE id=?')
                    ->execute([$newBalance, $now, $bankAccountId]);
                $pdo->prepare(
                    'INSERT INTO `BalanceAdjustment` (id, bankAccountId, previousBalance, newBalance, difference, date, reason, notes, createdById, createdAt)
                     VALUES (?,?,?,?,?,?,?,?,?,?)'
                )->execute([$adjId, $bankAccountId, $prev, $newBalance, $diff, $date, $reason, $notes ?: null, $user['id'], $now]);
                $pdo->prepare(
                    'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, status, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,?)'
                )->execute([
                    cuid(), $bankAccountId, $date, 'Ajuste de saldo — ' . $reason, $diff,
                    $diff >= 0 ? 'AJUSTE_CREDITO' : 'AJUSTE_DEBITO', 'CONCILIADO', $now, $now,
                ]);
                audit_log($user['id'], 'BALANCE_ADJUST', 'BalanceAdjustment', $adjId, sprintf('%s: %.2f → %.2f', $acc['label'], $prev, $newBalance));
                flash_set('ok', 'Saldo ajustado.');
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash_set('danger', $e->getMessage());
        }
        redirect('/admin/saldos.php');
    }
}

$adjustments = $pdo->query(
    'SELECT b.*, a.label AS accountLabel FROM `BalanceAdjustment` b
     LEFT JOIN `BankAccount` a ON a.id = b.bankAccountId
     ORDER BY b.createdAt DESC LIMIT 50'
)->fetchAll();
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="row-actions" style="margin-bottom:1rem">
  <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Voltar</a>
</div>

<div class="grid grid-2">
  <div class="panel form-card" id="form-ajuste">
    <h3 class="display" style="margin-top:0"><?= $edit ? 'Editar ajuste' : 'Ajustar saldo' ?></h3>
    <?php if (!$canWrite): ?>
      <div class="alert alert-warn">Somente Master pode ajustar.</div>
    <?php else: ?>
    <form method="post" data-mask-form>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
      <div class="field">
        <label class="label">Conta</label>
        <select class="select" name="bankAccountId" required>
          <?php foreach ($accounts as $a): ?>
            <option value="<?= e($a['id']) ?>" <?= (($edit['bankAccountId'] ?? '') === $a['id']) ? 'selected' : '' ?>>
              <?= e($a['label']) ?> — <?= e(money_br($a['balance'])) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="label">Novo saldo</label>
        <input class="input" name="newBalance" data-mask="money" inputmode="decimal" placeholder="0,00"
               value="<?= e($edit ? number_format((float) $edit['newBalance'], 2, ',', '.') : '') ?>" required>
      </div>
      <div class="field"><label class="label">Data</label><input class="input" type="date" name="date" value="<?= e($edit ? substr((string) $edit['date'], 0, 10) : date('Y-m-d')) ?>"></div>
      <div class="field"><label class="label">Motivo</label><input class="input" name="reason" required value="<?= e((string) ($edit['reason'] ?? 'Ajuste conforme extrato')) ?>"></div>
      <div class="field"><label class="label">Observações</label><textarea class="textarea" name="notes"><?= e((string) ($edit['notes'] ?? '')) ?></textarea></div>
      <div class="row-actions">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-ghost" href="<?= e(url_path($edit ? 'admin/saldos.php' : 'admin/index.php')) ?>">Voltar</a>
      </div>
    </form>
    <?php endif; ?>
  </div>

  <div>
    <h3 class="display" style="margin-top:0">Histórico de ajuste de saldos</h3>
    <div class="panel table-wrap m-list-desktop">
      <table class="data">
        <thead><tr><th>Data</th><th>Conta</th><th>De → Para</th><th>Diferença</th><th></th></tr></thead>
        <tbody>
        <?php if (!$adjustments): ?><tr><td colspan="5" class="empty">Nenhum ajuste.</td></tr><?php endif; ?>
        <?php foreach ($adjustments as $b): ?>
          <tr>
            <td><?= e(datetime_br($b['date'])) ?></td>
            <td><?= e($b['accountLabel']) ?></td>
            <td><?= e(money_br($b['previousBalance'])) ?> → <?= e(money_br($b['newBalance'])) ?></td>
            <td><?= e(money_br($b['difference'])) ?></td>
            <td>
              <?php if ($canWrite): ?>
              <div class="row-actions">
                <a class="btn btn-ghost" href="<?= e(url_path('admin/saldos.php?id=' . rawurlencode($b['id']))) ?>#form-ajuste">Editar</a>
                <form method="post" onsubmit="return confirm('Excluir ajuste e restaurar saldo anterior?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= e($b['id']) ?>">
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

    <div class="m-cards" aria-label="Histórico de ajustes">
      <?php if (!$adjustments): ?><div class="panel empty">Nenhum ajuste.</div><?php endif; ?>
      <?php foreach ($adjustments as $b): ?>
        <?php $diff = (float) $b['difference']; ?>
        <article class="m-card">
          <header class="m-card-head">
            <div>
              <div class="m-card-kicker"><?= e(datetime_br($b['date'])) ?></div>
              <div class="m-card-title"><?= e($b['accountLabel'] ?: 'Conta') ?></div>
            </div>
            <div class="m-card-amount <?= $diff >= 0 ? 'in' : 'out' ?>"><?= e(money_br($b['difference'])) ?></div>
          </header>
          <?php if (!empty($b['reason'])): ?><p class="m-card-desc"><?= e((string) $b['reason']) ?></p><?php endif; ?>
          <dl class="m-card-meta">
            <div><dt>De</dt><dd><?= e(money_br($b['previousBalance'])) ?></dd></div>
            <div><dt>Para</dt><dd><?= e(money_br($b['newBalance'])) ?></dd></div>
          </dl>
          <?php if ($canWrite): ?>
          <footer class="m-card-actions">
            <a class="btn btn-ghost" href="<?= e(url_path('admin/saldos.php?id=' . rawurlencode($b['id']))) ?>#form-ajuste">Editar</a>
            <form method="post" onsubmit="return confirm('Excluir ajuste e restaurar saldo anterior?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= e($b['id']) ?>">
              <button class="btn btn-danger" type="submit">Excluir</button>
            </form>
          </footer>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
