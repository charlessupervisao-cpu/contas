<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$user = Auth::requireLogin('lancamento');
$activeModule = 'lancamento';
$pageTitle = 'Lançamento';
$pdo = Database::pdo();
$campaign = Metrics::getCampaign();
$accounts = $campaign ? Lancamento::listAccountsOrdered($campaign['id']) : [];
$cid = $campaign['id'] ?? '';
$supStmt = $pdo->prepare('SELECT id,name,document,activityType FROM `Supplier` WHERE campaignId=? AND active=1 ORDER BY name');
$supStmt->execute([$cid]);
$suppliers = $supStmt->fetchAll();
$cabosStmt = $pdo->prepare('SELECT id,fullName FROM `CaboEleitoral` WHERE campaignId=? AND active=1 ORDER BY fullName');
$cabosStmt->execute([$cid]);
$cabos = $cabosStmt->fetchAll();
$vehStmt = $pdo->prepare('SELECT id,label,plate FROM `Vehicle` WHERE campaignId=? AND active=1 ORDER BY label');
$vehStmt->execute([$cid]);
$vehicles = $vehStmt->fetchAll();

$tipo = strtoupper((string) get('tipo', 'DESPESA'));
if (!in_array($tipo, ['RECEITA', 'DESPESA'], true)) {
    $tipo = 'DESPESA';
}

$formError = null;
$old = [];

if (request_method() === 'POST') {
    Auth::requireMaster();
    $kind = (string) post('kind', $tipo);
    $category = (string) post('category', '');
    $bankAccountId = (string) post('bankAccountId', '');
    if ($bankAccountId === '' && $campaign && $kind === 'DESPESA' && $category) {
        $bankAccountId = Lancamento::getDefaultAccountId($campaign['id'], 'EXPENSE_CATEGORY', $category) ?? '';
    }
    $installments = ($kind === 'DESPESA' && !empty(post('installments2x'))) ? 2 : 1;
    $old = [
        'kind' => $kind,
        'amount' => (string) post('amount', ''),
        'date' => (string) post('date', ''),
        'bankAccountId' => $bankAccountId,
        'donorDocType' => (string) post('donorDocType', ''),
        'donorName' => (string) post('donorName', ''),
        'donorCpf' => (string) post('donorCpf', ''),
        'receiptNumber' => (string) post('receiptNumber', ''),
        'description' => (string) post('description', ''),
        'category' => $category,
        'supplierId' => (string) post('supplierId', ''),
        'naturezaOp' => (string) post('naturezaOp', ''),
        'dataEmissao' => (string) post('dataEmissao', ''),
        'numeroNf' => (string) post('numeroNf', ''),
        'caboId' => (string) post('caboId', ''),
        'vehicleId' => (string) post('vehicleId', ''),
        'installments' => (string) $installments,
        'installmentAmount1' => (string) post('installmentAmount1', ''),
        'installmentDate1' => (string) post('installmentDate1', ''),
        'installmentAmount2' => (string) post('installmentAmount2', ''),
        'installmentDate2' => (string) post('installmentDate2', ''),
    ];
    $result = Lancamento::create([
        'kind' => $kind,
        'amount' => post('amount'),
        'date' => post('date'),
        'description' => post('description'),
        'bankAccountId' => $bankAccountId,
        'donorDocType' => post('donorDocType'),
        'donorName' => post('donorName'),
        'donorCpf' => post('donorCpf'),
        'receiptNumber' => post('receiptNumber'),
        'category' => $category,
        'supplierId' => post('supplierId') ?: null,
        'naturezaOp' => post('naturezaOp'),
        'dataEmissao' => post('dataEmissao'),
        'numeroNf' => post('numeroNf'),
        'caboId' => post('caboId') ?: null,
        'vehicleId' => post('vehicleId') ?: null,
        'installments' => $installments,
        'installmentParts' => $installments === 2 ? [
            ['amount' => post('installmentAmount1'), 'date' => post('installmentDate1')],
            ['amount' => post('installmentAmount2'), 'date' => post('installmentDate2')],
        ] : [],
    ], $user['id']);
    if ($result['ok']) {
        $data = $result['data'] ?? [];
        if (($data['installments'] ?? 1) > 1) {
            flash_set(
                'ok',
                sprintf(
                    'Despesa parcelada em 2x registrada. Total %s como despesas futuras — pendentes de pagamento na Conciliação (sem débito em conta até pagar cada parcela em Despesas).',
                    money_br((float) ($data['total'] ?? 0))
                )
            );
        } else {
            flash_set('ok', 'Lançamento registrado com sucesso.');
        }
        redirect('/admin/lancamento.php?tipo=' . urlencode($kind));
    }
    // Mantém os dados na tela e mostra o erro (ex.: CNPJ inválido)
    $formError = (string) ($result['error'] ?? 'Erro ao lançar.');
    $tipo = $kind;
}

