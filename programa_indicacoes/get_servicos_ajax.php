<?php
session_start();
require 'backend/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    $_SESSION['login_error_message'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit;
}


$indicacao_id = (int)($_GET['id'] ?? 0);
if ($indicacao_id === 0) {
    echo json_encode(['success' => false, 'message' => 'ID da indicação inválido.']);
    exit;
}

$query = "SELECT id, servico, descricao_outros FROM indicacao_servicos WHERE indicacao_id = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $indicacao_id);
$stmt->execute();
$result = $stmt->get_result();
$servicos = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success' => true, 'servicos' => $servicos]);
