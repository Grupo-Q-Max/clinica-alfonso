<?php
include_once 'backend/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function registrarAcesso($conexao, $usuario_id = null)
{
    if (isset($_SESSION['acesso_registrado']) && $_SESSION['acesso_registrado'] === true) {
        return;
    }

    if (!$conexao || $conexao->connect_error) {
        error_log("Erro ao registrar acesso: Conexão com o banco de dados inválida.");
        return;
    }

    date_default_timezone_set('America/Sao_Paulo');

    $data_acesso = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    try {

        $stmt = $conexao->prepare("SELECT id FROM logs_acesso 
                                  WHERE ip = ? AND user_agent = ? 
                                  AND data_acesso > DATE_SUB(NOW(), INTERVAL 30 MINUTE) 
                                  LIMIT 1");
        $stmt->bind_param('ss', $ip, $user_agent);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt = $conexao->prepare("INSERT INTO logs_acesso 
                                       (usuario_id, data_acesso, ip, user_agent) 
                                       VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $usuario_id, $data_acesso, $ip, $user_agent);
            $stmt->execute();
        }

        $_SESSION['acesso_registrado'] = true;
    } catch (Exception $e) {
        error_log("Erro ao registrar acesso: " . $e->getMessage());
    }
}

registrarAcesso($conexao);
