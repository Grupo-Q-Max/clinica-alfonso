<?php
session_start();
require 'backend/config.php';

// Validação de segurança
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    $_SESSION['login_error_message'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit;
}

// ==============================================================================
// === INÍCIO DA LÓGICA FINAL DE CADASTRO (POST) ================================
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Coleta dos dados do formulário
    $nome_servico = trim($_POST['nome_servico']);
    $tipo_cashback = $_POST['tipo_cashback'] ?? 'valor';
    $valor_cashback = (float)str_replace(',', '.', $_POST['valor_cashback'] ?? '0');
    $tem_limite = isset($_POST['tem_limite']) ? 1 : 0;

    // 2. Lógica para tratar o valor do limite
    $limite_cashback = null; // Padrão é NULL (sem limite)
    if ($tipo_cashback === 'percentual' && $tem_limite) {
        if (!empty($_POST['limite_cashback'])) {
            $limite_cashback = (float)str_replace(',', '.', $_POST['limite_cashback']);
        } else {
            // Se a caixa de limite está marcada mas o valor está vazio, retorna um erro.
            $_SESSION['form_feedback'] = ['type' => 'error', 'message' => 'O valor máximo do cashback deve ser preenchido.'];
            header("Location: cadastro_servico.php");
            exit;
        }
    }

    // 3. Validação dos campos
    if (empty($nome_servico) || !is_numeric($valor_cashback)) {
        $_SESSION['form_feedback'] = ['type' => 'error', 'message' => 'Por favor, preencha todos os campos corretamente.'];
    } else {
        // 4. Inserção no banco de dados
        $stmt = $conexao->prepare("INSERT INTO servicos (nome, tipo_cashback, valor_cashback, tem_limite, limite_cashback) VALUES (?, ?, ?, ?, ?)");
        // s = string, d = double, i = integer
        $stmt->bind_param("ssdid", $nome_servico, $tipo_cashback, $valor_cashback, $tem_limite, $limite_cashback);

        if ($stmt->execute()) {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Serviço cadastrado com sucesso!'];
            header("Location: cadastros.php");
            exit;
        } else {
            $_SESSION['form_feedback'] = ['type' => 'error', 'message' => 'Erro ao cadastrar o serviço. Tente novamente.'];
        }
    }
    // Se houver erro de validação, recarrega a página de cadastro
    header("Location: cadastro_servico.php");
    exit;
}
// ==============================================================================
// === FIM DA LÓGICA FINAL DE CADASTRO (POST) ====================================
// ==============================================================================

