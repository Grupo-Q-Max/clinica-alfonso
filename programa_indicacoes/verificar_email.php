<?php
session_start();
require 'backend/config.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    header('Location: login.php?status=erro&msg=' . urlencode('Token de verificação inválido ou ausente.'));
    exit();
}

$token = $_GET['token'];

$stmt = $conexao->prepare("SELECT email, expirado_em FROM usuarios_tokens WHERE token = ? AND usado = 0");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: login.php?status=erro&msg=' . urlencode('Token inválido ou já utilizado.'));
    exit();
}

$token_data = $result->fetch_assoc();
$email = $token_data['email'];
$expirado_em = new DateTime($token_data['expirado_em']);
$agora = new DateTime();

if ($agora > $expirado_em) {
    header('Location: login.php?status=erro&msg=' . urlencode('Seu token de verificação expirou. Por favor, solicite um novo.'));
    exit();
}

$conexao->begin_transaction();

try {
    $stmt_update_user = $conexao->prepare("UPDATE usuarios SET verificado = 1 WHERE email = ?");
    $stmt_update_user->bind_param("s", $email);
    $stmt_update_user->execute();
    $stmt_update_token = $conexao->prepare("UPDATE usuarios_tokens SET usado = 1 WHERE token = ?");
    $stmt_update_token->bind_param("s", $token);
    $stmt_update_token->execute();

    $conexao->commit();

    header('Location: login.php?status=sucesso&msg=' . urlencode('E-mail verificado com sucesso! Você já pode fazer login.'));
    exit();
} catch (Exception $e) {
    $conexao->rollback();
    error_log("Erro ao verificar e-mail: " . $e->getMessage());
    header('Location: login.php?status=erro&msg=' . urlencode('Ocorreu um erro ao verificar seu e-mail. Tente novamente.'));
    exit();
}
