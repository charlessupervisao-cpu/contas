<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$user = Auth::requireLogin('diario');
$activeModule = 'diario';
$pageTitle = '12 · Diário do dia';
$date = (string) get('date', date('Y-m-d'));
$day = Metrics::getDailyActivity($date);
$s = $day['summary'];
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<form method="get" class="row-actions" style="margin-bottom:1rem">
  <input class="input" type="date" name="date" value="<?= e($date) ?>" style="max-width:220px" onchange="this.form.submit()">
</form>
<div class="grid grid-4" style="margin-bottom:1rem">
  <div class="panel kpi"><div class="muted">Entradas</div><strong><?= e(money_br($s['totalIn'])) ?></strong><div class="muted"><?= (int)$s['receitas'] ?> receitas</div></div>
  <div class="panel kpi"><div class="muted">Saídas</div><strong><?= e(money_br($s['totalOut'])) ?></strong><div class="muted"><?= (int)$s['despesas'] ?> despesas</div></div>
  <div class="panel kpi"><div class="muted">Saldo do dia</div><strong><?= e(money_br($s['saldoDia'])) ?></strong></div>
  <div class="panel kpi"><div class="muted">Pend. conciliação</div><strong><?= (int)$s['pendentesConciliacao'] ?></strong></div>
</div>
<div class="panel">
  <h3 class="display" style="margin-top:0">Eventos</h3>
  <?php if (!$day['logs']): ?><div class="empty">Sem eventos neste dia.</div><?php endif; ?>
  <?php foreach ($day['logs'] as $log): ?>
    <div style="padding:.65rem 0;border-bottom:1px solid var(--line)">
      <strong><?= e($log['action']) ?></strong> · <?= e($log['entity']) ?>
      <div class="muted" style="font-size:.85rem"><?= e($log['details'] ?: '') ?> · <?= e($log['userName'] ?: 'sistema') ?> · <?= e(datetime_br($log['createdAt'])) ?></div>
    </div>
  <?php endforeach; ?>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
