<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];
$stmt = $conexao->prepare("SELECT nome, sobrenome, cpf, telefone, data_nascimento, email, genero, cep, endereco, numero, bairro, estado, cidade, complemento FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Formatando os dados para exibição
$nome = htmlspecialchars($user['nome'] ?? '');
$sobrenome = htmlspecialchars($user['sobrenome'] ?? '');
$cpf = htmlspecialchars($user['cpf'] ?? '');
$telefone = htmlspecialchars($user['telefone'] ?? '');
$data_nascimento_formatada = !empty($user['data_nascimento']) ? date('d/m/Y', strtotime($user['data_nascimento'])) : 'Não informado';
$email = htmlspecialchars($user['email'] ?? '');
$genero = $user['genero'] ?? 'Não informado';
$cep = htmlspecialchars($user['cep'] ?? 'Não informado');
$endereco = htmlspecialchars($user['endereco'] ?? 'Não informado');
$numero = htmlspecialchars($user['numero'] ?? 'Não informado');
$bairro = htmlspecialchars($user['bairro'] ?? 'Não informado');
$estado = htmlspecialchars($user['estado'] ?? 'Não informado');
$cidade = htmlspecialchars($user['cidade'] ?? 'Não informado');
$complemento = htmlspecialchars($user['complemento'] ?? 'Não informado');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil | Alfonso Rewards</title>
    <link rel="stylesheet" href="perfil.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="includes/perfil.js" defer></script>
</head>

<body>

    <?php include 'includes/header_logado.php'; ?>

    <main class="profile-main">
        <section class="profile-header">
            <div class="header-content">
                <h1>Meu Perfil</h1>
                <p>Gerencie suas informações pessoais e de contato.</p>
            </div>
            <button id="btnEditarPerfil" class="btn-primary"><i class="fas fa-user-edit"></i> Editar Perfil</button>
        </section>

        <?php if (isset($_SESSION['profile_update_message'])): ?>
            <div class="message-box <?php echo $_SESSION['profile_update_message']['type']; ?>">
                <?php echo $_SESSION['profile_update_message']['text'];
                unset($_SESSION['profile_update_message']); ?>
            </div>
        <?php endif; ?>

        <div class="profile-grid">
            <section class="profile-card">
                <h2 class="section-title"><i class="fas fa-user"></i> Informações Pessoais</h2>
                <div class="info-grid">
                    <div class="info-item"><label>Nome Completo</label><span><?php echo $nome . ' ' . $sobrenome; ?></span></div>
                    <div class="info-item"><label>CPF</label><span><?php echo $cpf; ?></span></div>
                    <div class="info-item"><label>Data de Nascimento</label><span><?php echo $data_nascimento_formatada; ?></span></div>
                    <div class="info-item"><label>Gênero</label><span><?php echo ucfirst($genero); ?></span></div>
                </div>
            </section>
            <section class="profile-card">
                <h2 class="section-title"><i class="fas fa-address-book"></i> Informações de Contato</h2>
                <div class="info-grid">
                    <div class="info-item"><label>E-mail</label><span><?php echo $email; ?></span></div>
                    <div class="info-item"><label>Telefone</label><span><?php echo $telefone; ?></span></div>
                </div>
            </section>
            <section class="profile-card">
                <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Informações de Endereço</h2>
                <div class="info-grid">
                    <div class="info-item"><label>CEP</label><span><?php echo $cep; ?></span></div>
                    <div class="info-item"><label>Endereço</label><span><?php echo "$endereco, $numero"; ?></span></div>
                    <div class="info-item"><label>Bairro</label><span><?php echo $bairro; ?></span></div>
                    <div class="info-item"><label>Cidade / Estado</label><span><?php echo "$cidade / $estado"; ?></span></div>
                    <div class="info-item"><label>Complemento</label><span><?php echo $complemento; ?></span></div>
                </div>
            </section>
        </div>

        <section class="deactivate-section">
            <h2 class="section-title">Ações da Conta</h2>
            <div class="deactivate-content">
                <p>Se desejar, você pode desativar sua conta em nosso programa. Esta ação é permanente.</p>
                <button id="btnDesativarConta" class="btn-deactivate"><i class="fas fa-user-slash"></i> Desativar Minha Conta</button>
            </div>
        </section>
    </main>

    <?php include '../footer.php'; ?>

    <div id="modalConfirmarDesativacao" class="modal-overlay">
        <div class="modal-content">
            <h2 class="modal-title warning-title"><i class="fas fa-exclamation-triangle"></i> Tem Certeza?</h2>

            <p>Ao confirmar, sua conta será temporariamente desativada. Todo o seu histórico e saldo serão mantidos e você poderá reativá-la no futuro. Deseja continuar?</p>
            <div class="modal-actions">
                <button id="btnCancelarDesativacao" class="btn-secondary">Cancelar</button>
                <button id="btnConfirmarDesativacao" class="btn-deactivate">Sim, desativar</button>
            </div>
        </div>
    </div>

    <div id="modalToken" class="modal-overlay">
        <div class="modal-content">
            <h2 id="modalTokenTitle" class="modal-title">Confirmação de Identidade</h2>
            <p id="modalTokenMessage">Um código de 6 dígitos foi enviado para o seu e-mail. Insira-o abaixo para continuar.</p>
            <div id="loadingSpinnerRequest" class="spinner-container" style="display: none;">
                <div class="spinner"></div><span>Enviando código...</span>
            </div>
            <p id="tokenSuccess" class="message-success"></p>

            <input type="text" id="inputToken" maxlength="6" placeholder="_ _ _ _ _ _">

            <div id="loadingSpinnerVerify" class="spinner-container" style="display: none;">
                <div class="spinner"></div><span>Verificando...</span>
            </div>
            <p id="tokenError" class="message-error"></p>

            <div class="modal-actions">
                <button id="btnCancelarToken" class="btn-secondary">Cancelar</button>
                <button id="btnConfirmarToken" class="btn-primary">Confirmar</button>
            </div>
        </div>
    </div>

    <div id="modalSucesso" class="modal-overlay">
        <div class="modal-content">
            <h2 class="modal-title success-title"><i class="fas fa-check-circle"></i> Verificação Concluída</h2>
            <p id="modalSucessoMessage">Redirecionando...</p>
        </div>
    </div>
</body>

</html>