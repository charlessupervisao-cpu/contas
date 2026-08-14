<?php
/** Layout full-screen do dashboard (página inicial do sistema). */
/** @var array $user */
/** @var string $pageTitle */
$pageTitle = $pageTitle ?? 'Dashboard';
$flash = flash_get();
$base = rtrim(env('APP_BASE', '') ?: '', '/');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#0f766e">
  <title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
  <?php require __DIR__ . '/pwa_head.php'; ?>
</head>
<body class="dash-home" data-base="<?= e($base) ?>">
<div class="dash-shell">
  <header class="dash-top">
    <div class="dash-brand">
      <div class="logo-mark"><img src="<?= e(asset('assets/img/contas-logo.png')) ?>" alt="<?= e(APP_NAME) ?>" width="40" height="40"></div>
      <div>
        <div class="display" style="font-weight:800;font-size:1.15rem"><?= e(APP_NAME) ?></div>
        <div class="muted" style="font-size:.75rem"><?= e(APP_TAGLINE) ?></div>
      </div>
    </div>
    <div class="dash-top-actions">
      <span class="badge badge-info hide-mobile"><?= e($user['name']) ?> · <?= e(ROLE_LABELS[$user['role']] ?? $user['role']) ?></span>
      <?php if (can_launch($user['role'])): ?>
        <a class="btn btn-primary" href="<?= e(url_path('admin/lancamento.php')) ?>"><span class="kbd" style="background:rgba(255,255,255,.2);color:#fff;border-color:transparent">F3</span> Lançar</a>
        <a class="btn btn-secondary" href="<?= e(url_path('admin/lancamento.php?tipo=RECEITA')) ?>">Entrada</a>
        <a class="btn btn-secondary" href="<?= e(url_path('admin/lancamento.php?tipo=DESPESA')) ?>">Saída</a>
      <?php endif; ?>
      <a class="btn btn-secondary" href="<?= e(url_path('admin/movimentacoes.php')) ?>">Movimentações</a>
      <?php if (can_launch($user['role'])): ?>
        <a class="btn btn-ghost hide-mobile" href="<?= e(url_path('admin/wizard.php')) ?>">Wizard</a>
      <?php else: ?>
        <span class="badge badge-warn hide-mobile">Somente leitura</span>
      <?php endif; ?>
      <a class="btn btn-ghost" href="<?= e(url_path('logout.php')) ?>">Sair</a>
    </div>
  </header>
  <main class="dash-main">
    <?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['type'] === 'danger' ? 'danger' : ($flash['type'] === 'ok' ? 'ok' : 'info')) ?> animate-fade">
        <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>
