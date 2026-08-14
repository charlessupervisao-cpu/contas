<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('fornecedores');
$activeModule = 'fornecedores';
$pageTitle = '6 · Fornecedores';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$errors = [];
$editId = trim((string) get('id', ''));
$edit = null;

if ($editId !== '' && $campaign) {
    $st = $pdo->prepare('SELECT * FROM `Supplier` WHERE id = ? AND campaignId = ? LIMIT 1');
    $st->execute([$editId, $campaign['id']]);
    $edit = $st->fetch() ?: null;
}

if (request_method() === 'POST' && $canWrite) {
    Auth::requireMaster();
    $action = (string) post('action', 'save');
    $now = now_sql();

    if ($action === 'import_csv_nfe' || $action === 'replace_tse' || $action === 'import_tse') {
        if (!$campaign) {
            flash_set('danger', 'Campanha não encontrada.');
            redirect('/admin/fornecedores.php');
        }
        $result = TseNfeCsv::replaceFromCsv((string) $campaign['id']);
        if (!empty($result['error'])) {
            flash_set('danger', $result['error']);
        } else {
            flash_set(
                'ok',
                sprintf(
                    'Importação: %d fornecedores (cadastro) · %d NF-es gravadas nos lançamentos de despesa.',
                    (int) $result['suppliers'],
                    (int) ($result['expenses'] ?? $result['nfes'])
                )
            );
        }
        redirect('/admin/fornecedores.php');
    }

    if ($action === 'delete') {
        $id = (string) post('id');
        $pdo->prepare('DELETE FROM `Supplier` WHERE id = ? AND campaignId = ?')->execute([$id, $campaign['id'] ?? '']);
        flash_set('ok', 'Fornecedor removido.');
        redirect('/admin/fornecedores.php');
    }

    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        $documentType = trim((string) post('documentType', 'CNPJ'));
        if (!in_array($documentType, ['CNPJ', 'CPF'], true)) {
            $documentType = 'CNPJ';
        }
        $document = format_cpf_cnpj((string) post('document', ''));
        $name = trim((string) post('name', ''));
        $tradeName = trim((string) post('tradeName', ''));
        $email = trim((string) post('email', ''));
        $phone = format_phone_br((string) post('phone', ''));
        $contactName = trim((string) post('contactName', ''));
        $zipCode = format_cep_br((string) post('zipCode', ''));
        $address = trim((string) post('address', ''));
        $addressNumber = trim((string) post('addressNumber', ''));
        $addressComplement = trim((string) post('addressComplement', ''));
        $neighborhood = trim((string) post('neighborhood', ''));
        $city = trim((string) post('city', ''));
        $state = strtoupper(trim((string) post('state', '')));
        $stateRegistration = trim((string) post('stateRegistration', ''));
        $municipalRegistration = trim((string) post('municipalRegistration', ''));
        $activityType = trim((string) post('activityType', ''));
        if ($activityType !== '' && !isset(SUPPLIER_ACTIVITY_TYPES[$activityType])) {
            $activityType = '';
        }
        $birthDateRaw = trim((string) post('birthDate', ''));
        $birthDate = null;
        if ($documentType === 'CPF' && $birthDateRaw !== '') {
            $birthDate = preg_match('/^\d{4}-\d{2}-\d{2}/', $birthDateRaw)
                ? substr($birthDateRaw, 0, 10)
                : substr(parse_date_input($birthDateRaw), 0, 10);
        }
        $notes = trim((string) post('notes', ''));

        if ($name === '') {
            $errors[] = 'Informe a razão social / nome do fornecedor.';
        }
        if ($documentType === 'CNPJ' && !is_valid_cnpj($document)) {
            $errors[] = 'CNPJ inválido.';
        }
        if ($documentType === 'CPF' && !is_valid_cpf($document)) {
            $errors[] = 'CPF inválido.';
        }
        if ($state !== '' && !preg_match('/^[A-Z]{2}$/', $state)) {
            $errors[] = 'UF inválida.';
        }
        if ($activityType === '') {
            $errors[] = 'Selecione o tipo de atividade (Serviço ou Venda).';
        }
        $phoneDigits = only_digits($phone);
        if ($phone !== '' && strlen($phoneDigits) < 10) {
            $errors[] = 'Telefone inválido. Use (DD) XXXX-XXXX ou (DD) XXXXX-XXXX.';
        }

        if (!$errors && $campaign) {
            // CPF: não persiste fantasia/contato/IE/IM
            if ($documentType === 'CPF') {
                $tradeName = '';
                $contactName = '';
                $stateRegistration = '';
                $municipalRegistration = '';
            } else {
                $birthDate = null;
            }

            $params = [
                $documentType,
                $document !== '' ? $document : null,
                $name,
                $tradeName !== '' ? $tradeName : null,
                $email !== '' ? $email : null,
                $phone !== '' ? $phone : null,
                $contactName !== '' ? $contactName : null,
                $zipCode !== '' ? $zipCode : null,
                $address !== '' ? $address : null,
                $addressNumber !== '' ? $addressNumber : null,
                $addressComplement !== '' ? $addressComplement : null,
                $neighborhood !== '' ? $neighborhood : null,
                $city !== '' ? $city : null,
                $state !== '' ? $state : null,
                $stateRegistration !== '' ? $stateRegistration : null,
                $municipalRegistration !== '' ? $municipalRegistration : null,
                $activityType !== '' ? $activityType : null,
                $birthDate,
                $notes !== '' ? $notes : null,
                $now,
            ];

            if ($id !== '') {
                $pdo->prepare(
                    'UPDATE `Supplier` SET documentType=?, document=?, name=?, tradeName=?, email=?, phone=?, contactName=?,
                     zipCode=?, address=?, addressNumber=?, addressComplement=?, neighborhood=?, city=?, state=?,
                     stateRegistration=?, municipalRegistration=?, activityType=?, birthDate=?, notes=?, updatedAt=?
                     WHERE id=? AND campaignId=?'
                )->execute([...$params, $id, $campaign['id']]);
                flash_set('ok', 'Fornecedor atualizado.');
            } else {
                $pdo->prepare(
                    'INSERT INTO `Supplier` (
                        id, campaignId, documentType, document, name, tradeName, email, phone, contactName,
                        zipCode, address, addressNumber, addressComplement, neighborhood, city, state,
                        stateRegistration, municipalRegistration, activityType, birthDate, notes, active, createdAt, updatedAt
                     ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?)'
                )->execute([cuid(), $campaign['id'], ...array_slice($params, 0, 19), $now, $now]);
                flash_set('ok', 'Fornecedor cadastrado.');
            }
            redirect('/admin/fornecedores.php');
        }
    }
}

