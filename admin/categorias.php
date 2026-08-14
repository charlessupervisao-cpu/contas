<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('categorias');
$activeModule = 'categorias';
$pageTitle = '4 · Naturezas de despesa';
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$editId = trim((string) get('id', ''));
$edit = null;
$errors = [];

if ($editId !== '') {
    $st = $pdo->prepare('SELECT * FROM `ExpenseCategory` WHERE id=? LIMIT 1');
    $st->execute([$editId]);
    $edit = $st->fetch() ?: null;
}

if (request_method() === 'POST' && $canWrite) {
    Auth::requireMaster();
    $action = (string) post('action', 'save');
    $now = now_sql();

    if ($action === 'toggle') {
        $id = (string) post('id');
        $active = (int) post('active');
        $pdo->prepare('UPDATE `ExpenseCategory` SET active=?, updatedAt=? WHERE id=?')
            ->execute([$active, $now, $id]);
        Categories::resetCache();
        audit_log($user['id'], 'UPDATE', 'ExpenseCategory', $id, $active ? 'Ativou categoria' : 'Desativou categoria');
        flash_set('ok', $active ? 'Categoria ativada.' : 'Categoria desativada.');
        redirect('/admin/categorias.php');
    }

    if ($action === 'delete') {
        $id = (string) post('id');
        $st = $pdo->prepare('SELECT code, label FROM `ExpenseCategory` WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) {
            $used = $pdo->prepare('SELECT COUNT(*) AS c FROM `Expense` WHERE category=?');
            $used->execute([$row['code']]);
            if ((int) $used->fetch()['c'] > 0) {
                flash_set('danger', 'Há lançamentos com esta categoria. Desative em vez de excluir.');
                redirect('/admin/categorias.php');
            }
            $pdo->prepare('DELETE FROM `AccountMapping` WHERE kind=? AND code=?')
                ->execute(['EXPENSE_CATEGORY', $row['code']]);
            $pdo->prepare('DELETE FROM `ExpenseCategory` WHERE id=?')->execute([$id]);
            Categories::resetCache();
            audit_log($user['id'], 'DELETE', 'ExpenseCategory', $id, 'Excluiu categoria ' . $row['label']);
            flash_set('ok', 'Categoria excluída.');
        }
        redirect('/admin/categorias.php');
    }

    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        $label = trim((string) post('label', ''));
        $code = Categories::normalizeCode((string) post('code', $label));
        $color = trim((string) post('color', '#0d9488')) ?: '#0d9488';
        if ($color !== '' && $color[0] !== '#') {
            $color = '#' . $color;
        }
        $sortOrder = (int) post('sortOrder', '0');

        if ($label === '' || $code === '') {
            $errors[] = 'Preencha o nome e o código da categoria.';
        }
        if (!$errors) {
            $dup = $pdo->prepare('SELECT id FROM `ExpenseCategory` WHERE code=? AND id<>? LIMIT 1');
            $dup->execute([$code, $id]);
            if ($dup->fetch()) {
                $errors[] = 'Já existe uma categoria com este código.';
            }
        }

        if (!$errors) {
            if ($id !== '') {
                $pdo->prepare(
                    'UPDATE `ExpenseCategory` SET code=?, label=?, color=?, sortOrder=?, updatedAt=? WHERE id=?'
                )->execute([$code, $label, $color, $sortOrder, $now, $id]);
                audit_log($user['id'], 'UPDATE', 'ExpenseCategory', $id, "Atualizou categoria {$label}");
                flash_set('ok', 'Categoria atualizada.');
            } else {
                $newId = cuid();
                $max = (int) $pdo->query('SELECT COALESCE(MAX(sortOrder),-1) AS m FROM `ExpenseCategory`')->fetch()['m'];
                if ($sortOrder <= 0) {
                    $sortOrder = $max + 1;
                }
                $pdo->prepare(
                    'INSERT INTO `ExpenseCategory` (id, code, label, color, active, sortOrder, createdAt, updatedAt)
                     VALUES (?,?,?,?,1,?,?,?)'
                )->execute([$newId, $code, $label, $color, $sortOrder, $now, $now]);
                audit_log($user['id'], 'CREATE', 'ExpenseCategory', $newId, "Cadastrou categoria {$label}");
                flash_set('ok', 'Categoria criada.');
            }
            Categories::resetCache();
            redirect('/admin/categorias.php');
        }
    }
}

