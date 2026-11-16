<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// --- BUSCA DE DADOS ---
$stmt = $conexao->prepare("SELECT cep, endereco, numero, bairro, estado, cidade, complemento FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete seu Endereço | Alfonso Rewards</title>
    <link rel="stylesheet" href="cadastro_endereco.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="includes/cadastro_endereco.js" defer></script>
</head>

<body>
    <?php include 'includes/header_logado.php'; ?>

    <main>
        <section class="section-container">
            <div class="form-wrapper">
                <div class="form-header">
                    <i class="form-icon fas fa-map-marked-alt"></i>
                    <h1 class="form-title">Complete Seu Endereço</h1>
                    <p class="form-subtitle">Precisamos do seu endereço para finalizar o seu cadastro e garantir o envio de suas recompensas.</p>
                </div>

                <?php if (isset($_SESSION['form_message'])): ?>
                    <div class="message-box <?php echo $_SESSION['form_message']['type']; ?>">
                        <?php echo $_SESSION['form_message']['text'];
                        unset($_SESSION['form_message']); ?>
                    </div>
                <?php endif; ?>

                <form action="processos/processar_endereco.php" method="POST" class="form-body">
                    <div class="form-grid">
                        <div class="input-group">
                            <label for="cep">CEP *</label>
                            <input type="text" id="cep" name="cep" value="<?php echo htmlspecialchars($usuario['cep'] ?? ''); ?>" required maxlength="9">
                        </div>
                        <div class="input-group">
                            <label for="endereco">Endereço *</label>
                            <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($usuario['endereco'] ?? ''); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="numero">Número *</label>
                            <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($usuario['numero'] ?? ''); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="bairro">Bairro *</label>
                            <input type="text" id="bairro" name="bairro" value="<?php echo htmlspecialchars($usuario['bairro'] ?? ''); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="cidade">Cidade *</label>
                            <input type="text" id="cidade" name="cidade" value="<?php echo htmlspecialchars($usuario['cidade'] ?? ''); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="estado">Estado *</label>
                            <input type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($usuario['estado'] ?? ''); ?>" required>
                        </div>
                        <div class="input-group full-width">
                            <label for="complemento">Complemento (Opcional)</label>
                            <input type="text" id="complemento" name="complemento" value="<?php echo htmlspecialchars($usuario['complemento'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn-primary">Salvar Endereço</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <?php include '../footer.php'; ?>
</body>

</html>