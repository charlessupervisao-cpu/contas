<?php
require_once dirname(__DIR__) . '/bootstrap.php';
try {
    Database::ping();
    $pdo = Database::pdo();
    $users = (int) $pdo->query('SELECT COUNT(*) AS c FROM `User`')->fetch()['c'];
    $campaigns = (int) $pdo->query('SELECT COUNT(*) AS c FROM `Campaign`')->fetch()['c'];
    $root = dirname(__DIR__);
    $modules = [
        'reports' => class_exists('Reports'),
        'electoralRules' => class_exists('ElectoralRules'),
        'oficialCss' => is_file($root . '/assets/css/relatorio-oficial.css'),
        'oficialTpl' => is_file($root . '/templates/relatorio_oficial.php'),
        'treGoData' => is_file($root . '/data/tre-go-2026.json'),
        'relatoriosPage' => is_file($root . '/admin/relatorios.php'),
    ];
    json_response([
        'ok' => true,
        'database' => true,
        'engine' => 'mysql',
        'runtime' => 'php',
        'brand' => APP_NAME,
        'build' => APP_BUILD,
        'domain' => APP_DOMAIN,
        'vendor' => APP_VENDOR,
        'modules' => $modules,
        'counts' => ['users' => $users, 'campaigns' => $campaigns],
        'timestamp' => date('c'),
    ]);
} catch (Throwable $e) {
    json_response([
        'ok' => false,
        'database' => false,
        'engine' => 'mysql',
        'runtime' => 'php',
        'brand' => APP_NAME,
        'build' => defined('APP_BUILD') ? APP_BUILD : null,
        'domain' => APP_DOMAIN,
        'vendor' => APP_VENDOR,
        'error' => $e->getMessage(),
    ], 503);
}
