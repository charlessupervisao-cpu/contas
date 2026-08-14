<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('contas');
$activeModule = 'contas';
$pageTitle = '2 · Cadastro das contas';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$editId = trim((string) get('id', ''));
$edit = null;
$errors = [];

$hasResourceOrigin = false;
$hasOpenedAt = false;
try {
    $hasResourceOrigin = (bool) $pdo->query("SHOW COLUMNS FROM `BankAccount` LIKE 'resourceOrigin'")->fetch();
    $hasOpenedAt = (bool) $pdo->query("SHOW COLUMNS FROM `BankAccount` LIKE 'openedAt'")->fetch();
} catch (Throwable) {
}

if ($editId !== '' && $campaign) {
    $st = $pdo->prepare('SELECT * FROM `BankAccount` WHERE id=? AND campaignId=? LIMIT 1');
    $st->execute([$editId, $campaign['id']]);
    $edit = $st->fetch() ?: null;
}

if (request_method() === 'POST' && $canWrite) {
    Auth::requireMaster();
    $action = (string) post('action', 'save');
    $now = now_sql();
    $cid = (string) ($campaign['id'] ?? '');

    if ($action === 'toggle') {
        $id = (string) post('id');
        $active = (int) post('active');
        if ($active === 1) {
            $st = $pdo->prepare('SELECT COUNT(*) AS c FROM `BankAccount` WHERE campaignId=? AND active=1 AND id<>?');
            $st->execute([$cid, $id]);
            if ((int) $st->fetch()['c'] >= MAX_BANK_ACCOUNTS) {
                flash_set('danger', 'Máximo de ' . MAX_BANK_ACCOUNTS . ' contas ativas.');
                redirect('/admin/contas.php');
            }
        }
        $pdo->prepare('UPDATE `BankAccount` SET active=?, updatedAt=? WHERE id=? AND campaignId=?')
            ->execute([$active, $now, $id, $cid]);
        audit_log($user['id'], 'UPDATE', 'BankAccount', $id, $active ? 'Ativou conta' : 'Desativou conta');
        flash_set('ok', $active ? 'Conta ativada.' : 'Conta desativada.');
        redirect('/admin/contas.php');
    }

    if ($action === 'delete') {
        $id = (string) post('id');
        $st = $pdo->prepare('SELECT label FROM `BankAccount` WHERE id=? AND campaignId=? LIMIT 1');
        $st->execute([$id, $cid]);
        $row = $st->fetch();
        if ($row) {
            $pdo->beginTransaction();
            try {
                $pdo->prepare('DELETE FROM `AccountMapping` WHERE bankAccountId=?')->execute([$id]);
                $pdo->prepare('DELETE FROM `BalanceAdjustment` WHERE bankAccountId=?')->execute([$id]);
                $pdo->prepare('DELETE FROM `BankTransaction` WHERE bankAccountId=?')->execute([$id]);
                $pdo->prepare('UPDATE `Revenue` SET bankAccountId=NULL WHERE bankAccountId=?')->execute([$id]);
                $pdo->prepare('UPDATE `Expense` SET bankAccountId=NULL WHERE bankAccountId=?')->execute([$id]);
                $pdo->prepare('DELETE FROM `BankAccount` WHERE id=? AND campaignId=?')->execute([$id, $cid]);
                $pdo->commit();
                audit_log($user['id'], 'DELETE', 'BankAccount', $id, 'Excluiu conta ' . $row['label'] . ' e vínculos (mapeamentos, extrato, ajustes)');
                flash_set('ok', 'Conta excluída com todos os vínculos.');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                flash_set('danger', $e->getMessage());
            }
        }
        redirect('/admin/contas.php');
    }

    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        $label = trim((string) post('label', ''));
        $bankName = trim((string) post('bankName', ''));
        $bankCode = trim((string) post('bankCode', ''));
        $agency = trim((string) post('agency', ''));
        $agencyDv = trim((string) post('agencyDv', ''));
        $accountNumber = trim((string) post('accountNumber', ''));
        $accountDv = trim((string) post('accountDv', ''));
        $accountType = trim((string) post('accountType', 'Corrente')) ?: 'Corrente';
        $balance = parse_money_input((string) post('balance', '0'));
        $resourceOrigin = trim((string) post('resourceOrigin', ''));
        $racNumber = trim((string) post('racNumber', ''));
        if ($resourceOrigin !== '' && !isset(BANK_RESOURCE_ORIGINS[$resourceOrigin])) {
            $errors[] = 'Fonte do recurso inválida.';
            $resourceOrigin = '';
        }
        $openedAtRaw = trim((string) post('openedAt', ''));
        $openedAt = $openedAtRaw !== '' ? $openedAtRaw : null;
        $todayYmd = (new DateTimeImmutable('today'))->format('Y-m-d');

        $depositCpf = 0;
        $depositCnpj = 0;
        if ($resourceOrigin !== '') {
            $flags = ElectoralRules::depositFlagsForOrigin($resourceOrigin);
            $depositCpf = $flags['cpf'] ? 1 : 0;
            $depositCnpj = $flags['cnpj'] ? 1 : 0;
        }
        if (array_key_exists('depositCpf', $_POST) || array_key_exists('depositCnpj', $_POST)) {
            $depositCpf = post('depositCpf') ? 1 : 0;
            $depositCnpj = post('depositCnpj') ? 1 : 0;
        }

        if ($label === '' || $bankName === '' || $agency === '' || $accountNumber === '') {
            $errors[] = 'Preencha rótulo, banco, agência e conta.';
        }
        if ($depositCpf === 0 && $depositCnpj === 0) {
            $errors[] = 'Selecione quem pode depositar nessa conta: CPF e/ou CNPJ.';
        }
        if ($openedAt !== null) {
            $dt = DateTimeImmutable::createFromFormat('Y-m-d', $openedAt);
            $valid = $dt && $dt->format('Y-m-d') === $openedAt;
            if (!$valid) {
                $errors[] = 'Data de abertura inválida.';
            } elseif ($openedAt > $todayYmd) {
                $errors[] = 'Data de abertura não pode ser futura.';
            }
        }

        if (!$errors && $campaign) {
            $originVal = $resourceOrigin !== '' ? $resourceOrigin : null;
            $agencyDvVal = $agencyDv !== '' ? $agencyDv : null;
            $accountDvVal = $accountDv !== '' ? $accountDv : null;
            $racVal = $racNumber !== '' ? $racNumber : null;
            $statementPath = null;
            $prevStatement = null;
            if ($id !== '' && is_array($edit) && !empty($edit['statementPdfPath'])) {
                $prevStatement = (string) $edit['statementPdfPath'];
            }
            if (!empty($_FILES['statementPdf']['name'])) {
                try {
                    $entityId = $id !== '' ? $id : cuid();
                    $statementPath = DocumentProofUpload::save($_FILES['statementPdf'], 'extratos', $entityId, $prevStatement);
                } catch (Throwable $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }
        if (!$errors && $campaign) {
            $originVal = $resourceOrigin !== '' ? $resourceOrigin : null;
            $agencyDvVal = $agencyDv !== '' ? $agencyDv : null;
            $accountDvVal = $accountDv !== '' ? $accountDv : null;
            $racVal = $racNumber !== '' ? $racNumber : null;
            if ($id !== '') {
                try {
                    if ($statementPath) {
                        $pdo->prepare(
                            'UPDATE `BankAccount` SET label=?, bankName=?, bankCode=?, agency=?, agencyDv=?, accountNumber=?, accountDv=?, accountType=?, resourceOrigin=?, openedAt=?, racNumber=?, depositCpf=?, depositCnpj=?, statementPdfPath=?, updatedAt=?
                             WHERE id=? AND campaignId=?'
                        )->execute([
                            $label, $bankName, $bankCode, $agency, $agencyDvVal, $accountNumber, $accountDvVal, $accountType,
                            $originVal, $openedAt, $racVal, $depositCpf, $depositCnpj, $statementPath, $now, $id, $cid,
                        ]);
                    } else {
                        $pdo->prepare(
                            'UPDATE `BankAccount` SET label=?, bankName=?, bankCode=?, agency=?, agencyDv=?, accountNumber=?, accountDv=?, accountType=?, resourceOrigin=?, openedAt=?, racNumber=?, depositCpf=?, depositCnpj=?, updatedAt=?
                             WHERE id=? AND campaignId=?'
                        )->execute([
                            $label, $bankName, $bankCode, $agency, $agencyDvVal, $accountNumber, $accountDvVal, $accountType,
                            $originVal, $openedAt, $racVal, $depositCpf, $depositCnpj, $now, $id, $cid,
                        ]);
                    }
                } catch (Throwable) {
                    $pdo->prepare(
                        'UPDATE `BankAccount` SET label=?, bankName=?, bankCode=?, agency=?, accountNumber=?, accountType=?, resourceOrigin=?, openedAt=?, depositCpf=?, depositCnpj=?, updatedAt=?
                         WHERE id=? AND campaignId=?'
                    )->execute([
                        $label, $bankName, $bankCode, $agency, $accountNumber, $accountType,
                        $originVal, $openedAt, $depositCpf, $depositCnpj, $now, $id, $cid,
                    ]);
                }
                audit_log($user['id'], 'UPDATE', 'BankAccount', $id, "Atualizou conta {$label}");
                flash_set('ok', 'Conta atualizada.');
            } else {
                $st = $pdo->prepare('SELECT COUNT(*) AS c FROM `BankAccount` WHERE campaignId=? AND active=1');
                $st->execute([$cid]);
                $activeCount = (int) $st->fetch()['c'];
                if ($activeCount >= MAX_BANK_ACCOUNTS) {
                    flash_set('danger', 'Máximo de ' . MAX_BANK_ACCOUNTS . ' contas ativas. Desative uma antes de criar.');
                    redirect('/admin/contas.php');
                }
                $newId = cuid();
                try {
                    $pdo->prepare(
                        'INSERT INTO `BankAccount` (id, campaignId, label, bankName, bankCode, agency, agencyDv, accountNumber, accountDv, accountType, resourceOrigin, openedAt, racNumber, balance, depositCpf, depositCnpj, statementPdfPath, active, sortOrder, createdAt, updatedAt)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?,?)'
                    )->execute([
                        $newId, $cid, $label, $bankName, $bankCode, $agency, $agencyDvVal, $accountNumber, $accountDvVal, $accountType,
                        $originVal, $openedAt, $racVal, $balance, $depositCpf, $depositCnpj, $statementPath, $activeCount, $now, $now,
                    ]);
                } catch (Throwable) {
                    $pdo->prepare(
                        'INSERT INTO `BankAccount` (id, campaignId, label, bankName, bankCode, agency, accountNumber, accountType, resourceOrigin, openedAt, balance, depositCpf, depositCnpj, active, sortOrder, createdAt, updatedAt)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?,?)'
                    )->execute([
                        $newId, $cid, $label, $bankName, $bankCode, $agency, $accountNumber, $accountType,
                        $originVal, $openedAt, $balance, $depositCpf, $depositCnpj, $activeCount, $now, $now,
                    ]);
                }
                audit_log($user['id'], 'CREATE', 'BankAccount', $newId, "Cadastrou conta {$label} · saldo inicial " . money_br($balance));
                flash_set('ok', 'Conta criada.');
            }
            redirect('/admin/contas.php');
        }
    }
}

$accounts = $campaign ? $pdo->prepare('SELECT * FROM `BankAccount` WHERE campaignId=? ORDER BY active DESC, sortOrder ASC, createdAt ASC') : null;
if ($accounts) {
    $accounts->execute([$campaign['id']]);
    $accounts = $accounts->fetchAll();
} else {
    $accounts = [];
}
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="row-actions" style="margin-bottom:1rem">
  <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Voltar</a>
  <?php if ($canWrite): ?>
    <a class="btn btn-primary" href="#form-conta"><?= $edit ? 'Editar formulário' : 'Nova conta' ?></a>
  <?php endif; ?>
</div>

<div class="grid grid-2">
  <div class="stack">
    <?php foreach ($accounts as $i => $a): ?>
      <?php
        $originCode = (string) ($a['resourceOrigin'] ?? '');
        $originLabel = $originCode !== ''
          ? ElectoralRules::bankOriginLabel($originCode)
          : '';
      ?>
      <div class="panel">
        <div class="row-actions" style="justify-content:space-between">
          <div>
            <div class="muted">Conta <?= $i + 1 ?></div>
            <div class="display"><?= e($a['label']) ?></div>
          </div>
          <div class="row-actions" style="gap:.35rem;flex-wrap:wrap;justify-content:flex-end">
            <?php if ($originLabel !== ''): ?>
              <span class="badge badge-info"><?= e($originLabel) ?></span>
            <?php endif; ?>
            <span class="badge <?= $a['active'] ? 'badge-ok' : 'badge-warn' ?>"><?= $a['active'] ? 'Ativa' : 'Inativa' ?></span>
          </div>
        </div>
        <div class="muted"><?= e($a['bankName']) ?> (<?= e($a['bankCode']) ?>)</div>
        <div>Ag <?= e($a['agency']) ?> · Cc <?= e($a['accountNumber']) ?> · <?= e($a['accountType']) ?></div>
        <?php if (!empty($a['openedAt'])): ?>
          <div class="muted" style="margin-top:.25rem;font-size:.8rem">Abertura: <?= e(date_br(substr((string) $a['openedAt'], 0, 10))) ?></div>
        <?php endif; ?>
        <?php
          $who = [];
          if (!empty($a['depositCpf'])) {
              $who[] = 'CPF';
          }
          if (!empty($a['depositCnpj'])) {
              $who[] = 'CNPJ';
          }
        ?>
        <div class="muted" style="margin-top:.35rem;font-size:.8rem">
          Depósito receita: <?= $who ? e(implode(' · ', $who)) : '—' ?>
        </div>
        <div class="display" style="margin-top:.5rem"><?= e(money_br($a['balance'])) ?></div>
        <?php if ($canWrite): ?>
        <div class="row-actions" style="margin-top:.75rem">
          <a class="btn btn-ghost" href="<?= e(url_path('admin/contas.php?id=' . rawurlencode($a['id']))) ?>#form-conta">Editar</a>
          <form method="post">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= e($a['id']) ?>">
            <input type="hidden" name="active" value="<?= $a['active'] ? 0 : 1 ?>">
            <button class="btn btn-secondary" type="submit"><?= $a['active'] ? 'Desativar' : 'Ativar' ?></button>
          </form>
          <form method="post" onsubmit="return confirm('Excluir conta e TODOS os vínculos (extrato, ajustes, mapeamentos)?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= e($a['id']) ?>">
            <button class="btn btn-danger" type="submit">Excluir</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$accounts): ?><div class="panel empty">Nenhuma conta cadastrada.</div><?php endif; ?>
  </div>

  <?php if ($canWrite): ?>
  <div class="panel form-card je-section" id="form-conta">
    <div class="je-kicker">Conta+JE §7.5 · Contas bancárias de campanha</div>
    <h3 class="display" style="margin-top:.2rem"><?= $edit ? 'Editar conta bancária' : 'Cadastrar conta bancária' ?></h3>
    <p class="muted" style="margin-top:0">Informe Tipo/Fonte, Banco, Agência+DV, Conta+DV e data de abertura (RAC em até 10 dias do CNPJ).</p>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" data-mask-form>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
      <div class="field"><label class="label req">Rótulo / identificação</label><input class="input" name="label" value="<?= e((string) ($edit['label'] ?? '')) ?>" required></div>
      <div class="grid grid-2">
        <div class="field"><label class="label req">Banco</label><input class="input" name="bankName" value="<?= e((string) ($edit['bankName'] ?? '')) ?>" required></div>
        <div class="field"><label class="label req">Código do banco</label><input class="input" name="bankCode" value="<?= e((string) ($edit['bankCode'] ?? '')) ?>" required></div>
      </div>
      <div class="grid grid-2">
        <div class="field"><label class="label req">Agência</label><input class="input" name="agency" value="<?= e((string) ($edit['agency'] ?? '')) ?>" required></div>
        <div class="field"><label class="label">DV da agência</label><input class="input" name="agencyDv" maxlength="4" value="<?= e((string) ($edit['agencyDv'] ?? '')) ?>"></div>
      </div>
      <div class="grid grid-2">
        <div class="field"><label class="label req">Conta</label><input class="input" name="accountNumber" value="<?= e((string) ($edit['accountNumber'] ?? '')) ?>" required></div>
        <div class="field"><label class="label">DV da conta</label><input class="input" name="accountDv" maxlength="4" value="<?= e((string) ($edit['accountDv'] ?? '')) ?>"></div>
      </div>
      <div class="grid grid-2">
        <div class="field"><label class="label">Tipo</label><input class="input" name="accountType" value="<?= e((string) ($edit['accountType'] ?? 'Corrente')) ?>"></div>
        <?php if (!$edit): ?>
        <div class="field"><label class="label">Saldo inicial</label><input class="input" name="balance" data-mask="money" inputmode="decimal" value="0,00" placeholder="0,00"></div>
        <?php else: ?>
        <div class="field"><label class="label">Saldo atual</label><input class="input" value="<?= e(money_br((float) $edit['balance'])) ?>" disabled><div class="muted" style="font-size:.8rem">Ajuste em Saldos</div></div>
        <?php endif; ?>
      </div>
      <?php
        $selOrigin = request_method() === 'POST'
          ? (string) post('resourceOrigin', '')
          : (string) ($edit['resourceOrigin'] ?? '');
        $openedVal = request_method() === 'POST'
          ? (string) post('openedAt', '')
          : (!empty($edit['openedAt']) ? substr((string) $edit['openedAt'], 0, 10) : '');
      ?>
      <div class="grid grid-2">
        <div class="field">
          <label class="label req">Fonte do recurso (Conta+JE)</label>
          <select class="select" name="resourceOrigin" required>
            <option value="">— selecionar —</option>
            <?php foreach (BANK_RESOURCE_ORIGINS as $code => $lab): ?>
              <option value="<?= e($code) ?>" <?= $selOrigin === $code ? 'selected' : '' ?>><?= e($lab) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="muted" style="font-size:.78rem;margin-top:.3rem">
            Conta+JE §7.5 / TRE-GO: Doações para Campanha, FEFC ou Fundo Partidário (contas separadas).
          </div>
        </div>
        <div class="field">
          <label class="label">Data de abertura</label>
          <input class="input" type="date" name="openedAt" max="<?= e((new DateTimeImmutable('today'))->format('Y-m-d')) ?>" value="<?= e($openedVal) ?>">
          <div class="muted" style="font-size:.78rem;margin-top:.3rem">Prazo: 10 dias após o CNPJ (RAC).</div>
        </div>
      </div>
      <div class="field">
        <label class="label">Nº RAC / protocolo</label>
        <input class="input" name="racNumber" value="<?= e((string) ($edit['racNumber'] ?? '')) ?>" placeholder="Requerimento de Abertura de Conta">
      </div>
      <div class="field">
        <label class="label">Extrato bancário PDF (Conta+JE)</label>
        <input class="input" type="file" name="statementPdf" accept="application/pdf,.pdf">
        <?php if (!empty($edit['statementPdfPath'])): ?>
          <div class="muted" style="font-size:.78rem;margin-top:.3rem">
            Arquivo atual: <a href="<?= e(url_path(ltrim((string) $edit['statementPdfPath'], '/'))) ?>" target="_blank" rel="noopener">abrir PDF</a>
          </div>
        <?php else: ?>
          <div class="muted" style="font-size:.78rem;margin-top:.3rem">PDF até 10 MB — incluso no pacote de entrega ao Conta+JE.</div>
        <?php endif; ?>
      </div>
      <?php
        $checkCpf = request_method() === 'POST'
          ? (bool) post('depositCpf')
          : ($edit ? !empty($edit['depositCpf']) : true);
        $checkCnpj = request_method() === 'POST'
          ? (bool) post('depositCnpj')
          : ($edit ? !empty($edit['depositCnpj']) : true);
        if (request_method() !== 'POST' && !$edit && $selOrigin !== '') {
            $flags = ElectoralRules::depositFlagsForOrigin($selOrigin);
            $checkCpf = $flags['cpf'];
            $checkCnpj = $flags['cnpj'];
        }
      ?>
      <div class="field deposit-who">
        <label class="label">Quem pode depositar nessa Conta</label>
        <div class="deposit-who-options">
          <label class="deposit-who-option">
            <input type="checkbox" name="depositCpf" value="1" <?= $checkCpf ? 'checked' : '' ?>>
            <span>CPF</span>
          </label>
          <label class="deposit-who-option">
            <input type="checkbox" name="depositCnpj" value="1" <?= $checkCnpj ? 'checked' : '' ?>>
            <span>CNPJ</span>
          </label>
        </div>
        <div class="muted" style="font-size:.78rem;margin-top:.3rem">Indica quem pode realizar depósito de receita nesta conta. A fonte do recurso sugere CPF (Doações) ou CNPJ (Fundo Partidário / FEFC).</div>
      </div>
      <div class="row-actions">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-ghost" href="<?= e(url_path($edit ? 'admin/contas.php' : 'admin/index.php')) ?>">Voltar</a>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
