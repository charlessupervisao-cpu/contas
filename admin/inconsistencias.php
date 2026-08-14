<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('inconsistencias');
$activeModule = 'inconsistencias';
$pageTitle = 'Verificar inconsistências';
$campaign = Metrics::getCampaign();

$issues = ElectoralRules::checkInconsistencies($campaign);
$impeditivas = array_values(array_filter($issues, static fn ($i) => ($i['level'] ?? '') === 'IMPEDITIVA'));
$naoImpeditivas = array_values(array_filter($issues, static fn ($i) => ($i['level'] ?? '') !== 'IMPEDITIVA'));
$total = count($issues);

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-toolbar filter-panel">
  <div class="page-toolbar-main">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Dashboard</a>
    <div>
      <div class="fin-kicker" style="margin:0">Conta+JE · TSE 2026</div>
      <strong style="font-size:1.05rem">Verificar inconsistências</strong>
    </div>
  </div>
  <div class="page-toolbar-side">
    <span class="badge <?= $total === 0 ? 'badge-ok' : 'badge-warn' ?>"><?= $total ?> pendência(s)</span>
    <span class="badge badge-danger"><?= count($impeditivas) ?> impeditiva(s)</span>
    <span class="badge badge-warn"><?= count($naoImpeditivas) ?> não impeditiva(s)</span>
    <a class="btn btn-ghost" href="<?= e(url_path('admin/base-legal.php')) ?>">Base TRE-GO</a>
    <a class="btn btn-ghost" href="<?= e(ElectoralRules::TRE_GO_HUB_URL) ?>" target="_blank" rel="noopener">Portal TRE-GO</a>
    <a class="btn btn-ghost" href="<?= e(ElectoralRules::MANUAL_URL) ?>" target="_blank" rel="noopener">Manual Conta+JE (PDF)</a>
  </div>
</div>

<p class="muted" style="margin:0 0 1rem;line-height:1.45;max-width:48rem">
  O CONTAS prepara e organiza os dados da campanha. A entrega oficial da prestação de contas é feita pelo sistema Conta+JE do TSE.
</p>

<div class="panel" style="margin-bottom:1rem">
  <h3 class="display" style="margin-top:0;font-size:1.05rem">Base legal</h3>
  <ul class="muted" style="margin:0;padding-left:1.2rem;line-height:1.55">
    <?php foreach (ElectoralRules::LEGAL_BASIS as $line): ?>
      <li><?= e($line) ?></li>
    <?php endforeach; ?>
  </ul>
</div>

<div class="row-actions" style="margin-bottom:1rem;gap:.5rem;flex-wrap:wrap">
  <div class="stat-chip <?= count($impeditivas) ? 'tone-rose' : 'tone-green' ?>">
    <div class="l">Impeditivas</div>
    <div class="n"><?= count($impeditivas) ?></div>
  </div>
  <div class="stat-chip <?= count($naoImpeditivas) ? 'tone-amber' : 'tone-green' ?>">
    <div class="l">Não impeditivas</div>
    <div class="n"><?= count($naoImpeditivas) ?></div>
  </div>
  <div class="stat-chip tone-blue">
    <div class="l">Total</div>
    <div class="n"><?= $total ?></div>
  </div>
</div>

<?php
$renderGroup = static function (string $title, array $list, string $badgeClass): void {
    ?>
  <div class="panel" style="margin-bottom:1rem">
    <h3 class="display" style="margin-top:0;font-size:1.1rem"><?= e($title) ?></h3>
    <?php if (!$list): ?>
      <div class="muted">Nenhuma inconsistência neste grupo.</div>
    <?php else: ?>
      <div class="stack" style="gap:.65rem">
        <?php foreach ($list as $issue): ?>
          <div class="row-actions" style="justify-content:space-between;align-items:flex-start;gap:.75rem;flex-wrap:wrap">
            <div style="flex:1;min-width:14rem">
              <span class="badge <?= e($badgeClass) ?>"><?= e((string) ($issue['code'] ?? '')) ?></span>
              <div style="margin-top:.35rem"><?= e((string) ($issue['message'] ?? '')) ?></div>
            </div>
            <?php if (!empty($issue['href'])): ?>
              <a class="btn btn-secondary" href="<?= e((string) $issue['href']) ?>">Corrigir</a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
    <?php
};
$renderGroup('Impeditivas', $impeditivas, 'badge-danger');
$renderGroup('Não impeditivas', $naoImpeditivas, 'badge-warn');
?>

<?php if ($total === 0): ?>
  <div class="alert alert-ok">Nenhuma inconsistência preventiva encontrada. Revise no Conta+JE antes da entrega oficial.</div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
