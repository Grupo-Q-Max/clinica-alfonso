<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuario_logado = isset($_SESSION['user_id']);
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

    body {
        padding-top: 100px;
        transition: padding-top var(--transition);
        font-family: var(--body-font);
    }

    body.scrolled {
        padding-top: 80px;
    }

    body.no-scroll {
        overflow: hidden;
    }

    .header-alfonso {
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        background-color: var(--dark-teal);
        box-shadow: var(--shadow-soft);
        padding: 15px 0;
        transition: padding var(--transition);
    }

    .navbar-alfonso {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 5%;
    }

    .nav-logo-alfonso img {
        height: 70px;
        filter: brightness(0) invert(1);
        transition: height var(--transition);
    }

    body.scrolled .header-alfonso {
        padding: 5px 0;
    }

    body.scrolled .nav-logo-alfonso img {
        height: 50px;
    }

    .nav-menu-alfonso {
        display: flex;
        gap: 2.5rem;
    }

    .nav-link-alfonso {
        color: var(--text-on-dark);
        font-weight: 700;
        font-size: 1.1rem;
        text-decoration: none;
        position: relative;
        padding-bottom: 8px;
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

    .mobile-nav-overlay-alfonso .mobile-nav-link-alfonso {
        color: var(--ivory-white);
        font-size: 2.5rem;
        font-family: var(--heading-font);
        margin: 1rem 0;
        text-decoration: none;
    }

    .mobile-nav-overlay-alfonso .mobile-nav-link-alfonso.active {
        color: var(--soft-gold);
    }

    @media (max-width: 992px) {
        .nav-menu-alfonso {
            display: none;
        }

        .hamburger-alfonso {
            display: block;
        }

        body {
            padding-top: 80px;
        }

        .nav-logo-alfonso img {
            height: 50px;
        }
    }
</style>

<header class="header-alfonso">
    <div class="navbar-alfonso">
        <a href="../index.html" class="nav-logo-alfonso">
            <img src="../imagens/logo_sem_fundo1.png" alt="Logo Clínica Alfonso">
        </a>
        <nav class="nav-menu-alfonso">
            <a href="../index.html" class="nav-link-alfonso <?= $current_page == '../index.html' ? 'active' : '' ?>">Home</a>
            <a href="index.php" class="nav-link-alfonso <?= $current_page == 'index.php' ? 'active' : '' ?>">Alfonso Rewards</a>
            <a href="sobre.php" class="nav-link-alfonso <?= $current_page == 'sobre.php' ? 'active' : '' ?>">Sobre Nós</a>
            <a href="faq.php" class="nav-link-alfonso <?= $current_page == 'faq.php' ? 'active' : '' ?>">FAQ</a>

            <?php if ($usuario_logado): ?>
                <a href="perfil.php" class="nav-link-alfonso <?= $current_page == 'perfil.php' ? 'active' : '' ?>">Meu Perfil</a>
                <a href="logout.php" class="nav-link-alfonso">Sair</a>
            <?php else: ?>
                <a href="cadastrar.php" class="nav-link-alfonso <?= $current_page == 'cadastrar.php' ? 'active' : '' ?>">Cadastre-se</a>
                <a href="login.php" class="nav-link-alfonso <?= $current_page == 'login.php' ? 'active' : '' ?>">Login</a>
            <?php endif; ?>
        </nav>
        <div class="hamburger-alfonso">
            <span class="bar-alfonso"></span>
            <span class="bar-alfonso"></span>
            <span class="bar-alfonso"></span>
        </div>
    </div>
</header>

<div class="mobile-nav-overlay-alfonso">
    <a href="index.php" class="mobile-nav-link-alfonso <?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a>
    <a href="sobre.php" class="mobile-nav-link-alfonso <?= $current_page == 'sobre.php' ? 'active' : '' ?>">Sobre Nós</a>
    <a href="faq.php" class="mobile-nav-link-alfonso <?= $current_page == 'faq.php' ? 'active' : '' ?>">FAQ</a>

    <?php if ($usuario_logado): ?>
        <a href="perfil.php" class="mobile-nav-link-alfonso <?= $current_page == 'perfil.php' ? 'active' : '' ?>">Meu Perfil</a>
        <a href="logout.php" class="mobile-nav-link-alfonso">Sair</a>
    <?php else: ?>
        <a href="cadastrar.php" class="mobile-nav-link-alfonso <?= $current_page == 'cadastrar.php' ? 'active' : '' ?>">Cadastre-se</a>
        <a href="login.php" class="mobile-nav-link-alfonso <?= $current_page == 'login.php' ? 'active' : '' ?>">Login</a>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const body = document.body;
        const hamburger = document.querySelector(".hamburger-alfonso");
        const mobileNavOverlay = document.querySelector(".mobile-nav-overlay-alfonso");
        const mobileNavLinks = document.querySelectorAll(".mobile-nav-link-alfonso");

        window.addEventListener("scroll", () => {
            body.classList.toggle("scrolled", window.scrollY > 50);
        });

        hamburger.addEventListener("click", () => {
            const isActive = hamburger.classList.toggle("active");
            mobileNavOverlay.classList.toggle("active", isActive);
            body.classList.toggle('no-scroll', isActive); 
        });

        mobileNavLinks.forEach(link => {
            link.addEventListener("click", () => {
                hamburger.classList.remove("active");
                mobileNavOverlay.classList.remove("active");
                body.classList.remove('no-scroll');
            });
        });
    });
</script>