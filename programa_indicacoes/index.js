document.addEventListener("DOMContentLoaded", function () {
  // --- LÓGICA DO PRELOADER ---
  const preloader = document.querySelector(".preloader");
  window.addEventListener("load", () => {
    preloader.classList.add("hidden");
  });

  // --- ANIMAÇÕES GSAP ---
  gsap.registerPlugin(ScrollTrigger);

  // 1. Animação de Entrada do Hero
  const heroContent = document.querySelector(".hero-content");
  const heroTl = gsap.timeline({
    defaults: { ease: "power3.out" },
    delay: 0.5,
  });
  heroTl
    .from(".hero-title", { y: 60, autoAlpha: 0, duration: 1.2 })
    .from(".hero-subtitle", { y: 40, autoAlpha: 0, duration: 1 }, "-=0.8")
    .from(
      ".perk-card",
      { autoAlpha: 0, y: 50, stagger: 0.15, duration: 0.8 },
      "-=0.6"
    )
    .from(".btn-hero", { y: 30, autoAlpha: 0, duration: 0.8 }, "-=0.5");

  // 2. Animação dos Títulos de Seção
  document.querySelectorAll(".section-header").forEach((header) => {
    gsap.from(header.children, {
      y: 60,
      autoAlpha: 0,
      stagger: 0.2,
      duration: 1,
      ease: "power3.out",
      scrollTrigger: {
        trigger: header,
        start: "top 85%",
        toggleActions: "play none none reverse",
        // Previne o bug de re-animação em alguns cenários
        once: true,
      },
    });
  });

  // 3. Animação em Cascata para os Grids (Como Funciona e Benefícios)
  gsap.from(".steps-grid .step-card", {
    autoAlpha: 0,
    y: 80,
    stagger: 0.15,
    duration: 1,
    ease: "power3.out",
    scrollTrigger: {
      trigger: ".steps-grid",
      start: "top 85%",
      toggleActions: "play none none reverse",
      once: true,
    },
  });

  gsap.from(".benefits-grid .benefit-card", {
    autoAlpha: 0,
    y: 80,
    stagger: 0.15,
    duration: 1,
    ease: "power3.out",
    scrollTrigger: {
      trigger: ".benefits-grid",
      start: "top 85%",
      toggleActions: "play none none reverse",
      once: true,
    },
  });

  // 4. LÓGICA DA NOVA SEÇÃO DE VANTAGENS INTERATIVA
  const featureItems = document.querySelectorAll(".feature-item");
  const vantagemImages = document.querySelectorAll(".vantagem-image");
  const vantagensImageColumn = document.querySelector(
    ".vantagens-image-column"
  );

  // Certifica-se de que a primeira imagem está ativa ao carregar
  if (vantagemImages.length > 0) {
    vantagemImages[0].classList.add("active");
  }
  if (featureItems.length > 0) {
    featureItems[0].classList.add("is-active");
  }

  featureItems.forEach((item, index) => {
    ScrollTrigger.create({
      trigger: item,
      start: "top center+=100", // Ativa um pouco antes de o item chegar no centro
      end: "bottom center-=100", // Desativa um pouco depois de o item passar o centro
      // Garante que o ScrollTrigger só crie a animação se a coluna de imagens estiver visível (desktop)
      // e para evitar comportamento estranho em mobile onde a imagem não é sticky
      // use "snap: 1" para fazer o scroll "parar" no meio do item.
      // removedores de "snap" em mobile
      // markers: true, // Para debug, descomente para ver os marcadores do ScrollTrigger
      onToggle: (self) => {
        // Usamos onToggle para garantir que ative e desative corretamente
        if (self.isActive) {
          // Ativa o item de texto
          featureItems.forEach((el) => el.classList.remove("is-active"));
          item.classList.add("is-active");

          // Ativa a imagem correspondente
          const imageId = item.dataset.image;
          vantagemImages.forEach((img) => {
            img.classList.toggle("active", img.dataset.image === imageId);
          });
        }
      },
    });
  });

  // 5. Animação do CTA
  const ctaContent = document.querySelector(".cta-content");
  gsap.from(ctaContent.children, {
    autoAlpha: 0,
    y: 50,
    stagger: 0.2,
    duration: 1,
    ease: "power3.out",
    scrollTrigger: {
      trigger: ctaContent,
      start: "top 80%",
      toggleActions: "play none none reverse",
    },
  });
});
