<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require '../backend/config.php';

function responderJson($success, $message, $extra = [])
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

if (!isset($_SESSION['user_id'])) {
    responderJson(false, 'Sessão expirada. Faça login novamente.');
}

$usuario_id = $_SESSION['user_id'];
$token_enviado = $_POST['token'] ?? '';
$motivo = $_POST['motivo'] ?? '';

if (strlen($token_enviado) !== 6 || empty($motivo)) {
    responderJson(false, 'Dados inválidos.');
}

// Pega o e-mail do usuário logado
$stmt_user = $conexao->prepare("SELECT email FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $usuario_id);
$stmt_user->execute();
$email = $stmt_user->get_result()->fetch_assoc()['email'];
$stmt_user->close();

$stmt = $conexao->prepare("SELECT token FROM usuarios_tokens WHERE email = ? AND expirado_em > NOW()");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$token_info = $result->fetch_assoc();
$stmt->close();

if (!$token_info || !password_verify($token_enviado, $token_info['token'])) {
    responderJson(false, 'Código inválido ou expirado. Por favor, tente novamente.');
}

$stmt_delete = $conexao->prepare("DELETE FROM usuarios_tokens WHERE email = ?");
$stmt_delete->bind_param("s", $email);
$stmt_delete->execute();
$stmt_delete->close();

if ($motivo === 'editar_perfil') {
    $_SESSION['perfil_verificado'] = true;
    responderJson(true, 'Verificação concluída! Redirecionando...', ['redirect' => 'editar_perfil.php']);
} elseif ($motivo === 'desativar_conta') {
    $stmt_deactivate = $conexao->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?");
    $stmt_deactivate->bind_param("i", $usuario_id);
    $stmt_deactivate->execute();
    $stmt_deactivate->close();

    session_destroy();
    responderJson(true, 'Conta desativada com sucesso. Você será desconectado.', ['redirect' => 'login.php']);
} else {
    responderJson(false, 'Ação desconhecida.');
}
