<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('representantes');
$activeModule = 'representantes';
$pageTitle = 'Representantes legais';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$editId = trim((string) get('id', ''));
$edit = null;
$errors = [];
$roles = REPRESENTATIVE_ROLES;

$tableOk = false;
try {
    $st = $pdo->prepare(
        'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1'
    );
    $st->execute(['Representative']);
    $tableOk = (bool) $st->fetch();
} catch (Throwable) {
}

if (!$tableOk) {
    flash_set('danger', 'Tabela de representantes ainda não disponível. Atualize o schema.');
    redirect('/admin/index.php');
}

if ($editId !== '' && $campaign) {
    $st = $pdo->prepare('SELECT * FROM `Representative` WHERE id=? AND campaignId=? LIMIT 1');
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
        $pdo->prepare('UPDATE `Representative` SET active=?, updatedAt=? WHERE id=? AND campaignId=?')
            ->execute([$active, $now, $id, $cid]);
        audit_log($user['id'], 'UPDATE', 'Representative', $id, $active ? 'Ativou representante' : 'Desativou representante');
        flash_set('ok', $active ? 'Representante ativado.' : 'Representante desativado.');
        redirect('/admin/representantes.php');
    }

    if ($action === 'delete') {
        $id = (string) post('id');
        $st = $pdo->prepare('SELECT name, role FROM `Representative` WHERE id=? AND campaignId=? LIMIT 1');
        $st->execute([$id, $cid]);
        $row = $st->fetch();
        if ($row) {
            $pdo->prepare('DELETE FROM `Representative` WHERE id=? AND campaignId=?')->execute([$id, $cid]);
            audit_log($user['id'], 'DELETE', 'Representative', $id, 'Excluiu ' . $row['name'] . ' · ' . ($roles[$row['role']] ?? $row['role']));
            flash_set('ok', 'Representante excluído.');
        }
        redirect('/admin/representantes.php');
    }

    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        $role = trim((string) post('role', ''));
        $name = trim((string) post('name', ''));
        $cpfRaw = trim((string) post('cpf', ''));
        $cpf = $cpfRaw !== '' ? only_digits($cpfRaw) : '';
        $email = trim((string) post('email', '')) ?: null;
        $phone = trim((string) post('phone', '')) ?: null;
        $oabUf = strtoupper(trim((string) post('oabUf', ''))) ?: null;
        $oabNumber = trim((string) post('oabNumber', '')) ?: null;
        $crcUf = strtoupper(trim((string) post('crcUf', ''))) ?: null;
        $crcNumber = trim((string) post('crcNumber', '')) ?: null;
        $roleOther = trim((string) post('roleOther', '')) ?: null;
        $notes = trim((string) post('notes', '')) ?: null;

        if ($name === '') {
            $errors[] = 'Informe o nome.';
        }
        if ($role === '' || !isset($roles[$role])) {
            $errors[] = 'Selecione a função (Conta+JE §7.3).';
        }
        if ($cpf !== '' && !is_valid_cpf($cpf)) {
            $errors[] = 'CPF inválido.';
        }
        if ($role === 'ADVOGADO') {
            if ($oabUf === null || $oabNumber === null) {
                $errors[] = 'Advogado(a): informe OAB (UF e número).';
            }
        }
        if ($role === 'CONTABILISTA') {
            if ($crcUf === null || $crcNumber === null) {
                $errors[] = 'Contabilista: informe CRC (UF e número).';
            }
        }
        if ($role === 'OUTROS' && ($roleOther === null || $roleOther === '')) {
            $errors[] = 'Descreva a função em “Outros”.';
        }

        if (!$errors && $campaign) {
            $cpfFmt = $cpf !== '' ? format_cpf_cnpj($cpf) : null;
            if ($id !== '') {
                $pdo->prepare(
                    'UPDATE `Representative` SET role=?, name=?, cpf=?, email=?, phone=?, oabUf=?, oabNumber=?, crcUf=?, crcNumber=?, roleOther=?, notes=?, updatedAt=?
                     WHERE id=? AND campaignId=?'
                )->execute([
                    $role, $name, $cpfFmt, $email, $phone, $oabUf, $oabNumber, $crcUf, $crcNumber, $roleOther, $notes, $now, $id, $cid,
                ]);
                audit_log($user['id'], 'UPDATE', 'Representative', $id, "Atualizou {$name} · " . ($roles[$role] ?? $role));
                flash_set('ok', 'Representante atualizado.');
            } else {
                $newId = cuid();
                $pdo->prepare(
                    'INSERT INTO `Representative` (id, campaignId, role, name, cpf, email, phone, oabUf, oabNumber, crcUf, crcNumber, roleOther, active, notes, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,?,?,?)'
                )->execute([
                    $newId, $cid, $role, $name, $cpfFmt, $email, $phone, $oabUf, $oabNumber, $crcUf, $crcNumber, $roleOther, $notes, $now, $now,
                ]);
                audit_log($user['id'], 'CREATE', 'Representative', $newId, "Cadastrou {$name} · " . ($roles[$role] ?? $role));
                flash_set('ok', 'Representante cadastrado.');
            }
            redirect('/admin/representantes.php');
        }
    }
}

