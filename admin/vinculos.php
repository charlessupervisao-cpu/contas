<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$user = Auth::requireLogin('vinculos');
$activeModule = 'vinculos';
$pageTitle = 'Vínculos conta';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$accounts = $campaign ? Lancamento::listAccountsOrdered($campaign['id']) : [];

if (request_method() === 'POST') {
    Auth::requireMaster();
    $items = [];
    foreach (Categories::map(false) as $code => $label) {
        $items[] = ['kind' => 'EXPENSE_CATEGORY', 'code' => $code, 'bankAccountId' => post('exp_'.$code) ?: null];
    }
    foreach (REVENUE_SOURCES as $code => $label) {
        $items[] = ['kind' => 'REVENUE_SOURCE', 'code' => $code, 'bankAccountId' => post('rev_'.$code) ?: null];
    }
    Lancamento::upsertMappings($campaign['id'], $items);
    flash_set('ok', 'Vínculos salvos.');
    redirect('/admin/vinculos.php');
}

$mapStmt = $pdo->prepare('SELECT kind, code, bankAccountId FROM `AccountMapping` WHERE campaignId=?');
$mapStmt->execute([$campaign['id'] ?? '']);
$map = [];
foreach ($mapStmt->fetchAll() as $m) {
    $map[$m['kind'].'|'.$m['code']] = $m['bankAccountId'];
}

$accountOptionLabel = static function (int $i, array $a): string {
    $base = 'Conta ' . ($i + 1) . ' — ' . (string) $a['label'];
    $origin = (string) ($a['resourceOrigin'] ?? '');
    if ($origin !== '') {
        $base .= ' · ' . ElectoralRules::bankOriginLabel($origin);
    }
    return $base;
};

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-form">
<div class="panel form-card animate-rise">
  <p class="muted">Defina em qual conta (1–4) cada categoria de despesa ou fonte de receita entra por padrão.</p>
  <p class="muted" style="margin-top:.35rem;line-height:1.45">
    Conta+JE: cada fonte de receita deve mapear para a conta bancária com a mesma fonte do recurso
    (Doações para Campanha, Fundo Partidário ou FEFC).
  </p>
  <form method="post">
    <h3 class="display">Despesas</h3>
    <div class="grid grid-2">
    <?php foreach (Categories::map(false) as $code => $label): ?>
      <div class="field">
        <label class="label"><?= e($label) ?></label>
        <select class="select" name="exp_<?= e($code) ?>" <?= can_launch($user['role'])?'':'disabled' ?>>
          <option value="">— sem vínculo —</option>
          <?php foreach ($accounts as $i => $a): ?>
            <option value="<?= e($a['id']) ?>" <?= ($map['EXPENSE_CATEGORY|'.$code] ?? '') === $a['id'] ? 'selected' : '' ?>><?= e($accountOptionLabel($i, $a)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endforeach; ?>
    </div>
    <h3 class="display">Receitas</h3>
    <div class="grid grid-2">
    <?php foreach (REVENUE_SOURCES as $code => $label): ?>
      <div class="field">
        <label class="label"><?= e($label) ?></label>
        <select class="select" name="rev_<?= e($code) ?>" <?= can_launch($user['role'])?'':'disabled' ?>>
          <option value="">— sem vínculo —</option>
          <?php foreach ($accounts as $i => $a): ?>
            <option value="<?= e($a['id']) ?>" <?= ($map['REVENUE_SOURCE|'.$code] ?? '') === $a['id'] ? 'selected' : '' ?>><?= e($accountOptionLabel($i, $a)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endforeach; ?>
    </div>
    <div class="row-actions" style="margin-top:1rem">
      <?php if (can_launch($user['role'])): ?>
        <button class="btn btn-primary" type="submit">Salvar vínculos</button>
      <?php endif; ?>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">Voltar</a>
    </div>
  </form>
</div>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