$rows = $pdo->query('SELECT * FROM `ExpenseCategory` ORDER BY active DESC, sortOrder ASC, label ASC')->fetchAll();
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="row-actions" style="margin-bottom:1rem">
  <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Voltar</a>
  <?php if ($canWrite): ?>
    <a class="btn btn-primary" href="#form-categoria"><?= $edit ? 'Editar formulário' : 'Nova categoria' ?></a>
  <?php endif; ?>
</div>

<div class="grid grid-2">
  <div class="stack">
    <?php foreach ($rows as $i => $a): ?>
      <div class="panel">
        <div class="row-actions" style="justify-content:space-between">
          <div>
            <div class="muted">#<?= (int) $a['sortOrder'] ?> · <?= e((string) $a['code']) ?></div>
            <div class="display" style="display:flex;align-items:center;gap:.5rem">
              <span style="width:.85rem;height:.85rem;border-radius:999px;background:<?= e((string) $a['color']) ?>;display:inline-block;flex:0 0 auto"></span>
              <?= e((string) $a['label']) ?>
            </div>
          </div>
          <span class="badge <?= !empty($a['active']) ? 'badge-ok' : 'badge-warn' ?>"><?= !empty($a['active']) ? 'Ativa' : 'Inativa' ?></span>
        </div>
        <?php if ($canWrite): ?>
        <div class="row-actions" style="margin-top:.75rem">
          <a class="btn btn-ghost" href="<?= e(url_path('admin/categorias.php?id=' . rawurlencode((string) $a['id']))) ?>#form-categoria">Editar</a>
          <form method="post">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= e((string) $a['id']) ?>">
            <input type="hidden" name="active" value="<?= !empty($a['active']) ? 0 : 1 ?>">
            <button class="btn btn-secondary" type="submit"><?= !empty($a['active']) ? 'Desativar' : 'Ativar' ?></button>
          </form>
          <form method="post" onsubmit="return confirm('Excluir categoria? Só é permitido se não houver lançamentos.');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= e((string) $a['id']) ?>">
            <button class="btn btn-danger" type="submit">Excluir</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="panel empty">Nenhuma categoria cadastrada.</div><?php endif; ?>
  </div>

  <?php if ($canWrite): ?>
  <div class="panel form-card" id="form-categoria">
    <h3 class="display" style="margin-top:0"><?= $edit ? 'Editar categoria' : 'Nova categoria' ?></h3>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
      <div class="field">
        <label class="label">Nome</label>
        <input class="input" name="label" value="<?= e((string) ($edit['label'] ?? post('label', ''))) ?>" required placeholder="Ex.: Comitê">
      </div>
      <div class="field">
        <label class="label">Código</label>
        <input class="input" name="code" value="<?= e((string) ($edit['code'] ?? post('code', ''))) ?>" placeholder="Ex.: COMITE" style="text-transform:uppercase">
        <div class="muted" style="font-size:.78rem;margin-top:.25rem">Usado nos lançamentos e vínculos. Se vazio, é gerado a partir do nome.</div>
      </div>
      <div class="grid grid-2">
        <div class="field">
          <label class="label">Cor</label>
          <input class="input" type="color" name="color" value="<?= e((string) ($edit['color'] ?? '#0d9488')) ?>">
        </div>
        <div class="field">
          <label class="label">Ordem</label>
          <input class="input" type="number" name="sortOrder" value="<?= e((string) ($edit['sortOrder'] ?? '0')) ?>">
        </div>
      </div>
      <div class="row-actions">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-ghost" href="<?= e(url_path($edit ? 'admin/categorias.php' : 'admin/index.php')) ?>">Voltar</a>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
