<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha | Alfonso Rewards</title>
    <link rel="stylesheet" href="esqueceu_senha.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="includes/esqueceu_senha.js" defer></script>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <section class="section-container">
            <div class="form-wrapper">
                <div class="form-header">
                    <i class="form-icon fas fa-key"></i>
                    <h1 class="form-title">Recuperar Senha</h1>
                    <p class="form-subtitle">Informe seu e-mail cadastrado. Enviaremos um link seguro para você criar uma nova senha.</p>
                </div>

                <div id="message-area" class="message-box" style="display: none;"></div>
                
                <form id="reset-form" class="form-body">
                    <div class="input-group">
                        <label for="email">Seu e-mail de cadastro</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-footer">
                        <button type="submit" id="submit-btn" class="btn-primary">Enviar Link de Recuperação</button>
                        <p class="back-link"><a href="login.php">Voltar para o Login</a></p>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <?php include '../footer.php'; ?>
</body>
</html>