<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once '../backend/config.php';
require_once '../vendor/autoload.php';
require_once '../backend/funcoes_email.php';

function responderJson($success, $message)
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(false, 'Método não permitido.');
}

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
if (!$email) {
    responderJson(false, 'Formato de e-mail inválido.');
}

$stmt = $conexao->prepare("SELECT id, nome FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    responderJson(true, 'Se o e-mail estiver cadastrado em nossa base de dados, um link de redefinição será enviado.');
}

$user = $result->fetch_assoc();
$nome = $user['nome'];
$token = bin2hex(random_bytes(32));
$expirado_em = date('Y-m-d H:i:s', strtotime('+1 hour'));


$token_hash_para_db = password_hash($token, PASSWORD_DEFAULT);

try {
    $stmt_insert = $conexao->prepare("INSERT INTO usuarios_tokens (email, token, motivo, expirado_em) VALUES (?, ?, 'reset_password', ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expirado_em = VALUES(expirado_em), confirmado = 0");

    $stmt_insert->bind_param("sss", $email, $token_hash_para_db, $expirado_em);

    $stmt_insert->execute();
} catch (Exception $e) {
    error_log("Erro ao salvar token de reset: " . $e->getMessage());
    responderJson(false, 'Ocorreu um erro no servidor. Tente novamente mais tarde.');
}

if (enviarEmailRecuperacao($email, $nome, $token)) {
    responderJson(true, 'Se o e-mail estiver cadastrado em nossa base de dados, um link de redefinição foi enviado.');
} else {
    responderJson(false, 'Não foi possível enviar o e-mail de recuperação no momento. Tente novamente.');
}
