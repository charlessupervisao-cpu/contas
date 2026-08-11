<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

if (!Auth::user()) {
    json_response(['ok' => false, 'error' => 'Sessão expirada. Faça login novamente.'], 401);
}

$cep = only_digits((string) ($_GET['cep'] ?? ''));
if (strlen($cep) !== 8) {
    json_response(['ok' => false, 'error' => 'CEP deve ter 8 dígitos.'], 400);
}

$url = 'https://viacep.com.br/ws/' . rawurlencode($cep) . '/json/';
$data = http_get_json($url, 8);
if ($data === null) {
    json_response(['ok' => false, 'error' => 'Falha ao consultar o ViaCEP.'], 502);
}
if (!empty($data['erro'])) {
    json_response(['ok' => false, 'error' => 'CEP não encontrado.'], 404);
}

$logradouro = trim((string) ($data['logradouro'] ?? ''));
$bairro = trim((string) ($data['bairro'] ?? ''));
$complemento = trim((string) ($data['complemento'] ?? ''));

json_response([
    'ok' => true,
    'cep' => preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep) ?: $cep,
    'address' => $logradouro,
    'neighborhood' => $bairro,
    'city' => (string) ($data['localidade'] ?? ''),
    'state' => (string) ($data['uf'] ?? ''),
    'complement' => $complemento,
    'addressComplement' => $complemento,
]);
