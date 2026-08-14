<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$user = Auth::requireLogin('cabos');
$activeModule = 'cabos';
$pageTitle = 'Cabos / Contratos';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();
$canWrite = can_launch($user['role']);
$editId = trim((string) get('id', ''));
$edit = null;
$editContract = null;
$errors = [];

if ($editId !== '' && $campaign) {
    $st = $pdo->prepare('SELECT * FROM `CaboEleitoral` WHERE id=? AND campaignId=? LIMIT 1');
    $st->execute([$editId, $campaign['id']]);
    $edit = $st->fetch() ?: null;
    if ($edit) {
        $ct = $pdo->prepare('SELECT * FROM `Contract` WHERE caboId=? AND status="ATIVO" ORDER BY createdAt DESC LIMIT 1');
        $ct->execute([$editId]);
        $editContract = $ct->fetch() ?: null;
    }
}

if (request_method() === 'POST' && $canWrite) {
    Auth::requireMaster();
    $action = (string) post('action', 'save');
    $now = now_sql();
    $cid = (string) ($campaign['id'] ?? '');

    if ($action === 'toggle') {
        $id = (string) post('id');
        $active = (int) post('active');
        $pdo->prepare('UPDATE `CaboEleitoral` SET active=?, updatedAt=? WHERE id=? AND campaignId=?')
            ->execute([$active, $now, $id, $cid]);
        if (!$active) {
            $pdo->prepare('UPDATE `Contract` SET status=?, updatedAt=? WHERE caboId=? AND status="ATIVO"')
                ->execute(['ENCERRADO', $now, $id]);
        }
        audit_log($user['id'], 'UPDATE', 'CaboEleitoral', $id, $active ? 'Ativou cabo' : 'Desativou cabo e encerrou contratos ativos');
        flash_set('ok', $active ? 'Cabo ativado.' : 'Cabo desativado (contratos ativos encerrados).');
        redirect('/admin/cabos.php');
    }

    if ($action === 'delete') {
        $id = (string) post('id');
        $st = $pdo->prepare('SELECT fullName,cpf FROM `CaboEleitoral` WHERE id=? AND campaignId=? LIMIT 1');
        $st->execute([$id, $cid]);
        $row = $st->fetch();
        if ($row) {
            $pdo->beginTransaction();
            try {
                $pdfs = $pdo->prepare('SELECT pdfPath FROM `Contract` WHERE caboId=?');
                $pdfs->execute([$id]);
                foreach ($pdfs->fetchAll() as $pdfRow) {
                    ContractPdfUpload::deletePrevious($pdfRow['pdfPath'] ?? null);
                }
                $pdo->prepare('UPDATE `Expense` SET caboId=NULL WHERE caboId=?')->execute([$id]);
                $pdo->prepare('DELETE FROM `Contract` WHERE caboId=?')->execute([$id]);
                $pdo->prepare('DELETE FROM `CaboEleitoral` WHERE id=? AND campaignId=?')->execute([$id, $cid]);
                $pdo->commit();
                audit_log($user['id'], 'DELETE', 'CaboEleitoral', $id, 'Excluiu cabo ' . $row['fullName'] . ' · contratos e vínculos de despesa removidos');
                flash_set('ok', 'Cabo excluído com contratos e vínculos.');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                flash_set('danger', $e->getMessage());
            }
        }
        redirect('/admin/cabos.php');
    }

    if ($action === 'save') {
        $id = trim((string) post('id', ''));
        $fullName = trim((string) post('fullName', ''));
        $teamCode = trim((string) post('teamCode', ''));
        $cpf = only_digits((string) post('cpf', ''));
        $motherName = trim((string) post('motherName', ''));
        $birthDateRaw = trim((string) post('birthDate', ''));
        $birthDate = $birthDateRaw !== ''
            ? (preg_match('/^\d{4}-\d{2}-\d{2}/', $birthDateRaw) ? substr($birthDateRaw, 0, 10) : substr(parse_date_input($birthDateRaw), 0, 10))
            : null;
        $phone = format_phone_br((string) post('phone', ''));
        $zipCode = format_cep_br((string) post('zipCode', ''));
        $city = trim((string) post('city', ''));
        $state = strtoupper(trim((string) post('state', 'GO')));
        if (strlen($state) !== 2) {
            $state = 'GO';
        }
        $zone = trim((string) post('zone', ''));
        $address = trim((string) post('address', ''));
        $addressNumber = trim((string) post('addressNumber', ''));

        if ($fullName === '' || !is_valid_cpf($cpf)) {
            $errors[] = 'Nome e CPF válidos são obrigatórios.';
        }
        if ($teamCode !== '' && !Teams::isValid($teamCode, true)) {
            $errors[] = 'Selecione uma equipe válida (cadastro em Configurações → Equipes).';
        }
        $dup = $pdo->prepare('SELECT id FROM `CaboEleitoral` WHERE cpf=? AND id<>? LIMIT 1');
        $dup->execute([$cpf, $id !== '' ? $id : '-']);
        if ($dup->fetch()) {
            $errors[] = 'Já existe cabo com este CPF.';
        }

        $hasPdfUpload = isset($_FILES['contractPdf'])
            && (int) ($_FILES['contractPdf']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        $hasContractDates = (bool) post('startDate') && (bool) post('endDate')
            && post('monthlyValue') !== '' && post('monthlyValue') !== null;

        $contractId = trim((string) post('contractId', ''));
        $existingContract = null;
        if ($contractId !== '') {
            $ctLoad = $pdo->prepare('SELECT * FROM `Contract` WHERE id=? LIMIT 1');
            $ctLoad->execute([$contractId]);
            $existingContract = $ctLoad->fetch() ?: null;
        }

        if ($hasPdfUpload && !$hasContractDates && !$existingContract) {
            $errors[] = 'Para enviar o PDF, preencha início, fim e valor mensal do contrato.';
        }

        if ($id === '') {
            $personnelLimit = ElectoralRules::personnelLimitForOffice((string) ($campaign['office'] ?? DEFAULT_OFFICE));
            if ($personnelLimit !== null) {
                $cntSt = $pdo->prepare('SELECT COUNT(*) AS c FROM `CaboEleitoral` WHERE campaignId=?');
                $cntSt->execute([$cid]);
                $currentCabos = (int) $cntSt->fetch()['c'];
                if ($currentCabos >= $personnelLimit) {
                    $errors[] = "Limite TRE-GO de militância/rua atingido ({$personnelLimit}) para o cargo da campanha.";
                }
            }
        }

        if (!$errors && $campaign) {
            try {
                $pdo->beginTransaction();
                if ($id !== '') {
                    $pdo->prepare(
                        'UPDATE `CaboEleitoral` SET fullName=?, cpf=?, birthDate=?, motherName=?, phone=?, zipCode=?, address=?, addressNumber=?, city=?, state=?, zone=?, teamCode=?, updatedAt=?
                         WHERE id=? AND campaignId=?'
                    )->execute([
                        $fullName, $cpf, $birthDate, $motherName ?: null, $phone ?: null,
                        $zipCode !== '' ? $zipCode : null, $address ?: null, $addressNumber !== '' ? $addressNumber : null,
                        $city ?: null, $state, $zone ?: null, $teamCode !== '' ? $teamCode : null, $now, $id, $cid,
                    ]);
                    $caboId = $id;
                    audit_log($user['id'], 'UPDATE', 'CaboEleitoral', $caboId, "Atualizou cabo {$fullName}");
                } else {
                    $caboId = cuid();
                    $pdo->prepare(
                        'INSERT INTO `CaboEleitoral` (id,campaignId,fullName,cpf,birthDate,motherName,phone,zipCode,address,addressNumber,city,state,zone,teamCode,roleTitle,active,createdAt,updatedAt)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?)'
                    )->execute([
                        $caboId, $cid, $fullName, $cpf, $birthDate, $motherName ?: null, $phone ?: null,
                        $zipCode !== '' ? $zipCode : null, $address ?: null, $addressNumber !== '' ? $addressNumber : null,
                        $city ?: null, $state, $zone ?: null, $teamCode !== '' ? $teamCode : null,
                        'Cabo Eleitoral', $now, $now,
                    ]);
                    audit_log($user['id'], 'CREATE', 'CaboEleitoral', $caboId, "Cadastrou cabo {$fullName}");
                }

                if ($hasContractDates || ($hasPdfUpload && $existingContract)) {
                    $monthly = $hasContractDates
                        ? parse_money_input((string) post('monthlyValue'))
                        : (float) ($existingContract['monthlyValue'] ?? 0);
                    $start = $hasContractDates
                        ? parse_date_input((string) post('startDate'))
                        : (string) ($existingContract['startDate'] ?? date('Y-m-d'));
                    $end = $hasContractDates
                        ? parse_date_input((string) post('endDate'))
                        : (string) ($existingContract['endDate'] ?? date('Y-m-d'));
                    $cNumber = trim((string) post('contractNumber', '')) ?: ('CT-' . date('Ymd') . '-' . substr($caboId, -4));
                    $fn = trim((string) post('functionDesc', '')) ?: 'Contrato por prazo determinado';
                    $prevPdf = $existingContract['pdfPath'] ?? null;

                    if ($contractId !== '' && $existingContract) {
                        $pdo->prepare(
                            'UPDATE `Contract` SET contractNumber=?, startDate=?, endDate=?, monthlyValue=?, totalValue=?, functionDesc=?, updatedAt=? WHERE id=? AND caboId=?'
                        )->execute([
                            $cNumber, $start, $end,
                            $monthly, $monthly * 3, $fn, $now, $contractId, $caboId,
                        ]);
                        $ctId = $contractId;
                        audit_log($user['id'], 'UPDATE', 'Contract', $contractId, "Atualizou contrato {$cNumber}");
                    } else {
                        $ctId = cuid();
                        $pdo->prepare(
                            'INSERT INTO `Contract` (id,caboId,contractNumber,startDate,endDate,monthlyValue,totalValue,functionDesc,status,createdAt,updatedAt)
                             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
                        )->execute([
                            $ctId, $caboId, $cNumber,
                            $start, $end,
                            $monthly, $monthly * 3, $fn, 'ATIVO', $now, $now,
                        ]);
                        audit_log($user['id'], 'CREATE', 'Contract', $ctId, "Criou contrato {$cNumber} para {$fullName}");
                    }

                    if ($hasPdfUpload) {
                        $pdfPath = ContractPdfUpload::save($_FILES['contractPdf'], $ctId, is_string($prevPdf) ? $prevPdf : null);
                        if ($pdfPath) {
                            $pdo->prepare('UPDATE `Contract` SET pdfPath=?, updatedAt=? WHERE id=?')
                                ->execute([$pdfPath, $now, $ctId]);
                        }
                    }
                }

                $pdo->commit();
                flash_set('ok', $id !== '' ? 'Cabo atualizado.' : 'Cabo cadastrado.');
                redirect('/admin/cabos.php');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = $e->getMessage();
            }
        }
    }
}

$teamOptions = Teams::active();

$rows = $pdo->query(
    'SELECT c.*,
      (SELECT COUNT(*) FROM `Contract` ct WHERE ct.caboId=c.id AND ct.status="ATIVO") AS contratosAtivos,
      (SELECT COALESCE(SUM(monthlyValue),0) FROM `Contract` ct WHERE ct.caboId=c.id AND ct.status="ATIVO") AS folha
     FROM `CaboEleitoral` c ORDER BY c.active DESC, c.fullName'
)->fetchAll();
$ativos = count(array_filter($rows, fn ($r) => (int) $r['active'] === 1));
$folha = array_sum(array_map(fn ($r) => (float) $r['folha'], $rows));
$contratos = array_sum(array_map(fn ($r) => (int) $r['contratosAtivos'], $rows));
$personnelLimit = ElectoralRules::personnelLimitForOffice((string) ($campaign['office'] ?? DEFAULT_OFFICE));
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="row-actions" style="margin-bottom:1rem;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
  <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">← Voltar</a>
  <?php if ($canWrite): ?>
    <a class="btn btn-primary" href="#form-cabo"><?= $edit ? 'Editar formulário' : 'Cadastrar + contrato' ?></a>
  <?php endif; ?>
</div>

<div class="grid grid-4" style="margin-bottom:1rem">
  <div class="stat-chip"><div class="l">Cadastrados</div><div class="n"><?= count($rows) ?><?= $personnelLimit !== null ? ' / ' . (int) $personnelLimit : '' ?></div></div>
  <div class="stat-chip"><div class="l">Ativos</div><div class="n"><?= $ativos ?></div></div>
  <div class="stat-chip"><div class="l">Contratos ativos</div><div class="n"><?= $contratos ?></div></div>
  <div class="stat-chip"><div class="l">Folha mensal</div><div class="n"><?= e(money_br($folha)) ?></div></div>
</div>
<?php if ($personnelLimit !== null): ?>
  <p class="muted" style="margin:-.35rem 0 1rem;font-size:.82rem">
    Teto TRE-GO 2026 de militância/rua para <?= e((string) ($campaign['office'] ?? DEFAULT_OFFICE)) ?>:
    <strong><?= (int) $personnelLimit ?></strong> pessoas.
    <a href="<?= e(url_path('admin/base-legal.php')) ?>">Ver base legal</a>
  </p>
<?php endif; ?>

<div class="grid grid-2" style="margin-bottom:1rem">
<?php foreach ($rows as $c): ?>
  <div class="panel">
    <div class="row-actions" style="justify-content:space-between">
      <strong><?= e($c['fullName']) ?></strong>
      <span class="badge <?= $c['active'] ? 'badge-ok' : 'badge-warn' ?>"><?= $c['active'] ? 'Ativo' : 'Inativo' ?></span>
    </div>
    <div class="muted">CPF <?= e(format_cpf_cnpj($c['cpf'])) ?> · <?= e(!empty($c['zipCode']) ? format_cep_br((string) $c['zipCode']) . ' · ' : '') ?><?= e($c['city'] ?: '—') ?> · <?= e($c['zone'] ?: '') ?></div>
    <?php if (!empty($c['teamCode'])): ?>
      <div class="muted" style="font-size:.82rem">Equipe: <?= e(Teams::label((string) $c['teamCode'])) ?></div>
    <?php endif; ?>
    <?php if (!empty($c['motherName']) || !empty($c['birthDate'])): ?>
      <div class="muted" style="font-size:.82rem">
        <?php if (!empty($c['motherName'])): ?>Mãe: <?= e($c['motherName']) ?><?php endif; ?>
        <?php if (!empty($c['birthDate'])): ?> · Nasc. <?= e(date_br($c['birthDate'])) ?><?php endif; ?>
      </div>
    <?php endif; ?>
    <div style="margin-top:.5rem">Contratos ativos: <?= (int) $c['contratosAtivos'] ?> · Folha <?= e(money_br($c['folha'])) ?></div>
    <?php if ($canWrite): ?>
    <div class="row-actions" style="margin-top:.75rem">
      <a class="btn btn-ghost" href="<?= e(url_path('admin/cabos.php?id=' . rawurlencode($c['id']))) ?>#form-cabo">Editar</a>
      <form method="post">
        <input type="hidden" name="action" value="toggle">
        <input type="hidden" name="id" value="<?= e($c['id']) ?>">
        <input type="hidden" name="active" value="<?= $c['active'] ? 0 : 1 ?>">
        <button class="btn btn-secondary" type="submit"><?= $c['active'] ? 'Desativar' : 'Ativar' ?></button>
      </form>
      <form method="post" onsubmit="return confirm('Excluir cabo, contratos e desvincular despesas?');">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= e($c['id']) ?>">
        <button class="btn btn-danger" type="submit">Excluir</button>
      </form>
    </div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>
<?php if (!$rows): ?><div class="panel empty" style="margin-bottom:1rem">Nenhum cabo cadastrado.</div><?php endif; ?>

<?php if ($canWrite): ?>
<div class="panel form-card" id="form-cabo">
  <h3 class="display" style="margin-top:0"><?= $edit ? 'Editar cabo + contrato' : 'Novo cabo + contrato' ?></h3>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
  <?php endif; ?>
  <form method="post" class="stack" data-mask-form enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
    <input type="hidden" name="contractId" value="<?= e((string) ($editContract['id'] ?? '')) ?>">
    <h4 style="margin:0">Dados pessoais</h4>
    <div class="grid grid-2">
      <div class="field">
        <label class="label">Nome completo</label>
        <input class="input" name="fullName" value="<?= e((string) ($edit['fullName'] ?? '')) ?>" required>
      </div>
      <div class="field">
        <label class="label">Equipe</label>
        <select class="select" name="teamCode">
          <option value="">— Selecione —</option>
          <?php foreach ($teamOptions as $t): ?>
            <option value="<?= e((string) $t['id']) ?>" <?= ((string) ($edit['teamCode'] ?? '') === (string) $t['id']) ? 'selected' : '' ?>>
              <?= e((string) $t['label']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="muted" style="font-size:.78rem;margin-top:.25rem">
          Cadastro prévio em <a href="<?= e(url_path('admin/equipes.php')) ?>">Equipes</a> · ordem A–Z.
        </div>
      </div>
    </div>
    <div class="grid grid-2">
      <div class="field">
        <label class="label">CPF</label>
        <input class="input" name="cpf" data-mask="cpf" inputmode="numeric" maxlength="14" placeholder="000.000.000-00" value="<?= e(isset($edit['cpf']) ? format_cpf_cnpj($edit['cpf']) : '') ?>" required>
      </div>
      <div class="field">
        <label class="label">Telefone</label>
        <input class="input" name="phone" data-mask="phone" inputmode="tel" maxlength="16" placeholder="(62) 99999-9999" value="<?= e((string) ($edit['phone'] ?? '')) ?>">
      </div>
    </div>
    <div class="grid grid-2">
      <div class="field"><label class="label">Nome da mãe</label><input class="input" name="motherName" value="<?= e((string) ($edit['motherName'] ?? '')) ?>"></div>
      <div class="field"><label class="label">Data de nascimento</label><input class="input" type="date" name="birthDate" value="<?= e(!empty($edit['birthDate']) ? substr((string) $edit['birthDate'], 0, 10) : '') ?>"></div>
    </div>
    <div class="grid grid-2">
      <div class="field">
        <label class="label">CEP</label>
        <input class="input" name="zipCode" data-mask="cep" data-cep-lookup inputmode="numeric" maxlength="9" placeholder="00000-000" value="<?= e(!empty($edit['zipCode']) ? format_cep_br((string) $edit['zipCode']) : '') ?>">
        <div data-cep-status class="muted" style="margin-top:.35rem;font-size:.82rem">Ao digitar o CEP, preenchemos o endereço.</div>
      </div>
      <div class="field">
        <label class="label">UF</label>
        <input class="input" name="state" maxlength="2" value="<?= e((string) ($edit['state'] ?? 'GO')) ?>" style="text-transform:uppercase">
      </div>
    </div>
    <div class="grid" style="grid-template-columns:1fr 5.5rem;gap:.75rem">
      <div class="field" style="margin:0">
        <label class="label">Endereço</label>
        <input class="input" name="address" value="<?= e((string) ($edit['address'] ?? '')) ?>" placeholder="Logradouro, bairro">
      </div>
      <div class="field" style="margin:0">
        <label class="label">Nr</label>
        <input class="input" name="addressNumber" value="<?= e((string) ($edit['addressNumber'] ?? '')) ?>" placeholder="Nº">
      </div>
    </div>
    <div class="grid grid-2">
      <div class="field"><label class="label">Cidade</label><input class="input" name="city" value="<?= e((string) ($edit['city'] ?? 'Goiânia')) ?>"></div>
      <div class="field"><label class="label">Zona</label><input class="input" name="zone" value="<?= e((string) ($edit['zone'] ?? '')) ?>"></div>
    </div>

    <h4 style="margin:0">Contrato (opcional)</h4>
    <div class="grid grid-2">
      <div class="field"><label class="label">Início</label><input class="input" type="date" name="startDate" value="<?= e($editContract ? substr((string) $editContract['startDate'], 0, 10) : '2026-07-01') ?>"></div>
      <div class="field"><label class="label">Fim</label><input class="input" type="date" name="endDate" value="<?= e($editContract ? substr((string) $editContract['endDate'], 0, 10) : '2026-10-05') ?>"></div>
    </div>
    <div class="grid grid-2">
      <div class="field">
        <label class="label">Valor mensal</label>
        <input class="input" name="monthlyValue" data-mask="money" value="<?= e($editContract ? number_format((float) $editContract['monthlyValue'], 2, ',', '.') : '') ?>" placeholder="0,00">
      </div>
      <div class="field">
        <label class="label">Nº contrato / PDF</label>
        <input class="input" name="contractNumber" value="<?= e((string) ($editContract['contractNumber'] ?? '')) ?>" placeholder="Nº (opcional)" style="margin-bottom:.45rem">
        <input class="input" type="file" name="contractPdf" accept="application/pdf,.pdf">
        <div class="muted" style="font-size:.78rem;margin-top:.35rem">Upload do contrato em PDF (máx. 8 MB).</div>
        <?php if (!empty($editContract['pdfPath'])): ?>
          <div style="margin-top:.4rem">
            <a class="btn btn-ghost" href="<?= e(url_path(ltrim((string) $editContract['pdfPath'], '/'))) ?>" target="_blank" rel="noopener">Ver PDF atual</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="field"><label class="label">Função</label><textarea class="textarea" name="functionDesc"><?= e((string) ($editContract['functionDesc'] ?? 'Articulação territorial e mobilização eleitoral por prazo determinado.')) ?></textarea></div>
    <div class="row-actions">
      <button class="btn btn-primary" type="submit">Salvar</button>
      <a class="btn btn-ghost" href="<?= e(url_path($edit ? 'admin/cabos.php' : 'admin/index.php')) ?>">Voltar</a>
    </div>
  </form>
</div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
