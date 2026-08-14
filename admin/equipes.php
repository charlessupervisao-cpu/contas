<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('equipes');
$activeModule = 'equipes';
$pageTitle = '7 · Equipes';
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$editId = trim((string) get('id', ''));
$edit = null;
$errors = [];

if ($editId !== '') {
    $st = $pdo->prepare('SELECT * FROM `Team` WHERE id=? LIMIT 1');
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
        $pdo->prepare('UPDATE `Team` SET active=?, updatedAt=? WHERE id=?')
            ->execute([$active, $now, $id]);
        Teams::resetCache();
        audit_log($user['id'], 'UPDATE', 'Team', $id, $active ? 'Ativou equipe' : 'Desativou equipe');
        flash_set('ok', $active ? 'Integrante ativado.' : 'Integrante desativado.');
        redirect('/admin/equipes.php');
    }

    if ($action === 'delete') {
        $id = (string) post('id');
        $st = $pdo->prepare('SELECT id, label FROM `Team` WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) {
            $used = $pdo->prepare('SELECT COUNT(*) AS c FROM `CaboEleitoral` WHERE teamCode=?');
            $used->execute([$row['id']]);
            if ((int) $used->fetch()['c'] > 0) {
                flash_set('danger', 'Há cabos vinculados a este integrante. Desative em vez de excluir.');
                redirect('/admin/equipes.php');
            }
            $pdo->prepare('DELETE FROM `Team` WHERE id=?')->execute([$id]);
            Teams::resetCache();
            audit_log($user['id'], 'DELETE', 'Team', $id, 'Excluiu equipe ' . $row['label']);
            flash_set('ok', 'Integrante excluído.');
        }
        redirect('/admin/equipes.php');
    }

    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        $label = trim((string) post('label', ''));
        $phone = format_phone_br((string) post('phone', ''));
        $city = trim((string) post('city', ''));
        $cityRegion = trim((string) post('cityRegion', ''));
        $sortOrder = (int) post('sortOrder', '0');

        if ($label === '') {
            $errors[] = 'Preencha o nome do integrante.';
        }
        if ($phone !== '' && strlen(only_digits($phone)) < 10) {
            $errors[] = 'Informe um telefone válido com DDD.';
        }

        if (!$errors) {
            if ($id !== '') {
                $pdo->prepare(
                    'UPDATE `Team` SET label=?, phone=?, city=?, cityRegion=?, sortOrder=?, updatedAt=? WHERE id=?'
                )->execute([
                    $label, $phone !== '' ? $phone : null, $city !== '' ? $city : null,
                    $cityRegion !== '' ? $cityRegion : null, $sortOrder, $now, $id,
                ]);
                audit_log($user['id'], 'UPDATE', 'Team', $id, "Atualizou integrante {$label}");
                flash_set('ok', 'Integrante atualizado.');
            } else {
                $newId = cuid();
                $memberNumber = Teams::nextMemberNumber();
                $max = (int) $pdo->query('SELECT COALESCE(MAX(sortOrder),-1) AS m FROM `Team`')->fetch()['m'];
                if ($sortOrder <= 0) {
                    $sortOrder = $max + 1;
                }
                $pdo->prepare(
                    'INSERT INTO `Team` (id, memberNumber, label, phone, city, cityRegion, active, sortOrder, createdAt, updatedAt)
                     VALUES (?,?,?,?,?,?,1,?,?,?)'
                )->execute([
                    $newId, $memberNumber, $label,
                    $phone !== '' ? $phone : null, $city !== '' ? $city : null,
                    $cityRegion !== '' ? $cityRegion : null, $sortOrder, $now, $now,
                ]);
                audit_log($user['id'], 'CREATE', 'Team', $newId, "Cadastrou integrante {$label} · nº {$memberNumber}");
                flash_set('ok', 'Integrante cadastrado.');
            }
        }

        if (!$errors) {
            Teams::resetCache();
            redirect('/admin/equipes.php');
        }
    }
}

$rows = $pdo->query('SELECT * FROM `Team` ORDER BY active DESC, memberNumber ASC, label ASC')->fetchAll();
$nextNumber = Teams::nextMemberNumber();
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="row-actions" style="margin-bottom:1rem">
  <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Voltar</a>
  <?php if ($canWrite): ?>
    <a class="btn btn-primary" href="#form-equipe"><?= $edit ? 'Editar formulário' : 'Novo integrante' ?></a>
  <?php endif; ?>
</div>

