document.addEventListener('DOMContentLoaded', function() {
    // Função para animar o círculo de progresso
    function animateProgressCircle() {
        const circle = document.querySelector('.progress-circle');
        if (!circle) return;

        const fill = circle.querySelector('.progress-circle-fill');
        const percent = circle.dataset.percent;
        const radius = fill.r.baseVal.value;
        const circumference = 2 * Math.PI * radius;

        // Animação com GSAP
        gsap.fromTo(fill, 
            { strokeDashoffset: circumference },
            { 
                strokeDashoffset: circumference - (percent / 100) * circumference,
                duration: 2,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: circle,
                    start: "top 85%",
                    toggleActions: "play none none none"
                }
            }
        );
    }
    
    // Inicia a animação do círculo
    animateProgressCircle();

    // Animação de entrada dos cards
    if (window.gsap) {
        gsap.from(".summary-card, .chart-card, .action-card", {
            delay: 0.2,
            autoAlpha: 0,
            y: 50,
            stagger: 0.1,
            duration: 1,
            ease: "power3.out"
        });
    }
});