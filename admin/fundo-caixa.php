<?php

declare(strict_types=1);

/**
 * Fundo de caixa — Conta+JE §10 (constituição / reposição / prestação).
 */
require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('fundo-caixa');
$activeModule = 'fundo-caixa';
$pageTitle = 'Fundo de caixa';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$accounts = $campaign ? Lancamento::listAccountsOrdered($campaign['id']) : [];
$kinds = [
    'CONSTITUICAO' => 'Constituição',
    'REPOSICAO' => 'Reposição',
    'PRESTACAO' => 'Prestação / baixa',
];

if (request_method() === 'POST' && $canWrite && $campaign) {
    Auth::requireMaster();
    $action = (string) post('action', 'save');
    $now = now_sql();
    $cid = (string) $campaign['id'];

    if ($action === 'delete') {
        $id = (string) post('id');
        $st = $pdo->prepare('SELECT * FROM `CashFund` WHERE id=? AND campaignId=? LIMIT 1');
        $st->execute([$id, $cid]);
        $row = $st->fetch();
        if ($row) {
            DocumentProofUpload::deletePrevious($row['proofPdfPath'] ?? null);
            $pdo->prepare('DELETE FROM `CashFund` WHERE id=? AND campaignId=?')->execute([$id, $cid]);
            audit_log($user['id'], 'DELETE', 'CashFund', $id, 'Excluiu lançamento de fundo de caixa');
            flash_set('ok', 'Registro excluído.');
        }
        redirect('/admin/fundo-caixa.php');
    }

    $kind = strtoupper(trim((string) post('kind', 'CONSTITUICAO')));
    if (!isset($kinds[$kind])) {
        $kind = 'CONSTITUICAO';
    }
    $date = parse_date_input((string) post('date'));
    $amount = parse_money_input((string) post('amount'));
    $description = trim((string) post('description', ''));
    $bankAccountId = trim((string) post('bankAccountId', '')) ?: null;
    if ($amount <= 0) {
        flash_set('danger', 'Informe um valor válido.');
        redirect('/admin/fundo-caixa.php');
    }
    if ($bankAccountId) {
        $okAcc = false;
        foreach ($accounts as $a) {
            if ((string) $a['id'] === $bankAccountId) {
                $okAcc = true;
                break;
            }
        }
        if (!$okAcc) {
            flash_set('danger', 'Conta bancária inválida.');
            redirect('/admin/fundo-caixa.php');
        }
    }

    $id = cuid();
    $proofPath = null;
    if (!empty($_FILES['proofPdf']['name'])) {
        try {
            $proofPath = DocumentProofUpload::save($_FILES['proofPdf'], 'fundo-caixa', $id);
        } catch (Throwable $e) {
            flash_set('danger', $e->getMessage());
            redirect('/admin/fundo-caixa.php');
        }
    }

    try {
        $pdo->prepare(
            'INSERT INTO `CashFund` (id, campaignId, bankAccountId, kind, date, amount, description, proofPdfPath, createdById, createdAt, updatedAt)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $id, $cid, $bankAccountId, $kind, $date, $amount,
            $description !== '' ? $description : null, $proofPath, $user['id'], $now, $now,
        ]);
        audit_log($user['id'], 'CREATE', 'CashFund', $id, "Fundo de caixa {$kind} " . money_br($amount));
        flash_set('ok', 'Fundo de caixa registrado.');
    } catch (Throwable $e) {
        if ($proofPath) {
            DocumentProofUpload::deletePrevious($proofPath);
        }
        flash_set('danger', 'Falha ao gravar: ' . $e->getMessage());
    }
    redirect('/admin/fundo-caixa.php');
}

$rows = [];
if ($campaign) {
    try {
        $st = $pdo->prepare(
            'SELECT f.*, a.label AS accountLabel FROM `CashFund` f
             LEFT JOIN `BankAccount` a ON a.id = f.bankAccountId
             WHERE f.campaignId=? ORDER BY f.date DESC, f.createdAt DESC'
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
  <div class="je-kicker">Conta+JE §10 · Fundo de caixa</div>
  <h2 class="display" style="margin-top:.2rem">Fundo de caixa</h2>
  <p class="muted" style="margin-top:0">Registre constituição, reposição e prestação do fundo de caixa para incluir no pacote Conta+JE.</p>

  <?php if ($canWrite): ?>
  <form method="post" enctype="multipart/form-data" data-mask-form style="margin-top:1rem">
    <input type="hidden" name="action" value="save">
    <div class="grid grid-2">
      <div class="field">
        <label class="label req">Tipo</label>
        <select class="select" name="kind" required>
          <?php foreach ($kinds as $code => $lab): ?>
            <option value="<?= e($code) ?>"><?= e($lab) ?></option>
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
      <div class="field">
        <label class="label">Conta de origem/destino</label>
        <select class="select" name="bankAccountId">
          <option value="">—</option>
          <?php foreach ($accounts as $a): ?>
            <option value="<?= e($a['id']) ?>"><?= e($a['label']) ?></option>
          <?php endforeach; ?>
        </select>
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
      <button class="btn btn-primary" type="submit">Registrar</button>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/entrega.php')) ?>">Pacote Conta+JE</a>
    </div>
  </form>
  <?php endif; ?>
</div>

<div class="panel" style="margin-top:1rem">
  <h3 class="display" style="margin-top:0;font-size:1.1rem">Histórico</h3>
  <?php if (!$rows): ?>
    <div class="empty muted">Nenhum lançamento de fundo de caixa.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Data</th><th>Tipo</th><th>Conta</th><th>Descrição</th><th>PDF</th><th>Valor</th>
            <?php if ($canWrite): ?><th></th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e(date_br(substr((string) $r['date'], 0, 10))) ?></td>
              <td><?= e($kinds[$r['kind'] ?? ''] ?? (string) ($r['kind'] ?? '')) ?></td>
              <td><?= e((string) ($r['accountLabel'] ?? '—')) ?></td>
              <td><?= e((string) ($r['description'] ?? '')) ?></td>
              <td>
                <?php if (!empty($r['proofPdfPath'])): ?>
                  <a href="<?= e(url_path(ltrim((string) $r['proofPdfPath'], '/'))) ?>" target="_blank" rel="noopener">PDF</a>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td><?= e(money_br((float) $r['amount'])) ?></td>
              <?php if ($canWrite): ?>
              <td>
                <form method="post" onsubmit="return confirm('Excluir este registro?');">
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
