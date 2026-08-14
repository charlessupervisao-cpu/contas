<?php
declare(strict_types=1);

/**
 * Relatórios Conta+JE §11 + prazos TRE-GO — formato oficial TSE (impressão/PDF/CSV).
 */
require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('relatorios');
$activeModule = 'relatorios';
$pageTitle = 'Relatórios';
$campaign = Metrics::getCampaign();

$type = trim((string) get('tipo', ''));
$format = strtolower(trim((string) get('formato', 'html')));

$catalog = Reports::catalog();
$groups = Reports::groupLabels();
$def = $type !== '' ? Reports::find($type) : null;

if ($def && $format === 'csv') {
    $payload = Reports::build($type, $campaign);
    $safe = preg_replace('/[^a-z0-9\-]+/i', '-', $type) ?: 'relatorio';
    $filename = 'ContaJE-' . $safe . '-' . date('Ymd-His') . '.csv';
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
      <div class="fin-kicker" style="margin:0">Formato oficial Conta+JE / TSE · TRE-GO 2026</div>
      <strong style="font-size:1.05rem">Relatórios e recibos eleitorais</strong>
    </div>
  </div>
  <div class="page-toolbar-side">
    <a class="btn btn-primary" href="<?= e(url_path('admin/entrega.php')) ?>">Pacote Conta+JE / TSE</a>
    <a class="btn btn-ghost" href="<?= e(url_path('admin/inconsistencias.php')) ?>">Inconsistências</a>
    <a class="btn btn-ghost" href="<?= e(url_path('admin/base-legal.php')) ?>">Base TRE-GO</a>
    <a class="btn btn-secondary" href="<?= e(ElectoralRules::CONTA_JE_URL) ?>" target="_blank" rel="noopener">Abrir Conta+JE</a>
  </div>
</div>

<div class="je-banner animate-rise">
  Relatórios no formato oficial Conta+JE (§11). Use CSV ou o <a href="<?= e(url_path('admin/entrega.php')) ?>">pacote ZIP de entrega</a> para conferir/carregar no portal do TSE.
</div>

<p class="muted" style="margin:0 0 1rem;max-width:52rem;line-height:1.45">
  Catálogo alinhado ao menu <strong>Relatórios</strong> do Conta+JE (§11 do Manual TSE):
  Diversos, Receitas, Despesas e Recibos Eleitorais, mais prazos TRE-GO (RF 72h, parcial e final).
  Cada relatório abre no <strong>formato oficial</strong> (cabeçalho Justiça Eleitoral, qualificação do prestador,
  tabela e rodapé Conta+JE). Use <em>Imprimir / PDF</em> ou exporte CSV (UTF-8) para conferência antes da entrega oficial.
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
            <a class="btn btn-primary" href="<?= e(url_path('admin/relatorios.php?tipo=' . rawurlencode($item['id']) . '&formato=oficial')) ?>">Abrir oficial</a>
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
    <li>Baixe o <a href="<?= e(url_path('admin/entrega.php')) ?>">pacote Conta+JE (ZIP)</a> e entregue no portal Conta+JE na janela TRE-GO.</li>
  </ol>
</div>

<?php else: ?>
<div class="page-toolbar filter-panel report-toolbar">
  <div class="page-toolbar-main">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/relatorios.php')) ?>">← Relatórios</a>
    <div>
      <div class="fin-kicker" style="margin:0"><?= e($groups[$def['group']] ?? 'Relatório') ?> · formato oficial TSE</div>
      <strong style="font-size:1.05rem"><?= e($def['label']) ?></strong>
    </div>
  </div>
  <div class="page-toolbar-side">
    <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir / PDF</button>
    <?php if (in_array('csv', $def['export'], true)): ?>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/relatorios.php?tipo=' . rawurlencode($type) . '&formato=csv')) ?>">Exportar CSV</a>
    <?php endif; ?>
    <a class="btn btn-ghost" href="<?= e(url_path('admin/relatorios.php?tipo=' . rawurlencode($type) . '&formato=oficial')) ?>">Atualizar</a>
    <a class="btn btn-ghost" href="<?= e(url_path('admin/inconsistencias.php')) ?>">Inconsistências</a>
  </div>
</div>

<?php
  require dirname(__DIR__) . '/templates/relatorio_oficial.php';
endif;

require dirname(__DIR__) . '/templates/admin_layout_end.php';
