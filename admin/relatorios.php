<?php
declare(strict_types=1);

/**
 * Relatórios Conta+JE §11 + prazos TRE-GO — conferência / impressão / CSV.
 */
require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('relatorios');
$activeModule = 'relatorios';
$pageTitle = 'Relatórios';
$campaign = Metrics::getCampaign();

$type = trim((string) get('tipo', ''));
$format = strtolower(trim((string) get('formato', 'html')));
$print = (int) get('print', 0) === 1 || $format === 'print';

$catalog = Reports::catalog();
$groups = Reports::groupLabels();
$def = $type !== '' ? Reports::find($type) : null;

if ($def && $format === 'csv') {
    $payload = Reports::build($type, $campaign);
    $filename = 'contas-' . $type . '-' . date('Ymd-His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo Reports::toCsv($payload);
    exit;
}

$payload = null;
if ($def) {
    $payload = Reports::build($type, $campaign);
    $pageTitle = (string) $def['label'];
}

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<?php if (!$def): ?>
<div class="page-toolbar filter-panel">
  <div class="page-toolbar-main">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Dashboard</a>
    <div>
      <div class="fin-kicker" style="margin:0">Conta+JE §11 · TRE-GO 2026</div>
      <strong style="font-size:1.05rem">Relatórios e recibos eleitorais</strong>
    </div>
  </div>
  <div class="page-toolbar-side">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/inconsistencias.php')) ?>">Inconsistências</a>
    <a class="btn btn-ghost" href="<?= e(url_path('admin/base-legal.php')) ?>">Base TRE-GO</a>
    <a class="btn btn-secondary" href="<?= e(ElectoralRules::CONTA_JE_URL) ?>" target="_blank" rel="noopener">Abrir Conta+JE</a>
  </div>
</div>

<p class="muted" style="margin:0 0 1rem;max-width:48rem;line-height:1.45">
  Relatórios de conferência alinhados ao menu <strong>Relatórios</strong> do Conta+JE
  (diversos, receitas, despesas, recibos) e aos prazos do TRE-GO (RF 72h, parcial e final).
  Use para revisar dados antes da entrega oficial no Conta+JE. Formatos: HTML/impressão (PDF via navegador) e CSV.
</p>

<?php foreach ($groups as $gid => $glabel): ?>
  <div class="panel" style="margin-bottom:1rem">
    <h3 class="display" style="margin-top:0;font-size:1.1rem"><?= e($glabel) ?></h3>
    <div class="stack" style="gap:.55rem">
      <?php foreach ($catalog as $item): ?>
        <?php if (($item['group'] ?? '') !== $gid) continue; ?>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem 1rem;align-items:flex-start;justify-content:space-between;padding:.35rem 0;border-bottom:1px solid var(--line)">
          <div style="max-width:36rem">
            <strong><?= e($item['label']) ?></strong>
            <div class="muted" style="font-size:.82rem;line-height:1.4;margin-top:.15rem"><?= e($item['desc']) ?></div>
          </div>
          <div class="row-actions" style="gap:.35rem">
            <a class="btn btn-primary" href="<?= e(url_path('admin/relatorios.php?tipo=' . rawurlencode($item['id']))) ?>">Abrir</a>
            <?php if (in_array('csv', $item['export'], true)): ?>
              <a class="btn btn-ghost" href="<?= e(url_path('admin/relatorios.php?tipo=' . rawurlencode($item['id']) . '&formato=csv')) ?>">CSV</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>

<div class="panel">
  <h3 class="display" style="margin-top:0;font-size:1.05rem">Conferência antes da entrega (Conta+JE §11.5)</h3>
  <ol class="muted" style="margin:0;padding-left:1.2rem;line-height:1.55">
    <li>Gere <strong>Qualificação</strong> e confira prestador/representantes/contas.</li>
    <li>Gere o <strong>Demonstrativo de Receitas e Despesas</strong>.</li>
    <li>Confira FCC, estimáveis e doações pela internet.</li>
    <li>Confira despesas efetuadas, não pagas e doações a candidatos/partidos.</li>
    <li>Verifique <strong>recibos eleitorais</strong> e <strong>inconsistências</strong>.</li>
    <li>Entregue a prestação no Conta+JE na janela TRE-GO correspondente.</li>
  </ol>
</div>

<?php else: ?>
<div class="page-toolbar filter-panel report-toolbar">
  <div class="page-toolbar-main">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/relatorios.php')) ?>">← Relatórios</a>
    <div>
      <div class="fin-kicker" style="margin:0"><?= e($groups[$def['group']] ?? 'Relatório') ?></div>
      <strong style="font-size:1.05rem"><?= e($def['label']) ?></strong>
    </div>
  </div>
  <div class="page-toolbar-side">
    <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir / PDF</button>
    <?php if (in_array('csv', $def['export'], true)): ?>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/relatorios.php?tipo=' . rawurlencode($type) . '&formato=csv')) ?>">Exportar CSV</a>
    <?php endif; ?>
    <a class="btn btn-ghost" href="<?= e(url_path('admin/inconsistencias.php')) ?>">Inconsistências</a>
  </div>
</div>

<?php
  $meta = $payload['meta'] ?? [];
  $columns = $payload['columns'] ?? [];
  $rows = $payload['rows'] ?? [];
  $summary = $payload['summary'] ?? [];
  $note = $payload['note'] ?? null;
?>

<div class="panel report-sheet animate-rise">
  <div class="report-head">
    <div class="fin-kicker" style="margin:0"><?= e(APP_NAME) ?> · build <?= e((string) ($meta['build'] ?? APP_BUILD)) ?></div>
    <h2 class="display" style="margin:.2rem 0 .35rem;font-size:1.35rem"><?= e((string) ($meta['title'] ?? $def['label'])) ?></h2>
    <div class="muted" style="font-size:.85rem;line-height:1.45">
      <?= e((string) ($meta['campaign'] ?? '')) ?>
      <?php if (!empty($meta['office'])): ?> · <?= e((string) $meta['office']) ?><?php endif; ?>
      <?php if (!empty($meta['party'])): ?> · <?= e((string) $meta['party']) ?><?php endif; ?>
      <?php if (!empty($meta['cnpj'])): ?> · CNPJ <?= e((string) $meta['cnpj']) ?><?php endif; ?>
      <br>
      Gerado em <?= e(datetime_br((string) ($meta['generatedAt'] ?? date('c')))) ?>
      · Conferência interna — entrega oficial no Conta+JE
    </div>
  </div>

  <?php if ($summary): ?>
    <div class="row-actions" style="margin:1rem 0;gap:.45rem;flex-wrap:wrap">
      <?php foreach ($summary as $sk => $sv): ?>
        <div class="stat-chip">
          <div class="l"><?= e((string) $sk) ?></div>
          <div class="n" style="font-size:.95rem"><?= e((string) $sv) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($note): ?>
    <div class="alert alert-info" style="margin-bottom:1rem"><?= e((string) $note) ?></div>
  <?php endif; ?>

  <?php if (!$columns): ?>
    <div class="empty">Nada a exibir neste relatório.</div>
  <?php elseif (!$rows): ?>
    <div class="empty">Nenhum lançamento encontrado para os filtros deste relatório.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table report-table">
        <thead>
          <tr>
            <?php foreach ($columns as $col): ?>
              <th><?= e((string) $col) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <?php foreach ($columns as $col): ?>
                <td><?= e((string) ($row[$col] ?? '')) ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="muted" style="margin-top:.65rem;font-size:.8rem"><?= count($rows) ?> linha(s)</div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
