<?php

require_once 'backend/config.php';
require_once 'backend/permissoes.php';

if (pode('acesso_logs')) {
    include_once 'registrar_acesso.php';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alfonso Rewards | Indique e Ganhe Sorrisos</title>

    <link rel="stylesheet" href="index.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>
    <script src="index.js" defer></script>
</head>

<body>

    <div class="preloader">
        <img src="../imagens/logo_sem_fundo.png" alt="Logo Clínica Alfonso">
    </div>

    <?php require 'includes/header.php'; ?>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1 class="hero-title">Indicou, Sorriu, Ganhou!</h1>
                <p class="hero-subtitle">Bem-vindo ao Alfonso Rewards: o programa exclusivo que valoriza o seu bom gosto e a sua confiança na Clínica Alfonso. Indique e transforme sorrisos em recompensas!</p>
                <div class="perks-container">
                    <div class="perk-card">
                        <div class="perk-icon"><i class="fas fa-hand-holding-usd"></i></div>
                        <h3>Recompensa em Dinheiro</h3>
                        <p>Receba cashback por cada indicação que fechar um tratamento!</p>
                    </div>
                    <div class="perk-card">
                        <div class="perk-icon"><i class="fas fa-smile-beam"></i></div>
                        <h3>Bônus para o Indicado</h3>
                        <p>Seu amigo também ganha um desconto especial no primeiro tratamento!</p>
                    </div>
                    <div class="perk-card">
                        <div class="perk-icon"><i class="fas fa-heart"></i></div>
                        <h3>Compartilhe Saúde Bucal</h3>
                        <p>Ajude quem você ama a ter um sorriso mais saudável e bonito.</p>
                    </div>
                </div>
                <a href="cadastrar.php" class="btn-primary btn-hero">Comece a indicar agora</a>
            </div>
        </section>

        <section id="como-funciona" class="section-container">
            <div class="section-header">
                <h2 class="section-title">Como Funciona? Seu Caminho para Recompensas.</h2>
                <p class="section-subtitle">O processo é claro e rápido. Em apenas quatro etapas, você já estará indicando e acumulando benefícios.</p>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                    <h3>1. Crie sua Conta</h3>
                    <p>Faça seu cadastro em nosso sistema seguro e intuitivo. Leva menos de 2 minutos!</p>
                </div>
                <div class="step-card">
                    <div class="step-icon"><i class="fas fa-share-alt"></i></div>
                    <h3>2. Indique seus Contatos</h3>
                    <p>Use seu painel exclusivo para enviar indicações de amigos e familiares interessados em saúde bucal.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>3. Acompanhe o Processo</h3>
                    <p>Monitore o status de cada indicação em tempo real, desde o primeiro contato até a finalização do tratamento.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon"><i class="fas fa-coins"></i></div>
                    <h3>4. Receba Suas Recompensas</h3>
                    <p>Assim que a indicação fechar um tratamento, suas recompensas são creditadas na sua conta!</p>
                </div>
            </div>
        </section>

        <section id="vantagens" class="section-container section-bg">
            <div class="section-header">
                <h2 class="section-title">As Vantagens Exclusivas do Alfonso Rewards.</h2>
                <p class="section-subtitle">Descubra os benefícios de fazer parte do nosso programa de indicações. Conveniência, transparência e valor para você.</p>
            </div>
            <div class="vantagens-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-desktop"></i></div>
                    <div class="feature-content">
                        <h3>Painel Intuitivo e Completo</h3>
                        <p>Acesse uma plataforma pensada para você. Gerencie suas indicações, visualize seus ganhos e solicite seus resgates com facilidade, a qualquer momento e de qualquer lugar.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-eye"></i></div>
                    <div class="feature-content">
                        <h3>Transparência Total do Processo</h3>
                        <p>Mantenha-se informado. Receba atualizações detalhadas sobre cada etapa da sua indicação, desde a consulta inicial até o início do tratamento, garantindo total clareza.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-handshake"></i></div>
                    <div class="feature-content">
                        <h3>Recompensas e Pagamentos Ágeis</h3>
                        <p>Valorizamos o seu tempo. Suas recompensas são processadas com a máxima eficiência e segurança, com pagamentos realizados diretamente na sua conta, sem burocracia.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="beneficios" class="section-container">
            <div class="section-header">
                <h2 class="section-title">O Que Você Ganha ao Indicar a Clínica Alfonso?</h2>
                <p class="section-subtitle">Além de ajudar seus amigos a sorrirem mais, você desfruta de uma série de vantagens exclusivas.</p>
            </div>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-piggy-bank"></i></div>
                    <h3>Ganho Financeiro</h3>
                    <p>Receba uma porcentagem do valor do tratamento fechado. Quanto mais indica, mais você ganha!</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-gift"></i></div>
                    <h3>Bônus e Surpresas</h3>
                    <p>Desfrute de bônus especiais em datas comemorativas ou ao atingir metas de indicações.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-star"></i></div>
                    <h3>Reconhecimento</h3>
                    <p>Faça parte de um grupo seleto de embaixadores da Clínica Alfonso, reconhecido por sua contribuição.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-chart-pie"></i></div>
                    <h3>Controle Total</h3>
                    <p>Visualize em seu painel todos os seus ganhos e o impacto das suas indicações.</p>
                </div>
            </div>
        </section>

    </main>

    <?php require '../footer.php'; ?>
</body>

</html>