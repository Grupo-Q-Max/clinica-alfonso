<?php
session_start();
require 'backend/config.php';

// Validação de segurança e permissão de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    $_SESSION['login_error_message'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit;
}

// ==========================================================================================
// === INÍCIO DA LÓGICA DE PROCESSAMENTO (POST) - JÁ CORRIGIDA ANTERIORMENTE ==============
// ==========================================================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $indicacao_id = (int)($_POST['indicacao_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $admin_id = $_SESSION['user_id'];

    if ($indicacao_id > 0 && !empty($status)) {
        // Busca o ID do usuário que fez a indicação
        $usuario_query = "SELECT usuario_id FROM indicacoes WHERE id = ?";
        $stmt_user = $conexao->prepare($usuario_query);
        $stmt_user->bind_param("i", $indicacao_id);
        $stmt_user->execute();
        $usuario_id = $stmt_user->get_result()->fetch_assoc()['usuario_id'];
        $stmt_user->close();

        $conexao->begin_transaction();
        try {
            $valor_a_creditar = 0;
            $tipo_ganho = 'outro';
            $descricao = "Status da indicação #$indicacao_id atualizado para $status.";

            // --- LÓGICA PARA "ORÇAMENTO ENVIADO" ---
            if ($status == 'Orçamento Enviado') {
                $servicos_orcamento = $_POST['servicos_proposta'] ?? [];
                $valores_finais_servicos = $_POST['valor_final_servico'] ?? [];
                $valor_pendente_total = 0;

                if (!empty($servicos_orcamento)) {
                    // Prepara a query para buscar as regras de cashback dos serviços selecionados
                    $placeholders = implode(',', array_fill(0, count($servicos_orcamento), '?'));
                    $types = str_repeat('s', count($servicos_orcamento));

                    $regras_sql = "SELECT nome, tipo_cashback, valor_cashback, tem_limite, limite_cashback FROM servicos WHERE nome IN ($placeholders)";
                    $stmt_regras = $conexao->prepare($regras_sql);
                    $stmt_regras->bind_param($types, ...$servicos_orcamento);
                    $stmt_regras->execute();
                    $regras_result = $stmt_regras->get_result();
                    $regras_db = [];
                    while ($row = $regras_result->fetch_assoc()) {
                        $regras_db[$row['nome']] = $row;
                    }
                    $stmt_regras->close();

                    // Itera sobre os serviços do orçamento para calcular o valor pendente
                    foreach ($servicos_orcamento as $index => $nome_servico) {
                        $regra = $regras_db[$nome_servico] ?? null;
                        if (!$regra) continue; // Pula se o serviço não for encontrado

                        $cashback_calculado = 0;
                        if ($regra['tipo_cashback'] == 'valor') {
                            $cashback_calculado = (float)$regra['valor_cashback'];
                        } elseif ($regra['tipo_cashback'] == 'percentual') {
                            $valor_final_servico = (float)str_replace(',', '.', $valores_finais_servicos[$index] ?? '0');
                            $percentual = (float)$regra['valor_cashback'];
                            $cashback_calculado = $valor_final_servico * ($percentual / 100);

                            // Aplica o limite, se houver
                            if ($regra['tem_limite'] && $regra['limite_cashback'] > 0) {
                                if ($cashback_calculado > (float)$regra['limite_cashback']) {
                                    $cashback_calculado = (float)$regra['limite_cashback'];
                                }
                            }
                        }
                        $valor_pendente_total += $cashback_calculado;
                    }
                }

                // Atualiza a indicação com o novo status e o valor pendente calculado
                $update_indicacao = $conexao->prepare("UPDATE indicacoes SET status = ?, admin_responsavel_id = ?, data_alteracao = NOW(), valor_pendente = ? WHERE id = ?");
                $update_indicacao->bind_param("sidi", $status, $admin_id, $valor_pendente_total, $indicacao_id);
                $update_indicacao->execute();
                $update_indicacao->close();

                // --- LÓGICA PARA "APROVADO" ---
            } elseif ($status == 'Aprovado') {
                // Busca o valor pendente que foi calculado no passo anterior
                $valor_pendente_query = "SELECT valor_pendente FROM indicacoes WHERE id = ?";
                $stmt_vp = $conexao->prepare($valor_pendente_query);
                $stmt_vp->bind_param("i", $indicacao_id);
                $stmt_vp->execute();
                $valor_a_creditar = (float)$stmt_vp->get_result()->fetch_assoc()['valor_pendente'];
                $stmt_vp->close();

                $tipo_ganho = 'comissao_aprovada';
                $descricao = "Comissão por indicação aprovada (#$indicacao_id)";

                // Atualiza a indicação, zerando o valor pendente pois ele será pago agora
                $update_indicacao = $conexao->prepare("UPDATE indicacoes SET status = ?, admin_responsavel_id = ?, data_alteracao = NOW(), valor_pendente = 0 WHERE id = ?");
                $update_indicacao->bind_param("sii", $status, $admin_id, $indicacao_id);
                $update_indicacao->execute();
                $update_indicacao->close();

                // --- LÓGICA PARA OUTROS STATUS (Indicação Boa, Rejeitada, etc.) ---
            } else {
                // Para outros status, apenas atualiza sem gerar crédito
                $update_indicacao = $conexao->prepare("UPDATE indicacoes SET status = ?, admin_responsavel_id = ?, data_alteracao = NOW() WHERE id = ?");
                $update_indicacao->bind_param("sii", $status, $admin_id, $indicacao_id);
                $update_indicacao->execute();
                $update_indicacao->close();
            }

            // --- SEÇÃO DE PAGAMENTO (executa se houver valor a creditar) ---
            if ($valor_a_creditar > 0 && $usuario_id) {
                // Adiciona o valor ao saldo do usuário
                $update_saldo = $conexao->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
                $update_saldo->bind_param("di", $valor_a_creditar, $usuario_id);
                $update_saldo->execute();
                $update_saldo->close();

                // Registra a transação na tabela de ganhos
                $insert_ganho = $conexao->prepare("INSERT INTO ganhos (usuario_id, indicacao_id, valor, tipo, descricao) VALUES (?, ?, ?, ?, ?)");
                $insert_ganho->bind_param("iidss", $usuario_id, $indicacao_id, $valor_a_creditar, $tipo_ganho, $descricao);
                $insert_ganho->execute();
                $insert_ganho->close();
            }

            $conexao->commit();
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Status da indicação atualizado com sucesso!'];
        } catch (Exception $e) {
            $conexao->rollback();
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao processar: ' . $e->getMessage()];
        }
    }
    header("Location: gerenciar_indicacoes.php?status=" . urlencode($_POST['status_filtro'] ?? 'Ativa'));
    exit;
}

