<?php
/** @var array $user */
/** @var string $activeModule */
/** @var string $pageTitle */
$activeModule = $activeModule ?? 'dashboard';
$pageTitle = $pageTitle ?? APP_NAME;
$flash = flash_get();
$primaryMods = array_values(array_filter(MODULES, static fn ($m) => ($m['group'] ?? '') === 'primary'));
$opsMods = array_values(array_filter(MODULES, static fn ($m) => ($m['group'] ?? '') === 'ops'));
$configMods = array_values(array_filter(MODULES, static fn ($m) => ($m['group'] ?? '') === 'config'));
$showConfig = false;
$configOpen = false;
foreach ($configMods as $cm) {
    if (!in_array($user['role'], $cm['roles'], true)) {
        continue;
    }
    $showConfig = true;
    if ($activeModule === ($cm['id'] ?? '')) {
        $configOpen = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
  <?php require __DIR__ . '/pwa_head.php'; ?>
</head>
<body data-base="<?= e(rtrim(env('APP_BASE', '') ?: '', '/')) ?>" class="<?= ($activeModule ?? '') === 'dashboard' ? 'dash-home' : '' ?>">
<div class="admin-shell">
  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="sidebar-brand-block">
        <img src="<?= e(asset('assets/img/pollicontas-logo.png')) ?>" alt="<?= e(APP_NAME) ?>" width="46" height="46">
        <div>
          <div class="display"><?= e(APP_NAME) ?></div>
          <div class="muted">Prestação de contas · <?= e((string) ELECTION_YEAR) ?></div>
        </div>
      </div>
      <nav aria-label="Menu principal">
        <?php foreach ($primaryMods as $mod): ?>
          <?php if (!in_array($user['role'], $mod['roles'], true)) continue; ?>
          <a class="<?= $activeModule === $mod['id'] ? 'active' : '' ?>" href="<?= e(url_path(ltrim($mod['href'], '/'))) ?>">
            <span><?= e($mod['label']) ?></span>
            <?php if (!empty($mod['fkey'])): ?>
              <kbd class="nav-kbd hide-touch" title="Atalho teclado"><?= e($mod['fkey']) ?></kbd>
              <?php if (!empty($mod['shortcut'])): ?>
                <kbd class="nav-kbd show-touch" title="Atalho"><?= e($mod['shortcut']) ?></kbd>
              <?php endif; ?>
            <?php elseif (!empty($mod['shortcut'])): ?>
              <kbd class="nav-kbd" title="Atalho"><?= e($mod['shortcut']) ?></kbd>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>

        <div class="nav-sep" role="separator" aria-hidden="true"></div>

        <?php foreach ($opsMods as $mod): ?>
          <?php if (!in_array($user['role'], $mod['roles'], true)) continue; ?>
          <a class="<?= $activeModule === $mod['id'] ? 'active' : '' ?>" href="<?= e(url_path(ltrim($mod['href'], '/'))) ?>">
            <?= e($mod['label']) ?>
          </a>
        <?php endforeach; ?>

        <a href="<?= e(url_path('logout.php')) ?>" class="nav-logout">Sair</a>
      </nav>
    </div>

    <div class="sidebar-bottom">
      <?php if ($showConfig): ?>
        <div class="nav-sep" role="separator" aria-hidden="true"></div>
        <details class="nav-accordion" <?= $configOpen ? 'open' : '' ?>>
          <summary class="nav-accordion-summary">Configurações</summary>
          <div class="nav-accordion-body">
            <?php foreach ($configMods as $mod): ?>
              <?php if (!in_array($user['role'], $mod['roles'], true)) continue; ?>
              <a class="nav-config <?= ($mod['id'] ?? '') === 'manual' ? 'nav-manual' : '' ?> <?= $activeModule === $mod['id'] ? 'active' : '' ?>"
                 href="<?= e(url_path(ltrim($mod['href'], '/'))) ?>">
                <?= e($mod['label']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </details>
      <?php endif; ?>

      <a class="sidebar-brand" href="https://www.synetiq.com.br" target="_blank" rel="noopener noreferrer" title="SynetIQ — Soluções Digitais Inteligentes">
        <img src="<?= e(asset('assets/img/synetiq-wordmark-sm.png')) ?>" alt="SynetIQ — Soluções Digitais Inteligentes" width="200" height="65" loading="lazy">
      </a>
    </div>
  </aside>
  <div>
    <main class="admin-main<?= ($activeModule ?? '') === 'dashboard' ? ' dash-main-inline' : '' ?>">
      <?php if (($activeModule ?? '') !== 'dashboard'): ?>
      <div class="topbar">
        <div>
          <div class="page-title"><?= e($pageTitle) ?></div>
          <div class="muted topbar-meta"><?= e($user['name']) ?> · <?= e(ROLE_LABELS[$user['role']] ?? $user['role']) ?></div>
        </div>
        <div class="row-actions hide-mobile">
          <?php if (can_launch($user['role'])): ?>
            <a class="btn btn-primary" href="<?= e(url_path('admin/lancamento.php')) ?>">Novo lançamento</a>
          <?php else: ?>
            <span class="badge badge-warn">Somente leitura</span>
          <?php endif; ?>
          <a class="btn btn-secondary" href="<?= e(url_path('admin/index.php')) ?>">Dashboard</a>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($flash): ?>
        <?php
          $flashType = $flash['type'] === 'danger' ? 'danger' : ($flash['type'] === 'ok' ? 'ok' : ($flash['type'] === 'warn' ? 'warn' : 'info'));
          // Erros ficam na tela até o usuário ler/corrigir; sucesso some sozinho
          $flashDismiss = in_array($flashType, ['ok', 'info'], true) ? ' data-auto-dismiss="3000"' : '';
        ?>
        <div class="alert alert-<?= e($flashType) ?> animate-fade"<?= $flashDismiss ?> role="<?= $flashType === 'danger' || $flashType === 'warn' ? 'alert' : 'status' ?>">
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>