$val = static function (string $key, string $default = '') use ($old): string {
    return (string) ($old[$key] ?? $default);
};

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-form">
<div class="panel form-card animate-rise">
  <h2 class="display" style="margin-top:0">Lançamento</h2>
  <p class="muted" style="margin-top:0"><?= e(($user['name'] ?? '') . ' · ' . (ROLE_LABELS[$user['role']] ?? $user['role'])) ?></p>

  <?php if (!can_launch($user['role'])): ?>
    <div class="alert alert-warn">Somente o perfil Master pode lançar. Você está em modo consulta.</div>
  <?php endif; ?>

  <?php if ($formError): ?>
    <div class="alert alert-danger" data-form-error role="alert"><?= e($formError) ?></div>
  <?php else: ?>
    <div class="alert alert-danger" data-form-error hidden role="alert"></div>
  <?php endif; ?>

  <?php
    $oldAmount = $val('amount');
    $oldDate = $val('date', date('Y-m-d'));
    if ($oldDate === '') {
        $oldDate = date('Y-m-d');
    }
    $oldAccount = $val('bankAccountId');
    $oldDocType = strtoupper($val('donorDocType'));
    $oldDonorDoc = $val('donorCpf');
    if ($oldDonorDoc !== '') {
        $oldDonorDoc = format_cpf_cnpj($oldDonorDoc);
    }
    $docError = false;
    if ($formError) {
        $errLower = mb_strtolower($formError);
        $docError = str_contains($errLower, 'cpf') || str_contains($errLower, 'cnpj');
    }
  ?>

  <form method="post" data-mask-form data-lancamento-form novalidate>
    <input type="hidden" name="donorDocType" value="<?= e($oldDocType) ?>" data-donor-doc-type-value>
    <div class="field row-actions">
      <label><input type="radio" name="kind" value="RECEITA" data-kind-toggle <?= $tipo === 'RECEITA' ? 'checked' : '' ?>> Receita</label>
      <label><input type="radio" name="kind" value="DESPESA" data-kind-toggle <?= $tipo === 'DESPESA' ? 'checked' : '' ?>> Despesa</label>
    </div>

    <?php
      $oldInst = $val('installments') === '2';
      $oldInstAmt1 = $val('installmentAmount1');
      $oldInstAmt2 = $val('installmentAmount2');
      $oldInstDate1 = $val('installmentDate1', $oldDate);
      $oldInstDate2 = $val('installmentDate2');
      if ($oldInstDate2 === '') {
          try {
              $oldInstDate2 = (new DateTimeImmutable($oldDate ?: date('Y-m-d')))->modify('+1 month')->format('Y-m-d');
          } catch (Throwable) {
              $oldInstDate2 = $oldDate;
          }
      }
    ?>
    <div class="grid grid-2">
      <div class="field">
        <label class="label">Valor</label>
        <input class="input" name="amount" data-mask="money" data-amount-total inputmode="decimal" placeholder="0,00" value="<?= e($oldAmount) ?>" <?= can_launch($user['role']) ? '' : 'disabled data-locked-disabled' ?>>
        <div class="muted" data-amount-total-hint hidden style="font-size:.78rem;margin-top:.3rem">Total das 2 parcelas (atualiza automaticamente).</div>
      </div>
      <div class="field" data-installments-toggle hidden>
        <label class="label">Pagamento</label>
        <label class="deposit-who-option" style="display:inline-flex;max-width:100%;margin-top:.15rem">
          <input type="checkbox" name="installments2x" value="1" data-installments-2x <?= $oldInst ? 'checked' : '' ?>>
          <span>Parcelar em 2 vezes</span>
        </label>
      </div>
      <div class="field" data-single-date-field>
        <label class="label">Data</label>
        <input class="input" type="date" name="date" data-single-date value="<?= e($oldDate) ?>" <?= can_launch($user['role']) ? '' : 'disabled data-locked-disabled' ?>>
      </div>
    </div>

    <div class="panel nfe-launch-box" data-installments-panel <?= $oldInst && $tipo === 'DESPESA' ? '' : 'hidden' ?> style="margin-bottom:.85rem">
      <strong class="nfe-launch-title">Parcelas (valor e data de cada pagamento)</strong>
      <div class="grid grid-2">
        <div class="field">
          <label class="label">1ª parcela — valor</label>
          <input class="input" name="installmentAmount1" data-mask="money" data-inst-amount="1" inputmode="decimal" placeholder="0,00" value="<?= e($oldInstAmt1) ?>">
        </div>
        <div class="field">
          <label class="label">1ª parcela — data</label>
          <input class="input" type="date" name="installmentDate1" data-inst-date="1" value="<?= e($oldInstDate1) ?>">
        </div>
      </div>
      <div class="grid grid-2">
        <div class="field">
          <label class="label">2ª parcela — valor</label>
          <input class="input" name="installmentAmount2" data-mask="money" data-inst-amount="2" inputmode="decimal" placeholder="0,00" value="<?= e($oldInstAmt2) ?>">
        </div>
        <div class="field">
          <label class="label">2ª parcela — data</label>
          <input class="input" type="date" name="installmentDate2" data-inst-date="2" value="<?= e($oldInstDate2) ?>">
        </div>
      </div>
      <div class="muted" data-installments-hint style="font-size:.78rem;margin-top:.15rem">
        Em 2x: duas despesas futuras. Você define valor e data de cada parcela. O total entra no orçamento; o caixa só muda ao pagar em Despesas.
      </div>
    </div>

    <div class="field">
      <label class="label">Conta bancária</label>
      <select class="select" name="bankAccountId" data-bank-account <?= can_launch($user['role']) ? '' : 'disabled data-locked-disabled' ?>>
        <option value="" data-deposit-cpf="0" data-deposit-cnpj="0">— selecione a conta —</option>
        <?php foreach ($accounts as $i => $a): ?>
          <?php
            $depCpf = !empty($a['depositCpf']) ? '1' : '0';
            $depCnpj = !empty($a['depositCnpj']) ? '1' : '0';
            $who = [];
            if ($depCpf === '1') {
                $who[] = 'CPF';
            }
            if ($depCnpj === '1') {
                $who[] = 'CNPJ';
            }
            $whoLabel = $who ? ' · depósito ' . implode('/', $who) : '';
          ?>
          <option
            value="<?= e($a['id']) ?>"
            data-deposit-cpf="<?= $depCpf ?>"
            data-deposit-cnpj="<?= $depCnpj ?>"
            <?= $oldAccount === (string) $a['id'] ? 'selected' : '' ?>
          >Conta <?= $i + 1 ?> — <?= e($a['label']) ?> (<?= e(money_br($a['balance'])) ?><?= e($whoLabel) ?>)</option>
        <?php endforeach; ?>
      </select>
      <div class="muted" data-account-hint style="font-size:.78rem;margin-top:.3rem">
        Em receita, a conta define se o doador informa CPF ou CNPJ.
      </div>
    </div>

    <div data-kind-block="RECEITA">
      <div data-donor-fields hidden>
        <div class="field" data-donor-type-picker hidden>
          <label class="label">Quem está depositando</label>
          <div class="row-actions deposit-who-options" style="margin:0">
            <label class="deposit-who-option">
              <input type="radio" name="donorDocTypeChoice" value="CPF" data-donor-doc-type <?= $oldDocType === 'CPF' ? 'checked' : '' ?>>
              <span>CPF</span>
            </label>
            <label class="deposit-who-option">
              <input type="radio" name="donorDocTypeChoice" value="CNPJ" data-donor-doc-type <?= $oldDocType === 'CNPJ' ? 'checked' : '' ?>>
              <span>CNPJ</span>
            </label>
          </div>
        </div>
        <div class="grid grid-2">
          <div class="field">
            <label class="label">Nome do doador</label>
            <input class="input" name="donorName" data-donor-name autocomplete="name" value="<?= e($val('donorName')) ?>">
          </div>
          <div class="field">
            <label class="label" data-donor-doc-label><?= $oldDocType === 'CNPJ' ? 'CNPJ do doador' : 'CPF do doador' ?></label>
            <input
              class="input<?= $docError ? ' is-invalid-input' : '' ?>"
              name="donorCpf"
              data-donor-doc-input
              data-mask="<?= $oldDocType === 'CNPJ' ? 'cnpj' : 'cpf' ?>"
              inputmode="numeric"
              placeholder="<?= $oldDocType === 'CNPJ' ? '00.000.000/0000-00' : '000.000.000-00' ?>"
              autocomplete="off"
              value="<?= e($oldDonorDoc) ?>"
            >
            <div class="field-error" data-donor-doc-error<?= $docError ? '' : ' hidden' ?>>
              <?= $docError ? e((string) $formError) : '' ?>
            </div>
          </div>
        </div>
      </div>
      <p class="muted" data-donor-wait style="font-size:.82rem;margin:.15rem 0 .75rem">
        Selecione a conta bancária para liberar os dados do doador.
      </p>
      <div class="field">
        <label class="label">Recibo</label>
        <input class="input" name="receiptNumber" value="<?= e($val('receiptNumber')) ?>">
      </div>
    </div>

    <div data-kind-block="DESPESA">
      <div class="field">
        <label class="label">Categoria</label>
        <select class="select" name="category">
          <?php foreach (Categories::map() as $code => $label): ?>
            <option value="<?= e($code) ?>" <?= $val('category') === $code ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="label">Fornecedor</label>
        <select class="select" name="supplierId">
          <option value="">— selecionar —</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= e($s['id']) ?>">
              <?= e($s['name']) ?><?= !empty($s['document']) ? ' · ' . e((string) $s['document']) : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="panel nfe-launch-box">
        <strong class="nfe-launch-title">Dados da NF-e <span class="muted" style="font-weight:500">(opcional)</span></strong>
        <div class="field" style="margin-bottom:.65rem">
          <label class="label">Tipo de operação</label>
          <div class="row-actions deposit-who-options" style="margin:0">
            <?php $oldNat = strtoupper($val('naturezaOp')); if ($oldNat === 'SERV') { $oldNat = 'SERVICO'; } if ($oldNat === 'VEND') { $oldNat = 'COMPRA'; } ?>
            <label class="deposit-who-option">
              <input type="radio" name="naturezaOp" value="COMPRA" <?= $oldNat === 'COMPRA' ? 'checked' : '' ?>>
              <span>Compra</span>
            </label>
            <label class="deposit-who-option">
              <input type="radio" name="naturezaOp" value="SERVICO" <?= $oldNat === 'SERVICO' ? 'checked' : '' ?>>
              <span>Serviço</span>
            </label>
          </div>
        </div>
        <div class="grid grid-2">
          <div class="field">
            <label class="label">Data Emissão</label>
            <input class="input" type="date" name="dataEmissao" data-mask="date" value="<?= e($val('dataEmissao')) ?>">
          </div>
          <div class="field">
            <label class="label">Nº NF</label>
            <input class="input" name="numeroNf" inputmode="numeric" value="<?= e($val('numeroNf')) ?>">
          </div>
        </div>
      </div>

      <div class="grid grid-2">
        <div class="field">
          <label class="label">Cabo (se aplicável)</label>
          <select class="select" name="caboId">
            <option value="">—</option>
            <?php foreach ($cabos as $c): ?>
              <option value="<?= e($c['id']) ?>"><?= e($c['fullName']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label class="label">Veículo</label>
          <select class="select" name="vehicleId">
            <option value="">—</option>
            <?php foreach ($vehicles as $v): ?>
              <option value="<?= e($v['id']) ?>"><?= e($v['label']) ?> · <?= e($v['plate']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

    </div>

    <div class="field">
      <label class="label">Descrição</label>
      <textarea class="textarea" name="description" data-shared-description><?= e($val('description')) ?></textarea>
    </div>

    <div class="row-actions" style="margin-top:1rem">
      <?php if (can_launch($user['role'])): ?>
        <button class="btn btn-primary" type="submit" data-lancamento-submit>Registrar lançamento</button>
      <?php endif; ?>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">Voltar</a>
    </div>
  </form>
</div>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
