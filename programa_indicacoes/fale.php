<?php
session_start();
$usuario_logado = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fale Conosco</title>
    <link rel="icon" href="imagens/qxbackfavicon.webp" class="favicon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        <?php include 'faq.css'; ?>.main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 20px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .banner {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #0f0;
            border-radius: 15px;
            padding: 3rem;
            margin: 20px auto;
            margin-top: 70px;
            max-width: 450px;
            box-shadow: 0 0 30px rgba(0, 255, 0, 0.2);
            position: relative;
            animation: slideDown 0.5s ease-out;
            text-align: center;
        }

        .text-container {
            padding: 1.5rem;
            margin: 1rem 0;
            border-left: 3px solid #0f0;
            background: rgba(0, 255, 0, 0.05);
            position: relative;
            padding-left: 20px;
        }

        .text1 {
            font-size: 1.2rem;
            color: #0f0;
            text-shadow: 0 0 10px rgba(0, 255, 0, 0.3);
        }

        .text2 p {
            color: #fff;
            margin: 1rem 0;
        }

        .text2 strong a {
            color: #0f0;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .text2 strong a:hover {
            text-shadow: 0 0 15px rgba(0, 255, 0, 0.5);
        }

        .submit-button {
            display: inline-block;
            width: 100%;
            max-width: 300px;
            margin: 1.5rem 10px;
            padding: 15px 30px;
            background: rgba(0, 255, 0, 0.1);
            border: 2px solid #0f0;
            border-radius: 50px;
            color: #0f0;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .submit-button:hover {
            background: #0f0;
            color: #000;
            transform: scale(1.05);
            box-shadow: 0 0 25px rgba(0, 255, 0, 0.5);
        }

        @media (max-width: 768px) {
            .banner {
                padding: 1.5rem;
                margin: 1rem;
            }

            .text-container {
                padding: 1rem;
                padding-left: 15px;
            }

            .submit-button {
                margin: 1rem auto;
                display: block;
            }
        }
    </style>
</head>

<body>
    <?php if ($usuario_logado): ?>
        <?php include 'includes/header_logado.php'; ?>
    <?php else: ?>
        <?php include 'includes/header.php'; ?>
    <?php endif; ?>

    <div class="hamburguer-menu" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="main-content">
        <div class="banner">
            <div class="text-container text2">
                <p>
                    Envie um <strong><a
                            href="mailto:contato@grupoqmax.com.br?subject=Olá%20Equipe&body=Gostaria%20de%20mais%20informações%20sobre%20os%20serviços."
                            target="_blank">e-mail</a>
                    </strong>ou uma mensagem no nosso <strong><a href="https://wa.me/551132303062"
                            target="_blank">whatsapp</a>.
                    </strong></p>
            </div>

            <div class="text-container text1">
                A gente quer conhecer você também, fale conosco! Logo um de nossos atendentes entrará em
                contato com você!
            </div>

            <div class="button-container">
                <a class="submit-button main-cta"
                    href="mailto:contato@grupoqmax.com.br?subject=Olá%20Equipe&body=Gostaria%20de%20mais%20informações%20sobre%20os%20serviços."
                    target="_blank">Enviar um e-mail</a>
                <a class="submit-button main-cta" href="https://wa.me/551132303062" target="_blank">Fale por
                    whatsapp</a>
            </div>
        </div>
    </div>

    <?php include 'includes/cookie.php'; ?>
    <?php include 'includes/footer.php'; ?>
    <script src="includes/script.js"></script>
</body>

</html>