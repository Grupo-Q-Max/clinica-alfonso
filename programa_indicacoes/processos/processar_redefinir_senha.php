<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../backend/config.php';

// --- FUNÇÃO DE FEEDBACK E REDIRECIONAMENTO ---
function redirecionarComErro($token, $mensagem)
{
    $_SESSION['reset_error'] = $mensagem;
    // Redireciona de volta para a página de redefinição, mantendo o token na URL
    header("Location: ../redefinir_senha.php?token=" . urlencode($token));
    exit;
}

// 1. VERIFICAÇÃO INICIAL
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit;
}

// 2. COLETA E VALIDAÇÃO DOS DADOS
$token_from_url = $_POST['token'] ?? '';
$email = $_POST['email'] ?? ''; // Pegamos o e-mail que foi validado na página anterior
$nova_senha = $_POST['nova_senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

if (empty($token_from_url) || empty($email) || empty($nova_senha)) {
    redirecionarComErro($token_from_url, 'Ocorreu um erro. Por favor, tente o processo novamente desde o início.');
}

if ($nova_senha !== $confirmar_senha) {
    redirecionarComErro($token_from_url, 'As senhas não coincidem.');
}

// Validação de força da senha
if (strlen($nova_senha) < 7 || !preg_match('/[A-Z]/', $nova_senha) || !preg_match('/[0-9]/', $nova_senha) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $nova_senha)) {
    redirecionarComErro($token_from_url, 'A nova senha não atende aos requisitos de segurança (mínimo 7 caracteres, 1 maiúscula, 1 número e 1 símbolo).');
}


// 3. VERIFICAÇÃO SEGURA DO TOKEN
try {
    $stmt_check = $conexao->prepare("SELECT token FROM usuarios_tokens WHERE email = ? AND motivo = 'reset_password' AND confirmado = 0 AND expirado_em > NOW()");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $token_valido = false;

    while ($row = $result_check->fetch_assoc()) {
        if (password_verify($token_from_url, $row['token'])) {
            $token_valido = true;
            break;
        }
    }
    $stmt_check->close();

    if (!$token_valido) {
        redirecionarComErro($token_from_url, 'Token inválido, expirado ou já utilizado.');
    }

    // 4. ATUALIZAÇÃO DA SENHA E LIMPEZA DO TOKEN
    $conexao->begin_transaction();

    // Criptografa a nova senha
    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    // Atualiza a senha do usuário e marca que a senha foi trocada
    $stmt_update = $conexao->prepare("UPDATE usuarios SET senha = ?, trocou_senha = 1 WHERE email = ?");
    $stmt_update->bind_param("ss", $nova_senha_hash, $email);
    $stmt_update->execute();
    $stmt_update->close();

    // Apaga o token de redefinição para que não possa ser usado novamente
    $stmt_delete = $conexao->prepare("DELETE FROM usuarios_tokens WHERE email = ? AND motivo = 'reset_password'");
    $stmt_delete->bind_param("s", $email);
    $stmt_delete->execute();
    $stmt_delete->close();

    $conexao->commit();

    // 5. SUCESSO!
    $_SESSION['login_success_message'] = "Sua senha foi redefinida com sucesso! Você já pode fazer login com a nova senha.";
    header("Location: ../login.php");
    exit;
} catch (Exception $e) {
    $conexao->rollback();
    error_log("Erro ao redefinir senha: " . $e->getMessage());
    redirecionarComErro($token_from_url, 'Ocorreu um erro no servidor ao tentar atualizar sua senha. Por favor, tente novamente.');
}
