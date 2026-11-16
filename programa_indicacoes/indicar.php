<?php
session_start();
require 'backend/config.php';

$indicador_id = null;
$nome_completo_indicador = '';
$modo_convidado = false;
$endereco_completo = true;

if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    $modo_convidado = true;
    $codigo_indicador = trim($_GET['ref']);

    $stmt = $conexao->prepare("SELECT id, nome, sobrenome FROM usuarios WHERE codigo_indicador = ? AND ativo = 1");
    $stmt->bind_param("s", $codigo_indicador);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $indicador = $result->fetch_assoc();
        $indicador_id = $indicador['id'];
        $nome_completo_indicador = trim($indicador['nome'] . ' ' . $indicador['sobrenome']);
        $_SESSION['ref_indicador_id'] = $indicador['id'];
    } else {
        header("Location: login.php?erro=ref_invalido");
        exit;
    }
    $stmt->close();
} elseif (isset($_SESSION['user_id'])) {
    if ($_SESSION['tipo_conta'] === 'admin') {
        header("Location: admin.php");
        exit;
    } elseif ($_SESSION['tipo_conta'] !== 'indicador') {
        header("Location: login.php");
        exit;
    }

    $indicador_id = $_SESSION['user_id'];

    $stmt_endereco = $conexao->prepare("SELECT cep, endereco FROM usuarios WHERE id = ?");
    $stmt_endereco->bind_param("i", $indicador_id);
    $stmt_endereco->execute();
    $usuario = $stmt_endereco->get_result()->fetch_assoc();
    $stmt_endereco->close();

    if (empty($usuario['cep']) || empty($usuario['endereco'])) {
        $endereco_completo = false;
    }
} else {
    header("Location: login.php");
    exit;
}

$query_servicos = "SELECT nome FROM servicos ORDER BY nome ASC";
$resultado_servicos = $conexao->query($query_servicos);
$lista_de_servicos = [];
if ($resultado_servicos->num_rows > 0) {
    while ($servico = $resultado_servicos->fetch_assoc()) {
        $lista_de_servicos[] = $servico['nome'];
    }
}

