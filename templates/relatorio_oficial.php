<?php
/**
 * Layout oficial Conta+JE / TSE para impressão e conferência.
 * @var array $def
 * @var array $payload
 * @var array $campaign
 */
$meta = $payload['meta'] ?? [];
$columns = $payload['columns'] ?? [];
$rows = $payload['rows'] ?? [];
$summary = $payload['summary'] ?? [];
$note = $payload['note'] ?? null;
$title = (string) ($meta['title'] ?? ($def['label'] ?? 'Relatório'));
$generated = datetime_br((string) ($meta['generatedAt'] ?? date('c')));
$cnpj = (string) ($meta['cnpj'] ?? ($campaign['cnpjCampaign'] ?? ''));
if ($cnpj !== '' && strlen(only_digits($cnpj)) === 14) {
    $d = only_digits($cnpj);
    $cnpj = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d) ?: $cnpj;
}
$moneyCols = ['Valor', 'ValorRaw', 'Total'];
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/relatorio-oficial.css')) ?>">
<article class="tse-report" aria-label="Relatório oficial Conta+JE">
  <header class="tse-report-brand">
    <div>
      <h1>Justiça Eleitoral — Prestação de Contas</h1>
      <div class="sub">
        Conta+JE · Eleições <?= e((string) ($campaign['electionYear'] ?? ELECTION_YEAR)) ?>
        · Formato de conferência alinhado ao Manual Conta+JE (TSE)
      </div>
    </div>
    <div class="tse-report-seal">
      <?= e(APP_NAME) ?><br>
      <?= e(APP_DOMAIN) ?><br>
      Build <?= e((string) ($meta['build'] ?? APP_BUILD)) ?>
    </div>
  </header>

  <h2 class="tse-report-title"><?= e($title) ?></h2>

  <table class="tse-report-meta">
    <tr>
      <th>Prestador / Candidato(a)</th>
      <td><?= e((string) ($meta['campaign'] ?? ($campaign['candidateName'] ?? '—'))) ?></td>
      <th>Nº</th>
      <td><?= e((string) ($campaign['candidateNumber'] ?? '—')) ?></td>
    </tr>
    <tr>
      <th>Cargo</th>
      <td><?= e((string) ($meta['office'] ?? ($campaign['office'] ?? '—'))) ?></td>
      <th>Partido</th>
      <td><?= e((string) ($meta['party'] ?? trim(($campaign['party'] ?? '') . '/' . ($campaign['partyNumber'] ?? ''), '/'))) ?></td>
    </tr>
    <tr>
      <th>CNPJ de campanha</th>
      <td><?= e($cnpj !== '' ? $cnpj : '—') ?></td>
      <th>UF / Ano</th>
      <td><?= e((string) ($campaign['state'] ?? 'GO')) ?> / <?= e((string) ($campaign['electionYear'] ?? ELECTION_YEAR)) ?></td>
    </tr>
    <tr>
      <th>Emissão</th>
      <td colspan="3"><?= e($generated) ?> · Documento de conferência interna (não substitui a entrega no Conta+JE)</td>
    </tr>
  </table>

  <?php if ($summary): ?>
    <div class="tse-report-summary">
      <?php foreach ($summary as $sk => $sv): ?>
        <div><strong><?= e((string) $sk) ?></strong><?= e((string) $sv) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($note)): ?>
    <div class="tse-report-note"><?= e((string) $note) ?></div>
  <?php endif; ?>

  <?php if (!$columns): ?>
    <div class="tse-report-empty">Nada a exibir neste relatório.</div>
  <?php elseif (!$rows): ?>
    <div class="tse-report-empty">Nenhum lançamento encontrado para os filtros deste relatório.</div>
  <?php else: ?>
    <table class="tse-report-table">
      <thead>
        <tr>
          <?php foreach ($columns as $col): ?>
            <th class="<?= in_array($col, $moneyCols, true) ? 'num' : '' ?>"><?= e((string) $col) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($columns as $col): ?>
              <td class="<?= in_array($col, $moneyCols, true) ? 'num' : '' ?>"><?= e((string) ($row[$col] ?? '')) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="font-size:8.5pt;color:#555"><?= count($rows) ?> registro(s)</div>
  <?php endif; ?>

  <div class="tse-report-sign">
    <div>
      <div class="line">Candidato(a) / Prestador(a)</div>
    </div>
    <div>
      <div class="line">Contabilista / Responsável técnico</div>
    </div>
  </div>

  <footer class="tse-report-foot">
    Relatório gerado por <?= e(APP_NAME) ?> (<?= e(APP_VENDOR) ?>) no padrão de nomenclatura e agrupamento do
    Manual Conta+JE — TSE/STI (Relatórios e recibos eleitorais, §11). A entrega oficial da prestação de contas
    à Justiça Eleitoral deve ser feita exclusivamente pelo sistema Conta+JE
    (<?= e(ElectoralRules::CONTA_JE_URL) ?>), conforme Res.-TSE nº 23.607/2019 e orientações do TRE-GO.
  </footer>
</article>
