<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Função para redirecionar com feedback
function redirecionarComFeedback($type, $message)
{
    $_SESSION['feedback'] = ['type' => $type, 'message' => $message];
    header("Location: resgate.php");
    exit;
}

// Lógica de processamento do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conexao->prepare("SELECT saldo, primeiro_resgate, cpf FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();

    $valor_resgate = floatval($_POST['valor_resgate']);
    $metodo_pagamento = $_POST['metodo_pagamento'] ?? 'selecione';

    // Validações
    if ($user_data['primeiro_resgate'] && $valor_resgate < 100) {
        redirecionarComFeedback('error', 'Para o primeiro resgate, o valor mínimo é R$ 100,00.');
    }
    if (!$user_data['primeiro_resgate'] && $valor_resgate < 10) {
        redirecionarComFeedback('error', 'O valor mínimo para resgate é R$ 10,00.');
    }
    if ($valor_resgate > $user_data['saldo']) {
        redirecionarComFeedback('error', 'Valor de resgate superior ao saldo disponível.');
    }
    if ($metodo_pagamento === 'selecione') {
        redirecionarComFeedback('error', 'Por favor, selecione um método de pagamento.');
    }

    $dados_pagamento = "";
    if ($metodo_pagamento === 'pix') {
        $dados_pagamento = "Chave PIX (CPF): " . $user_data['cpf'];
    } elseif ($metodo_pagamento === 'conta_bancaria') {
        $agencia = trim($_POST['agencia'] ?? '');
        $numero_conta = trim($_POST['numero_conta'] ?? '');
        $tipo_conta = $_POST['tipo_conta'] ?? '';
        if (empty($agencia) || empty($numero_conta) || empty($tipo_conta)) {
            redirecionarComFeedback('error', 'Para conta bancária, todos os campos são obrigatórios.');
        }
        $dados_pagamento = "Agência: $agencia, Conta: $numero_conta, Tipo: $tipo_conta";
    }

    // Processamento da transação
    $conexao->begin_transaction();
    try {
        $novo_saldo = $user_data['saldo'] - $valor_resgate;
        $stmt_saldo = $conexao->prepare("UPDATE usuarios SET saldo = ? WHERE id = ?");
        $stmt_saldo->bind_param("di", $novo_saldo, $usuario_id);
        $stmt_saldo->execute();

        $stmt_solicitacao = $conexao->prepare("INSERT INTO solicitacao_resgate (usuario_id, valor_resgate, metodo_pagamento, dados_pagamento) VALUES (?, ?, ?, ?)");
        $stmt_solicitacao->bind_param("idss", $usuario_id, $valor_resgate, $metodo_pagamento, $dados_pagamento);
        $stmt_solicitacao->execute();

        if ($user_data['primeiro_resgate']) {
            $stmt_flag = $conexao->prepare("UPDATE usuarios SET primeiro_resgate = 0 WHERE id = ?");
            $stmt_flag->bind_param("i", $usuario_id);
            $stmt_flag->execute();
        }

        $conexao->commit();
        $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Sua solicitação de resgate foi enviada com sucesso! O pagamento será processado em breve.'];
        header('Location: resgate.php');
        exit;
    } catch (Exception $e) {
        $conexao->rollback();
        redirecionarComFeedback('error', 'Ocorreu um erro ao processar sua solicitação. Tente novamente.');
    }
}

