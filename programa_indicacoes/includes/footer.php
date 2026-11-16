<style>
    .footer {
        background-color: #000;
        color: #fff;
        text-align: center;
    }

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .footer-logo img {
        max-width: 60px;
        margin-bottom: 1px;
        transition: transform 0.2s ease;
    }

    .footer-logo-img:hover {
        transform: scale(1.1);
        filter: brightness(1.3);
    }

    .footer-columns {
        display: flex;
        justify-content: space-between;
        width: 100%;
        max-width: 1200px;
    }

    .footer-column {
        flex: 1;
    }

    .footer-column h4 {
        margin-bottom: 10px;
        font-size: 20px;
        color: #19b419;
    }

    .footer-column ul {
        list-style: none;
        padding: 0;
    }

    .footer-column ul li {
        margin: 5px 0;
    }

    .footer-column ul li a {
        color: #fff;
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer-column ul li a:hover {
        color: #0f0;
    }

    .footer-bottom {
        background-color: #000000;
        padding: 16px 0;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .social-media {
        margin-bottom: 0px;
    }

    .social-media a {
        margin: 0 10px;
        font-size: 18px;
        text-decoration: none;
    }

    .social-icon {
        width: 35px;
        height: 35px;
        transition: transform 0.2s ease;
    }

    .social-icon:hover {
        transform: scale(1.1);
        filter: brightness(1.5);
    }

    .footer-bottom p {
        font-size: 14px;
    }

    .footer-links {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .footer-links a {
        color: #fff;
        text-decoration: none;
        font-size: 14px;
    }

    .footer-links a:hover {
        color: #000000;
    }

    @media (max-width: 768px) {
        .footer-columns {
            display: flex;
            width: 100%;
            max-width: 500px;
            margin-bottom: 20px;
        }
    }

    @media (max-width: 480px) {
        .footer-columns {
            display: flex;
            width: 100%;
            max-width: 500px;
            margin-bottom: 20px;
        }
    }

    a {
        text-decoration: none;
        color: #fff;
    }

    a:hover {
        cursor: pointer;
        color: #0f0;
        text-shadow: 0px 0px 3px #0f0, 0px 0px 6px #0f0;
    }
</style>

<footer class="footer">
    <!---------------------------------------------- Suas colunas de sobre, contato, localização ------------------------------------>
    <div class="footer-bottom">
        <div class="social-media">
            <a class="footer-logo" href="https://grupoqmax.com.br/grupoqmax/" target="_blank">
                <img src="imagens/qx back logo grupo.webp" alt="Logo QXBACK" class="footer-logo-img">
            </a>
            <a href="https://www.facebook.com/quexisback/" target="_blank">
                <img src="imagens/social_12942327.webp" alt="Facebook" class="social-icon">
            </a>
            <a href="https://www.linkedin.com/company/grupoqmax/" target="_blank">
                <img src="imagens/linkedin_3991775.webp" alt="LinkedIn" class="social-icon">
            </a>
            <a href="https://www.instagram.com/qx.back/" target="_blank">
                <img src="imagens/instagram_2111463.webp" alt="Instagram" class="social-icon">
            </a>
        </div>
        <p>Grupo Q-Max © 2025 – Todos direitos reservados - 12.552.249/0001-99 -
            <a href="politica.html" target="_blank">Termos de Privacidade</a>.
        </p>
        <p>Desenvolvido por <a class="a" href="https://grupoqmax.com.br/marketing" target="_blank">Q-Max Marketing</a></p>
    </div>
</footer>