$rows = [];
if ($campaign) {
    $st = $pdo->prepare('SELECT * FROM `Representative` WHERE campaignId=? ORDER BY active DESC, role ASC, name ASC');
    $st->execute([$campaign['id']]);
    $rows = $st->fetchAll();
}
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-toolbar filter-panel">
  <div class="page-toolbar-main">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Painel</a>
    <div>
      <div class="fin-kicker" style="margin:0">Conta+JE §7.3 · Representação legal</div>
      <strong style="font-size:1.05rem">Representantes da prestação</strong>
    </div>
  </div>
  <div class="page-toolbar-side">
    <?php if ($canWrite): ?>
      <a class="btn btn-primary" href="#form-rep"><?= $edit ? 'Editar formulário' : 'Novo representante' ?></a>
    <?php endif; ?>
    <a class="btn btn-ghost" href="<?= e(url_path('admin/entrega.php')) ?>">Entrega TSE</a>
  </div>
</div>

<div class="je-banner">
  Cadastre advogado(a) com OAB, contabilista com CRC e demais responsáveis — os mesmos papéis do Conta+JE.
</div>

<div class="grid grid-2">
  <div class="stack">
    <?php foreach ($rows as $r): ?>
      <div class="panel">
        <div class="row-actions" style="justify-content:space-between;align-items:flex-start">
          <div>
            <div class="muted"><?= e($roles[$r['role']] ?? $r['role']) ?><?php if ($r['role'] === 'OUTROS' && !empty($r['roleOther'])): ?> — <?= e((string) $r['roleOther']) ?><?php endif; ?></div>
            <div class="display"><?= e($r['name']) ?></div>
          </div>
          <span class="badge <?= !empty($r['active']) ? 'badge-ok' : 'badge-warn' ?>"><?= !empty($r['active']) ? 'Ativo' : 'Inativo' ?></span>
        </div>
        <div class="muted" style="margin-top:.35rem;font-size:.85rem">
          <?php if (!empty($r['cpf'])): ?>CPF <?= e((string) $r['cpf']) ?> · <?php endif; ?>
          <?php if ($r['role'] === 'ADVOGADO' && (!empty($r['oabNumber']) || !empty($r['oabUf']))): ?>
            OAB <?= e(trim(($r['oabUf'] ?? '') . ' ' . ($r['oabNumber'] ?? ''))) ?>
          <?php elseif ($r['role'] === 'CONTABILISTA' && (!empty($r['crcNumber']) || !empty($r['crcUf']))): ?>
            CRC <?= e(trim(($r['crcUf'] ?? '') . ' ' . ($r['crcNumber'] ?? ''))) ?>
          <?php else: ?>
            <?= e((string) ($r['email'] ?? $r['phone'] ?? '—')) ?>
          <?php endif; ?>
        </div>
        <?php if ($canWrite): ?>
        <div class="row-actions" style="margin-top:.75rem">
          <a class="btn btn-ghost" href="<?= e(url_path('admin/representantes.php?id=' . rawurlencode($r['id']))) ?>#form-rep">Editar</a>
          <form method="post">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= e($r['id']) ?>">
            <input type="hidden" name="active" value="<?= !empty($r['active']) ? 0 : 1 ?>">
            <button class="btn btn-secondary" type="submit"><?= !empty($r['active']) ? 'Desativar' : 'Ativar' ?></button>
          </form>
          <form method="post" onsubmit="return confirm('Excluir este representante?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= e($r['id']) ?>">
            <button class="btn btn-danger" type="submit">Excluir</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="panel empty">Nenhum representante cadastrado.</div><?php endif; ?>
  </div>

  <?php if ($canWrite): ?>
  <div class="panel form-card je-section" id="form-rep">
    <div class="je-kicker">Cadastro Conta+JE</div>
    <h3 class="display" style="margin-top:.15rem"><?= $edit ? 'Editar representante' : 'Novo representante' ?></h3>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>
    <?php
      $formRole = request_method() === 'POST' ? (string) post('role', '') : (string) ($edit['role'] ?? 'ADVOGADO');
    ?>
    <form method="post" data-mask-form>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
      <div class="field">
        <label class="label">Função</label>
        <select class="select" name="role" required>
          <?php foreach ($roles as $code => $lab): ?>
            <option value="<?= e($code) ?>" <?= $formRole === $code ? 'selected' : '' ?>><?= e($lab) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label class="label">Nome</label><input class="input" name="name" value="<?= e((string) ($edit['name'] ?? post('name', ''))) ?>" required></div>
      <div class="grid grid-2">
        <div class="field"><label class="label">CPF</label><input class="input" name="cpf" data-mask="cpf" inputmode="numeric" value="<?= e((string) ($edit['cpf'] ?? post('cpf', ''))) ?>"></div>
        <div class="field"><label class="label">Telefone</label><input class="input" name="phone" data-mask="phone" value="<?= e((string) ($edit['phone'] ?? post('phone', ''))) ?>"></div>
      </div>
      <div class="field"><label class="label">E-mail</label><input class="input" type="email" name="email" value="<?= e((string) ($edit['email'] ?? post('email', ''))) ?>"></div>
      <div class="grid grid-2">
        <div class="field"><label class="label">OAB UF</label><input class="input" name="oabUf" maxlength="2" style="text-transform:uppercase" value="<?= e((string) ($edit['oabUf'] ?? post('oabUf', ''))) ?>" placeholder="GO"></div>
        <div class="field"><label class="label">OAB nº</label><input class="input" name="oabNumber" value="<?= e((string) ($edit['oabNumber'] ?? post('oabNumber', ''))) ?>"></div>
      </div>
      <div class="grid grid-2">
        <div class="field"><label class="label">CRC UF</label><input class="input" name="crcUf" maxlength="2" style="text-transform:uppercase" value="<?= e((string) ($edit['crcUf'] ?? post('crcUf', ''))) ?>" placeholder="GO"></div>
        <div class="field"><label class="label">CRC nº</label><input class="input" name="crcNumber" value="<?= e((string) ($edit['crcNumber'] ?? post('crcNumber', ''))) ?>"></div>
      </div>
      <div class="field"><label class="label">Outros (descrição)</label><input class="input" name="roleOther" value="<?= e((string) ($edit['roleOther'] ?? post('roleOther', ''))) ?>" placeholder="Se função = Outros"></div>
      <div class="field"><label class="label">Observações</label><textarea class="input" name="notes" rows="2"><?= e((string) ($edit['notes'] ?? post('notes', ''))) ?></textarea></div>
      <div class="row-actions">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-ghost" href="<?= e(url_path($edit ? 'admin/representantes.php' : 'admin/index.php')) ?>">Voltar</a>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