$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
$feedback = $_SESSION['form_feedback'] ?? null;
unset($_SESSION['form_feedback']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fazer uma Indicação | Alfonso Rewards</title>
    <link rel="stylesheet" href="indicar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="<?= $modo_convidado ? 'guest-mode' : '' ?>">

    <?php if ($modo_convidado): ?>
        <header class="guest-welcome-banner">
            <div class="guest-content">
                <img src="imagens/logo_sem_fundo1.png" alt="Logo Clínica Alfonso" class="guest-logo">
                <h2>Bem-vindo(a) ao Alfonso Rewards!</h2>
                <p>Você está indicando a convite de <strong><?php echo htmlspecialchars($nome_completo_indicador); ?></strong>.</p>
            </div>
        </header>
    <?php else: ?>
        <?php include __DIR__ . '/includes/header_logado.php'; ?>
    <?php endif; ?>

    <main class="main-content">
        <section class="section-container">
            <div class="form-wrapper">
                <div class="form-header">
                    <i class="form-icon fas fa-share-alt"></i>
                    <h1 class="form-title">Realizar uma Indicação</h1>
                    <p class="form-subtitle">Preencha os dados do paciente para que nossa equipe possa apresentar a excelência da Clínica Alfonso.</p>
                </div>

                <form id="formIndicacao" action="processos/processar_indicacao.php" method="POST" class="form-body">
                    <?php if ($modo_convidado): ?>
                        <input type="hidden" name="ref_code" value="<?php echo htmlspecialchars($_GET['ref']); ?>">
                    <?php endif; ?>

                    <h2 class="form-section-title">Dados do Paciente Indicado</h2>
                    <div class="form-grid">
                        <div class="input-group full-width">
                            <label for="nome_paciente">Nome Completo do Paciente *</label>
                            <input type="text" id="nome_paciente" name="nome_paciente" value="<?= htmlspecialchars($form_data['nome_paciente'] ?? ''); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="cpf">CPF</label>
                            <input type="text" id="cpf" name="cpf" value="<?= htmlspecialchars($form_data['cpf'] ?? ''); ?>"  placeholder="000.000.000-00" maxlength="14">
                        </div>
                        <div class="input-group">
                            <label for="telefone">Telefone *</label>
                            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($form_data['telefone'] ?? ''); ?>" required placeholder="(00) 00000-0000" maxlength="15">
                        </div>
                        <div class="input-group full-width">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($form_data['email'] ?? ''); ?>" placeholder="email@exemplo.com" >
                        </div>
                    </div>

                    <h2 class="form-section-title">Tratamentos de Interesse</h2>
                    <div class="servicos-container" id="servicos-container"></div>
                    <button type="button" class="btn-secondary" id="btnAdicionarServico">+ Adicionar outro tratamento</button>

                    <div class="form-footer">
                        <button type="submit" class="btn-primary">Enviar Indicação</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <div class="popup-overlay" id="feedbackPopup">
        <div class="popup-content">
            <div class="popup-icon"><i id="popup-icon" class="fas"></i></div>
            <h2 id="popup-title"></h2>
            <p id="popup-message"></p>
            <div class="popup-actions">
                <button class="btn-primary" id="ok-btn" style="display: none;">Tentar Novamente</button>
                <a href="indicacoes.php" class="btn-secondary" id="ver-indicacoes-btn" style="display: none;">Ver Minhas Indicações</a>
                <button class="btn-primary" id="nova-indicacao-btn" style="display: none;">Fazer Outra Indicação</button>
            </div>
        </div>
    </div>

    <div class="popup-overlay" id="addressPopup">
        <div class="popup-content">
            <div class="popup-icon warning"><i class="fas fa-map-marker-alt"></i></div>
            <h2>Endereço Incompleto</h2>
            <p>Para realizar indicações, é necessário que seu endereço esteja completo. Isso garante a segurança e o processamento correto de suas recompensas.</p>
            <a href="cadastro_endereco.php" class="btn-primary">Completar Meu Endereço</a>
        </div>
    </div>

    <?php if (!$modo_convidado): ?>
        <?php include '../footer.php'; ?>
    <?php endif; ?>

    <script>
        const telefoneInput = document.getElementById('telefone');
        const cpfInput = document.getElementById('cpf'); // Seleciona o campo CPF

        // Máscara dinâmica para Telefone (Fixo ou Celular)
        telefoneInput.addEventListener('input', (event) => {
            let valor = event.target.value.replace(/\D/g, '').slice(0, 11);
            if (valor.length === 0) {
                event.target.value = '';
                return;
            }
            if (valor.length <= 2) {
                event.target.value = `(${valor}`;
            } else if (valor.length <= 6) {
                event.target.value = `(${valor.substring(0, 2)}) ${valor.substring(2)}`;
            } else if (valor.length <= 10) {
                event.target.value = `(${valor.substring(0, 2)}) ${valor.substring(2, 6)}-${valor.substring(6)}`;
            } else {
                event.target.value = `(${valor.substring(0, 2)}) ${valor.substring(2, 7)}-${valor.substring(7, 11)}`;
            }
        });

        // Máscara para CPF
        cpfInput.addEventListener('input', (event) => {
            let valor = event.target.value.replace(/\D/g, ''); // Remove tudo o que não é dígito
            if (valor.length > 11) valor = valor.slice(0, 11); // Limita a 11 dígitos

            // Aplica a formatação do CPF (000.000.000-00)
            if (valor.length > 9) {
                valor = valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
            } else if (valor.length > 6) {
                valor = valor.replace(/(\d{3})(\d{3})(\d{3})/, "$1.$2.$3");
            } else if (valor.length > 3) {
                valor = valor.replace(/(\d{3})(\d{3})/, "$1.$2");
            }
            event.target.value = valor;
        });

        window.config = {
            enderecoCompleto: <?php echo json_encode($endereco_completo); ?>,
            modoConvidado: <?php echo json_encode($modo_convidado); ?>,
            servicosDisponiveis: <?php echo json_encode($lista_de_servicos); ?>,
            servicosSelecionados: <?php echo json_encode($form_data['servicos_interesse'] ?? []); ?>,
            outrosDescricoes: <?php echo json_encode($form_data['outros_servicos_descricao'] ?? []); ?>,
            feedbackData: <?php echo json_encode($feedback); ?>
        };
    </script>
    <script src="indicar.js"></script>
</body>

</html>