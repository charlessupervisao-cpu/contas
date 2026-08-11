<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$body = json_decode(file_get_contents('php://input') ?: '{}', true) ?: [];
$key = (string) ($body['key'] ?? $_POST['key'] ?? '');
$result = validate_nfe_key($key);
json_response($result, $result['valid'] ? 200 : 400);
