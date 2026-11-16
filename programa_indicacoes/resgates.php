<?php
session_start();
require 'backend/config.php';

// Validação de segurança e permissão de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    $_SESSION['login_error_message'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit;
}

// Lógica de processamento do formulário para aprovar/rejeitar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    $solicitacao_id = (int)$_POST['solicitacao_id'];
    $acao = $_POST['acao'];

    if (($acao === 'aprovar' || $acao === 'rejeitar') && $solicitacao_id > 0) {
        $novo_status = $acao === 'aprovar' ? 'aprovado' : 'rejeitado';

        $update = $conexao->prepare("UPDATE solicitacao_resgate SET status_resgate = ? WHERE id = ?");
        $update->bind_param("si", $novo_status, $solicitacao_id);

        if ($update->execute()) {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Solicitação ' . $novo_status . ' com sucesso!'];
        } else {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao processar a solicitação.'];
        }

        // Redireciona para a mesma página de filtro de onde a ação foi tomada
        $status_redirect = $_POST['status_filtro'] ?? 'pendente';
        header("Location: resgates.php?status=" . $status_redirect);
        exit;
    }
}

// Lógica para buscar as solicitações com base no filtro
$status_filtro = $_GET['status'] ?? 'pendente';
$stmt = $conexao->prepare("
    SELECT sr.*, u.nome, u.sobrenome 
    FROM solicitacao_resgate sr
    JOIN usuarios u ON sr.usuario_id = u.id
    WHERE sr.status_resgate = ?
    ORDER BY sr.data_solicitacao DESC
");
$stmt->bind_param("s", $status_filtro);
$stmt->execute();
$result = $stmt->get_result();
$solicitacoes = $result->fetch_all(MYSQLI_ASSOC);

// Pega a mensagem de feedback da sessão, se houver
if (isset($_SESSION['feedback'])) {
    $feedback = $_SESSION['feedback'];
    unset($_SESSION['feedback']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Resgates</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="resgates.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="admin-body">
    <?php include 'includes/header_admin.php'; ?>

    <main class="main-content">
        <section class="page-header">
            <h1>Solicitações de Resgate</h1>
            <p>Gerencie as solicitações de resgate de saldo dos usuários.</p>
        </section>

        <?php if (isset($feedback)): ?>
            <div class="alert <?= $feedback['type'] === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($feedback['message']) ?>
            </div>
        <?php endif; ?>

        <section class="actions-bar">
            <div class="filter-buttons">
                <a href="?status=pendente" class="filter-button <?= $status_filtro == 'pendente' ? 'active' : '' ?>">Pendentes</a>
                <a href="?status=aprovado" class="filter-button <?= $status_filtro == 'aprovado' ? 'active' : '' ?>">Aprovados</a>
                <a href="?status=rejeitado" class="filter-button <?= $status_filtro == 'rejeitado' ? 'active' : '' ?>">Rejeitados</a>
            </div>
        </section>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Valor</th>
                        <th>Método</th>
                        <th>Dados de Pagamento</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($solicitacoes)): ?>
                        <tr>
                            <td colspan="7" class="no-results">Nenhuma solicitação encontrada para este status.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <tr>
                                <td><?= htmlspecialchars($solicitacao['id']) ?></td>
                                <td><?= htmlspecialchars($solicitacao['nome'] . ' ' . $solicitacao['sobrenome']) ?></td>
                                <td>R$ <?= number_format($solicitacao['valor_resgate'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars(ucfirst($solicitacao['metodo_pagamento'])) ?></td>
                                <td class="payment-details">
                                    <?= !empty($solicitacao['dados_pagamento']) ? htmlspecialchars($solicitacao['dados_pagamento']) : 'N/A' ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $solicitacao['status_resgate'] ?>">
                                        <?= ucfirst($solicitacao['status_resgate']) ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <?php if ($solicitacao['status_resgate'] == 'pendente'): ?>
                                        <form method="POST" action="resgates.php" id="form-<?= $solicitacao['id'] ?>" style="display: flex; gap: 0.5rem;">
                                            <input type="hidden" name="solicitacao_id" value="<?= $solicitacao['id'] ?>">
                                            <input type="hidden" name="status_filtro" value="<?= $status_filtro ?>">
                                            <input type="hidden" name="acao" id="acao-<?= $solicitacao['id'] ?>" value="">

                                            <button type="button" class="btn-primary success action-btn" data-form-id="<?= $solicitacao['id'] ?>" data-action="aprovar" data-question="Tem certeza que deseja APROVAR esta solicitação?">
                                                <i class="fas fa-check"></i> Aprovar
                                            </button>
                                            <button type="button" class="btn-primary danger action-btn" data-form-id="<?= $solicitacao['id'] ?>" data-action="rejeitar" data-question="Tem certeza que deseja REJEITAR esta solicitação?">
                                                <i class="fas fa-times"></i> Rejeitar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span>Processado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- MODAL DE CONFIRMAÇÃO DE AÇÃO -->
    <div class="modal-overlay" id="confirmActionModal" style="display: none;">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmação</h3>
                <button class="close-modal" id="closeConfirmModal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmActionText">Você tem certeza?</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancelActionBtn">Cancelar</button>
                <button type="button" class="btn-primary" id="confirmActionBtn">Confirmar</button>
            </div>
        </div>
    </div>

    <script src="resgates.js"></script>
</body>

</html>