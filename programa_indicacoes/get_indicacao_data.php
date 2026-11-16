<?php
require 'backend/config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID não fornecido']);
    exit;
}

$id = intval($_GET['id']);
$query = "SELECT valor_pendente FROM indicacoes WHERE id = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Indicação não encontrada']);
    exit;
}

$data = $result->fetch_assoc();
echo json_encode([
    'valor_pendente' => floatval($data['valor_pendente'])
]);