$rows = [];
if ($campaign) {
    $st = $pdo->prepare(
        "SELECT s.*,
                COUNT(e.id) AS qtd,
                COALESCE(SUM(e.amount), 0) AS valor
         FROM `Supplier` s
         LEFT JOIN `Expense` e ON e.supplierId = s.id AND e.status <> 'CANCELADA'
         WHERE s.campaignId = ?
         GROUP BY s.id
         ORDER BY s.name ASC"
    );
    $st->execute([$campaign['id']]);
    $rows = $st->fetchAll();
}

$totalValor = array_sum(array_map(static fn ($r) => (float) $r['valor'], $rows));
$totalQtd = (int) array_sum(array_map(static fn ($r) => (int) $r['qtd'], $rows));
$wantNew = trim((string) get('novo', '')) === '1';
$formMode = $canWrite && ($edit !== null || $wantNew || $errors !== []);
$dt = (string) ($edit['documentType'] ?? (string) post('documentType', 'CNPJ'));
if (!in_array($dt, ['CNPJ', 'CPF'], true)) {
    $dt = 'CNPJ';
}
$act = (string) ($edit['activityType'] ?? (string) post('activityType', ''));

// Em erro de validação, reexibe o que o usuário digitou
if ($errors && request_method() === 'POST' && (string) post('action', '') === 'save') {
    $edit = [
        'id' => trim((string) post('id', '')),
        'documentType' => $dt,
        'document' => (string) post('document', ''),
        'name' => (string) post('name', ''),
        'tradeName' => (string) post('tradeName', ''),
        'contactName' => (string) post('contactName', ''),
        'phone' => (string) post('phone', ''),
        'birthDate' => (string) post('birthDate', ''),
        'email' => (string) post('email', ''),
        'zipCode' => (string) post('zipCode', ''),
        'address' => (string) post('address', ''),
        'addressNumber' => (string) post('addressNumber', ''),
        'addressComplement' => (string) post('addressComplement', ''),
        'neighborhood' => (string) post('neighborhood', ''),
        'city' => (string) post('city', ''),
        'state' => (string) post('state', 'GO'),
        'activityType' => $act,
        'stateRegistration' => (string) post('stateRegistration', ''),
        'municipalRegistration' => (string) post('municipalRegistration', ''),
        'notes' => (string) post('notes', ''),
    ];
    $formMode = true;
}

