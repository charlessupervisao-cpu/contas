<?php

declare(strict_types=1);

/**
 * Transferência entre contas bancárias de campanha — Conta+JE §10.
 */
require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('transferencias');
$activeModule = 'transferencias';
$pageTitle = 'Transferência entre contas';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$accounts = $campaign ? Lancamento::listAccountsOrdered($campaign['id']) : [];

if (request_method() === 'POST' && $canWrite && $campaign) {
    Auth::requireMaster();
    $action = (string) post('action', 'save');
    $now = now_sql();
    $cid = (string) $campaign['id'];

    if ($action === 'delete') {
        $id = (string) post('id');
        $st = $pdo->prepare('SELECT * FROM `AccountTransfer` WHERE id=? AND campaignId=? LIMIT 1');
        $st->execute([$id, $cid]);
        $row = $st->fetch();
        if ($row) {
            try {
                $pdo->beginTransaction();
                $amt = (float) $row['amount'];
                $pdo->prepare('UPDATE `BankAccount` SET balance = balance + ?, updatedAt=? WHERE id=? AND campaignId=?')
                    ->execute([$amt, $now, $row['fromAccountId'], $cid]);
                $pdo->prepare('UPDATE `BankAccount` SET balance = balance - ?, updatedAt=? WHERE id=? AND campaignId=?')
                    ->execute([$amt, $now, $row['toAccountId'], $cid]);
                DocumentProofUpload::deletePrevious($row['proofPdfPath'] ?? null);
                $pdo->prepare('DELETE FROM `AccountTransfer` WHERE id=? AND campaignId=?')->execute([$id, $cid]);
                $pdo->commit();
                audit_log($user['id'], 'DELETE', 'AccountTransfer', $id, 'Estornou transferência entre contas');
                flash_set('ok', 'Transferência excluída e saldos estornados.');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                flash_set('danger', $e->getMessage());
            }
        }
        redirect('/admin/transferencias.php');
    }

    $fromId = (string) post('fromAccountId');
    $toId = (string) post('toAccountId');
    $date = parse_date_input((string) post('date'));
    $amount = parse_money_input((string) post('amount'));
    $description = trim((string) post('description', ''));

    if ($fromId === '' || $toId === '' || $fromId === $toId) {
        flash_set('danger', 'Selecione contas de origem e destino diferentes.');
        redirect('/admin/transferencias.php');
    }
    if ($amount <= 0) {
        flash_set('danger', 'Informe um valor válido.');
        redirect('/admin/transferencias.php');
    }

    $from = null;
    $to = null;
    foreach ($accounts as $a) {
        if ((string) $a['id'] === $fromId) {
            $from = $a;
        }
        if ((string) $a['id'] === $toId) {
            $to = $a;
        }
    }
    if (!$from || !$to) {
        flash_set('danger', 'Conta inválida.');
        redirect('/admin/transferencias.php');
    }
    if ((float) $from['balance'] < $amount) {
        flash_set('danger', 'Saldo insuficiente na conta de origem.');
        redirect('/admin/transferencias.php');
    }

    $id = cuid();
    $proofPath = null;
    if (!empty($_FILES['proofPdf']['name'])) {
        try {
            $proofPath = DocumentProofUpload::save($_FILES['proofPdf'], 'transferencias', $id);
        } catch (Throwable $e) {
            flash_set('danger', $e->getMessage());
            redirect('/admin/transferencias.php');
        }
    }

    try {
        $pdo->beginTransaction();
        $pdo->prepare(
            'INSERT INTO `AccountTransfer` (id, campaignId, fromAccountId, toAccountId, date, amount, description, proofPdfPath, createdById, createdAt, updatedAt)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $id, $cid, $fromId, $toId, $date, $amount,
            $description !== '' ? $description : null, $proofPath, $user['id'], $now, $now,
        ]);
        $pdo->prepare('UPDATE `BankAccount` SET balance = balance - ?, updatedAt=? WHERE id=?')
            ->execute([$amount, $now, $fromId]);
        $pdo->prepare('UPDATE `BankAccount` SET balance = balance + ?, updatedAt=? WHERE id=?')
            ->execute([$amount, $now, $toId]);
        $descTx = $description !== '' ? $description : 'Transferência entre contas Conta+JE';
        $pdo->prepare(
            'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, documentRef, status, createdAt, updatedAt)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            cuid(), $fromId, $date, 'Débito — ' . $descTx, $amount, 'DEBITO', $id, 'CONCILIADO', $now, $now,
        ]);
        $pdo->prepare(
            'INSERT INTO `BankTransaction` (id, bankAccountId, date, description, amount, type, documentRef, status, createdAt, updatedAt)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            cuid(), $toId, $date, 'Crédito — ' . $descTx, $amount, 'CREDITO', $id, 'CONCILIADO', $now, $now,
        ]);
        $pdo->commit();
        audit_log(
            $user['id'],
            'CREATE',
            'AccountTransfer',
            $id,
            sprintf('Transferência %s → %s · %s', $from['label'], $to['label'], money_br($amount))
        );
        flash_set('ok', 'Transferência registrada.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($proofPath) {
            DocumentProofUpload::deletePrevious($proofPath);
        }
        flash_set('danger', 'Falha ao gravar: ' . $e->getMessage());
    }
    redirect('/admin/transferencias.php');
}

