<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$user = Auth::requireLogin('wizard');
$activeModule = 'wizard';
$pageTitle = 'Wizard de dados';
$campaign = Metrics::getCampaign();
$pdo = Database::pdo();

if (request_method() === 'POST') {
    Auth::requireMaster();
    if (!$campaign) {
        flash_set('danger', 'Campanha não encontrada.');
        redirect('/admin/wizard.php');
    }

    // Carregar dados de demonstração (recria contas, vínculos, fornecedores e lançamentos demo)
    if ((string) post('action') === 'load_demo') {
        $ack = (string) post('demo_ack', '') === '1';
        $confirm = trim((string) post('demo_confirm', ''));
        if (!$ack || mb_strtoupper($confirm) !== 'DEMO') {
            flash_set('danger', 'Confirmação inválida. Marque a opção e digite DEMO para carregar os dados de demonstração.');
            redirect('/admin/wizard.php#dados-demo');
        }
        try {
            $summary = Demo::loadDemoData($user['id']);
            flash_set(
                'ok',
                sprintf(
                    'Dados de demonstração carregados: %d contas, %d receitas, %d despesas (%d parcelas a vencer), %d cabos, %d veículos, %d fornecedores. Abra o Dashboard para validar o card de parcelas e os campos raros.',
                    (int) ($summary['accounts'] ?? 0),
                    (int) ($summary['revenues'] ?? 0),
                    (int) ($summary['expenses'] ?? 0),
                    (int) ($summary['parcelas'] ?? 0),
                    (int) ($summary['cabos'] ?? 0),
                    (int) ($summary['vehicles'] ?? 0),
                    (int) ($summary['suppliers'] ?? 0)
                )
            );
        } catch (Throwable $e) {
            flash_set('danger', 'Falha ao carregar demo: ' . $e->getMessage());
        }
        redirect('/admin/wizard.php#dados-demo');
    }

    // Limpeza seletiva: só lançamentos financeiros + diário
    if ((string) post('action') === 'clear_launches') {
        $confirm = trim((string) post('confirm_text', ''));
        $ack = (string) post('confirm_ack', '') === '1';
        if (!$ack || mb_strtoupper($confirm) !== 'APAGAR') {
            flash_set('danger', 'Confirmação inválida. Marque a opção e digite APAGAR para continuar.');
            redirect('/admin/wizard.php#limpar-lancamentos');
        }
        $result = Demo::clearFinancialLaunches((string) $campaign['id'], $user['id']);
        if (!$result['ok']) {
            flash_set('danger', $result['error'] ?? 'Não foi possível apagar os lançamentos.');
            redirect('/admin/wizard.php#limpar-lancamentos');
        }
        $c = $result['counts'] ?? [];
        flash_set(
            'ok',
            sprintf(
                'Lançamentos apagados: %d receitas, %d despesas (%d parcelas), %d no extrato, %d ajustes. Diário limpo. Cadastros, vínculos, foto e prazo da eleição mantidos. Saldos das contas zerados — ajuste em Saldos se necessário.',
                (int) ($c['receitas'] ?? 0),
                (int) ($c['despesas'] ?? 0),
                (int) ($c['parcelas'] ?? 0),
                (int) ($c['extrato'] ?? 0),
                (int) ($c['ajustes'] ?? 0)
            )
        );
        redirect('/admin/wizard.php#limpar-lancamentos');
    }

    $now = now_sql();
    $photoPath = $campaign['photoUrl'] ?? null;

    try {
        if (!empty(post('remove_photo'))) {
            PhotoUpload::deletePrevious($photoPath);
            $photoPath = null;
        }
        if (!empty($_FILES['photo']['name'])) {
            $saved = PhotoUpload::saveCandidatePhoto($_FILES['photo'], (string) $campaign['id'], $photoPath);
            if ($saved) {
                $photoPath = $saved;
            }
        }

        $cnpjCampaign = format_cpf_cnpj((string) post('cnpjCampaign', ''));
        if ($cnpjCampaign !== '' && !is_valid_cnpj($cnpjCampaign)) {
            flash_set('danger', 'CNPJ do deputado / campanha inválido.');
            redirect('/admin/wizard.php');
        }
        $addressZip = only_digits((string) post('addressZip', ''));
        $hasAddress = (bool) $pdo->query("SHOW COLUMNS FROM `Campaign` LIKE 'addressStreet'")->fetch();
        if ($hasAddress) {
            $pdo->prepare(
                'UPDATE `Campaign` SET candidateName=?, candidateFullName=?, candidateNumber=?, party=?, partyNumber=?, office=?, cnpjCampaign=?, totalBudget=?, legalSpendLimit=?, website=?, situation=?, reelection=?, photoUrl=?, electoralTitle=?, phone=?, email=?, addressZip=?, addressStreet=?, addressNumber=?, addressComplement=?, addressDistrict=?, addressCity=?, addressState=?, updatedAt=? WHERE id=?'
            )->execute([
                trim((string) post('candidateName')),
                trim((string) post('candidateFullName')),
                trim((string) post('candidateNumber')),
                trim((string) post('party')),
                trim((string) post('partyNumber')),
                trim((string) post('office', 'Deputado Estadual')),
                $cnpjCampaign !== '' ? $cnpjCampaign : null,
                parse_money_input((string) post('totalBudget')),
                parse_money_input((string) post('legalSpendLimit')),
                post('website') ?: null,
                trim((string) post('situation', 'Em campanha')),
                post('reelection') ? 1 : 0,
                $photoPath,
                trim((string) post('electoralTitle', '')) ?: null,
                trim((string) post('phone', '')) ?: null,
                trim((string) post('email', '')) ?: null,
                $addressZip !== '' ? $addressZip : null,
                trim((string) post('addressStreet', '')) ?: null,
                trim((string) post('addressNumber', '')) ?: null,
                trim((string) post('addressComplement', '')) ?: null,
                trim((string) post('addressDistrict', '')) ?: null,
                trim((string) post('addressCity', '')) ?: null,
                strtoupper(trim((string) post('addressState', ''))) ?: null,
                $now,
                $campaign['id'],
            ]);
        } else {
            $pdo->prepare(
                'UPDATE `Campaign` SET candidateName=?, candidateFullName=?, candidateNumber=?, party=?, partyNumber=?, office=?, cnpjCampaign=?, totalBudget=?, legalSpendLimit=?, website=?, situation=?, reelection=?, photoUrl=?, updatedAt=? WHERE id=?'
            )->execute([
                trim((string) post('candidateName')),
                trim((string) post('candidateFullName')),
                trim((string) post('candidateNumber')),
                trim((string) post('party')),
                trim((string) post('partyNumber')),
                trim((string) post('office', 'Deputado Estadual')),
                $cnpjCampaign !== '' ? $cnpjCampaign : null,
                parse_money_input((string) post('totalBudget')),
                parse_money_input((string) post('legalSpendLimit')),
                post('website') ?: null,
                trim((string) post('situation', 'Em campanha')),
                post('reelection') ? 1 : 0,
                $photoPath,
                $now,
                $campaign['id'],
            ]);
        }
        audit_log($user['id'], 'UPDATE', 'Campaign', (string) $campaign['id'], 'Atualizou dados da campanha · CNPJ ' . ($cnpjCampaign ?: '—'));
        flash_set('ok', 'Campanha e foto atualizadas.');
    } catch (Throwable $e) {
        flash_set('danger', $e->getMessage());
    }
    redirect('/admin/wizard.php');
}

