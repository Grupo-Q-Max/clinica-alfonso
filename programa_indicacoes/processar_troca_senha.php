<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['precisa_trocar_senha'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: trocar_senha_obrigatorio.php");
    exit;
}

$nova_senha = $_POST['nova_senha'];
$confirma_senha = $_POST['confirma_senha'];

if (empty($nova_senha) || $nova_senha !== $confirma_senha) {
    $_SESSION['error_msg'] = "As senhas não coincidem ou estão em branco.";
    header("Location: trocar_senha_obrigatorio.php");
    exit;
}

if (strlen($nova_senha) < 8) {
    $_SESSION['error_msg'] = "A senha deve ter no mínimo 8 caracteres.";
    header("Location: trocar_senha_obrigatorio.php");
    exit;
}

$senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
$usuario_id = $_SESSION['usuario_id'];

$stmt = $conexao->prepare("UPDATE usuarios SET senha = ?, trocou_senha = 1 WHERE id = ?");
$stmt->bind_param("si", $senha_hash, $usuario_id);

if ($stmt->execute()) {
    unset($_SESSION['precisa_trocar_senha']);

    header("Location: indicador.php");
    exit;
} else {
    $_SESSION['error_msg'] = "Ocorreu um erro ao atualizar sua senha. Tente novamente.";
    header("Location: trocar_senha_obrigatorio.php");
    exit;
}
