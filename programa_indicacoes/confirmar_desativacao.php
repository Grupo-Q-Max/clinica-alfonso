<?php
session_start();
require 'backend/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $token = $_POST['token'] ?? '';

    if (empty($email) || empty($token)) {
        header("Location: desativar_user.php?erro=Preencha todos os campos.");
        exit;
    }

    $stmt = $conexao->prepare("SELECT * FROM usuarios_tokens WHERE email = ? AND token = ? AND expirado_em > NOW() AND confirmado = 0 AND motivo = 'desativacao'");

    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt_update_user = $conexao->prepare("UPDATE usuarios SET tipo_conta = 'desativado' WHERE email = ?");
        $stmt_update_user->bind_param("s", $email);
        $stmt_update_user->execute();

        $stmt_update_token = $conexao->prepare("UPDATE usuarios_tokens SET confirmado = 1 WHERE email = ? AND token = ?");

        $stmt_update_token->bind_param("ss", $email, $token);
        $stmt_update_token->execute();

        $stmt_delete = $conexao->prepare("DELETE FROM usuarios_tokens WHERE email = ? AND token = ?");

        $stmt_delete->bind_param("ss", $email, $token);
        $stmt_delete->execute();

        session_unset();
        session_destroy();

        header("Location: login.php?desativado=1");
        exit;
    } else {
        header("Location: desativar_user.php?erro=Código inválido ou expirado.");
        exit;
    }
}