if (!is_array($edit)) {
    $edit = [];
}

require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>

<?php if ($formMode): ?>
<div class="row-actions" style="margin-bottom:1rem;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
  <a class="btn btn-ghost" href="<?= e(url_path('admin/fornecedores.php')) ?>">← Voltar</a>
</div>

<div class="panel form-card" id="form-fornecedor" style="margin-top:0">
  <h2 class="display" style="margin-top:0"><?= ($edit['id'] ?? '') !== '' ? 'Editar fornecedor' : 'Novo fornecedor' ?></h2>
  <p class="muted">Cadastro do emitente. Número de nota, chave e série ficam no <a href="<?= e(url_path('admin/lancamento.php?tipo=DESPESA')) ?>">lançamento de despesa</a>.</p>

  <?php if ($errors): ?>
    <div class="alert alert-danger" style="margin-bottom:1rem">
      <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" data-mask-form data-supplier-form>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">

    <div class="grid grid-2">
      <div class="field">
        <label class="label">Tipo de documento</label>
        <select class="input" name="documentType" data-supplier-doctype required>
          <option value="CNPJ" <?= $dt === 'CNPJ' ? 'selected' : '' ?>>CNPJ</option>
          <option value="CPF" <?= $dt === 'CPF' ? 'selected' : '' ?>>CPF</option>
        </select>
      </div>
      <div class="field">
        <label class="label">CPF / CNPJ</label>
        <input class="input" name="document" data-supplier-document data-mask="<?= $dt === 'CPF' ? 'cpf' : 'cnpj' ?>" <?= $dt === 'CNPJ' ? 'data-cnpj-lookup' : '' ?> inputmode="numeric" maxlength="<?= $dt === 'CPF' ? '14' : '18' ?>" value="<?= e((string) ($edit['document'] ?? '')) ?>" placeholder="<?= $dt === 'CPF' ? '000.000.000-00' : '00.000.000/0000-00' ?>" required>
        <div data-cnpj-status class="muted" style="margin-top:.35rem;font-size:.82rem">
          <?= $dt === 'CNPJ'
            ? 'Ao completar o CNPJ (14 dígitos), buscamos razão social, endereço e inscrição estadual.'
            : 'Informe o CPF manualmente (sem busca automática).' ?>
        </div>
      </div>
    </div>

    <div class="field">
      <label class="label">Razão social / Nome</label>
      <input class="input" name="name" value="<?= e((string) ($edit['name'] ?? '')) ?>" required>
    </div>

    <div data-show-when-doc="CNPJ" <?= $dt !== 'CNPJ' ? 'hidden' : '' ?>>
      <div class="field">
        <label class="label">Nome fantasia</label>
        <input class="input" name="tradeName" value="<?= e((string) ($edit['tradeName'] ?? '')) ?>" <?= $dt !== 'CNPJ' ? 'disabled' : '' ?>>
      </div>
      <div class="grid grid-2">
        <div class="field">
          <label class="label">Contato</label>
          <input class="input" name="contactName" value="<?= e((string) ($edit['contactName'] ?? '')) ?>" <?= $dt !== 'CNPJ' ? 'disabled' : '' ?>>
        </div>
        <div class="field">
          <label class="label">Telefone</label>
          <input class="input" name="phone" data-mask="phone" inputmode="tel" maxlength="16" value="<?= e((string) ($edit['phone'] ?? '')) ?>" placeholder="(62) 99999-9999" <?= $dt !== 'CNPJ' ? 'disabled' : '' ?>>
        </div>
      </div>
    </div>

    <div data-show-when-doc="CPF" <?= $dt !== 'CPF' ? 'hidden' : '' ?>>
      <div class="grid grid-2">
        <div class="field">
          <label class="label">Dt nascimento</label>
          <input class="input" type="date" name="birthDate" value="<?= e(!empty($edit['birthDate']) ? substr((string) $edit['birthDate'], 0, 10) : '') ?>" <?= $dt !== 'CPF' ? 'disabled' : '' ?>>
        </div>
        <div class="field">
          <label class="label">Telefone</label>
          <input class="input" name="phone" data-mask="phone" inputmode="tel" maxlength="16" value="<?= e((string) ($edit['phone'] ?? '')) ?>" placeholder="(62) 99999-9999" <?= $dt !== 'CPF' ? 'disabled' : '' ?>>
        </div>
      </div>
    </div>

    <div class="field">
      <label class="label">E-mail</label>
      <input class="input" type="email" name="email" value="<?= e((string) ($edit['email'] ?? '')) ?>">
    </div>

    <div class="grid grid-2">
      <div class="field">
        <label class="label">CEP</label>
        <input class="input" name="zipCode" data-mask="cep" data-cep-lookup inputmode="numeric" maxlength="9" value="<?= e((string) ($edit['zipCode'] ?? '')) ?>" placeholder="00000-000">
        <div data-cep-status class="muted" style="margin-top:.35rem;font-size:.82rem">Ao completar o CEP (8 dígitos), preenchemos o endereço.</div>
      </div>
      <div class="field">
        <label class="label">Logradouro</label>
        <input class="input" name="address" value="<?= e((string) ($edit['address'] ?? '')) ?>">
      </div>
    </div>
    <div class="grid grid-2">
      <div class="field">
        <label class="label">Número</label>
        <input class="input" name="addressNumber" value="<?= e((string) ($edit['addressNumber'] ?? '')) ?>">
      </div>
      <div class="field">
        <label class="label">Complemento</label>
        <input class="input" name="addressComplement" value="<?= e((string) ($edit['addressComplement'] ?? '')) ?>">
      </div>
    </div>
    <div class="grid grid-2">
      <div class="field">
        <label class="label">Bairro</label>
        <input class="input" name="neighborhood" value="<?= e((string) ($edit['neighborhood'] ?? '')) ?>">
      </div>
      <div class="field">
        <label class="label">Município</label>
        <input class="input" name="city" value="<?= e((string) ($edit['city'] ?? '')) ?>">
      </div>
    </div>
    <div class="grid grid-2">
      <div class="field">
        <label class="label">UF</label>
        <input class="input" name="state" maxlength="2" value="<?= e((string) ($edit['state'] ?? 'GO')) ?>" style="text-transform:uppercase">
      </div>
      <div class="field">
        <label class="label">Categoria / natureza (atividade)</label>
        <select class="input" name="activityType" required>
          <option value="">— selecione —</option>
          <?php foreach (SUPPLIER_ACTIVITY_TYPES as $code => $label): ?>
            <option value="<?= e($code) ?>" <?= $act === $code ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="grid grid-2" data-show-when-doc="CNPJ" <?= $dt !== 'CNPJ' ? 'hidden' : '' ?>>
      <div class="field">
        <label class="label">Inscrição estadual</label>
        <input class="input" name="stateRegistration" value="<?= e((string) ($edit['stateRegistration'] ?? '')) ?>" <?= $dt !== 'CNPJ' ? 'disabled' : '' ?>>
      </div>
      <div class="field">
        <label class="label">Inscrição municipal</label>
        <input class="input" name="municipalRegistration" value="<?= e((string) ($edit['municipalRegistration'] ?? '')) ?>" <?= $dt !== 'CNPJ' ? 'disabled' : '' ?>>
      </div>
    </div>

    <div class="field">
      <label class="label">Observações</label>
      <textarea class="input" name="notes" rows="3"><?= e((string) ($edit['notes'] ?? '')) ?></textarea>
    </div>

    <div class="row-actions">
      <button class="btn btn-primary" type="submit">Salvar fornecedor</button>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/fornecedores.php')) ?>">Voltar</a>
    </div>
  </form>
