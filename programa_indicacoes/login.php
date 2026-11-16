<?php
session_start();

// Se o usuário JÁ ESTÁ logado, redireciona para a página de perfil.
// Corrigido para a variável de sessão correta.
if (isset($_SESSION['user_id'])) {
    header('Location: perfil.php'); // Ajuste o caminho se necessário
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Alfonso Rewards</title>
    <link rel="stylesheet" href="login.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="includes/login.js" defer></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <section class="section-container">
            <div class="form-wrapper">
                <div class="form-header">
                    <i class="form-icon fas fa-sign-in-alt"></i>
                    <h1 class="form-title">Acessar Plataforma</h1>
                    <p class="form-subtitle">Bem-vindo de volta! Faça login para gerenciar suas indicações.</p>
                </div>

                <form id="login-form" method="POST" action="processos/processar_login.php" class="form-body">
                    <div class="input-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn-primary">Entrar</button>

                        <?php if (isset($_SESSION['login_error_message'])): ?>
                            <div class="form-message-error">
                                <?php echo $_SESSION['login_error_message'];
                                unset($_SESSION['login_error_message']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['login_success_message'])): ?>
                            <div class="form-message-success">
                                <?php echo $_SESSION['login_success_message'];
                                unset($_SESSION['login_success_message']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="extra-links">
                            <a href="esqueceu_senha.php">Esqueceu a senha?</a>
                            <span>|</span>
                            <a href="cadastrar.php">Não tem uma conta? Cadastre-se</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <div class="popup-overlay" id="feedback-popup">
        <div class="popup-content">
            <h2 id="popup-title">Título</h2>
            <p id="popup-message">Mensagem de exemplo.</p>
            <button class="btn-primary" id="popup-ok-btn">OK</button>
        </div>
    </div>
    <?php include '../footer.php'; ?>
    <?php if (isset($_SESSION['login_success_message'])): ?>
        showPopup('Sucesso!', '<?php echo $_SESSION['login_success_message']; ?>', false);
        <?php unset($_SESSION['login_success_message']); ?>
    <?php endif; ?>

</body>

</html>