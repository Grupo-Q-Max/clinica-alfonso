<?php
session_start();
require 'backend/config.php';

// Verifica se o usuário é um administrador logado
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    $_SESSION['login_error_message'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit;
}

// Valida o ID do serviço passado pela URL
$servico_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$servico_id) {
    header("Location: cadastros.php");
    exit;
}

// Processa o formulário quando enviado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CORRIGIDO: Coleta todos os novos dados do formulário
    $nome_servico = trim($_POST['nome_servico']);
    $tipo_cashback = $_POST['tipo_cashback'] ?? 'valor';
    $valor_cashback = (float)str_replace(',', '.', $_POST['valor_cashback'] ?? '0');
    $tem_limite = isset($_POST['tem_limite']) ? 1 : 0;

    // CORRIGIDO: Lógica para tratar o limite de cashback
    $limite_cashback = null;
    if ($tipo_cashback === 'percentual' && $tem_limite) {
        // Só define um valor se o campo não estiver vazio
        if (!empty($_POST['limite_cashback'])) {
            $limite_cashback = (float)str_replace(',', '.', $_POST['limite_cashback']);
        } else {
            // Se a caixa está marcada mas o valor está vazio, retorna um erro
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'O valor máximo do cashback deve ser preenchido.'];
            header("Location: editar_servico.php?id=" . $servico_id);
            exit;
        }
    }

    if (empty($nome_servico) || !is_numeric($valor_cashback)) {
        $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Por favor, preencha todos os campos corretamente.'];
    } else {
        // CORRIGIDO: Query de UPDATE com os novos campos
        $stmt = $conexao->prepare("UPDATE servicos SET nome = ?, tipo_cashback = ?, valor_cashback = ?, tem_limite = ?, limite_cashback = ? WHERE id = ?");
        // CORRIGIDO: bind_param atualizado com os tipos corretos (s=string, d=double, i=integer)
        $stmt->bind_param("ssdiid", $nome_servico, $tipo_cashback, $valor_cashback, $tem_limite, $limite_cashback, $servico_id);

        if ($stmt->execute()) {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Serviço atualizado com sucesso!'];
        } else {
            $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao atualizar o serviço.'];
        }
        header("Location: cadastros.php");
        exit;
    }
}

// CORRIGIDO: Busca todos os novos campos do serviço para preencher o formulário
$stmt = $conexao->prepare("SELECT nome, tipo_cashback, valor_cashback, tem_limite, limite_cashback FROM servicos WHERE id = ?");
$stmt->bind_param("i", $servico_id);
$stmt->execute();
$result = $stmt->get_result();
$servico = $result->fetch_assoc();

if (!$servico) {
    $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Serviço não encontrado.'];
    header("Location: cadastros.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Serviço</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="editar_servico.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="admin-body">
    <?php include 'includes/header_admin.php'; ?>

    <main class="main-content">
        <div class="form-card">
            <div class="form-header">
                <h1><i class="fas fa-edit"></i> Editar Serviço</h1>
                <p>Altere as informações do serviço abaixo.</p>
            </div>

            <?php if (isset($_SESSION['feedback'])): ?>
                <div class="alert <?= $_SESSION['feedback']['type'] === 'success' ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($_SESSION['feedback']['message']) ?>
                    <?php unset($_SESSION['feedback']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="editar_servico.php?id=<?= $servico_id; ?>">
                <div class="form-group">
                    <label for="nome_servico">Nome do Serviço</label>
                    <input type="text" id="nome_servico" name="nome_servico" value="<?= htmlspecialchars($servico['nome']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Tipo de Cashback para o Indicador</label>
                    <div class="radio-group">
                        <label for="tipo_valor"><input type="radio" id="tipo_valor" name="tipo_cashback" value="valor" <?= ($servico['tipo_cashback'] == 'valor') ? 'checked' : '' ?>> Valor Fixo (R$)</label>
                        <label for="tipo_percentual"><input type="radio" id="tipo_percentual" name="tipo_cashback" value="percentual" <?= ($servico['tipo_cashback'] == 'percentual') ? 'checked' : '' ?>> Percentual (%)</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="valor_cashback" id="label_valor_cashback">Valor do Cashback (R$)</label>
                    <input type="text" id="valor_cashback" name="valor_cashback" value="<?= htmlspecialchars(number_format($servico['valor_cashback'], 2, ',', '.')) ?>" required>
                </div>

                <div id="limite-container" class="form-group-wrapper">
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="tem_limite" name="tem_limite" value="1" <?= $servico['tem_limite'] ? 'checked' : '' ?>>
                        <label for="tem_limite">Definir valor máximo de cashback para este serviço</label>
                    </div>

                    <div class="form-group" id="limite-valor-container">
                        <label for="limite_cashback">Valor Máximo (R$)</label>
                        <input type="text" id="limite_cashback" name="limite_cashback" value="<?= $servico['limite_cashback'] ? htmlspecialchars(number_format($servico['limite_cashback'], 2, ',', '.')) : '' ?>" placeholder="Ex: 150,00">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="cadastros.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tipoValorRadio = document.getElementById('tipo_valor');
            const tipoPercentualRadio = document.getElementById('tipo_percentual');
            const labelValorCashback = document.getElementById('label_valor_cashback');
            const inputValorCashback = document.getElementById('valor_cashback');
            const limiteContainer = document.getElementById('limite-container');
            const temLimiteCheckbox = document.getElementById('tem_limite');
            const limiteValorContainer = document.getElementById('limite-valor-container');
            const inputLimiteCashback = document.getElementById('limite_cashback');

            function atualizarTipoCashback() {
                if (tipoPercentualRadio.checked) {
                    labelValorCashback.textContent = 'Percentual do Cashback (%)';
                    inputValorCashback.placeholder = 'Ex: 10';
                    limiteContainer.style.display = 'block';
                } else {
                    labelValorCashback.textContent = 'Valor do Cashback (R$)';
                    inputValorCashback.placeholder = 'Ex: 50,00';
                    limiteContainer.style.display = 'none';
                }
            }

            function atualizarVisibilidadeLimite() {
                if (temLimiteCheckbox.checked) {
                    limiteValorContainer.style.display = 'block';
                    inputLimiteCashback.required = true;
                } else {
                    limiteValorContainer.style.display = 'none';
                    inputLimiteCashback.required = false;
                }
            }

            tipoValorRadio.addEventListener('change', atualizarTipoCashback);
            tipoPercentualRadio.addEventListener('change', atualizarTipoCashback);
            temLimiteCheckbox.addEventListener('change', atualizarVisibilidadeLimite);

            // Inicia o estado correto do formulário ao carregar a página
            atualizarTipoCashback();
            atualizarVisibilidadeLimite();
        });
    </script>
</body>

</html>