</div>

<?php else: ?>

<div class="panel" style="margin-bottom:1rem">
  <div class="row-actions" style="justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap">
    <div>
      <h2 class="display" style="margin:0">Fornecedores</h2>
      <p class="muted" style="margin:.35rem 0 0">
        Cadastro do emitente (CNPJ/CPF). <strong>Número de NF-e fica no lançamento de despesa</strong>, não aqui.
      </p>
    </div>
    <?php if ($canWrite): ?>
      <div class="row-actions">
        <form method="post" onsubmit="return confirm('Importar CSV do TSE: atualiza cadastro de emitentes e grava as NF-es como lançamentos de despesa?');">
          <input type="hidden" name="action" value="import_csv_nfe">
          <button class="btn btn-secondary" type="submit">Importar CSV → lançamentos</button>
        </form>
        <a class="btn btn-primary" href="<?= e(url_path('admin/fornecedores.php?novo=1')) ?>">Novo fornecedor</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="row-actions" style="margin-bottom:1rem">
  <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Voltar</a>
</div>

<div class="grid grid-3" style="margin-bottom:1rem">
  <div class="stat-chip"><div class="l">Fornecedores</div><div class="n"><?= count($rows) ?></div></div>
  <div class="stat-chip"><div class="l">Lançamentos vinculados</div><div class="n"><?= number_format($totalQtd, 0, ',', '.') ?></div></div>
  <div class="stat-chip"><div class="l">Valor em despesas</div><div class="n"><?= e(money_br($totalValor)) ?></div></div>
