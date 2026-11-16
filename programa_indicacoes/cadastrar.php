<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crie sua Conta | Alfonso Rewards</title>
    <link rel="stylesheet" href="cadastrar.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>
    <script src="cadastrar.js" defer></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <section class="section-container">
            <div class="form-wrapper">
                <div class="form-header">
                    <i class="form-icon fas fa-user-plus"></i>
                    <h1 class="form-title">Crie Sua Conta</h1>
                    <p class="form-subtitle">Faça parte do Alfonso Rewards e comece a ganhar benefícios.</p>
                </div>

                <form id="form-cadastro" action="processos/processar_cadastro.php" method="POST" class="form-body">
                    <div class="form-grid">
                        <div class="form-column">
                            <div class="input-group">
                                <label for="nome">Nome</label>
                                <input type="text" id="nome" name="nome" required value="<?php echo isset($_SESSION['form_data']['nome']) ? htmlspecialchars($_SESSION['form_data']['nome']) : ''; ?>">
                            </div>
                            <div class="input-group">
                                <label for="sobrenome">Sobrenome</label>
                                <input type="text" id="sobrenome" name="sobrenome" required value="<?php echo isset($_SESSION['form_data']['sobrenome']) ? htmlspecialchars($_SESSION['form_data']['sobrenome']) : ''; ?>">
                            </div>
                            <div class="input-group">
                                <label for="cpf">CPF</label>
                                <input type="text" id="cpf" name="cpf" minlength="14" maxlength="14" oninput="formatarCPF(this)" required value="<?php echo isset($_SESSION['form_data']['cpf']) ? htmlspecialchars($_SESSION['form_data']['cpf']) : ''; ?>">

                                <?php if (isset($_SESSION['cpf_error_message'])): ?>
                                    <div class="form-error"><?php echo $_SESSION['cpf_error_message'];
                                                            unset($_SESSION['cpf_error_message']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="input-group">
                                <label for="email">E-mail</label>
                                <input type="email" id="email" name="email" required value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
                                <?php if (isset($_SESSION['email_error'])): ?>
                                    <div class="form-error"><?php echo $_SESSION['email_error'];
                                                            unset($_SESSION['email_error']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-column">
                            <div class="input-group">
                                <label for="data_nascimento">Data de Nascimento</label>
                                <input type="date" id="data_nascimento" name="data_nascimento" required value="<?php echo isset($_SESSION['form_data']['data_nascimento']) ? htmlspecialchars($_SESSION['form_data']['data_nascimento']) : ''; ?>">
                            </div>
                            <div class="input-group">
                                <label for="telefone">Telefone</label>
                                <input type="text" id="telefone" name="telefone" minlength="15" maxlength="15" oninput="formatarTelefone(this)" required value="<?php echo isset($_SESSION['form_data']['telefone']) ? htmlspecialchars($_SESSION['form_data']['telefone']) : ''; ?>">
                            </div>
                            <div class="input-group">
                                <label for="senha">Senha</label>
                                <input type="password" id="senha" name="senha" required>
                                <div id="error-senha" class="form-error"></div>
                            </div>
                            <div class="input-group">
                                <label for="confirmar-senha">Confirme a Senha</label>
                                <input type="password" id="confirmar-senha" name="confirmar-senha" required>
                                <div id="error-confirmSenha" class="form-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <div class="checkbox-group">
                            <input type="checkbox" id="privacy" name="privacy" required>
                            <label for="privacy">Li e aceito os <a href="politica.html" target="_blank">Termos de Privacidade</a></label>
                        </div>
                        <button type="submit" class="btn-primary">Finalizar Cadastro</button>

                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="form-message"><?php echo $_SESSION['message'];
                                                        unset($_SESSION['message']); ?></div>
                        <?php endif; ?>

                        <p class="login-link">Já tem uma conta? <a href="login.php">Faça login</a></p>
                    </div>
                    <?php if (isset($_SESSION['form_error_message'])): ?>
                        <div class="form-message-error">
                            <?php echo $_SESSION['form_error_message'];
                            unset($_SESSION['form_error_message']); ?>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </section>
    </main>

    <?php
    include '../footer.php';
    unset($_SESSION['form_data']);
    ?>

    <script src="includes/cadastrar.js"></script>
</body>

</html>