<div class="grid grid-2">
  <div class="stack">
    <?php foreach ($rows as $a): ?>
      <div class="panel">
        <div class="row-actions" style="justify-content:space-between">
          <div>
            <div class="muted">Código <?= e((string) ($a['memberNumber'] ?? '—')) ?></div>
            <div class="display"><?= e((string) $a['label']) ?></div>
            <?php if (!empty($a['phone']) || !empty($a['city'])): ?>
              <div class="muted" style="font-size:.82rem;margin-top:.25rem">
                <?php if (!empty($a['phone'])): ?><?= e((string) $a['phone']) ?><?php endif; ?>
                <?php if (!empty($a['city'])): ?>
                  <?= !empty($a['phone']) ? ' · ' : '' ?><?= e((string) $a['city']) ?><?php if (!empty($a['cityRegion'])): ?> / <?= e((string) $a['cityRegion']) ?><?php endif; ?>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
          <span class="badge <?= !empty($a['active']) ? 'badge-ok' : 'badge-warn' ?>"><?= !empty($a['active']) ? 'Ativo' : 'Inativo' ?></span>
        </div>
        <?php if ($canWrite): ?>
        <div class="row-actions" style="margin-top:.75rem">
          <a class="btn btn-ghost" href="<?= e(url_path('admin/equipes.php?id=' . rawurlencode((string) $a['id']))) ?>#form-equipe">Editar</a>
          <form method="post">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= e((string) $a['id']) ?>">
            <input type="hidden" name="active" value="<?= !empty($a['active']) ? 0 : 1 ?>">
            <button class="btn btn-secondary" type="submit"><?= !empty($a['active']) ? 'Desativar' : 'Ativar' ?></button>
          </form>
          <form method="post" onsubmit="return confirm('Excluir integrante? Só é permitido se não houver cabos vinculados.');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= e((string) $a['id']) ?>">
            <button class="btn btn-danger" type="submit">Excluir</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="panel empty">Nenhum integrante cadastrado.</div><?php endif; ?>
  </div>

  <?php if ($canWrite): ?>
  <div class="panel form-card" id="form-equipe">
    <h3 class="display" style="margin-top:0"><?= $edit ? 'Editar integrante' : 'Novo integrante' ?></h3>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="post" data-mask-form>
      <input type="hidden" name="action" value="save">
      <?php if ($edit): ?>
        <input type="hidden" name="id" value="<?= e((string) $edit['id']) ?>">
      <?php endif; ?>
      <div class="field">
        <label class="label">ID</label>
        <input class="input" value="<?= e((string) ($edit['id'] ?? 'Gerado automaticamente ao salvar')) ?>" readonly disabled>
        <div class="muted" style="font-size:.78rem;margin-top:.25rem">Identificador interno — somente visualização.</div>
      </div>
      <div class="field">
        <label class="label">Código</label>
        <input class="input" value="<?= e((string) ($edit['memberNumber'] ?? $nextNumber)) ?>" readonly disabled>
        <div class="muted" style="font-size:.78rem;margin-top:.25rem">Número automático.</div>
      </div>
      <div class="field">
        <label class="label">Nome</label>
        <input class="input" name="label" value="<?= e((string) ($edit['label'] ?? post('label', ''))) ?>" required placeholder="Ex.: Ana Paula Mendes">
      </div>
      <div class="field">
        <label class="label">Telefone</label>
        <input class="input" name="phone" data-mask="phone" inputmode="tel" placeholder="(62) 90000-0000" value="<?= e((string) ($edit['phone'] ?? post('phone', ''))) ?>">
      </div>
      <div class="field">
        <label class="label">Cidade</label>
        <input class="input" name="city" value="<?= e((string) ($edit['city'] ?? post('city', ''))) ?>" placeholder="Ex.: Goiânia">
      </div>
      <div class="field">
        <label class="label">Região da cidade</label>
        <input class="input" name="cityRegion" value="<?= e((string) ($edit['cityRegion'] ?? post('cityRegion', ''))) ?>" placeholder="Ex.: Setor Bueno">
      </div>
      <div class="field">
        <label class="label">Ordem</label>
        <input class="input" type="number" name="sortOrder" value="<?= e((string) ($edit['sortOrder'] ?? post('sortOrder', '0'))) ?>">
      </div>
      <div class="row-actions">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-ghost" href="<?= e(url_path($edit ? 'admin/equipes.php' : 'admin/index.php')) ?>">Voltar</a>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