// Busca os dados do usuário para exibir na página
$stmt = $conexao->prepare("SELECT nome, saldo, cpf, primeiro_resgate FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Busca o histórico de solicitações de resgate do usuário
$stmt_historico = $conexao->prepare("
    SELECT data_solicitacao, valor_resgate, metodo_pagamento, status_resgate
    FROM solicitacao_resgate
    WHERE usuario_id = ?
    ORDER BY data_solicitacao DESC
");
$stmt_historico->bind_param("i", $usuario_id);
$stmt_historico->execute();
$historico_resgates = $stmt_historico->get_result()->fetch_all(MYSQLI_ASSOC);

if (isset($_SESSION['feedback'])) {
    $feedback = $_SESSION['feedback'];
    unset($_SESSION['feedback']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Solicitar Resgate de Saldo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="resgate.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="dashboard-body">
    <?php include 'includes/header_logado.php'; ?>

    <main class="main-content">
        <section class="page-header">
            <h1>Solicitar Resgate</h1>
            <p>Transfira seu saldo disponível para sua conta.</p>
        </section>

        <?php if (isset($feedback) && $feedback['type'] === 'error'): ?>
            <div class="alert error">
                <?= htmlspecialchars($feedback['message']) ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <!-- Conteúdo do formulário permanece o mesmo -->
            <div class="saldo-box">
                <span>Saldo Disponível</span>
                <strong>R$ <?= number_format($user['saldo'], 2, ',', '.') ?></strong>
            </div>
            <?php $minimo_resgate = $user['primeiro_resgate'] ? 100 : 10; ?>
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <p>
                    <?php if ($user['primeiro_resgate']): ?>
                        <strong>ATENÇÃO:</strong> Este é seu primeiro resgate. O valor mínimo é <strong>R$ 100,00</strong>.
                    <?php else: ?>
                        O valor mínimo para resgate é <strong>R$ 10,00</strong>.
                    <?php endif; ?>
                </p>
            </div>
            <form method="POST" action="resgate.php">
                <div class="form-group">
                    <label for="valor_resgate">Valor do Resgate (R$)</label>
                    <input type="number" id="valor_resgate" name="valor_resgate" min="<?= $minimo_resgate ?>" max="<?= $user['saldo'] ?>" step="0.01" placeholder="0,00" required>
                </div>
                <div class="form-group">
                    <label for="metodo_pagamento">Método de Pagamento</label>
                    <select id="metodo_pagamento" name="metodo_pagamento" required>
                        <option value="selecione" disabled selected>Selecione um método</option>
                        <option value="pix">PIX (via CPF)</option>
                        <option value="conta_bancaria">Conta Bancária</option>
                    </select>
                </div>
                <div id="pix-fields" class="payment-fields hidden">
                    <label>Chave PIX (CPF)</label>
                    <input type="text" value="<?= htmlspecialchars($user['cpf']) ?>" readonly>
                </div>
                <div id="bancaria-fields" class="payment-fields hidden">
                    <div class="form-group">
                        <label for="agencia">Agência</label>
                        <input type="text" id="agencia" name="agencia" placeholder="Ex: 0001">
                    </div>
                    <div class="form-group">
                        <label for="numero_conta">Número da Conta com dígito</label>
                        <input type="text" id="numero_conta" name="numero_conta" placeholder="Ex: 12345-6">
                    </div>
                    <div class="form-group">
                        <label for="tipo_conta">Tipo de Conta</label>
                        <select id="tipo_conta" name="tipo_conta">
                            <option value="corrente">Corrente</option>
                            <option value="poupanca">Poupança</option>
                        </select>
                    </div>
                </div>
                <button type="submit" id="submit-btn" class="btn-primary" <?= ($user['saldo'] < $minimo_resgate) ? 'disabled' : '' ?>>
                    <i class="fas fa-paper-plane"></i> Solicitar Resgate
                </button>
            </form>
        </div>

        <!-- SEÇÃO DE HISTÓRICO DE RESGATES -->
        <section class="history-section">
            <h2><i class="fas fa-history"></i> Histórico de Resgates</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Método</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historico_resgates)): ?>
                            <tr>
                                <td colspan="4" class="no-results">Você ainda não fez nenhuma solicitação de resgate.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historico_resgates as $resgate): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($resgate['data_solicitacao'])) ?></td>
                                    <td>R$ <?= number_format($resgate['valor_resgate'], 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $resgate['metodo_pagamento']))) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $resgate['status_resgate'] ?>">
                                            <?= ucfirst($resgate['status_resgate']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- MODAL DE SUCESSO -->
    <div class="modal-overlay" id="successModal">
        <div class="modal-content" style="max-width: 450px; text-align: center;">
            <div class="modal-header" style="justify-content: center; border-bottom: none; padding-bottom: 0;">
                <h3 class="modal-title" style="text-align:center;">
                    <i class="fas fa-check-circle" style="color: var(--success-color); font-size: 3rem; display:block; margin-bottom: 1rem;"></i>
                    Sucesso!
                </h3>
            </div>
            <div class="modal-body" style="padding-top: 0;">
                <p>Sua solicitação de resgate foi enviada! O pagamento será processado em breve.</p>
            </div>
            <div class="modal-actions" style="justify-content: center;">
                <button type="button" class="btn-primary" id="closeSuccessModalBtn">Ok, entendi</button>
            </div>
        </div>
    </div>

    <script src="resgate.js"></script>

    <?php if (isset($feedback) && $feedback['type'] === 'success'): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                openModal('successModal');
            });
        </script>
    <?php endif; ?>
</body>

</html>