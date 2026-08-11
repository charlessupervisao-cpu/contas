<?php

declare(strict_types=1);

require_once dirname(dirname(__DIR__)) . '/bootstrap.php';

$user = Auth::requireLogin('fornecedores');
$activeModule = 'fornecedores';
$pageTitle = 'Fornecedor';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$id = trim((string) get('id', ''));

$st = $pdo->prepare('SELECT * FROM `Supplier` WHERE id = ? AND campaignId = ? LIMIT 1');
$st->execute([$id, $campaign['id'] ?? '']);
$s = $st->fetch();
if (!$s) {
    flash_set('danger', 'Fornecedor não encontrado.');
    redirect('/admin/fornecedores.php');
}

$exp = $pdo->prepare(
    "SELECT * FROM `Expense` WHERE supplierId = ? AND status <> 'CANCELADA'
     ORDER BY COALESCE(dataEmissao, date) DESC, numeroNf DESC"
);
$exp->execute([$id]);
$expenses = $exp->fetchAll();

$qtd = count($expenses);
$valor = array_sum(array_map(static fn ($n) => (float) $n['amount'], $expenses));

require dirname(dirname(__DIR__)) . '/templates/admin_layout_start.php';
?>
<div class="row-actions" style="margin-bottom:1rem">
  <a class="btn btn-ghost" href="<?= e(url_path('admin/fornecedores.php')) ?>">← Fornecedores</a>
  <?php if (can_launch($user['role'])): ?>
    <a class="btn btn-secondary" href="<?= e(url_path('admin/fornecedores.php?id=' . rawurlencode((string) $s['id']))) ?>#form-fornecedor">Editar cadastro</a>
    <a class="btn btn-primary" href="<?= e(url_path('admin/lancamento.php?tipo=DESPESA')) ?>">Novo lançamento</a>
  <?php endif; ?>
</div>

<div class="panel" style="margin-bottom:1rem">
  <h2 class="display" style="margin-top:0"><?= e((string) $s['name']) ?></h2>
  <p class="muted" style="margin:.35rem 0 0">
    Cadastro do emitente
    <?php if (!empty($s['activityType'])): ?>
      · <?= e(SUPPLIER_ACTIVITY_TYPES[$s['activityType']] ?? (string) $s['activityType']) ?>
    <?php endif; ?>
    — NF-e aparece nos lançamentos abaixo.
  </p>
  <div class="grid grid-4" style="margin-top:1rem">
    <div class="stat-chip"><div class="l">CNPJ / CPF</div><div class="n" style="font-size:1rem"><?= e((string) ($s['document'] ?: '—')) ?></div></div>
    <div class="stat-chip"><div class="l">Lançamentos</div><div class="n"><?= $qtd ?></div></div>
    <div class="stat-chip"><div class="l">Valor</div><div class="n"><?= e(money_br($valor)) ?></div></div>
    <div class="stat-chip"><div class="l">Município / UF</div><div class="n" style="font-size:.95rem"><?= e(trim(($s['city'] ?: '—') . ' / ' . ($s['state'] ?: '—'))) ?></div></div>
  </div>
  <?php if (!empty($s['phone']) || !empty($s['email']) || !empty($s['address'])): ?>
    <div class="muted" style="margin-top:1rem;font-size:.9rem">
      <?php if (!empty($s['phone'])): ?>Tel.: <?= e((string) $s['phone']) ?> · <?php endif; ?>
      <?php if (!empty($s['email'])): ?><?= e((string) $s['email']) ?> · <?php endif; ?>
      <?php if (!empty($s['zipCode']) || !empty($s['address'])): ?>
        <?= e(trim((string) ($s['address'] ?? '') . ', ' . (string) ($s['addressNumber'] ?? '') . ' — ' . (string) ($s['zipCode'] ?? ''))) ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<h3 class="display" style="margin-top:0">Lançamentos de despesa (NF-e)</h3>
<p class="muted">Tipo de operação, data de emissão, nº NF e valor ficam no lançamento — não no cadastro do fornecedor.</p>

<?php
  $opLabel = static function (?string $code): string {
      $c = strtoupper(trim((string) $code));
      if ($c === 'SERV') {
          $c = 'SERVICO';
      }
      if ($c === 'VEND') {
          $c = 'COMPRA';
      }
      return NFE_OPERATION_TYPES[$c] ?? ($c !== '' ? $c : '—');
  };
?>
<div class="panel table-wrap m-list-desktop">
  <table class="data">
    <thead>
      <tr>
        <th>Tipo op.</th>
        <th>Data Emissão</th>
        <th>Nº NF</th>
        <th>Valor</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$expenses): ?>
      <tr><td colspan="5" class="empty">Nenhum lançamento vinculado. Use “Novo lançamento” ou importe o CSV na lista de fornecedores.</td></tr>
    <?php endif; ?>
    <?php foreach ($expenses as $n): ?>
      <tr>
        <td><?= e($opLabel($n['naturezaOp'] ?? null)) ?></td>
        <td><?= e(!empty($n['dataEmissao']) ? date_br($n['dataEmissao']) : date_br($n['date'])) ?></td>
        <td><?= e((string) ($n['numeroNf'] ?: '—')) ?></td>
        <td><?= e(money_br((float) $n['amount'])) ?></td>
        <td><?= e((string) ($n['status'] ?: '—')) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="m-cards" aria-label="Lançamentos NF-e do fornecedor">
  <?php if (!$expenses): ?>
    <div class="panel empty">Nenhum lançamento vinculado. Use “Novo lançamento” ou importe o CSV na lista de fornecedores.</div>
  <?php endif; ?>
  <?php foreach ($expenses as $n): ?>
    <?php
      $emissao = !empty($n['dataEmissao']) ? date_br($n['dataEmissao']) : date_br($n['date']);
      $nfLabel = (string) (($n['numeroNf'] ?? '') !== '' ? $n['numeroNf'] : 'Sem nº NF');
    ?>
    <article class="m-card">
      <header class="m-card-head">
        <div>
          <div class="m-card-kicker"><?= e($emissao) ?> · <?= e($opLabel($n['naturezaOp'] ?? null)) ?></div>
          <div class="m-card-title">NF <?= e($nfLabel) ?></div>
        </div>
        <div class="m-card-amount out"><?= e(money_br((float) $n['amount'])) ?></div>
      </header>
      <dl class="m-card-meta">
        <div><dt>Status</dt><dd><?= e((string) ($n['status'] ?: '—')) ?></dd></div>
      </dl>
    </article>
  <?php endforeach; ?>
</div>
<?php require dirname(dirname(__DIR__)) . '/templates/admin_layout_end.php'; ?>