$status = Demo::wizardStatus();
$steps = [
    ['campanha', 'Campanha + foto + endereço', '/admin/wizard.php', true],
    ['contas', 'Contas bancárias (Doações / FP / FEFC)', '/admin/contas.php', true],
    ['representantes', 'Representantes legais', '/admin/representantes.php', true],
    ['vinculos', 'Vínculos', '/admin/vinculos.php', true],
    ['fornecedores', 'Fornecedores', '/admin/fornecedores.php', true],
    ['veiculos', 'Veículos', '/admin/veiculos.php', false],
    ['cabos', 'Cabos', '/admin/cabos.php', false],
    ['receitas', 'Receitas / Despesas', '/admin/lancamento.php', true],
    ['inconsistencias', 'Verificar inconsistências', '/admin/inconsistencias.php', true],
];
$photoUrl = !empty($campaign['photoUrl']) ? url_path(ltrim((string) $campaign['photoUrl'], '/')) : '';
$launchCounts = $campaign ? Demo::financialLaunchCounts((string) $campaign['id']) : [
    'receitas' => 0, 'despesas' => 0, 'parcelas' => 0, 'extrato' => 0, 'ajustes' => 0, 'diario' => 0,
];
require dirname(__DIR__) . '/templates/admin_layout_start.php';
?>
<div class="page-form">
<div class="grid grid-2">
  <div class="panel">
    <h3 class="display" style="margin-top:0">Progresso</h3>
    <?php foreach ($steps as [$key,$label,$href,$required]): ?>
      <?php $done = ($status[$key] ?? 0) > 0 || ($key==='campanha' && ($status['campanha']??0)>0); ?>
      <div class="row-actions" style="justify-content:space-between;padding:.55rem 0;border-bottom:1px solid var(--line)">
        <div>
          <strong><?= e($label) ?></strong>
          <?= $required ? '<span class="badge badge-info">obrigatório</span>' : '<span class="badge badge-warn">opcional</span>' ?>
        </div>
        <div class="row-actions">
          <span class="badge <?= $done?'badge-ok':'badge-warn' ?>"><?= $done?'ok':'pendente' ?></span>
          <a class="btn btn-ghost" href="<?= e(url_path(ltrim($href,'/'))) ?>">Abrir</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="panel form-card">
    <h3 class="display" style="margin-top:0">Dados da campanha</h3>
    <form method="post" enctype="multipart/form-data">
      <div class="field">
        <label class="label">Foto do deputado (página inicial)</label>
        <div class="photo-upload-box">
          <div class="candidate-photo-frame preview">
            <?php if ($photoUrl): ?>
              <img src="<?= e($photoUrl) ?>" alt="Foto do deputado" width="180" height="240">
            <?php else: ?>
              <span class="photo-placeholder">Sem foto</span>
            <?php endif; ?>
          </div>
          <div>
            <input class="input" type="file" name="photo" accept="image/jpeg,image/png,image/webp">
            <p class="muted" style="font-size:.8rem;margin:.55rem 0 0">
              Retrato <strong>3∶4</strong> · tamanho ideal <strong>600 × 800 px</strong> · JPG/PNG/WebP · máx. 2,5&nbsp;MB.<br>
              A imagem é cortada ao centro e salva em 600×800 para a home.
            </p>
            <?php if ($photoUrl): ?>
              <label class="row-actions" style="margin-top:.55rem">
                <input type="checkbox" name="remove_photo" value="1"> Remover foto atual
              </label>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="field"><label class="label">Nome urna</label><input class="input" name="candidateName" value="<?= e($campaign['candidateName'] ?? '') ?>" required></div>
      <div class="field"><label class="label">Nome completo</label><input class="input" name="candidateFullName" value="<?= e($campaign['candidateFullName'] ?? '') ?>" required></div>
      <div class="field">
        <label class="label">CNPJ do deputado / campanha</label>
        <input class="input" name="cnpjCampaign" data-mask="cnpj" data-cnpj-lookup inputmode="numeric" placeholder="00.000.000/0000-00" value="<?= e((string) ($campaign['cnpjCampaign'] ?? '')) ?>" required>
        <div data-cnpj-status class="muted" style="margin-top:.35rem;font-size:.82rem">Digite o CNPJ para validar e ver a razão social automaticamente.</div>
      </div>
      <div class="grid grid-2">
        <div class="field"><label class="label">Número</label><input class="input" name="candidateNumber" value="<?= e($campaign['candidateNumber'] ?? '') ?>" required></div>
        <div class="field"><label class="label">Partido</label><input class="input" name="party" value="<?= e($campaign['party'] ?? '') ?>" required></div>
      </div>
      <div class="grid grid-2">
        <div class="field"><label class="label">Nº partido</label><input class="input" name="partyNumber" value="<?= e($campaign['partyNumber'] ?? '') ?>"></div>
        <div class="field"><label class="label">Cargo</label><input class="input" name="office" value="<?= e($campaign['office'] ?? 'Deputado Estadual') ?>"></div>
      </div>
      <div class="grid grid-2">
        <div class="field"><label class="label">Orçamento</label><input class="input" name="totalBudget" data-mask="money" inputmode="decimal" value="<?= e(number_format((float)($campaign['totalBudget'] ?? 0), 2, ',', '.')) ?>" required></div>
        <div class="field"><label class="label">Limite legal</label><input class="input" name="legalSpendLimit" data-mask="money" inputmode="decimal" value="<?= e(number_format((float)($campaign['legalSpendLimit'] ?? DEFAULT_LEGAL_SPEND_LIMIT), 2, ',', '.')) ?>" required>
          <?php $teto = ElectoralRules::spendLimitForOffice((string) ($campaign['office'] ?? DEFAULT_OFFICE)); ?>
          <?php if ($teto !== null): ?>
            <div class="muted" style="font-size:.78rem;margin-top:.25rem">Teto TRE-GO 2026 para o cargo: <strong><?= e(money_br($teto)) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="field"><label class="label">Site de campanha (interno)</label><input class="input" name="website" value="<?= e($campaign['website'] ?? '') ?>" placeholder="opcional"></div>
      <div class="field"><label class="label">Situação</label><input class="input" name="situation" value="<?= e($campaign['situation'] ?? '') ?>"></div>
      <label class="row-actions"><input type="checkbox" name="reelection" value="1" <?= !empty($campaign['reelection'])?'checked':'' ?>> Reeleição</label>

      <h3 class="display" style="margin:1.25rem 0 .5rem;font-size:1.05rem">Qualificação Conta+JE</h3>
      <p class="muted" style="font-size:.82rem;margin:0 0 .75rem">Endereço e contatos exigidos na análise preventiva (Res.-TSE 23.607/2019).</p>
      <div class="grid grid-2">
        <div class="field"><label class="label">Título eleitoral</label><input class="input" name="electoralTitle" value="<?= e((string) ($campaign['electoralTitle'] ?? '')) ?>"></div>
        <div class="field"><label class="label">Telefone</label><input class="input" name="phone" data-mask="phone" value="<?= e((string) ($campaign['phone'] ?? '')) ?>"></div>
      </div>
      <div class="field"><label class="label">E-mail</label><input class="input" type="email" name="email" value="<?= e((string) ($campaign['email'] ?? '')) ?>"></div>
      <div class="grid grid-2">
        <div class="field"><label class="label">CEP</label><input class="input" name="addressZip" data-mask="cep" data-cep-lookup inputmode="numeric" value="<?= e((string) ($campaign['addressZip'] ?? '')) ?>"></div>
        <div class="field"><label class="label">UF</label><input class="input" name="addressState" maxlength="2" value="<?= e((string) ($campaign['addressState'] ?? $campaign['state'] ?? 'GO')) ?>"></div>
      </div>
      <div class="field"><label class="label">Logradouro</label><input class="input" name="addressStreet" value="<?= e((string) ($campaign['addressStreet'] ?? '')) ?>"></div>
      <div class="grid grid-2">
        <div class="field"><label class="label">Número</label><input class="input" name="addressNumber" value="<?= e((string) ($campaign['addressNumber'] ?? '')) ?>"></div>
        <div class="field"><label class="label">Complemento</label><input class="input" name="addressComplement" value="<?= e((string) ($campaign['addressComplement'] ?? '')) ?>"></div>
      </div>
      <div class="grid grid-2">
        <div class="field"><label class="label">Bairro</label><input class="input" name="addressDistrict" value="<?= e((string) ($campaign['addressDistrict'] ?? '')) ?>"></div>
        <div class="field"><label class="label">Cidade</label><input class="input" name="addressCity" value="<?= e((string) ($campaign['addressCity'] ?? '')) ?>"></div>
      </div>

      <div class="row-actions" style="margin-top:1rem">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">Voltar</a>
      </div>
    </form>
  </div>
