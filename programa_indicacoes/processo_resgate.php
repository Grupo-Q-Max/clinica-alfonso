<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "message" => "Usuário não autenticado."]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $conexao->prepare("SELECT nome, sobrenome, cpf, telefone, saldo, primeiro_resgate FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    echo json_encode(["status" => "error", "message" => "Usuário não encontrado. Faça login novamente."]);
    exit;
}

$userSaldo = $user['saldo'];
$primeiroResgate = $user['primeiro_resgate'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valor_resgate = floatval($_POST['valor_resgate']);
    $metodo_pagamento = $_POST['metodo_pagamento'];

    if ($primeiroResgate && $valor_resgate < 100) {
        echo json_encode(["status" => "error", "message" => "Para o primeiro resgate, o valor mínimo é R$ 100,00."]);
        exit;
    } elseif (!$primeiroResgate && $valor_resgate < 10) {
        echo json_encode(["status" => "error", "message" => "O valor mínimo para resgate é R$ 10,00."]);
        exit;
    }

    if ($metodo_pagamento == 'selecione' || empty($metodo_pagamento)) {
        echo json_encode(["status" => "error", "message" => "Você deve selecionar um método de pagamento válido."]);
        exit;
    }

    if ($valor_resgate <= 0 || $valor_resgate > $userSaldo) {
        echo json_encode(["status" => "error", "message" => "Valor de resgate inválido ou saldo insuficiente."]);
        exit;
    }

    $dados_pagamento = "";

    if ($metodo_pagamento == 'conta_bancaria') {
        $agencia = $_POST['agencia'] ?? '';
        $numero_conta = $_POST['numero_conta'] ?? '';
        $tipo_conta = $_POST['tipo_conta'] ?? '';

        if (empty($agencia) || empty($numero_conta) || empty($tipo_conta)) {
            echo json_encode(["status" => "error", "message" => "Todos os campos bancários são obrigatórios."]);
            exit;
        }

        $dados_pagamento = "Agência: $agencia, Conta: $numero_conta, Tipo: $tipo_conta";
    } elseif ($metodo_pagamento == 'pix') {
        $dados_pagamento = "Chave PIX: " . $user['cpf'];
    } else {
        echo json_encode(["status" => "error", "message" => "Método de pagamento inválido."]);
        exit;
    }

    $conexao->begin_transaction();

    try {
        $stmt = $conexao->prepare("INSERT INTO solicitacao_resgate (usuario_id, valor_resgate, metodo_pagamento, dados_pagamento) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $usuario_id, $valor_resgate, $metodo_pagamento, $dados_pagamento);
        $stmt->execute();

        $newSaldo = $userSaldo - $valor_resgate;
        $updateSaldo = $conexao->prepare("UPDATE usuarios SET saldo = ? WHERE id = ?");
        $updateSaldo->bind_param("di", $newSaldo, $usuario_id);
        $updateSaldo->execute();

        if ($primeiroResgate) {
            $updateFlag = $conexao->prepare("UPDATE usuarios SET primeiro_resgate = FALSE WHERE id = ?");
            $updateFlag->bind_param("i", $usuario_id);
            $updateFlag->execute();
        }

        $conexao->commit();

        header('Location: solicitacoes_resgate.php');
        exit;
    } catch (Exception $e) {
        $conexao->rollback();
        echo json_encode(["status" => "error", "message" => "Erro ao solicitar resgate: " . $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resgate de Saldo</title>
    <link rel="stylesheet" href="processo_resgate.css">
    <link rel="icon" href="imagens/qxbackfavicon.webp" class="favicon">

</head>

<body>
    <?php include 'includes/header_logado.php'; ?>
    <div class="main-content">
        <div class="container">
            <h2>Resgate seu dinheiro</h2>
            <div class="saldo-box">Saldo disponível: <strong>R$ <?= number_format($userSaldo, 2, ',', '.') ?></strong></div>

            <?php if ($primeiroResgate): ?>
                <div class="info-box">
                    <strong>ATENÇÃO:</strong> Este é seu primeiro resgate. O valor mínimo para o primeiro resgate é <strong>R$ 100,00</strong>.
                </div>
            <?php else: ?>
                <div class="info-box">
                    O valor mínimo para resgate é <strong>R$ 10,00</strong>.
                </div>
            <?php endif; ?>

            <form method="POST" action="processo_resgate.php" onsubmit="return validarFormulario()">
                <div class="form-group">
                    <label for="valor-resgate">Valor do Resgate:</label>
                    <input type="number" id="valor-resgate" name="valor_resgate"
                        min="<?= $primeiroResgate ? '100' : '10' ?>"
                        step="0.01"
                        placeholder="R$ <?= $primeiroResgate ? '100,00' : '10,00' ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="metodo-pagamento">Método de Pagamento:</label>
                    <select id="metodo-pagamento" name="metodo_pagamento" onchange="showPaymentFields()" required>
                        <option value="selecione" disabled selected>Selecione seu método de pagamento</option>
                        <option value="conta_bancaria">Conta Bancária</option>
                        <option value="pix">PIX</option>
                    </select>
                </div>
                <div id="pix-fields" class="form-group hidden">
                    <label>Chave PIX (CPF):</label>
                    <input type="text" value="<?= htmlspecialchars($user['cpf']) ?>" readonly>
                    <input type="hidden" name="dados_pagamento" value="<?= htmlspecialchars($user['cpf']) ?>">
                </div>
                <div id="bancaria-fields" class="hidden">
                    <div class="form-group">
                        <label for="agencia">Agência:</label>
                        <input type="text" id="agencia" name="agencia" placeholder="Número da Agência">
                    </div>
                    <div class="form-group">
                        <label for="numero-conta">Número da Conta:</label>
                        <input type="text" id="numero-conta" name="numero_conta" placeholder="Número da Conta">
                    </div>
                    <div class="form-group">
                        <label for="tipo-conta">Tipo de Conta:</label>
                        <select id="tipo-conta" name="tipo_conta">
                            <option value="corrente">Corrente</option>
                            <option value="poupanca">Poupança</option>
                        </select>
                    </div>
                </div>
                <br>
                <button type="submit" id="submit-btn" <?= ($userSaldo < ($primeiroResgate ? 100 : 10)) ? 'disabled' : '' ?>>
                    Solicitar Resgate
                </button>
            </form>
        </div>
    </div>
    <div id="popup-msg" class="popup"></div>
    <?php include 'includes/footer.php'; ?>
    <script>
        function showPaymentFields() {
            let metodo = document.getElementById('metodo-pagamento').value;
            document.getElementById('pix-fields').classList.toggle('hidden', metodo !== 'pix');
            document.getElementById('bancaria-fields').classList.toggle('hidden', metodo !== 'conta_bancaria');
        }

        function exibirPopup(mensagem, tipo) {
            let popup = document.getElementById('popup-msg');
            popup.innerHTML = mensagem;
            popup.className = 'popup ' + tipo;
            popup.style.display = 'block';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 3000);
        }

        function validarFormulario() {
            let valor = parseFloat(document.getElementById('valor-resgate').value);
            let saldo = <?= $userSaldo ?>;
            let metodo = document.getElementById('metodo-pagamento').value;
            let primeiroResgate = <?= $primeiroResgate ? 'true' : 'false' ?>;

            if (primeiroResgate && valor < 100) {
                exibirPopup("Para o primeiro resgate, o valor mínimo é R$ 100,00.", "error");
                return false;
            } else if (!primeiroResgate && valor < 10) {
                exibirPopup("O valor mínimo para resgate é R$ 10,00.", "error");
                return false;
            }

            if (metodo == 'selecione' || metodo == '') {
                exibirPopup("Por favor, selecione um método de pagamento válido.", "error");
                return false;
            }

            if (valor <= 0) {
                exibirPopup("Valor de resgate inválido!", "error");
                return false;
            }
            if (valor > saldo) {
                exibirPopup("Saldo insuficiente!", "error");
                return false;
            }

            if (metodo == 'conta_bancaria') {
                let agencia = document.getElementById('agencia').value;
                let numeroConta = document.getElementById('numero-conta').value;
                let tipoConta = document.getElementById('tipo-conta').value;

                if (agencia === '' || numeroConta === '' || tipoConta === '') {
                    exibirPopup("Todos os campos bancários são obrigatórios.", "error");
                    return false;
                }
            }

            return true;
        }

        function atualizarEstadoBotao() {
            let saldo = <?= $userSaldo ?>;
            let primeiroResgate = <?= $primeiroResgate ? 'true' : 'false' ?>;
            let minValor = primeiroResgate ? 100 : 10;
            let btn = document.getElementById('submit-btn');

            if (saldo < minValor) {
                btn.disabled = true;
                btn.title = `Saldo insuficiente para resgate mínimo de R$ ${minValor},00`;
            } else {
                btn.disabled = false;
                btn.title = '';
            }
        }

        window.onload = function() {
            showPaymentFields();
            atualizarEstadoBotao();

            document.getElementById('valor-resgate').addEventListener('input', function() {
                let primeiroResgate = <?= $primeiroResgate ? 'true' : 'false' ?>;
                let minValor = primeiroResgate ? 100 : 10;
                let valor = parseFloat(this.value) || 0;

                if (valor < minValor) {
                    this.setCustomValidity(`O valor mínimo é R$ ${minValor},00`);
                } else {
                    this.setCustomValidity('');
                }
            });
        };
    </script>
</body>

</html>