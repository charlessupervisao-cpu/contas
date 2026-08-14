<?php
/**
 * Web App Manifest (PWA) — paths respeitam APP_BASE.
 */
require_once __DIR__ . '/bootstrap.php';

$base = app_base_path(); // '' or '/subdir'
$scope = ($base === '' ? '/' : $base . '/');
$start = url_path('index.php');

$iconsBase = asset('assets/img/icons');

$manifest = [
    'id' => $scope,
    'name' => 'CONTAS',
    'short_name' => 'CONTAS',
    'description' => 'Prestação de contas para políticos · Dashboard financeiro · Desenvolvido por Synetiq',
    'lang' => 'pt-BR',
    'dir' => 'ltr',
    'start_url' => $start,
    'scope' => $scope,
    'display' => 'standalone',
    'display_override' => ['standalone', 'minimal-ui'],
    'orientation' => 'any',
    'background_color' => '#0a254a',
    'theme_color' => '#0a254a',
    'categories' => ['finance', 'business', 'productivity'],
    'prefer_related_applications' => false,
    'icons' => [
        [
            'src' => $iconsBase . '/icon-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => $iconsBase . '/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => $iconsBase . '/icon-192-maskable.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ],
        [
            'src' => $iconsBase . '/icon-512-maskable.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ],
    ],
    'shortcuts' => [
        [
            'name' => 'Dashboard',
            'short_name' => 'Início',
            'url' => url_path('admin/index.php'),
            'icons' => [['src' => $iconsBase . '/icon-192.png', 'sizes' => '192x192']],
        ],
        [
            'name' => 'Novo lançamento',
            'short_name' => 'Lançar',
            'url' => url_path('admin/lancamento.php'),
            'icons' => [['src' => $iconsBase . '/icon-192.png', 'sizes' => '192x192']],
        ],
        [
            'name' => 'Despesas',
            'short_name' => 'Despesas',
            'url' => url_path('admin/despesas.php'),
            'icons' => [['src' => $iconsBase . '/icon-192.png', 'sizes' => '192x192']],
        ],
        [
            'name' => 'Manual de Uso',
            'short_name' => 'Manual',
            'url' => url_path('admin/manual.php'),
            'icons' => [['src' => $iconsBase . '/icon-192.png', 'sizes' => '192x192']],
        ],
    ],
];

header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
