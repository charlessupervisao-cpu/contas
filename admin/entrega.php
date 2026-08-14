<?php
declare(strict_types=1);

/**
 * Hub de entrega Conta+JE / TSE — conferência + pacote de envio.
 */
require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('entrega');
$activeModule = 'entrega';
$pageTitle = 'Entrega ao Conta+JE / TSE';
$campaign = Metrics::getCampaign();

if (request_method() === 'POST' && (string) post('action') === 'pacote') {
    Auth::requireLogin('entrega');
    $pack = ContaJeExport::buildPackage($campaign);
    if (!$pack['ok'] || empty($pack['zip'])) {
        flash_set('danger', $pack['error'] ?? 'Falha ao gerar pacote.');
        redirect('/admin/entrega.php');
    }
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . ($pack['filename'] ?? 'ContaJE-pacote.zip') . '"');
    header('Content-Length: ' . strlen((string) $pack['zip']));
    echo $pack['zip'];
    exit;
}

$issues = $campaign ? ElectoralRules::checkInconsistencies($campaign) : [];
$imped = array_values(array_filter($issues, static fn ($i) => ($i['level'] ?? '') === 'IMPEDITIVA'));
$nao = array_values(array_filter($issues, static fn ($i) => ($i['level'] ?? '') !== 'IMPEDITIVA'));
$deadlines = ElectoralRules::treGoCatalog()['deadlines'] ?? [];

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-toolbar filter-panel">
  <div class="page-toolbar-main">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Painel</a>
    <div>
      <div class="fin-kicker" style="margin:0">Conta+JE §12 · Entrega oficial TSE</div>
      <strong style="font-size:1.05rem">Preparar e enviar a prestação</strong>
    </div>
  </div>
  <div class="page-toolbar-side">
    <span class="badge <?= count($imped) ? 'badge-danger' : 'badge-ok' ?>"><?= count($imped) ?> impeditiva(s)</span>
    <a class="btn btn-secondary" href="<?= e(ElectoralRules::CONTA_JE_URL) ?>" target="_blank" rel="noopener">Abrir Conta+JE</a>
  </div>
</div>

<div class="je-banner animate-rise">
  <div>
    <strong>Fluxo oficial Conta+JE</strong>
    <p class="muted" style="margin:.25rem 0 0;line-height:1.45;max-width:44rem">
      O CONTAS organiza e exporta os dados no padrão do Manual Conta+JE.
      A <strong>entrega formal</strong> à Justiça Eleitoral é feita no portal Conta+JE do TSE.
      Use o pacote ZIP abaixo para conferir/carregar os CSVs oficiais (§11) antes de enviar.
    </p>
  </div>
</div>

<div class="grid grid-2" style="margin-top:1rem">
  <div class="panel je-section">
    <div class="je-section-title">1. Conferência preventiva</div>
    <ul class="muted" style="margin:0;padding-left:1.1rem;line-height:1.55">
      <li>Qualificação do prestador (endereço e contatos)</li>
      <li>Representantes (advogado OAB + contabilista CRC)</li>
      <li>Contas Doações / FEFC / Fundo Partidário (RAC, 10 dias)</li>
      <li>Doações e despesas com espécie/pagamento/NF</li>
      <li>Recibos eleitorais e inconsistências</li>
    </ul>
    <div class="row-actions" style="margin-top:.85rem;flex-wrap:wrap;gap:.4rem">
      <a class="btn btn-ghost" href="<?= e(url_path('admin/wizard.php')) ?>">Qualificação</a>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/inconsistencias.php')) ?>">Inconsistências</a>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/relatorios.php')) ?>">Relatórios</a>
    </div>
  </div>
  <div class="panel je-section">
    <div class="je-section-title">2. Prazos TRE-GO 2026</div>
    <ul style="margin:0;padding-left:1.1rem;line-height:1.55;font-size:.9rem">
      <li>Parcial: <?= e((string) ($deadlines['partialAccounts']['start'] ?? '2026-09-09')) ?> a <?= e((string) ($deadlines['partialAccounts']['end'] ?? '2026-09-13')) ?></li>
      <li>Final: <?= e((string) ($deadlines['finalAccounts']['start'] ?? '2026-10-05')) ?> a <?= e((string) ($deadlines['finalAccounts']['end'] ?? '2026-11-03')) ?></li>
      <li>RF: <?= (int) ($deadlines['financialReportHoursAfterReceipt'] ?? 72) ?>h após cada recurso</li>
    </ul>
    <div class="row-actions" style="margin-top:.85rem">
      <a class="btn btn-ghost" href="<?= e(url_path('admin/base-legal.php')) ?>">Base legal TRE-GO</a>
    </div>
  </div>
</div>

<div class="panel je-section" style="margin-top:1rem">
  <div class="je-section-title">3. Pacote de envio ao Conta+JE / TSE</div>
  <p class="muted" style="line-height:1.45">
    Gera um ZIP com CSVs nomeados como no Conta+JE (qualificação, representantes, contas,
    doações, despesas, recibos, demonstrativo, inconsistências) + manifesto JSON e instruções.
  </p>
  <?php if (count($imped) > 0): ?>
    <div class="alert alert-warn">Há <?= count($imped) ?> inconsistência(s) impeditiva(s). Corrija antes da entrega oficial.</div>
  <?php endif; ?>
  <form method="post" class="row-actions" style="flex-wrap:wrap;gap:.5rem">
    <input type="hidden" name="action" value="pacote">
    <button class="btn btn-primary" type="submit" <?= $campaign ? '' : 'disabled' ?>>Baixar pacote Conta+JE (ZIP)</button>
    <a class="btn btn-secondary" href="<?= e(ElectoralRules::CONTA_JE_URL) ?>" target="_blank" rel="noopener">Ir ao portal Conta+JE</a>
    <a class="btn btn-ghost" href="<?= e(ElectoralRules::MANUAL_URL) ?>" target="_blank" rel="noopener">Manual Conta+JE (PDF)</a>
  </form>
</div>

<div class="panel" style="margin-top:1rem">
  <h3 class="display" style="margin-top:0;font-size:1.05rem">Pendências atuais</h3>
  <?php if (!$issues): ?>
    <div class="muted">Nenhuma pendência listada.</div>
  <?php else: ?>
    <div class="stack" style="gap:.45rem">
      <?php foreach (array_slice($issues, 0, 12) as $issue): ?>
        <div style="padding:.55rem .65rem;border:1px solid var(--line);border-radius:var(--radius-sm)">
          <span class="badge <?= ($issue['level'] ?? '') === 'IMPEDITIVA' ? 'badge-danger' : 'badge-warn' ?>"><?= e((string) ($issue['level'] ?? '')) ?></span>
          <?= e((string) ($issue['message'] ?? '')) ?>
          <?php if (!empty($issue['href'])): ?>
            · <a href="<?= e((string) $issue['href']) ?>">corrigir</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="muted" style="margin-top:.55rem;font-size:.8rem"><?= count($imped) ?> impeditiva(s) · <?= count($nao) ?> não impeditiva(s)</div>
  <?php endif; ?>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
