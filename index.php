<?php
/**
 * Entrada do sistema: dashboard é a home após login.
 * Visitante não autenticado vai para o login.
 */
require_once __DIR__ . '/bootstrap.php';

if (Auth::user()) {
    redirect('/admin/index.php');
}

// Splash breve + redireciona ao login (home autenticada = dashboard)
$target = url_path('login.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta http-equiv="refresh" content="1.6;url=<?= e($target) ?>">
  <title><?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
  <?php require __DIR__ . '/templates/pwa_head.php'; ?>
</head>
<body class="login-shell" data-base="<?= e(rtrim(env('APP_BASE', '') ?: '', '/')) ?>">
  <div class="login-card animate-rise" style="text-align:center">
    <img src="<?= e(asset('assets/img/pollicontas-logo.png')) ?>" alt="<?= e(APP_NAME) ?>" width="72" height="72" style="margin:0 auto 1rem;border-radius:18px;box-shadow:var(--shadow);display:block">
    <div class="display" style="font-size:2rem;font-weight:800;color:var(--navy)"><?= e(APP_NAME) ?></div>
    <p class="muted">Dashboard financeiro · Prestação de Contas · Goiás 2026</p>
    <div class="splash-bar" style="margin-top:1.5rem"></div>
  </div>
  <script>setTimeout(function(){ location.href = <?= json_encode($target) ?>; }, 1600);</script>
</body>
</html>