$rows = [];
if ($campaign) {
    try {
        $st = $pdo->prepare(
            'SELECT t.*, fa.label AS fromLabel, ta.label AS toLabel
             FROM `AccountTransfer` t
             LEFT JOIN `BankAccount` fa ON fa.id = t.fromAccountId
             LEFT JOIN `BankAccount` ta ON ta.id = t.toAccountId
             WHERE t.campaignId=? ORDER BY t.date DESC, t.createdAt DESC'
        );
        $st->execute([(string) $campaign['id']]);
        $rows = $st->fetchAll() ?: [];
    } catch (Throwable) {
        $rows = [];
    }
}

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-form">
<div class="panel form-card animate-rise je-section">
  <div class="je-kicker">Conta+JE §10 · Transferências</div>
  <h2 class="display" style="margin-top:.2rem">Transferência entre contas</h2>
  <p class="muted" style="margin-top:0">Movimenta saldos entre contas de campanha (Doações / FEFC / Fundo) com comprovante para o pacote Conta+JE.</p>

  <?php if ($canWrite): ?>
  <form method="post" enctype="multipart/form-data" data-mask-form style="margin-top:1rem">
    <input type="hidden" name="action" value="save">
    <div class="grid grid-2">
      <div class="field">
        <label class="label req">Conta origem</label>
        <select class="select" name="fromAccountId" required>
          <option value="">— selecionar —</option>
          <?php foreach ($accounts as $a): ?>
            <option value="<?= e($a['id']) ?>"><?= e($a['label']) ?> (<?= e(money_br($a['balance'])) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="label req">Conta destino</label>
        <select class="select" name="toAccountId" required>
          <option value="">— selecionar —</option>
          <?php foreach ($accounts as $a): ?>
            <option value="<?= e($a['id']) ?>"><?= e($a['label']) ?> (<?= e(money_br($a['balance'])) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="label req">Data</label>
        <input class="input" type="date" name="date" value="<?= e(date('Y-m-d')) ?>" required>
      </div>
      <div class="field">
        <label class="label req">Valor</label>
        <input class="input" name="amount" data-mask="money" inputmode="decimal" placeholder="0,00" required>
      </div>
    </div>
    <div class="field">
      <label class="label">Descrição</label>
      <textarea class="textarea" name="description" rows="2"></textarea>
    </div>
    <div class="field">
      <label class="label">Comprovante PDF</label>
      <input class="input" type="file" name="proofPdf" accept="application/pdf,.pdf">
    </div>
    <div class="row-actions" style="margin-top:1rem">
      <button class="btn btn-primary" type="submit">Transferir</button>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/entrega.php')) ?>">Pacote Conta+JE</a>
    </div>
  </form>
  <?php endif; ?>
</div>

<div class="panel" style="margin-top:1rem">
  <h3 class="display" style="margin-top:0;font-size:1.1rem">Histórico</h3>
  <?php if (!$rows): ?>
    <div class="empty muted">Nenhuma transferência registrada.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Data</th><th>Origem</th><th>Destino</th><th>Descrição</th><th>PDF</th><th>Valor</th>
            <?php if ($canWrite): ?><th></th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e(date_br(substr((string) $r['date'], 0, 10))) ?></td>
              <td><?= e((string) ($r['fromLabel'] ?? '')) ?></td>
              <td><?= e((string) ($r['toLabel'] ?? '')) ?></td>
              <td><?= e((string) ($r['description'] ?? '')) ?></td>
              <td>
                <?php if (!empty($r['proofPdfPath'])): ?>
                  <a href="<?= e(url_path(ltrim((string) $r['proofPdfPath'], '/'))) ?>" target="_blank" rel="noopener">PDF</a>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td><?= e(money_br((float) $r['amount'])) ?></td>
              <?php if ($canWrite): ?>
              <td>
                <form method="post" onsubmit="return confirm('Excluir e estornar saldos?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                  <button class="btn btn-ghost" type="submit">Excluir</button>
                </form>
              </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
