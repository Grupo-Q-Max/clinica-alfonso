<?php
session_start();
require 'backend/config         ';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $conexao->prepare("SELECT tipo_conta FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['tipo_conta'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Acesso negado. Somente administradores podem gerenciar resgates."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && isset($_POST['solicitacao_id'])) {
    $solicitacao_id = $_POST['solicitacao_id'];
    $acao = $_POST['acao'];

    if ($acao === 'aprovar') {
        $stmt = $conexao->prepare("UPDATE solicitacao_resgate SET status_resgate = 'aprovado' WHERE id = ?");
        $stmt->bind_param("i", $solicitacao_id);
        $stmt->execute();

        $stmt = $conexao->prepare("SELECT usuario_id, valor_resgate FROM solicitacao_resgate WHERE id = ?");
        $stmt->bind_param("i", $solicitacao_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $solicitacao = $result->fetch_assoc();

        $_SESSION['message'] = "Solicitação aprovada com sucesso.";
        header("Location: resgates.php");
        exit;
    } elseif ($acao === 'rejeitar') {
        $stmt = $conexao->prepare("UPDATE solicitacao_resgate SET status_resgate = 'rejeitado' WHERE id = ?");
        $stmt->bind_param("i", $solicitacao_id);
        $stmt->execute();

        $stmt = $conexao->prepare("SELECT usuario_id, valor_resgate FROM solicitacao_resgate WHERE id = ?");
        $stmt->bind_param("i", $solicitacao_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $solicitacao = $result->fetch_assoc();

        $usuario_id = $solicitacao['usuario_id'];
        $valor_resgate = $solicitacao['valor_resgate'];

        $stmt = $conexao->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
        $stmt->bind_param("di", $valor_resgate, $usuario_id);
        $stmt->execute();

        $_SESSION['message'] = "Solicitação rejeitada com sucesso. Saldo retornado.";
        header("Location: resgates.php");
        exit;
    }
}
