<?php
session_start();
$usuario_logado = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dúvidas Frequentes | Alfonso Rewards</title>
    <link rel="stylesheet" href="faq.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>
    <script src="faq.js" defer></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <section class="hero-faq">
            <div class="hero-content">
                <h1 class="hero-title">Dúvidas Frequentes</h1>
                <p class="hero-subtitle">
                    Tudo o que você precisa saber sobre o Alfonso Rewards. Encontre aqui as respostas para as perguntas mais comuns sobre nosso programa de indicações.
                </p>
            </div>
        </section>

        <section class="section-container">
            <div class="faq-container">
                <div class="faq-item">
                    <button class="faq-question">
                        <span>O que é o Alfonso Rewards?</span>
                        <i class="fas fa-chevron-down faq-toggle"></i>
                    </button>
                    <div class="faq-answer">
                        <p>É o programa de recompensas exclusivo da Clínica Alfonso, criado para agradecer e bonificar as pessoas que indicam nossos tratamentos, transformando confiança em benefícios reais, como cashback e descontos.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>Quem pode participar do programa?</span>
                        <i class="fas fa-chevron-down faq-toggle"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Qualquer pessoa acima de 18 anos pode participar! Você não precisa ser um paciente da clínica para indicar nossos serviços e ganhar recompensas.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>Como eu começo a indicar?</span>
                        <i class="fas fa-chevron-down faq-toggle"></i>
                    </button>
                    <div class="faq-answer">
                        <p>É simples! Basta criar sua conta no portal Alfonso Rewards, acessar seu painel exclusivo e preencher os dados do contato que você deseja indicar para um de nossos tratamentos.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>Quais tratamentos posso indicar?</span>
                        <i class="fas fa-chevron-down faq-toggle"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Você pode indicar qualquer um dos nossos principais tratamentos, como Implantes Dentários, Lentes de Contato, Clareamento Dental, entre outros. As recompensas variam de acordo com o tratamento indicado.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>Como eu recebo minhas recompensas?</span>
                        <i class="fas fa-chevron-down faq-toggle"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Assim que a pessoa indicada iniciar o tratamento e o primeiro pagamento for confirmado, sua recompensa será creditada em seu painel. Você poderá então solicitar o resgate do valor diretamente para sua conta bancária.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-question">
                        <span>Meu amigo indicado também ganha algo?</span>
                        <i class="fas fa-chevron-down faq-toggle"></i>
                    </button>
                    <div class="faq-answer">
                        <p>Sim! Para tornar a indicação ainda mais especial, seu amigo receberá um desconto exclusivo ou um benefício no primeiro tratamento ao mencionar que foi indicado por você através do programa.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-container cta-faq">
            <div class="cta-content">
                <h2 class="section-title">Ainda tem dúvidas?</h2>
                <p class="section-subtitle">Nossa equipe está pronta para ajudar. Entre em contato conosco e teremos o prazer de esclarecer qualquer outra questão.</p>
                <a href="contato.php" class="btn-primary">Fale Conosco</a>
            </div>
        </section>
    </main>
    <script src="includes/script.js"></script>

        <script>
        document.querySelectorAll('.faq-question').forEach(button => {

            button.addEventListener('click', () => {

                const faqItem = button.parentElement;

                const answer = faqItem.querySelector('.faq-answer');

                const toggle = button.querySelector('.faq-toggle');



                document.querySelectorAll('.faq-item').forEach(item => {

                    if (item !== faqItem) {

                        item.classList.remove('active');

                        item.querySelector('.faq-answer').style.maxHeight = null;


                    }

                });



                faqItem.classList.toggle('active');


                answer.style.maxHeight = faqItem.classList.contains('active') ?

                    answer.scrollHeight + 'px' :

                    null;

            });

        });



        window.addEventListener('load', () => {

            document.querySelectorAll('.faq-item').forEach(item => {

                item.classList.remove('active');

                item.querySelector('.faq-answer').style.maxHeight = null;


            });

        });
    </script>
    <?php include '../footer.php'; ?>

</body>

</html>