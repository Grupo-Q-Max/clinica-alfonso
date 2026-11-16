<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$cnpj = isset($_GET['cnpj']) ? preg_replace('/\D/', '', $_GET['cnpj']) : '';

if (strlen($cnpj) !== 14) {
    echo json_encode(['error' => 'CNPJ inválido']);
    exit;
}

$url = "https://receitaws.com.br/v1/cnpj/{$cnpj}";
$response = file_get_contents($url);

if ($response === false) {
    echo json_encode(['error' => 'Erro ao consultar CNPJ']);
} else {
    echo $response;
}
   