<?php
session_start();
require_once '../backend/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acesso negado.');
}

$token = $_POST['token'] ?? '';
$nova_senha = $_POST['nova_senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

if (empty($token) || empty($nova_senha) || empty($confirmar_senha)) {
    $_SESSION['message'] = "Todos os campos são obrigatórios.";
    header('Location: ../redefinir_senha.php?token=' . urlencode($token));
    exit;
}

if (strlen($nova_senha) < 7) {
    $_SESSION['message'] = "A nova senha deve ter no mínimo 7 caracteres.";
    header('Location: ../redefinir_senha.php?token=' . urlencode($token));
    exit;
}

if ($nova_senha !== $confirmar_senha) {
    $_SESSION['message'] = "As senhas não coincidem.";
    header('Location: ../redefinir_senha.php?token=' . urlencode($token));
    exit;
}

$stmt = $conexao->prepare("SELECT email FROM usuarios_tokens WHERE token = ? AND motivo = 'reset_password' AND confirmado = 0 AND expirado_em > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Token inválido ou expirado. Por favor, solicite um novo.";
    header('Location: ../esqueceu_senha.php');
    exit;
}

$token_data = $result->fetch_assoc();
$email = $token_data['email'];

$conexao->begin_transaction();
try {
    $senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
    $stmt_update = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
    $stmt_update->bind_param("ss", $senha_hash, $email);
    $stmt_update->execute();

    $stmt_invalidate = $conexao->prepare("UPDATE usuarios_tokens SET confirmado = 1 WHERE token = ?");
    $stmt_invalidate->bind_param("s", $token);
    $stmt_invalidate->execute();
    
    $conexao->commit();
} catch (Exception $e) {
    $conexao->rollback();
    error_log("Erro ao atualizar senha QXBack: " . $e->getMessage());
    $_SESSION['message'] = "Erro ao atualizar a senha. Tente novamente.";
    header('Location: ../redefinir_senha.php?token=' . urlencode($token));
    exit;
}

$_SESSION['reset_success'] = true;
header('Location: ../login.php');
exit;
?>