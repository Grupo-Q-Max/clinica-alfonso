<?php
session_start();
require '../backend/config.php';

// Função para redirecionar com uma mensagem de feedback
function redirecionarComFeedback($type, $message)
{
    // Você pode usar uma session para mostrar a mensagem na página de usuários
    $_SESSION['feedback'] = ['type' => $type, 'message' => $message];
    header("Location: ../usuarios.php");
    exit;
}

// Verificações de segurança
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$action = $_POST['action'] ?? '';
$usuario_id = (int)($_POST['usuario_id'] ?? 0);

if (empty($action) || $usuario_id === 0) {
    redirecionarComFeedback('error', 'Ação ou ID de usuário inválido.');
}

$mensagem_sucesso = '';
$query = '';
$novo_status = 0;

try {
    switch ($action) {
        case 'ativar_status':
            $query = "UPDATE usuarios SET ativo = 1 WHERE id = ?";
            $novo_status = 1;
            $mensagem_sucesso = "Usuário ativado com sucesso.";
            break;

        case 'desativar_status': // Nova ação para bloquear/desativar
            $query = "UPDATE usuarios SET ativo = 0 WHERE id = ?";
            $novo_status = 0;
            $mensagem_sucesso = "Usuário bloqueado com sucesso.";
            break;

        default:
            throw new Exception("Ação desconhecida.");
    }

    $stmt = $conexao->prepare($query);
    if (!$stmt) {
        throw new Exception("Erro ao preparar a consulta: " . $conexao->error);
    }

    $stmt->bind_param("i", $usuario_id);
    if ($stmt->execute()) {
        redirecionarComFeedback('success', $mensagem_sucesso);
    } else {
        throw new Exception("Erro ao executar a consulta.");
    }
} catch (Exception $e) {
    // Log do erro para depuração
    error_log("Erro em processar_usuario.php: " . $e->getMessage());
    redirecionarComFeedback('error', 'Ocorreu um erro no servidor. A operação foi cancelada.');
}
