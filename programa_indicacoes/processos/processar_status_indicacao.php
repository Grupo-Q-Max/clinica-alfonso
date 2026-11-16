<?php
session_start();
require '../backend/config.php';

// --- FUNÇÃO DE FEEDBACK E REDIRECIONAMENTO ---
function redirecionarComFeedback($type, $message, $status_filtro)
{
    $_SESSION['form_feedback'] = ['type' => $type, 'text' => $message];
    header("Location: ../gerenciar_indicacoes.php?status=" . urlencode($status_filtro));
    exit;
}

// 1. VERIFICAÇÃO DE SEGURANÇA
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_conta'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

// 2. COLETA DE DADOS
$indicacao_id = (int)($_POST['indicacao_id'] ?? 0);
$novo_status = $_POST['status'] ?? '';
$admin_id = $_SESSION['usuario_id'];
$status_filtro_retorno = $_POST['status_filtro_retorno'] ?? 'Ativa';

if ($indicacao_id === 0 || empty($novo_status)) {
    redirecionarComFeedback('error', 'Dados inválidos.', $status_filtro_retorno);
}

// Busca dados essenciais da indicação
$stmt_data = $conexao->prepare("SELECT usuario_id, valor_pendente FROM indicacoes WHERE id = ?");
$stmt_data->bind_param("i", $indicacao_id);
$stmt_data->execute();
$indicacao_data = $stmt_data->get_result()->fetch_assoc();
$usuario_id = $indicacao_data['usuario_id'];
$stmt_data->close();

if (!$usuario_id) {
    redirecionarComFeedback('error', 'Indicação não encontrada.', $status_filtro_retorno);
}

// 3. LÓGICA DE NEGÓCIO E EXECUÇÃO NO BANCO DE DADOS
$conexao->begin_transaction();
try {
    switch ($novo_status) {
        case 'Indicação Boa':
            $valor_a_pagar = 20.00;
            $descricao_ganho = "Bônus por indicação qualificada";
            $tipo_ganho = 'indicacao_boa';

            // Atualiza status e zera valor pendente inicial
            $stmt = $conexao->prepare("UPDATE indicacoes SET status = ?, admin_responsavel_id = ?, data_alteracao = NOW(), valor_pendente = 0 WHERE id = ?");
            $stmt->bind_param("sii", $novo_status, $admin_id, $indicacao_id);
            break;

        case 'Proposta Enviada':
            $servicos_valores = $_POST['servico_valor'] ?? [];
            $valor_pendente_final = 0;

            foreach ($servicos_valores as $servico_id => $valor) {
                $valor_usuario = (float)$valor;
                $valor_pendente_final += $valor_usuario;

                $stmt_servico = $conexao->prepare("UPDATE indicacao_servicos SET valor_usuario = ? WHERE id = ? AND indicacao_id = ?");
                $stmt_servico->bind_param("dii", $valor_usuario, $servico_id, $indicacao_id);
                $stmt_servico->execute();
                $stmt_servico->close();
            }

            $stmt = $conexao->prepare("UPDATE indicacoes SET status = ?, admin_responsavel_id = ?, data_alteracao = NOW(), valor_pendente = ? WHERE id = ?");
            $stmt->bind_param("sidi", $novo_status, $admin_id, $valor_pendente_final, $indicacao_id);
            break;

        case 'Fechada':
            $valor_a_pagar = (float)($indicacao_data['valor_pendente'] ?? 0);
            if ($valor_a_pagar > 0) {
                $descricao_ganho = "Liberação de valor pendente por fechamento";
                $tipo_ganho = 'proposta_fechada';
            }

            $stmt = $conexao->prepare("UPDATE indicacoes SET status = ?, admin_responsavel_id = ?, data_alteracao = NOW(), valor_pendente = 0 WHERE id = ?");
            $stmt->bind_param("sii", $novo_status, $admin_id, $indicacao_id);
            break;

        case 'Rejeitada':
            $motivo_rejeicao = trim(htmlspecialchars($_POST['motivo_rejeicao'] ?? 'Motivo não especificado.'));
            $stmt_motivo = $conexao->prepare("INSERT INTO motivos_rejeicao (indicacao_id, motivo) VALUES (?, ?) ON DUPLICATE KEY UPDATE motivo = VALUES(motivo)");
            $stmt_motivo->bind_param("is", $indicacao_id, $motivo_rejeicao);
            $stmt_motivo->execute();
            $stmt_motivo->close();

            $stmt = $conexao->prepare("UPDATE indicacoes SET status = ?, admin_responsavel_id = ?, data_alteracao = NOW() WHERE id = ?");
            $stmt->bind_param("sii", $novo_status, $admin_id, $indicacao_id);
            break;
    }

    $stmt->execute();
    $stmt->close();

    if (isset($valor_a_pagar) && $valor_a_pagar > 0) {
        $stmt_saldo = $conexao->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
        $stmt_saldo->bind_param("di", $valor_a_pagar, $usuario_id);
        $stmt_saldo->execute();
        $stmt_saldo->close();

        $stmt_ganho = $conexao->prepare("INSERT INTO ganhos (usuario_id, indicacao_id, valor, tipo, descricao) VALUES (?, ?, ?, ?, ?)");
        $stmt_ganho->bind_param("iidss", $usuario_id, $indicacao_id, $valor_a_pagar, $tipo_ganho, $descricao_ganho);
        $stmt_ganho->execute();
        $stmt_ganho->close();
    }

    $conexao->commit();
    redirecionarComFeedback('success', 'Status da indicação atualizado com sucesso!', $status_filtro_retorno);
} catch (Exception $e) {
    $conexao->rollback();
    error_log("ERRO AO ATUALIZAR STATUS: " . $e->getMessage());
    redirecionarComFeedback('error', 'Ocorreu um erro no banco de dados. A operação foi cancelada.', $status_filtro_retorno);
}
