<?php
require_once '../vendor/autoload.php';
require_once '../backend/config.php';

use Dotenv\Dotenv;

header('Content-Type: application/json');

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit;
}

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$token = $_POST['token'] ?? '';

if (!$email || empty($token)) {
    echo json_encode(['success' => false, 'message' => 'E-mail e token são obrigatórios e o e-mail deve ser válido.']);
    exit;
}

$stmt = $conexao->prepare("
    SELECT id, expirado_em 
    FROM usuarios_tokens 
    WHERE email = ? 
    AND token = ? 
    AND motivo = 'editar_perfil' 
    ORDER BY id DESC 
    LIMIT 1
");

$stmt->bind_param("ss", $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Token inválido ou não encontrado.']);
    exit;
}

$row = $result->fetch_assoc();
$data_expiracao = $row['expirado_em'];

if (strtotime($data_expiracao) < time()) {
    echo json_encode(['success' => false, 'message' => 'Token expirado.']);
    exit;
}

if ($stmt->affected_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Erro ao processar a exclusão do token.']);
    exit;
}

echo json_encode(['success' => true, 'redirect' => 'editar_perfil.php?token=' . urlencode($token)]);
exit;
