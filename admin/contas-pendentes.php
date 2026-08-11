<?php
/**
 * Contas parceladas e futuras — pendentes de conciliação / baixa manual.
 * Não saíram da conta bancária até o operador dar baixa.
 */
require_once dirname(__DIR__) . '/bootstrap.php';
$user = Auth::requireLogin('contas-pendentes');
$activeModule = 'contas-pendentes';
$pageTitle = 'Contas parceladas e futuras';
$canWrite = can_launch($user['role']);
$campaign = Metrics::getCampaign();

if (request_method() === 'POST' && $canWrite) {
    Auth::requireMaster();
    $action = (string) post('action', '');
    if ($action === 'pay') {
        $r = LancamentoCrud::payFutureExpense((string) post('id'), $user['id']);
        flash_set(
            $r['ok'] ? 'ok' : 'danger',
            $r['ok']
                ? 'Baixa manual concluída: saldo da conta debitado. Item segue na Conciliação para conferência do extrato.'
                : ($r['error'] ?? 'Erro na baixa.')
        );
        redirect('/admin/contas-pendentes.php');
    }
    if ($action === 'pay_due_today') {
        $cid = (string) ($campaign['id'] ?? '');
        $r = LancamentoCrud::payDueExpensesThrough(date('Y-m-d'), $user['id'], $cid !== '' ? $cid : null);
        $n = (int) ($r['paid'] ?? 0);
        flash_set(
            'ok',
            $n > 0
                ? sprintf('Baixa das vencidas hoje: %d conta(s) · %s debitados.', $n, money_br((float) ($r['total'] ?? 0)))
                : 'Nenhuma conta com vencimento hoje para baixar.'
        );
        redirect('/admin/contas-pendentes.php');
    }
}

$rows = Metrics::listPendingFutureExpenses($campaign['id'] ?? null);
$total = array_sum(array_column($rows, 'amount'));
$todayYmd = (new DateTimeImmutable('today'))->format('Y-m-d');
$dueToday = 0;
foreach ($rows as $r) {
    if (substr((string) ($r['date'] ?? ''), 0, 10) <= $todayYmd) {
        $dueToday++;
    }
}

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-toolbar filter-panel">
  <div class="page-toolbar-main">
    <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Dashboard</a>
    <div>
      <div class="fin-kicker" style="margin:0">Pendentes de conciliação</div>
      <strong style="font-size:1.05rem">Contas parceladas e futuras</strong>
    </div>
  </div>
  <div class="page-toolbar-side">
    <span class="badge badge-warn"><?= count($rows) ?> pendente(s)</span>
    <span class="badge badge-warn"><?= e(money_br($total)) ?></span>
    <?php if ($canWrite && $dueToday > 0): ?>
      <form method="post" onsubmit="return confirm('Dar baixa em todas as contas vencidas até hoje? O saldo das contas será debitado.');">
        <input type="hidden" name="action" value="pay_due_today">
        <button class="btn btn-secondary" type="submit">Baixar vencidas (<?= (int) $dueToday ?>)</button>
      </form>
    <?php endif; ?>
    <a class="btn btn-ghost" href="<?= e(url_path('admin/conciliacao.php?accountId=all')) ?>">Ver conciliação</a>
  </div>
</div>

<p class="muted" style="margin:0 0 1rem;line-height:1.45;max-width:48rem">
  Contas com <strong>vencimento futuro</strong> ou <strong>parceladas</strong> entram no orçamento, mas
  <strong>não são consideradas pagas</strong> enquanto o dinheiro não sair da conta.
  Use a <strong>baixa manual</strong> para debitar o saldo; o extrato permanece pendente na Conciliação até a conferência.
</p>

