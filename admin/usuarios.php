<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('usuarios');
$activeModule = 'usuarios';
$pageTitle = 'Usuários / Perfis';
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$errors = [];
$editId = trim((string) get('id', ''));
$edit = null;

if ($editId !== '') {
    $st = $pdo->prepare('SELECT id,name,email,role,active FROM `User` WHERE id=? LIMIT 1');
    $st->execute([$editId]);
    $edit = $st->fetch() ?: null;
}

if (request_method() === 'POST' && $canWrite) {
    Auth::requireMaster();
    $action = (string) post('action', 'save');
    $now = now_sql();

    if ($action === 'delete') {
        $id = (string) post('id');
        if ($id === $user['id']) {
            flash_set('danger', 'Você não pode excluir o próprio usuário.');
            redirect('/admin/usuarios.php');
        }
        $st = $pdo->prepare('SELECT name,email FROM `User` WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) {
            $pdo->prepare('UPDATE `Revenue` SET createdById=NULL WHERE createdById=?')->execute([$id]);
            $pdo->prepare('UPDATE `Expense` SET createdById=NULL WHERE createdById=?')->execute([$id]);
            $pdo->prepare('UPDATE `BalanceAdjustment` SET createdById=NULL WHERE createdById=?')->execute([$id]);
            $pdo->prepare('UPDATE `AuditLog` SET userId=NULL WHERE userId=?')->execute([$id]);
            $pdo->prepare('DELETE FROM `User` WHERE id=?')->execute([$id]);
            audit_log($user['id'], 'DELETE', 'User', $id, 'Excluiu usuário ' . $row['name'] . ' (' . $row['email'] . ')');
            flash_set('ok', 'Usuário excluído (vínculos de criação desassociados).');
        }
        redirect('/admin/usuarios.php');
    }

    if ($action === 'toggle') {
        $id = (string) post('id');
        if ($id === $user['id']) {
            flash_set('danger', 'Você não pode desativar o próprio usuário.');
            redirect('/admin/usuarios.php');
        }
        $active = (int) post('active');
        $pdo->prepare('UPDATE `User` SET active=?, updatedAt=? WHERE id=?')->execute([$active, $now, $id]);
        audit_log($user['id'], 'UPDATE', 'User', $id, $active ? 'Ativou usuário' : 'Desativou usuário');
        flash_set('ok', $active ? 'Usuário ativado.' : 'Usuário desativado.');
        redirect('/admin/usuarios.php');
    }

    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        $name = trim((string) post('name', ''));
        $email = strtolower(trim((string) post('email', '')));
        $role = normalize_role((string) post('role', 'CONSULTA'));
        $password = (string) post('password', '');
        $active = post('active') ? 1 : 0;

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe nome e e-mail válidos.';
        }
        if (!isset(ROLE_LABELS[$role])) {
            $errors[] = 'Perfil inválido.';
        }
        if ($id === '' && strlen($password) < 6) {
            $errors[] = 'Senha obrigatória (mín. 6 caracteres) para novo usuário.';
        }
        if ($password !== '' && strlen($password) < 6) {
            $errors[] = 'Senha deve ter ao menos 6 caracteres.';
        }

        $dup = $pdo->prepare('SELECT id FROM `User` WHERE email=? AND id<>? LIMIT 1');
        $dup->execute([$email, $id !== '' ? $id : '-']);
        if ($dup->fetch()) {
            $errors[] = 'Já existe usuário com este e-mail.';
        }

        if (!$errors) {
            if ($id !== '') {
                if ($password !== '') {
                    $pdo->prepare(
                        'UPDATE `User` SET name=?, email=?, role=?, active=?, passwordHash=?, updatedAt=? WHERE id=?'
                    )->execute([$name, $email, $role, $active, password_hash($password, PASSWORD_BCRYPT), $now, $id]);
                } else {
                    $pdo->prepare(
                        'UPDATE `User` SET name=?, email=?, role=?, active=?, updatedAt=? WHERE id=?'
                    )->execute([$name, $email, $role, $active, $now, $id]);
                }
                audit_log($user['id'], 'UPDATE', 'User', $id, "Atualizou usuário {$name} ({$email}) · perfil {$role}");
                flash_set('ok', 'Usuário atualizado.');
            } else {
                $newId = cuid();
                $pdo->prepare(
                    'INSERT INTO `User` (id,name,email,passwordHash,role,active,createdAt,updatedAt) VALUES (?,?,?,?,?,?,?,?)'
                )->execute([$newId, $name, $email, password_hash($password, PASSWORD_BCRYPT), $role, $active, $now, $now]);
                audit_log($user['id'], 'CREATE', 'User', $newId, "Cadastrou usuário {$name} ({$email}) · perfil {$role}");
                flash_set('ok', 'Usuário cadastrado.');
            }
            redirect('/admin/usuarios.php');
        }
    }
}

$rows = $pdo->query('SELECT id,name,email,role,active,createdAt FROM `User` ORDER BY role, name')->fetchAll();
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="row-actions" style="margin-bottom:1rem">
  <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Voltar</a>
  <?php if ($canWrite): ?>
    <a class="btn btn-primary" href="#form-usuario"><?= $edit ? 'Editar formulário' : 'Novo usuário' ?></a>
  <?php endif; ?>
</div>

