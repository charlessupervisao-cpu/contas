<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('veiculos');
$activeModule = 'veiculos';
$pageTitle = '8 · Veículos';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$editId = trim((string) get('id', ''));
$edit = null;
$errors = [];

if ($editId !== '' && $campaign) {
    $st = $pdo->prepare('SELECT * FROM `Vehicle` WHERE id=? AND campaignId=? LIMIT 1');
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
        $pdo->prepare('UPDATE `Vehicle` SET active=?, updatedAt=? WHERE id=? AND campaignId=?')
            ->execute([$active, $now, $id, $cid]);
        audit_log($user['id'], 'UPDATE', 'Vehicle', $id, $active ? 'Ativou veículo' : 'Desativou veículo');
        flash_set('ok', $active ? 'Veículo ativado.' : 'Veículo desativado.');
        redirect('/admin/veiculos.php');
    }

    if ($action === 'delete') {
        $id = (string) post('id');
        $st = $pdo->prepare('SELECT label,plate FROM `Vehicle` WHERE id=? AND campaignId=? LIMIT 1');
        $st->execute([$id, $cid]);
        $row = $st->fetch();
        if ($row) {
            $pdo->prepare('UPDATE `Expense` SET vehicleId=NULL WHERE vehicleId=?')->execute([$id]);
            $pdo->prepare('DELETE FROM `Vehicle` WHERE id=? AND campaignId=?')->execute([$id, $cid]);
            audit_log($user['id'], 'DELETE', 'Vehicle', $id, 'Excluiu veículo ' . $row['label'] . ' · ' . $row['plate'] . ' (despesas desvinculadas)');
            flash_set('ok', 'Veículo excluído e vínculos de despesa removidos.');
        }
        redirect('/admin/veiculos.php');
    }

    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        $plate = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) post('plate')) ?? '');
        $label = trim((string) post('label'));
        $type = trim((string) post('type', 'AUTOMÓVEL'));
        if (!isset(VEHICLE_TYPES[$type])) {
            $type = 'AUTOMÓVEL';
        }
        if ($label === '' || $plate === '') {
            $errors[] = 'Rótulo e placa são obrigatórios.';
        }
        if (!$errors && $campaign) {
            $brand = trim((string) post('brand', '')) ?: null;
            $model = trim((string) post('model', '')) ?: null;
            $year = trim((string) post('year', '')) ?: null;
            if ($id !== '') {
                $pdo->prepare(
                    'UPDATE `Vehicle` SET label=?, plate=?, brand=?, model=?, year=?, type=?, updatedAt=? WHERE id=? AND campaignId=?'
                )->execute([$label, $plate, $brand, $model, $year, $type, $now, $id, $cid]);
                audit_log($user['id'], 'UPDATE', 'Vehicle', $id, "Atualizou veículo {$label} · {$plate}");
                flash_set('ok', 'Veículo atualizado.');
            } else {
                $newId = cuid();
                $pdo->prepare(
                    'INSERT INTO `Vehicle` (id,campaignId,label,plate,brand,model,year,type,active,createdAt,updatedAt)
                     VALUES (?,?,?,?,?,?,?,?,1,?,?)'
                )->execute([$newId, $cid, $label, $plate, $brand, $model, $year, $type, $now, $now]);
                audit_log($user['id'], 'CREATE', 'Vehicle', $newId, "Cadastrou veículo {$label} · {$plate}");
                flash_set('ok', 'Veículo cadastrado.');
            }
            redirect('/admin/veiculos.php');
        }
    }
}

$rows = [];
if ($campaign) {
    $st = $pdo->prepare('SELECT * FROM `Vehicle` WHERE campaignId=? ORDER BY active DESC, label');
    $st->execute([$campaign['id']]);
    $rows = $st->fetchAll();
}
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="row-actions" style="margin-bottom:1rem">
  <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Voltar</a>
  <?php if ($canWrite): ?>
    <a class="btn btn-primary" href="#form-veiculo"><?= $edit ? 'Editar formulário' : 'Novo veículo' ?></a>
  <?php endif; ?>
</div>

<div class="grid grid-2">
  <div class="stack">
    <?php foreach ($rows as $v): ?>
      <div class="panel">
        <div class="row-actions" style="justify-content:space-between;align-items:flex-start">
          <div>
            <strong><?= e($v['label']) ?></strong>
            <div class="muted"><?= e($v['plate']) ?> · <?= e((string) $v['brand']) ?> <?= e((string) $v['model']) ?> · <?= e(VEHICLE_TYPES[$v['type']] ?? $v['type']) ?></div>
          </div>
          <span class="badge <?= $v['active'] ? 'badge-ok' : 'badge-warn' ?>"><?= $v['active'] ? 'Ativo' : 'Inativo' ?></span>
        </div>
        <?php if ($canWrite): ?>
        <div class="row-actions" style="margin-top:.75rem">
          <a class="btn btn-ghost" href="<?= e(url_path('admin/veiculos.php?id=' . rawurlencode($v['id']))) ?>#form-veiculo">Editar</a>
          <form method="post">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= e($v['id']) ?>">
            <input type="hidden" name="active" value="<?= $v['active'] ? 0 : 1 ?>">
            <button class="btn btn-secondary" type="submit"><?= $v['active'] ? 'Desativar' : 'Ativar' ?></button>
          </form>
          <form method="post" onsubmit="return confirm('Excluir veículo e desvincular das despesas?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= e($v['id']) ?>">
            <button class="btn btn-danger" type="submit">Excluir</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="panel empty">Nenhum veículo cadastrado.</div><?php endif; ?>
  </div>

  <?php if ($canWrite): ?>
  <div class="panel form-card" id="form-veiculo">
    <h3 class="display" style="margin-top:0"><?= $edit ? 'Editar veículo' : 'Novo veículo' ?></h3>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
      <div class="field"><label class="label">Rótulo</label><input class="input" name="label" value="<?= e((string) ($edit['label'] ?? '')) ?>" required></div>
      <div class="field"><label class="label">Placa</label><input class="input" name="plate" value="<?= e((string) ($edit['plate'] ?? '')) ?>" required></div>
      <div class="grid grid-2">
        <div class="field"><label class="label">Marca</label><input class="input" name="brand" value="<?= e((string) ($edit['brand'] ?? '')) ?>"></div>
        <div class="field"><label class="label">Modelo</label><input class="input" name="model" value="<?= e((string) ($edit['model'] ?? '')) ?>"></div>
      </div>
      <div class="grid grid-2">
        <div class="field"><label class="label">Ano</label><input class="input" name="year" value="<?= e((string) ($edit['year'] ?? '')) ?>"></div>
        <div class="field">
          <label class="label">Tipo</label>
          <?php $vt = (string) ($edit['type'] ?? 'AUTOMÓVEL'); ?>
          <select class="input" name="type" required>
            <?php foreach (VEHICLE_TYPES as $code => $labelType): ?>
              <option value="<?= e($code) ?>" <?= $vt === $code || ($vt === 'Automóvel' && $code === 'AUTOMÓVEL') ? 'selected' : '' ?>><?= e($labelType) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row-actions">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-ghost" href="<?= e(url_path($edit ? 'admin/veiculos.php' : 'admin/index.php')) ?>">Voltar</a>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