</div>

<?php if ($user['role'] === 'MASTER' && $campaign): ?>
<section class="panel demo-zone animate-rise" id="dados-demo" style="margin-top:1rem">
  <h3 class="display" style="margin-top:0;color:var(--navy)">Dados de demonstração</h3>
  <p class="muted" style="margin-top:0;line-height:1.45">
    Carrega um conjunto completo para testar o sistema: contas, vínculos, fornecedores, cabos, contratos com PDF,
    veículos, doações PF (sem PJ), Fundo/Vaquinha, NF-e, despesa cancelada, <strong>despesas futuras</strong> e
    <strong>parcelas 2x</strong> (contam no orçamento; o dinheiro só sai no vencimento ou pagamento manual —
    pendentes na Conciliação). Inclui vencimento <strong>hoje</strong> e nesta semana no
    quadro do <strong>Dashboard</strong>. <strong>Substitui</strong> os cadastros operacionais atuais
    (mantém usuários e a estrutura da campanha/foto quando possível).
  </p>
  <form method="post" class="stack" onsubmit="return confirm('Carregar dados de DEMONSTRAÇÃO? Isso recria contas, fornecedores, contratos/PDF e lançamentos de exemplo.');">
    <input type="hidden" name="action" value="load_demo">
    <label class="row-actions" style="align-items:flex-start;gap:.55rem">
      <input type="checkbox" name="demo_ack" value="1" required>
      <span style="font-size:.88rem;line-height:1.4">
        Entendo que os dados operacionais atuais serão <strong>substituídos</strong> pelos dados de demonstração
        (incluindo PDFs de contrato de exemplo).
      </span>
    </label>
    <div class="field" style="margin:0;max-width:22rem">
      <label class="label">Digite DEMO para confirmar</label>
      <input class="input" name="demo_confirm" autocomplete="off" placeholder="DEMO" required>
    </div>
    <div class="row-actions">
      <button class="btn btn-primary" type="submit">Carregar dados de demonstração</button>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/index.php')) ?>">Ver Dashboard</a>
    </div>
  </form>