<div class="panel" style="margin-bottom:1rem">
  <p class="muted" style="margin-top:0">Acesso por perfil. Somente <strong>Master</strong> pode lançar e gerenciar cadastros.</p>
  <div class="grid grid-2">
    <?php foreach (PROFILE_MATRIX as $code => $p): ?>
      <div class="glass" style="padding:1rem;border-radius:1rem">
        <div class="row-actions" style="justify-content:space-between">
          <strong><?= e($p['label']) ?></strong>
          <span class="badge <?= $p['canLaunch'] ? 'badge-ok' : 'badge-info' ?>"><?= $p['canLaunch'] ? 'Pode lançar' : 'Somente leitura' ?></span>
        </div>
        <div class="muted" style="margin-top:.35rem;font-size:.9rem"><?= e($p['description']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="panel table-wrap m-list-desktop">
<table class="data">
  <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): $role = normalize_role($r['role']); ?>
    <tr>
      <td><?= e($r['name']) ?></td>
      <td><?= e($r['email']) ?></td>
      <td><?= e(ROLE_LABELS[$role] ?? $role) ?></td>
      <td><span class="badge <?= $r['active'] ? 'badge-ok' : 'badge-warn' ?>"><?= $r['active'] ? 'Ativo' : 'Inativo' ?></span></td>
      <td>
        <?php if ($canWrite): ?>
        <div class="row-actions">
          <a class="btn btn-ghost" href="<?= e(url_path('admin/usuarios.php?id=' . rawurlencode($r['id']))) ?>#form-usuario">Editar</a>
          <?php if ($r['id'] !== $user['id']): ?>
          <form method="post">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= e($r['id']) ?>">
            <input type="hidden" name="active" value="<?= $r['active'] ? 0 : 1 ?>">
            <button class="btn btn-secondary" type="submit"><?= $r['active'] ? 'Desativar' : 'Ativar' ?></button>
          </form>
          <form method="post" onsubmit="return confirm('Excluir este usuário e desassociar vínculos?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= e($r['id']) ?>">
            <button class="btn btn-danger" type="submit">Excluir</button>
          </form>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="m-cards" aria-label="Lista de usuários">
  <?php if (!$rows): ?><div class="panel empty">Nenhum usuário.</div><?php endif; ?>
  <?php foreach ($rows as $r): $role = normalize_role($r['role']); ?>
    <article class="m-card">
      <header class="m-card-head">
        <div>
          <div class="m-card-kicker"><?= e($r['email']) ?> · <?= e(ROLE_LABELS[$role] ?? $role) ?></div>
          <div class="m-card-title"><?= e($r['name']) ?></div>
        </div>
        <span class="badge <?= $r['active'] ? 'badge-ok' : 'badge-warn' ?>"><?= $r['active'] ? 'Ativo' : 'Inativo' ?></span>
      </header>
      <dl class="m-card-meta">
        <div><dt>Perfil</dt><dd><?= e(ROLE_LABELS[$role] ?? $role) ?></dd></div>
        <div><dt>E-mail</dt><dd><?= e($r['email']) ?></dd></div>
      </dl>
      <?php if ($canWrite): ?>
      <footer class="m-card-actions">
        <a class="btn btn-ghost" href="<?= e(url_path('admin/usuarios.php?id=' . rawurlencode($r['id']))) ?>#form-usuario">Editar</a>
        <?php if ($r['id'] !== $user['id']): ?>
        <form method="post">
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= e($r['id']) ?>">
          <input type="hidden" name="active" value="<?= $r['active'] ? 0 : 1 ?>">
          <button class="btn btn-secondary" type="submit"><?= $r['active'] ? 'Desativar' : 'Ativar' ?></button>
        </form>
        <form method="post" onsubmit="return confirm('Excluir este usuário e desassociar vínculos?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= e($r['id']) ?>">
          <button class="btn btn-danger" type="submit">Excluir</button>
        </form>
        <?php endif; ?>
      </footer>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>

<?php if ($canWrite): ?>
<div class="panel form-card" id="form-usuario" style="margin-top:1rem">
  <h3 class="display" style="margin-top:0"><?= $edit ? 'Editar usuário' : 'Novo usuário' ?></h3>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
  <?php endif; ?>
  <form method="post">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
    <div class="field"><label class="label">Nome</label><input class="input" name="name" value="<?= e((string) ($edit['name'] ?? '')) ?>" required></div>
    <div class="field"><label class="label">E-mail</label><input class="input" type="email" name="email" value="<?= e((string) ($edit['email'] ?? '')) ?>" required></div>
    <div class="grid grid-2">
      <div class="field">
        <label class="label">Perfil</label>
        <select class="input" name="role" required>
          <?php foreach (ROLE_LABELS as $code => $label): ?>
            <option value="<?= e($code) ?>" <?= (($edit['role'] ?? 'CONSULTA') === $code) ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="label">Senha <?= $edit ? '(deixe em branco para manter)' : '' ?></label>
        <div class="password-field">
          <input class="input" type="password" name="password" id="user-password" <?= $edit ? '' : 'required' ?> minlength="6" autocomplete="new-password">
          <button type="button" class="password-toggle" data-password-toggle aria-label="Mostrar senha" title="Mostrar senha">
            <span class="pw-icon-show" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </span>
            <span class="pw-icon-hide" aria-hidden="true" hidden>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </span>
          </button>
        </div>
      </div>
    </div>
    <label class="row-actions" style="margin:.75rem 0">
      <input type="checkbox" name="active" value="1" <?= !isset($edit['active']) || !empty($edit['active']) ? 'checked' : '' ?>> Ativo
    </label>
    <div class="row-actions">
      <button class="btn btn-primary" type="submit">Salvar</button>
      <?php if ($edit): ?>
        <a class="btn btn-ghost" href="<?= e(url_path('admin/usuarios.php')) ?>">Voltar</a>
      <?php else: ?>
        <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">Voltar</a>
      <?php endif; ?>
    </div>
  </form>
</div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
