<?php
require_once dirname(__DIR__) . '/bootstrap.php';
try {
    Database::ping();
    $pdo = Database::pdo();
    $users = (int) $pdo->query('SELECT COUNT(*) AS c FROM `User`')->fetch()['c'];
    $campaigns = (int) $pdo->query('SELECT COUNT(*) AS c FROM `Campaign`')->fetch()['c'];
    json_response([
        'ok' => true,
        'database' => true,
        'engine' => 'mysql',
        'runtime' => 'php',
        'brand' => APP_NAME,
        'domain' => APP_DOMAIN,
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
        'domain' => APP_DOMAIN,
        'error' => $e->getMessage(),
    ], 503);
}