</section>

<section class="panel danger-zone animate-rise" id="limpar-lancamentos" style="margin-top:1rem">
  <h3 class="display" style="margin-top:0;color:var(--danger)">Apagar lançamentos</h3>
  <p class="muted" style="margin-top:0;line-height:1.45">
    Remove <strong>todas as receitas e despesas</strong> (incluindo parcelas <strong>FUTURA</strong> e canceladas),
    o extrato da conciliação, os ajustes de saldo, resíduos de NF-e avulsa e o <strong>diário</strong>.
    Mantém fornecedores, cabos/contratos, veículos, contas, vínculos, dados do deputado, foto da campanha
    e a contagem regressiva até <?= e(date_br(CAMPAIGN_END_DATE)) ?>.
  </p>
  <div class="stat-strip" style="margin:.85rem 0 1rem">
    <div class="stat-chip tone-green"><div class="l">Receitas</div><div class="n"><?= (int) $launchCounts['receitas'] ?></div></div>
    <div class="stat-chip tone-rose"><div class="l">Despesas</div><div class="n"><?= (int) $launchCounts['despesas'] ?></div></div>
    <div class="stat-chip tone-amber"><div class="l">Parcelas</div><div class="n"><?= (int) ($launchCounts['parcelas'] ?? 0) ?></div></div>
    <div class="stat-chip tone-blue"><div class="l">Extrato</div><div class="n"><?= (int) $launchCounts['extrato'] ?></div></div>
    <div class="stat-chip tone-amber"><div class="l">Ajustes</div><div class="n"><?= (int) $launchCounts['ajustes'] ?></div></div>
    <div class="stat-chip"><div class="l">Diário</div><div class="n"><?= (int) $launchCounts['diario'] ?></div></div>
  </div>
  <form method="post" class="stack" onsubmit="return confirm('Apagar TODOS os lançamentos de receita e despesa (incluindo parcelas a vencer)? Esta ação não pode ser desfeita.');">
    <input type="hidden" name="action" value="clear_launches">
    <label class="row-actions" style="align-items:flex-start;gap:.55rem">
      <input type="checkbox" name="confirm_ack" value="1" required>
      <span style="font-size:.88rem;line-height:1.4">
        Entendo que os saldos das contas serão <strong>zerados</strong>, que parcelas futuras também serão apagadas
        e que cadastros (fornecedores, cabos, veículos, contas, vínculos e campanha) serão preservados.
      </span>
    </label>
    <div class="field" style="margin:0;max-width:22rem">
      <label class="label">Digite APAGAR para confirmar</label>
      <input class="input" name="confirm_text" autocomplete="off" placeholder="APAGAR" required>
    </div>
    <div class="row-actions">
      <button class="btn btn-danger" type="submit">
        Apagar receitas e despesas
      </button>
      <a class="btn btn-ghost" href="<?= e(url_path('admin/saldos.php')) ?>">Ir para Saldos</a>
    </div>
  </form>
</section>
<?php endif; ?>
</div>
<?php require dirname(__DIR__) . '/templates/admin_layout_end.php'; ?>
