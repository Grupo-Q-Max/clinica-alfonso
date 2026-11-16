<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['perfil_verificado']) || $_SESSION['perfil_verificado'] !== true) {
    $_SESSION['profile_update_message'] = ['type' => 'error', 'text' => 'Acesso negado. Por favor, valide sua identidade para editar o perfil.'];
    header("Location: perfil.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];
$stmt = $conexao->prepare("SELECT nome, sobrenome, nome_social, cpf, telefone, data_nascimento, email, genero, cep, endereco, numero, bairro, estado, cidade, complemento FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | Alfonso Rewards</title>
    <link rel="stylesheet" href="editar_perfil.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="includes/editar_perfil.js" defer></script>
</head>

<body>

    <?php include 'includes/header_logado.php'; ?>

    <main class="profile-main">
        <form action="processos/processar_edicao_perfil.php" method="POST">
            <section class="profile-header">
                <div class="header-content">
                    <h1>Editar Perfil</h1>
                    <p>Atualize suas informações pessoais e de contato.</p>
                </div>
                <div class="header-actions">
                    <a href="perfil.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
                </div>
            </section>

            <div class="profile-grid">
                <section class="profile-card">
                    <h2 class="section-title"><i class="fas fa-user"></i> Informações Pessoais</h2>
                    <div class="info-grid">
                        <div class="input-group">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="sobrenome">Sobrenome</label>
                            <input type="text" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($user['sobrenome']); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="cpf">CPF (não pode ser alterado)</label>
                            <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($user['cpf']); ?>" readonly disabled>
                        </div>
                        <div class="input-group">
                            <label for="data_nascimento">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo $user['data_nascimento']; ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="genero">Gênero</label>
                            <select id="genero" name="genero" required>
                                <option value="masculino" <?php echo ($user['genero'] === 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="feminino" <?php echo ($user['genero'] === 'feminino') ? 'selected' : ''; ?>>Feminino</option>
                                <option value="outro" <?php echo ($user['genero'] === 'outro') ? 'selected' : ''; ?>>Outro</option>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="profile-card">
                    <h2 class="section-title"><i class="fas fa-address-book"></i> Informações de Contato</h2>
                    <div class="info-grid">
                        <div class="input-group">
                            <label for="email">E-mail (não pode ser alterado)</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled>
                        </div>
                        <div class="input-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($user['telefone']); ?>" required>
                        </div>
                    </div>
                </section>

                <section class="profile-card">
                    <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Informações de Endereço</h2>
                    <div class="info-grid">
                        <div class="input-group">
                            <label for="cep">CEP</label>
                            <input type="text" id="cep" name="cep" value="<?php echo htmlspecialchars($user['cep'] ?? '');?>">
                        </div>
                        <div class="input-group">
                            <label for="endereco">Endereço</label>
                            <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($user['endereco'] ?? ''); ?>">
                        </div>
                        <div class="input-group">
                            <label for="numero">Número</label>
                            <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($user['numero'] ?? ''); ?>">
                        </div>
                        <div class="input-group">
                            <label for="bairro">Bairro</label>
                            <input type="text" id="bairro" name="bairro" value="<?php echo htmlspecialchars($user['bairro']?? ''); ?>">
                        </div>
                        <div class="input-group">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" value="<?php echo htmlspecialchars($user['cidade'] ?? ''); ?>">
                        </div>
                        <div class="input-group">
                            <label for="estado">Estado</label>
                            <input type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($user['estado']?? ''); ?>">
                        </div>
                        <div class="input-group full-width">
                            <label for="complemento">Complemento</label>
                            <input type="text" id="complemento" name="complemento" value="<?php echo htmlspecialchars($user['complemento']?? ''); ?>">
                        </div>
                    </div>
                </section>
            </div>
        </form>
    </main>

    <?php include '../footer.php'; ?>

</body>

</html>