<?php
session_start();
$usuario_logado = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós | Alfonso Rewards</title>
    <link rel="stylesheet" href="sobre.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>
    <script src="sobre.js" defer></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <section class="hero-sobre">
            <div class="hero-content">
                <h1 class="hero-title">Sobre o Alfonso Rewards</h1>
                <p class="hero-subtitle">
                    O Alfonso Rewards é um programa inovador que nasceu da nossa crença no poder da confiança. Nosso objetivo é reconhecer e recompensar os pacientes que compartilham a nossa paixão por um cuidado odontológico de excelência, transformando indicações em benefícios reais.
                </p>
            </div>
        </section>

        <section class="section-container">
            <div class="section-header">
                <h2 class="section-title">Tratamentos Elegíveis para Indicação</h2>
                <p class="section-subtitle">Indique qualquer um dos nossos tratamentos de ponta e seja recompensado. Cada novo sorriso que ajudamos a criar, graças a você, gera um benefício direto.</p>
            </div>
            <div class="treatments-grid">
                <div class="treatment-card">
                    <div class="treatment-icon"><i class="fas fa-tooth"></i></div>
                    <h4>Implantes Dentários</h4>
                    <p>Recompensa de 50% sobre o valor da primeira parcela do tratamento.</p>
                </div>
                <div class="treatment-card">
                    <div class="treatment-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
                    <h4>Lentes de Contato</h4>
                    <p>Recompensa de 10% sobre o valor total do tratamento, pago na conclusão.</p>
                </div>
                <div class="treatment-card">
                    <div class="treatment-icon"><i class="far fa-smile-beam"></i></div>
                    <h4>Clareamento Dental</h4>
                    <p>Receba um bônus fixo para cada indicação que iniciar o tratamento de clareamento.</p>
                </div>
                <div class="treatment-card">
                    <div class="treatment-icon"><i class="fas fa-user-plus"></i></div>
                    <h4>Indique um Amigo</h4>
                    <p>Tem um amigo que quer fazer parte do programa? Indique-o e ganhe bônus também!</p>
                </div>
            </div>
        </section>

        <section class="section-container section-bg">
            <div class="cta-card">
                <div class="cta-content">
                    <h2 class="cta-title">Faça Parte do Nosso Círculo</h2>
                    <p>Ainda não tem uma conta? Cadastre-se em minutos e comece a transformar suas indicações em recompensas hoje mesmo.</p>
                    <a href="cadastrar.php" class="btn-primary">Criar Minha Conta</a>
                </div>
                <div class="cta-visual">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </section>

        <section class="section-container">
            <div class="section-header">
                <h2 class="section-title">Benefícios Exclusivos do Programa</h2>
            </div>
            <div class="benefits-list">
                <div class="benefit-item">
                    <div class="benefit-icon"><i class="fas fa-gift"></i></div>
                    <div class="benefit-text">
                        <h3>Recompensa Contínua</h3>
                        <p>Se o seu indicado realizar novos tratamentos no futuro, você continua ganhando!</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon"><i class="fas fa-certificate"></i></div>
                    <div class="benefit-text">
                        <h3>Recompensa Justa e Transparente</h3>
                        <p>Bônus por avaliação realizada + percentuais claros sobre os tratamentos.</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon"><i class="fas fa-user-check"></i></div>
                    <div class="benefit-text">
                        <h3>Aberto a Todos</h3>
                        <p>Você não precisa ser nosso paciente para indicar. O programa é aberto a todos que confiam em nosso trabalho.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../footer.php'; ?>
</body>

</html>