<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require '../backend/config.php';
require '../vendor/autoload.php';
require '../backend/funcoes_email.php';

function responderJson($success, $message)
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    responderJson(false, 'Sessão expirada. Por favor, faça login novamente.');
}

$usuario_id = $_SESSION['user_id'];

$stmt_user = $conexao->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $usuario_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

if (!$user) {
    responderJson(false, 'Usuário não encontrado.');
}

$email = $user['email'];
$nome = $user['nome'];

// Gera um token numérico de 6 dígitos seguro
$token_puro = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$token_hash = password_hash($token_puro, PASSWORD_BCRYPT);

// Salva o token HASH no banco de dados
try {
    $stmt_token = $conexao->prepare("INSERT INTO usuarios_tokens (email, token, expirado_em) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE)) ON DUPLICATE KEY UPDATE token = VALUES(token), expirado_em = VALUES(expirado_em)");
    if ($stmt_token === false) {
        throw new Exception("Falha na preparação da query: " . $conexao->error);
    }
    $stmt_token->bind_param("ss", $email, $token_hash);
    $stmt_token->execute();

    // Verifica se a linha foi realmente inserida/atualizada
    if ($stmt_token->affected_rows === 0 && $stmt_token->errno !== 0) {
        throw new Exception("Nenhuma linha afetada ao salvar o token: " . $stmt_token->error);
    }
    $stmt_token->close();
} catch (Exception $e) {
    error_log("Erro Crítico ao salvar token no DB: " . $e->getMessage());
    responderJson(false, 'Não foi possível gerar o código de verificação no momento. Tente novamente.');
}

// Envia o e-mail com o token PURO (o número)
if (enviarEmailTokenAcao($email, $nome, $token_puro)) {
    responderJson(true, 'Código enviado com sucesso!');
} else {
    responderJson(false, 'Falha ao enviar o código para o seu e-mail. Verifique o endereço e tente novamente.');
}