</div>

<div class="panel table-wrap m-list-desktop">
  <table class="data">
    <thead>
      <tr>
        <th>Nome</th>
        <th>CPF/CNPJ</th>
        <th>Atividade</th>
        <th>Lançamentos</th>
        <th>Valor</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="6" class="empty">Nenhum fornecedor. Clique em Novo fornecedor ou importe o CSV.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>
          <strong><?= e((string) $r['name']) ?></strong>
          <?php if (!empty($r['tradeName'])): ?>
            <div class="muted" style="font-size:.82rem"><?= e((string) $r['tradeName']) ?></div>
          <?php endif; ?>
          <?php if (!empty($r['city']) || !empty($r['state'])): ?>
            <div class="muted" style="font-size:.78rem">
              <?= e(trim((string) ($r['city'] ?? '') . ($r['state'] ? ' / ' . $r['state'] : ''))) ?>
            </div>
          <?php endif; ?>
        </td>
        <td><?= e((string) ($r['document'] ?: '—')) ?></td>
        <td><?= e(SUPPLIER_ACTIVITY_TYPES[$r['activityType'] ?? ''] ?? ((string) ($r['activityType'] ?: '—'))) ?></td>
        <td><?= (int) $r['qtd'] ?></td>
        <td><?= e(money_br((float) $r['valor'])) ?></td>
        <td>
          <div class="row-actions">
            <a class="btn btn-ghost" href="<?= e(url_path('admin/fornecedores/ver.php?id=' . rawurlencode((string) $r['id']))) ?>">Ver</a>
            <?php if ($canWrite): ?>
              <a class="btn btn-ghost" href="<?= e(url_path('admin/fornecedores.php?id=' . rawurlencode((string) $r['id']))) ?>">Editar</a>
              <form method="post" onsubmit="return confirm('Excluir este fornecedor?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= e((string) $r['id']) ?>">
                <button class="btn btn-secondary" type="submit">Excluir</button>
              </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="m-cards" aria-label="Lista de fornecedores">
  <?php if (!$rows): ?><div class="panel empty">Nenhum fornecedor. Clique em Novo fornecedor ou importe o CSV.</div><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <article class="m-card">
      <header class="m-card-head">
        <div>
          <div class="m-card-kicker"><?= e((string) ($r['document'] ?: 'Sem documento')) ?></div>
          <div class="m-card-title"><?= e((string) $r['name']) ?></div>
          <?php if (!empty($r['tradeName'])): ?><p class="m-card-desc"><?= e((string) $r['tradeName']) ?></p><?php endif; ?>
        </div>
        <div class="m-card-amount out"><?= e(money_br((float) $r['valor'])) ?></div>
      </header>
      <dl class="m-card-meta">
        <div><dt>Atividade</dt><dd><?= e(SUPPLIER_ACTIVITY_TYPES[$r['activityType'] ?? ''] ?? ((string) ($r['activityType'] ?: '—'))) ?></dd></div>
        <div><dt>Lançamentos</dt><dd><?= (int) $r['qtd'] ?></dd></div>
        <div><dt>Cidade</dt><dd><?= e(trim((string) ($r['city'] ?? '') . ($r['state'] ? ' / ' . $r['state'] : '')) ?: '—') ?></dd></div>
      </dl>
      <footer class="m-card-actions">
        <a class="btn btn-secondary" href="<?= e(url_path('admin/fornecedores/ver.php?id=' . rawurlencode((string) $r['id']))) ?>">Ver</a>
        <?php if ($canWrite): ?>
          <a class="btn btn-ghost" href="<?= e(url_path('admin/fornecedores.php?id=' . rawurlencode((string) $r['id']))) ?>">Editar</a>
          <form method="post" onsubmit="return confirm('Excluir este fornecedor?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= e((string) $r['id']) ?>">
            <button class="btn btn-danger" type="submit">Excluir</button>
          </form>
        <?php endif; ?>
      </footer>
    </article>
  <?php endforeach; ?>
</div>

<?php endif; ?>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
