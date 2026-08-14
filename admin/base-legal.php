<?php
declare(strict_types=1);

/**
 * Base oficial TRE-GO / Conta+JE / TSE 2026 — hub de referência no CONTAS.
 */
require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('base-legal');
$activeModule = 'base-legal';
$pageTitle = 'Base legal TRE-GO 2026';
$campaign = Metrics::getCampaign();
$catalog = ElectoralRules::treGoCatalog();
$deadlines = $catalog['deadlines'] ?? [];
$limits = $catalog['spendLimitsGoBrl'] ?? [];
$personnel = $catalog['personnelLimitsGo'] ?? [];
$links = ElectoralRules::officialLinks();
$office = (string) ($campaign['office'] ?? DEFAULT_OFFICE);
$spendHint = ElectoralRules::spendLimitForOffice($office);
$personnelHint = ElectoralRules::personnelLimitForOffice($office);

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-form animate-rise">
  <div class="panel">
    <div class="fin-kicker" style="margin:0">TRE-GO · CECEP · Eleições 2026</div>
    <h2 class="display" style="margin:.25rem 0 .5rem">Base de dados e normas para prestação de contas</h2>
    <p class="muted" style="margin:0;max-width:52rem;line-height:1.5">
      Fonte oficial:
      <a href="<?= e(ElectoralRules::TRE_GO_HUB_URL) ?>" target="_blank" rel="noopener noreferrer">
        Prestação de Contas — Eleições 2026 (TRE-GO)
      </a>.
      O CONTAS usa esses dados para limites, prazos, vínculos de contas e inconsistências.
      A entrega oficial permanece no <a href="<?= e(ElectoralRules::CONTA_JE_URL) ?>" target="_blank" rel="noopener">CONTA+JE</a>.
    </p>
  </div>

  <div class="grid grid-2" style="margin-top:1rem">
    <div class="panel">
      <h3 class="display" style="margin-top:0;font-size:1.1rem">Prazos importantes (TRE-GO)</h3>
      <ul style="margin:0;padding-left:1.1rem;line-height:1.55;font-size:.9rem">
        <li>Abertura de contas: <strong><?= (int) ($deadlines['bankAccountOpenDaysFromCnpj'] ?? 10) ?> dias</strong> após o CNPJ (RAC)</li>
        <li>Distribuição FEFC/FP (mulheres, negros, indígenas): até <strong><?= e((string) ($deadlines['fefcFpDistributionToProtectedCandidatesUntil'] ?? '2026-09-08')) ?></strong></li>
        <li>Relatórios financeiros: <strong><?= (int) ($deadlines['financialReportHoursAfterReceipt'] ?? 72) ?>h</strong> após cada recurso financeiro</li>
        <li>Prestação <strong>parcial</strong>: <?= e((string) ($deadlines['partialAccounts']['start'] ?? '')) ?> a <?= e((string) ($deadlines['partialAccounts']['end'] ?? '')) ?></li>
        <li>Prestação <strong>final</strong>: <?= e((string) ($deadlines['finalAccounts']['start'] ?? '')) ?> a <?= e((string) ($deadlines['finalAccounts']['end'] ?? '')) ?></li>
        <li>Final (2º turno): <?= e((string) ($deadlines['finalAccountsSecondRound']['start'] ?? '')) ?> a <?= e((string) ($deadlines['finalAccountsSecondRound']['end'] ?? '')) ?></li>
        <li>FCC (vaquinha): a partir de <?= e((string) ($deadlines['fccStart'] ?? '2026-05-15')) ?></li>
      </ul>
    </div>
    <div class="panel">
      <h3 class="display" style="margin-top:0;font-size:1.1rem">Limites Goiás 2026</h3>
      <div class="muted" style="font-size:.82rem;margin-bottom:.55rem">Cargo da campanha: <strong><?= e($office) ?></strong></div>
      <?php if ($spendHint !== null): ?>
        <div class="stat-chip tone-blue" style="margin-bottom:.55rem">
          <div class="l">Teto de gastos (TRE-GO)</div>
          <div class="n" style="font-size:1.05rem"><?= e(money_br($spendHint)) ?></div>
        </div>
      <?php endif; ?>
      <?php if ($personnelHint !== null): ?>
        <div class="stat-chip" style="margin-bottom:.75rem">
          <div class="l">Teto de militância/rua</div>
          <div class="n" style="font-size:1.05rem"><?= e((string) $personnelHint) ?> pessoas</div>
        </div>
      <?php endif; ?>
      <table class="table" style="font-size:.82rem">
        <thead><tr><th>Cargo</th><th>Gastos</th><th>Pessoal</th></tr></thead>
        <tbody>
          <?php
            $labels = [
              'GOVERNADOR_1T' => 'Governador (1º turno)',
              'GOVERNADOR_2T_ACRESCIMO' => 'Governador (+2º turno)',
              'SENADOR' => 'Senador',
              'DEPUTADO_FEDERAL' => 'Deputado federal',
              'DEPUTADO_ESTADUAL' => 'Deputado estadual',
            ];
            foreach ($labels as $k => $lab):
              $spend = $limits[$k] ?? ($k === 'GOVERNADOR_1T' ? ($limits['GOVERNADOR'] ?? null) : null);
              $persKey = str_starts_with($k, 'GOVERNADOR') ? 'GOVERNADOR' : $k;
              $pers = $personnel[$persKey] ?? null;
          ?>
            <tr>
              <td><?= e($lab) ?></td>
              <td><?= $spend !== null ? e(money_br((float) $spend)) : '—' ?></td>
              <td><?= $pers !== null ? e((string) $pers) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel" style="margin-top:1rem">
    <h3 class="display" style="margin-top:0;font-size:1.1rem">Contas bancárias (obrigatórias)</h3>
    <p class="muted" style="line-height:1.45">
      <?= e((string) (($catalog['bankAccounts']['rule'] ?? ''))) ?>
      Preferência TRE-GO: <?= e((string) ($catalog['bankAccounts']['preferredBank'] ?? 'Banco do Brasil')) ?>.
      Pagamentos: <?= e((string) ($catalog['bankAccounts']['paymentAdvice'] ?? 'PIX')) ?>.
    </p>
    <div class="row-actions">
      <a class="btn btn-primary" href="<?= e(url_path('admin/contas.php')) ?>">Configurar contas</a>
      <a class="btn btn-secondary" href="<?= e(ElectoralRules::RAC_URL) ?>" target="_blank" rel="noopener">Emitir RAC</a>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/inconsistencias.php')) ?>">Ver inconsistências</a>
    </div>
  </div>

  <div class="panel" style="margin-top:1rem">
    <h3 class="display" style="margin-top:0;font-size:1.1rem">Comunicações à Justiça Eleitoral</h3>
    <ul style="margin:0;padding-left:1.1rem;line-height:1.55;font-size:.9rem">
      <li>Carreatas: antecedência mínima de <strong><?= (int) (($catalog['communications']['carreataMinHours'] ?? 24)) ?>h</strong> (<?= e((string) ($catalog['communications']['carreataBasis'] ?? '')) ?>)</li>
      <li>Eventos de arrecadação: <strong><?= (int) (($catalog['communications']['fundraisingEventMinBusinessDays'] ?? 5)) ?> dias úteis</strong> (<?= e((string) ($catalog['communications']['fundraisingEventBasis'] ?? '')) ?>)</li>
      <li>Cargos estaduais/federais (GO): protocolo TRE —
        tel. <?= e((string) ($catalog['communications']['stateFederalProtocol']['phone'] ?? '')) ?>,
        e-mail <?= e((string) ($catalog['communications']['stateFederalProtocol']['email'] ?? '')) ?></li>
      <li>Suporte CECEP: <?= e((string) ($catalog['support']['cecepEmail'] ?? 'cecep@tre-go.jus.br')) ?></li>
    </ul>
  </div>

  <?php $gru = $catalog['gru'] ?? []; if ($gru): ?>
  <div class="panel" style="margin-top:1rem">
    <h3 class="display" style="margin-top:0;font-size:1.1rem">Recolhimento à União (GRU)</h3>
    <p class="muted" style="line-height:1.45;margin:0 0 .65rem">
      <?= e((string) ($gru['note'] ?? 'GRU modelo SIMPLES via Portal PagTesouro.')) ?>
    </p>
    <div class="row-actions" style="flex-wrap:wrap;gap:.45rem">
      <a class="btn btn-secondary" href="<?= e((string) ($gru['pageUrl'] ?? '#')) ?>" target="_blank" rel="noopener">Página TRE-GO</a>
      <a class="btn btn-ghost" href="<?= e((string) ($gru['pagTesouroEmit'] ?? '#')) ?>" target="_blank" rel="noopener">Emitir GRU</a>
      <a class="btn btn-ghost" href="<?= e((string) ($gru['pagTesouroPay'] ?? '#')) ?>" target="_blank" rel="noopener">Pagar GRU (PIX)</a>
      <?php if (!empty($gru['guidePixUrl'])): ?>
        <a class="btn btn-ghost" href="<?= e((string) $gru['guidePixUrl']) ?>" target="_blank" rel="noopener">Passo a passo PIX</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="panel" style="margin-top:1rem">
    <h3 class="display" style="margin-top:0;font-size:1.1rem">Base legal</h3>
    <ul style="margin:0;padding-left:1.1rem;line-height:1.55;font-size:.9rem">
      <?php foreach (ElectoralRules::LEGAL_BASIS as $item): ?>
        <li><?= e($item) ?></li>
      <?php endforeach; ?>
    </ul>
    <p class="muted" style="font-size:.82rem;margin:.75rem 0 0;line-height:1.4">
      <?= e((string) ($catalog['sanctions']['noAccounts'] ?? '')) ?>
    </p>
  </div>

  <div class="panel" style="margin-top:1rem">
    <h3 class="display" style="margin-top:0;font-size:1.1rem">Links oficiais (TRE-GO / TSE)</h3>
    <p class="muted" style="font-size:.82rem;margin:0 0 .65rem">
      Extraídos do hub TRE-GO 2026 e subpáginas (CECEP, GRU, RAC, Conta+JE, limites).
    </p>
    <?php
      $groupLabels = [
        'hub' => 'Hub TRE-GO',
        'sistema' => 'Sistema Conta+JE',
        'bancario' => 'Contas e RAC',
        'cadastro' => 'Cadastro',
        'limites' => 'Limites',
        'arrecadacao' => 'Arrecadação',
        'gru' => 'GRU / União',
        'norma' => 'Normas e calendário',
        'suporte' => 'Suporte',
      ];
      $byGroup = [];
      foreach (($catalog['links'] ?? []) as $link) {
          if (!is_array($link)) {
              continue;
          }
          $g = (string) ($link['group'] ?? 'hub');
          $byGroup[$g][] = $link;
      }
      foreach ($groupLabels as $gk => $glabel):
          if (empty($byGroup[$gk])) {
              continue;
          }
    ?>
      <div style="margin-bottom:.85rem">
        <div class="fin-kicker" style="margin:0 0 .35rem"><?= e($glabel) ?></div>
        <div class="stack" style="gap:.3rem">
          <?php foreach ($byGroup[$gk] as $link): ?>
            <a href="<?= e((string) $link['url']) ?>" target="_blank" rel="noopener noreferrer" style="font-weight:700">
              <?= e((string) $link['label']) ?> ↗
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
