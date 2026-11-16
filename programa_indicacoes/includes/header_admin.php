<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="apple-touch-icon" sizes="180x180" href="/imagens/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/imagens/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/imagens/favicon-16x16.png">
<link rel="manifest" href="/imagens/site.webmanifest">
<link rel="shortcut icon" href="/imagens/favicon.ico">
<style>
    /* --- RAÍZ: TEMA PADRONIZADO "CLÍNICA ALFONSO" --- */
    :root {
        --dark-teal: #30414B;
        --ivory-white: #FCFCFC;
        --soft-gold: #C0B283;
        --text-on-dark: #FFFFFF;
        --shadow-soft: 0 5px 20px rgba(48, 65, 75, 0.07);
        --transition: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        --heading-font: 'Playfair Display', serif;
        --body-font: 'Lato', sans-serif;
    }

    /* Reseta o padding do body que será aplicado pelo CSS da página principal */
    body {
        padding-top: 0 !important;
        font-family: var(--body-font);
    }

    body.body-header-logado {
        padding-top: 90px;
    }

    body.no-scroll {
        overflow: hidden;
    }

    /* --- HEADER ELEGANTE E À PROVA DE FALHAS --- */
    .header-alfonso {
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        background-color: var(--dark-teal);
        box-shadow: var(--shadow-soft);
        padding: 10px 0;
    }

    .navbar-alfonso {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1400px;
        /* Um pouco mais largo para o admin */
        margin: 0 auto;
        padding: 0 2%;
        /* Padding um pouco menor para mais espaço */
    }

    .nav-logo-alfonso img {
        height: 70px;
        filter: brightness(0) invert(1);
        display: block;
    }

    .nav-menu-alfonso {
        display: flex;
        align-items: center;
        gap: 1.8rem;
        /* Espaçamento ajustado para mais itens */
    }

    .nav-link-alfonso {
        color: var(--text-on-dark);
        font-weight: 700;
        font-size: 1rem;
        /* Fonte um pouco menor para caber mais itens */
        text-decoration: none;
        position: relative;
        padding: 10px 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: color var(--transition);
    }

    .nav-link-alfonso:hover {
        color: var(--soft-gold);
    }

    .nav-link-alfonso::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background-color: var(--soft-gold);
        transition: width var(--transition);
    }

    .nav-link-alfonso:hover::after,
    .nav-link-alfonso.active::after {
        width: 100%;
    }

    .nav-link-alfonso.active {
        color: var(--soft-gold);
    }

    .logout-form {
        margin: 0;
        padding: 0;
        display: flex;
    }

    .logout-btn {
        background: none;
        border: none;
        color: var(--text-on-dark);
        font-family: var(--body-font);
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        padding: 10px 0;
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: color var(--transition);
    }

    .logout-btn:hover {
        color: var(--soft-gold);
    }

    .logout-btn::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background-color: var(--soft-gold);
        transition: width var(--transition);
    }

    .logout-btn:hover::after {
        width: 100%;
    }

    /* --- MENU HAMBÚRGUER --- */
    .hamburger-alfonso {
        display: none;
        z-index: 1002;
        cursor: pointer;
        position: relative;
        width: 30px;
        height: 24px;
    }

    .bar-alfonso {
        display: block;
        width: 100%;
        height: 3px;
        background-color: var(--text-on-dark);
        transition: all 0.4s ease-in-out;
        position: absolute;
        border-radius: 3px;
    }

    .bar-alfonso:nth-child(1) {
        top: 0;
    }

    .bar-alfonso:nth-child(2) {
        top: 50%;
        transform: translateY(-50%);
    }

    .bar-alfonso:nth-child(3) {
        bottom: 0;
    }

    .hamburger-alfonso.active .bar-alfonso:nth-child(1) {
        top: 50%;
        transform: translateY(-50%) rotate(45deg);
    }

    .hamburger-alfonso.active .bar-alfonso:nth-child(2) {
        opacity: 0;
    }

    .hamburger-alfonso.active .bar-alfonso:nth-child(3) {
        top: 50%;
        transform: translateY(-50%) rotate(-45deg);
    }

    .mobile-nav-overlay-alfonso {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--dark-teal);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: transform 0.6s cubic-bezier(0.86, 0, 0.07, 1);
        transform: translateY(-100%);
        z-index: 999;
    }

    .mobile-nav-overlay-alfonso.active {
        transform: translateY(0);
    }

    .mobile-nav-link-alfonso,
    .mobile-logout-btn {
        color: var(--ivory-white);
        font-size: 2rem;
        font-family: var(--heading-font);
        margin: 0.7rem 0;
        text-decoration: none;
        background: none;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .mobile-nav-overlay-alfonso .mobile-nav-link-alfonso.active {
        color: var(--soft-gold);
    }

    /* --- RESPONSIVIDADE --- */
    @media (max-width: 1200px) {
        .nav-menu-alfonso {
            display: none;
        }

        .hamburger-alfonso {
            display: block;
        }
    }

    @media (max-width: 992px) {
        body.body-header-logado {
            padding-top: 70px;
        }

        .nav-logo-alfonso img {
            height: 50px;
        }
    }
</style>

<header class="header-alfonso">
    <div class="navbar-alfonso">
        <a href="admin.php" class="nav-logo-alfonso">
            <img src="imagens/logo_sem_fundo1.png" alt="Logo Clínica Alfonso">
        </a>
        <nav class="nav-menu-alfonso">
            <a href="admin.php" class="nav-link-alfonso <?= $current_page == 'admin.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Painel</a>
            <a href="gerenciar_indicacoes.php" class="nav-link-alfonso <?= $current_page == 'gerenciar_indicacoes.php' ? 'active' : '' ?>"><i class="fas fa-tasks"></i> Indicações</a>
            <a href="usuarios.php" class="nav-link-alfonso <?= $current_page == 'usuarios.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Usuários</a>
            <a href="cadastros.php" class="nav-link-alfonso <?= $current_page == 'cadastros.php' ? 'active' : '' ?>"><i class="fas fa-cogs"></i> Serviços</a>
            <a href="resgates.php" class="nav-link-alfonso <?= $current_page == 'resgates.php' ? 'active' : '' ?>"><i class="fas fa-hand-holding-usd"></i> Resgates</a>
            <a href="logs.php" class="nav-link-alfonso <?= $current_page == 'logs.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Relatórios</a>
            <form method="POST" action="logout.php" class="logout-form">
                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</button>
            </form>
        </nav>
        <div class="hamburger-alfonso">
            <span class="bar-alfonso"></span>
            <span class="bar-alfonso"></span>
            <span class="bar-alfonso"></span>
        </div>
    </div>
</header>

<div class="mobile-nav-overlay-alfonso">
    <a href="admin.php" class="mobile-nav-link-alfonso <?= $current_page == 'admin.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Painel</a>
    <a href="gerenciar_indicacoes.php" class="mobile-nav-link-alfonso <?= $current_page == 'gerenciar_indicacoes.php' ? 'active' : '' ?>"><i class="fas fa-tasks"></i> Indicações</a>
    <a href="usuarios.php" class="mobile-nav-link-alfonso <?= $current_page == 'usuarios.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Usuários</a>
    <a href="cadastros.php" class="mobile-nav-link-alfonso <?= $current_page == 'cadastros.php' ? 'active' : '' ?>"><i class="fas fa-cogs"></i> Cadastros</a>
    <a href="resgates.php" class="mobile-nav-link-alfonso <?= $current_page == 'resgates.php' ? 'active' : '' ?>"><i class="fas fa-hand-holding-usd"></i> Resgates</a>
    <a href="logs.php" class="mobile-nav-link-alfonso <?= $current_page == 'logs.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Relatórios</a>
    <form method="POST" action="logout.php" style="margin-top: 1.5rem;">
        <button type="submit" class="mobile-logout-btn"><i class="fas fa-sign-out-alt"></i> Sair</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.querySelector(".hamburger-alfonso");
        const mobileNavOverlay = document.querySelector(".mobile-nav-overlay-alfonso");
        const body = document.body;

        body.classList.add('body-header-logado');

        if (hamburger && mobileNavOverlay) {
            hamburger.addEventListener("click", () => {
                const isActive = hamburger.classList.toggle("active");
                mobileNavOverlay.classList.toggle("active", isActive);
                body.classList.toggle('no-scroll', isActive);
            });

            document.querySelectorAll(".mobile-nav-link-alfonso, .mobile-logout-btn").forEach(link => {
                link.addEventListener("click", () => {
                    hamburger.classList.remove("active");
                    mobileNavOverlay.classList.remove("active");
                    body.classList.remove('no-scroll');
                });
            });
        }
    });
</script>