// Lógica para exibir feedback de erros de validação
if (isset($_SESSION['form_feedback'])) {
    $form_feedback = $_SESSION['form_feedback'];
    unset($_SESSION['form_feedback']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Novo Serviço</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cadastro_servico.css">
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
                <h1><i class="fas fa-plus-circle"></i> Cadastrar Novo Serviço</h1>
                <p>Preencha as informações do novo serviço abaixo.</p>
            </div>

            <?php if (isset($form_feedback)): ?>
                <div class="alert <?= $form_feedback['type'] === 'success' ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($form_feedback['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="cadastro_servico.php" id="form-servico">
                <div class="form-group">
                    <label for="nome_servico">Nome do Serviço</label>
                    <input type="text" id="nome_servico" name="nome_servico" placeholder="Ex: Clareamento Dental" required>
                </div>

                <div class="form-group">
                    <label>Tipo de Cashback para o Indicador</label>
                    <div class="radio-group">
                        <label for="tipo_valor"><input type="radio" id="tipo_valor" name="tipo_cashback" value="valor" checked> Valor Fixo (R$)</label>
                        <label for="tipo_percentual"><input type="radio" id="tipo_percentual" name="tipo_cashback" value="percentual"> Percentual (%)</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="valor_cashback" id="label_valor_cashback">Valor do Cashback (R$)</label>
                    <input type="text" id="valor_cashback" name="valor_cashback" placeholder="Ex: 50,00" required>
                </div>

                <div id="limite-container" class="form-group-wrapper" style="display: none;">
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="tem_limite" name="tem_limite" value="1">
                        <label for="tem_limite">Definir valor máximo de cashback para este serviço</label>
                    </div>

                    <div class="form-group" id="limite-valor-container" style="display: none;">
                        <label for="limite_cashback">Valor Máximo (R$)</label>
                        <input type="text" id="limite_cashback" name="limite_cashback" placeholder="Ex: 150,00">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="cadastros.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Cadastrar Serviço</button>
                </div>
            </form>

            <div class="global-action-section">
                <div class="global-action-header">
                    <h3><i class="fas fa-globe"></i> Ação Global</h3>
                    <p>Use com cuidado. Esta ação aplicará a regra de limite a todos os serviços existentes.</p>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="aplicar_limite_global_check" value="1">
                    <label for="aplicar_limite_global_check">
                        Aplicar a regra de "Valor Máximo" (R$ <span id="valor-limite-global">0,00</span>) definida acima para <strong>todos os outros serviços</strong> que usam cashback percentual.
                    </label>
                </div>
                <button type="button" id="btn-aplicar-global" class="btn-primary danger" disabled>Aplicar Regra Global</button>
            </div>
        </div>
    </main>

    <script>
        // O JavaScript para a interatividade da página permanece o mesmo.
        document.addEventListener('DOMContentLoaded', () => {
            const tipoValorRadio = document.getElementById('tipo_valor');
            const tipoPercentualRadio = document.getElementById('tipo_percentual');
            const labelValorCashback = document.getElementById('label_valor_cashback');
            const inputValorCashback = document.getElementById('valor_cashback');
            const limiteContainer = document.getElementById('limite-container');
            const temLimiteCheckbox = document.getElementById('tem_limite');
            const limiteValorContainer = document.getElementById('limite-valor-container');
            const inputLimiteCashback = document.getElementById('limite_cashback');
            const aplicarLimiteGlobalCheck = document.getElementById('aplicar_limite_global_check');
            const valorLimiteGlobalSpan = document.getElementById('valor-limite-global');
            const btnAplicarGlobal = document.getElementById('btn-aplicar-global');

            function atualizarTipoCashback() {
                if (tipoPercentualRadio.checked) {
                    labelValorCashback.textContent = 'Percentual do Cashback (%)';
                    inputValorCashback.placeholder = 'Ex: 10';
                    limiteContainer.style.display = 'block';
                } else {
                    labelValorCashback.textContent = 'Valor do Cashback (R$)';
                    inputValorCashback.placeholder = 'Ex: 50,00';
                    limiteContainer.style.display = 'none';
                    temLimiteCheckbox.checked = false;
                    limiteValorContainer.style.display = 'none';
                }
            }

            function atualizarVisibilidadeLimite() {
                if (temLimiteCheckbox.checked) {
                    limiteValorContainer.style.display = 'block';
                    inputLimiteCashback.required = true;
                } else {
                    limiteValorContainer.style.display = 'none';
                    inputLimiteCashback.required = false;
                    inputLimiteCashback.value = '';
                }
            }

            function atualizarAcaoGlobal() {
                const limiteDefinido = temLimiteCheckbox.checked && parseFloat(inputLimiteCashback.value.replace(',', '.')) > 0;

                if (limiteDefinido) {
                    valorLimiteGlobalSpan.textContent = inputLimiteCashback.value;
                    btnAplicarGlobal.disabled = !aplicarLimiteGlobalCheck.checked;
                } else {
                    valorLimiteGlobalSpan.textContent = '0,00';
                    btnAplicarGlobal.disabled = true;
                }
            }

            tipoValorRadio.addEventListener('change', atualizarTipoCashback);
            tipoPercentualRadio.addEventListener('change', atualizarTipoCashback);
            temLimiteCheckbox.addEventListener('change', atualizarVisibilidadeLimite);

            temLimiteCheckbox.addEventListener('change', atualizarAcaoGlobal);
            inputLimiteCashback.addEventListener('input', atualizarAcaoGlobal);
            aplicarLimiteGlobalCheck.addEventListener('change', atualizarAcaoGlobal);

            btnAplicarGlobal.addEventListener('click', () => {
                const valorLimite = inputLimiteCashback.value;
                if (confirm(`ATENÇÃO!\n\nVocê tem certeza que deseja aplicar o limite de R$ ${valorLimite} para TODOS os outros serviços com cashback em percentual?\n\nEsta ação não pode ser desfeita.`)) {
                    alert('A lógica para aplicar a regra global será implementada no próximo passo.');
                }
            });

            atualizarTipoCashback();
            atualizarVisibilidadeLimite();
            atualizarAcaoGlobal();
        });
    </script>
</body>

</html>