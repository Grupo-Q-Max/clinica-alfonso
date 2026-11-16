    <style>
        .footer {
            background-color: var(--dark-teal);
            color: var(--ivory-white);
            padding: 6rem 5%;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .footer-col {
            flex: 1;
            min-width: 280px;
        }

        .footer-logo-col {
            display: flex;
            justify-content: flex-start;
        }

        .footer-logo {
            height: 120px;
            filter: brightness(0) invert(1);
            transition: transform var(--transition);
        }

        .footer-logo:hover {
            transform: scale(1.05);
        }

        .footer-contact-col {
            text-align: center;
        }

        .footer-col h4 {
            font-family: var(--heading-font);
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
            color: var(--soft-gold);
            font-weight: 700;
        }

        .footer-contact-col p {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1rem;
            color: rgba(252, 252, 252, 0.9);
            font-size: 1.1rem;
        }

        .footer-contact-col p i {
            color: var(--soft-gold);
        }

        .footer-social-col {
            text-align: right;
        }

        .social-icons-footer {
            display: flex;
            justify-content: flex-end;
            gap: 1.5rem;
        }

        .social-icons-footer a {
            color: var(--ivory-white);
            font-size: 1.8rem;
            transition: color var(--transition), transform var(--transition);
        }

        .social-icons-footer a:hover {
            color: var(--soft-gold);
            transform: translateY(-4px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 4rem;
            margin-top: 4rem;
            border-top: 1px solid rgba(252, 252, 252, 0.2);
            font-size: 1rem;
            color: rgba(252, 252, 252, 0.7);
        }
    </style>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-col footer-logo-col">
                <a href="#inicio"><img src="imagens/logo_sem_fundo1.png" alt="Logo Clínica Alfonso Rodapé"
                        class="footer-logo"></a>
            </div>
            <div class="footer-col footer-contact-col">
                <h4>Contato</h4>
                <p><i class="fas fa-map-marker-alt"></i> Rua Catiguá,159, conjunto 518 - Tatuapé, SP</p>
                <p><i class="fas fa-phone"></i> (11) 96768-7666</p>
                <p><i class="fas fa-envelope"></i> contato@clinicaalfonso.com.br</p>
            </div>
            <div class="footer-col footer-social-col">
                <h4>Siga-nos</h4>
                <div class="social-icons-footer">
                    <a href="https://www.instagram.com/dranayaraalfonso/" aria-label="Instagram"><i class="fab fa-instagram"></i></a>

                    <a href="https://wa.me/5511967687666" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Clínica Alfonso. Todos os direitos reservados.</p>
        </div>
    </footer>