// Lógica para buscar as indicações para exibição (GET)
$filter_status = $_GET['status'] ?? 'Ativa';
$query = "SELECT
            i.id, i.nome_paciente, i.telefone, i.email,
            i.data_criacao, i.status, i.valor_pendente,
            CONCAT(u.nome, ' ', u.sobrenome) AS nome_indicador,
            GROUP_CONCAT(DISTINCT s.servico SEPARATOR ', ') AS servicos_descricao
        FROM indicacoes i
        JOIN usuarios u ON i.usuario_id = u.id 
        LEFT JOIN indicacao_servicos s ON s.indicacao_id = i.id
        WHERE i.status = ?
        GROUP BY i.id
        ORDER BY i.data_criacao DESC";

$stmt = $conexao->prepare($query);
$stmt->bind_param("s", $filter_status);
$stmt->execute();
$indicacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (isset($_SESSION['feedback'])) {
    $feedback = $_SESSION['feedback'];
    unset($_SESSION['feedback']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Indicações</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="gerenciar_indicacoes.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="admin-body">
    <?php include 'includes/header_admin.php'; ?>
    <main class="main-content">
        <section class="page-header">
            <h1>Gerenciar Indicações</h1>
            <p>Acompanhe e gerencie todas as indicações do sistema.</p>
        </section>

        <?php if (isset($feedback)): ?>
            <div class="alert <?= $feedback['type'] === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($feedback['message']) ?>
            </div>
        <?php endif; ?>

        <section class="actions-bar">
            <div class="filter-buttons">
                <a href="?status=Ativa" class="filter-button <?= $filter_status == 'Ativa' ? 'active' : '' ?>">Pendentes</a>
                <a href="?status=Indicação Boa" class="filter-button <?= $filter_status == 'Indicação Boa' ? 'active' : '' ?>">Indicação Boa</a>
                <a href="?status=Orçamento Enviado" class="filter-button <?= $filter_status == 'Orçamento Enviado' ? 'active' : '' ?>">Orçamento Enviado</a>
                <a href="?status=Aprovado" class="filter-button <?= $filter_status == 'Aprovado' ? 'active' : '' ?>">Aprovado</a>
                <a href="?status=Rejeitada" class="filter-button <?= $filter_status == 'Rejeitada' ? 'active' : '' ?>">Rejeitadas</a>
            </div>
        </section>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Serviços Indicados</th>
                        <th>Indicador</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Valor Pendente</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($indicacoes)): ?>
                        <tr>
                            <td colspan="7" class="no-results">Nenhuma indicação encontrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($indicacoes as $indicacao): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($indicacao['nome_paciente']) ?></strong><br>
                                    <small><?= htmlspecialchars($indicacao['email']) ?></small><br>
                                    <small><?= htmlspecialchars($indicacao['telefone']) ?></small>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($indicacao['servicos_descricao'])) {
                                        foreach (explode(', ', $indicacao['servicos_descricao']) as $servico) {
                                            echo '<span class="servico-item">' . htmlspecialchars($servico) . '</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($indicacao['nome_indicador']) ?></td>
                                <td><?= date('d/m/Y', strtotime($indicacao['data_criacao'])) ?></td>
                                <td><span class="status-badge status-<?= strtolower(str_replace(' ', '_', $indicacao['status'])) ?>"><?= $indicacao['status'] ?></span></td>
                                <td class="valor-pendente-cell">R$ <?= number_format($indicacao['valor_pendente'], 2, ',', '.') ?></td>
                                <td>
                                    <form id="form-<?= $indicacao['id'] ?>" method="POST" action="gerenciar_indicacoes.php">
                                        <input type="hidden" name="indicacao_id" value="<?= $indicacao['id'] ?>">
                                        <input type="hidden" name="status_filtro" value="<?= $filter_status ?>">
                                        <input type="hidden" name="status" id="status-<?= $indicacao['id'] ?>" value="">
                                    </form>
                                    <select class="status-select" data-indicacao-id="<?= $indicacao['id'] ?>">
                                        <option value="">Alterar Status</option>
                                        <?php if ($indicacao['status'] == 'Ativa'): ?>
                                            <option value="Indicação Boa">Indicação Boa</option>
                                            <option value="Orçamento Enviado">Orçamento Enviado</option>
                                        <?php elseif ($indicacao['status'] == 'Orçamento Enviado'): ?>
                                            <option value="Aprovado">Aprovar Orçamento</option>
                                        <?php endif; ?>
                                        <option value="Rejeitada">Rejeitar</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div class="modal-overlay" id="modal-orcamento-enviado">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-paper-plane"></i> Orçamento Enviado</h3><button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Para cada serviço, informe o valor final que foi orçado para o paciente. O cashback será calculado com base nesse valor.</p>
                <div class="form-group">
                    <label>Serviços e Valores Finais:</label>
                    <div id="servicos-list-container"></div>
                </div>
                <div class="form-group total-display">
                    <strong>Valor Pendente Total:</strong>
                    <span class="amount">R$ <span id="total-pendente-display">0.00</span></span>
                </div>

            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary close-modal">Cancelar</button>
                <button type="button" class="btn-primary" id="confirm-orcamento-btn">Confirmar Envio</button>
            </div>
        </div>
    </div>
    <div class="modal-overlay" id="modal-confirm">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmação</h3><button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirm-text"></p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary close-modal">Cancelar</button>
                <button type="button" class="btn-primary" id="confirm-action-btn">Confirmar</button>
            </div>
        </div>
    </div>

    <script src="includes/gerenciar_indicacoes.js"></script>
</body>

</html>