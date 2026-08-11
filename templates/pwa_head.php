<?php
/** PWA meta + ícones (paths respeitam APP_BASE). */
$iconsBase = asset('assets/img/icons');
$manifestUrl = url_path('manifest.php');
?>
<meta name="theme-color" content="#0a254a">
<meta name="application-name" content="<?= e(APP_NAME) ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="<?= e(APP_NAME) ?>">
<meta name="mobile-web-app-capable" content="yes">
<meta name="description" content="<?= e(APP_TAGLINE) ?> · Desenvolvido por <?= e(APP_VENDOR) ?>">
<link rel="manifest" href="<?= e($manifestUrl) ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= e($iconsBase . '/icon-192.png') ?>">
<link rel="icon" type="image/png" sizes="512x512" href="<?= e($iconsBase . '/icon-512.png') ?>">
<link rel="apple-touch-icon" href="<?= e($iconsBase . '/apple-touch-180.png') ?>">