<div class="panel table-wrap m-list-desktop">
<table class="data">
  <thead>
    <tr>
      <th>Vencimento</th>
      <th>Tipo</th>
      <th>Descrição</th>
      <th>Categoria</th>
      <th>Conta</th>
      <th>Valor</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php if (!$rows): ?>
    <tr><td colspan="7" class="empty">Nenhuma conta parcelada ou futura pendente.</td></tr>
  <?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <?php
      $due = substr((string) ($r['date'] ?? ''), 0, 10);
      $isOverdue = $due !== '' && $due <= $todayYmd;
      $isParcel = (int) ($r['installmentCount'] ?? 0) > 1;
    ?>
    <tr>
      <td>
        <?= e(date_br($r['date'])) ?>
        <?php if ($isOverdue): ?><div><span class="badge badge-danger">Vencida</span></div><?php endif; ?>
      </td>
      <td>
        <?php if ($isParcel): ?>
          <span class="badge badge-warn">Parcela <?= (int) $r['installmentNumber'] ?>/<?= (int) $r['installmentCount'] ?></span>
        <?php else: ?>
          <span class="badge badge-warn">Futura</span>
        <?php endif; ?>
      </td>
      <td>
        <strong><?= e((string) ($r['supplierName'] ?: '—')) ?></strong>
        <div class="muted" style="font-size:.78rem"><?= e(mb_strimwidth((string) $r['description'], 0, 80, '…')) ?></div>
      </td>
      <td><?= e(Categories::label((string) $r['category'])) ?></td>
      <td><?= e((string) ($r['accountLabel'] ?: '—')) ?></td>
      <td><strong><?= e(money_br((float) $r['amount'])) ?></strong></td>
      <td>
        <?php if ($canWrite): ?>
          <form method="post" onsubmit="return confirm('Confirmar baixa manual desta conta? O valor será debitado do saldo da conta bancária.');">
            <input type="hidden" name="action" value="pay">
            <input type="hidden" name="id" value="<?= e((string) $r['id']) ?>">
            <button class="btn btn-primary" type="submit">Baixa manual</button>
          </form>
        <?php else: ?>
          <span class="muted">Somente leitura</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="m-cards" aria-label="Contas pendentes">
  <?php if (!$rows): ?><div class="panel empty">Nenhuma conta parcelada ou futura pendente.</div><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <?php
      $due = substr((string) ($r['date'] ?? ''), 0, 10);
      $isOverdue = $due !== '' && $due <= $todayYmd;
      $isParcel = (int) ($r['installmentCount'] ?? 0) > 1;
    ?>
    <article class="m-card">
      <header class="m-card-head">
        <div>
          <div class="m-card-kicker">
            <?= e(date_br($r['date'])) ?>
            <?php if ($isParcel): ?>
              · <span class="badge badge-warn">Parc. <?= (int) $r['installmentNumber'] ?>/<?= (int) $r['installmentCount'] ?></span>
            <?php else: ?>
              · <span class="badge badge-warn">Futura</span>
            <?php endif; ?>
            <?php if ($isOverdue): ?> · <span class="badge badge-danger">Vencida</span><?php endif; ?>
          </div>
          <div class="m-card-title"><?= e((string) ($r['supplierName'] ?: 'Sem fornecedor')) ?></div>
        </div>
        <div class="m-card-amount out"><?= e(money_br((float) $r['amount'])) ?></div>
      </header>
      <p class="m-card-desc"><?= e((string) $r['description']) ?></p>
      <dl class="m-card-meta">
        <div><dt>Categoria</dt><dd><?= e(Categories::label((string) $r['category'])) ?></dd></div>
        <div><dt>Conta</dt><dd><?= e((string) ($r['accountLabel'] ?: '—')) ?></dd></div>
      </dl>
      <?php if ($canWrite): ?>
      <footer class="m-card-actions">
        <form method="post" onsubmit="return confirm('Confirmar baixa manual desta conta?');">
          <input type="hidden" name="action" value="pay">
          <input type="hidden" name="id" value="<?= e((string) $r['id']) ?>">
          <button class="btn btn-primary" type="submit">Baixa manual</button>
        </form>
      </footer>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
