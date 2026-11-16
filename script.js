document.addEventListener("DOMContentLoaded", function () {
  // --- 1. LÓGICA DO PRELOADER COM FALLBACK SEGURO ---
  const preloader = document.querySelector(".preloader");
  if (preloader) {
    const hidePreloader = () => {
      preloader.classList.add("hidden");
    };

    window.addEventListener("load", hidePreloader);
    setTimeout(hidePreloader, 2500);
  }

  // --- 2. HEADER E MENU MOBILE RESPONSIVO ---
  const header = document.querySelector(".header");
  const hamburger = document.querySelector(".hamburger");
  const mobileNavOverlay = document.querySelector(".mobile-nav-overlay");
  const mobileNavLinks = document.querySelectorAll(".mobile-nav-link, .mobile-menu-cta");
  const body = document.body;
  const backToTopBtn = document.getElementById("backToTop");

  // Scroll: Header e Botão Voltar ao Topo
  window.addEventListener("scroll", () => {
    const scrollPos = window.scrollY;
    if (header) {
      header.classList.toggle("scrolled", scrollPos > 40);
    }
    if (backToTopBtn) {
      backToTopBtn.classList.toggle("visible", scrollPos > 450);
    }
  });

  if (backToTopBtn) {
    backToTopBtn.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  // Toggle do menu mobile
  if (hamburger && mobileNavOverlay) {
    const toggleMenu = (open) => {
      const isActive = typeof open === "boolean" ? open : hamburger.classList.toggle("active");
      if (typeof open === "boolean") {
        hamburger.classList.toggle("active", open);
      }
      mobileNavOverlay.classList.toggle("active", isActive);
      body.classList.toggle("no-scroll", isActive);

      if (isActive && typeof gsap !== "undefined") {
        gsap.fromTo(
          mobileNavLinks,
          { autoAlpha: 0, y: 25 },
          {
            autoAlpha: 1,
            y: 0,
            duration: 0.4,
            stagger: 0.07,
            delay: 0.2,
            ease: "power2.out",
          }
        );
      }
    };

    hamburger.addEventListener("click", () => toggleMenu());
    hamburger.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        toggleMenu();
      }
    });

    mobileNavLinks.forEach((link) => {
      link.addEventListener("click", () => toggleMenu(false));
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && mobileNavOverlay.classList.contains("active")) {
        toggleMenu(false);
      }
    });
  }

  // --- 3. ACCORDION DO FAQ (DÚVIDAS FREQUENTES) ---
  const faqItems = document.querySelectorAll(".faq-item");
  faqItems.forEach((item) => {
    const questionBtn = item.querySelector(".faq-question");
    if (questionBtn) {
      questionBtn.addEventListener("click", () => {
        const isOpen = item.classList.contains("active");

        // Fecha os outros itens abertos para manter o design limpo
        faqItems.forEach((other) => {
          if (other !== item) other.classList.remove("active");
        });

        // Alterna o item atual
        item.classList.toggle("active", !isOpen);
      });
    }
  });

  // --- 4. ANIMAÇÕES GSAP & SCROLLTRIGGER ---
  if (typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined") {
    gsap.registerPlugin(ScrollTrigger);

    // Hero Timeline
    const heroContent = document.querySelector(".hero-content");
    if (heroContent) {
      const heroTl = gsap.timeline({ defaults: { ease: "power3.out" } });
      heroTl
        .from(".hero-badge", { y: 20, autoAlpha: 0, duration: 0.7, delay: 0.1 })
        .from(".hero-title", { y: 40, autoAlpha: 0, duration: 1 }, "-=0.4")
        .from(".hero-subtitle", { y: 30, autoAlpha: 0, duration: 0.8 }, "-=0.6")
        .from(".hero-actions", { y: 25, autoAlpha: 0, duration: 0.8 }, "-=0.5");
    }

    // Títulos de Seção
    document.querySelectorAll(".section-header").forEach((secHeader) => {
      gsap.from(secHeader.children, {
        y: 40,
        autoAlpha: 0,
        stagger: 0.14,
        duration: 0.85,
        ease: "power2.out",
        scrollTrigger: {
          trigger: secHeader,
          start: "top 88%",
          toggleActions: "play none none reverse",
        },
      });
    });

    // Cards de Diferenciais
    if (document.querySelector(".features-grid")) {
      gsap.from(".feature-item", {
        y: 50,
        autoAlpha: 0,
        stagger: 0.15,
        duration: 0.8,
        ease: "power2.out",
        scrollTrigger: {
          trigger: ".features-grid",
          start: "top 85%",
          toggleActions: "play none none reverse",
        },
      });
    }

    // Seção Sobre (Bem-vindo)
    const bemVindoSection = document.querySelector(".bem-vindo");
    if (bemVindoSection) {
      gsap.from(".bem-vindo-conteudo > *", {
        x: -40,
        autoAlpha: 0,
        stagger: 0.12,
        duration: 0.85,
        ease: "power2.out",
        scrollTrigger: {
          trigger: bemVindoSection,
          start: "top 80%",
          toggleActions: "play none none reverse",
        },
      });

      gsap.from(".bem-vindo-imagem", {
        x: 40,
        autoAlpha: 0,
        duration: 1,
        ease: "power2.out",
        scrollTrigger: {
          trigger: bemVindoSection,
          start: "top 80%",
          toggleActions: "play none none reverse",
        },
      });
    }

    // Cards de Tratamentos / Serviços
    if (document.querySelector(".services-grid")) {
      gsap.from(".service-card", {
        y: 50,
        autoAlpha: 0,
        stagger: 0.12,
        duration: 0.8,
        ease: "power2.out",
        scrollTrigger: {
          trigger: ".services-grid",
          start: "top 85%",
          toggleActions: "play none none reverse",
        },
      });
    }

    // Seção Parceiro / Experiência Real
    const partnerSection = document.querySelector(".partner-section");
    if (partnerSection) {
      gsap.from(".partner-video-col", {
        x: -40,
        autoAlpha: 0,
        duration: 0.9,
        ease: "power2.out",
        scrollTrigger: {
          trigger: partnerSection,
          start: "top 82%",
          toggleActions: "play none none reverse",
        },
      });

      gsap.from(".partner-text-col > *", {
        x: 40,
        autoAlpha: 0,
        stagger: 0.12,
        duration: 0.85,
        ease: "power2.out",
        scrollTrigger: {
          trigger: partnerSection,
          start: "top 82%",
          toggleActions: "play none none reverse",
        },
      });
    }

    // Passos da Abordagem
    if (document.querySelector(".steps-container")) {
      gsap.from(".step-item", {
        y: 40,
        autoAlpha: 0,
        stagger: 0.18,
        duration: 0.85,
        ease: "power2.out",
        scrollTrigger: {
          trigger: ".steps-container",
          start: "top 85%",
          toggleActions: "play none none reverse",
        },
      });
    }

    // Itens do FAQ
    if (document.querySelector(".faq-container")) {
      gsap.from(".faq-item", {
        y: 35,
        autoAlpha: 0,
        stagger: 0.12,
        duration: 0.75,
        ease: "power2.out",
        scrollTrigger: {
          trigger: ".faq-container",
          start: "top 85%",
          toggleActions: "play none none reverse",
        },
      });
    }

    // Cards de Contato
    if (document.querySelector(".contact-cards-grid")) {
      gsap.from(".contact-card", {
        y: 40,
        autoAlpha: 0,
        stagger: 0.15,
        duration: 0.8,
        ease: "power2.out",
        scrollTrigger: {
          trigger: ".contact-cards-grid",
          start: "top 85%",
          toggleActions: "play none none reverse",
        },
      });
    }
  }

  // --- 5. LIGHTBOX UNIFICADO E RESPONSIVO ---
  const galeriaImgs = document.querySelectorAll(".galeria-card img");
  if (galeriaImgs.length > 0) {
    const lightbox = document.createElement("div");
    lightbox.className = "site-lightbox";
    lightbox.innerHTML = `
      <button class="site-lightbox-close" aria-label="Fechar">&times;</button>
      <img src="" alt="Imagem ampliada">
    `;
    document.body.appendChild(lightbox);

    const lbImg = lightbox.querySelector("img");
    const lbClose = lightbox.querySelector(".site-lightbox-close");

    const openLightbox = (src, alt) => {
      lbImg.src = src;
      lbImg.alt = alt || "Imagem da galeria";
      lightbox.classList.add("open");
      body.classList.add("no-scroll");
    };

    const closeLightbox = () => {
      lightbox.classList.remove("open");
      body.classList.remove("no-scroll");
    };

    galeriaImgs.forEach((img) => {
      img.addEventListener("click", () => {
        openLightbox(img.src, img.alt);
      });
    });

    lbClose.addEventListener("click", closeLightbox);
    lightbox.addEventListener("click", (e) => {
      if (e.target === lightbox) {
        closeLightbox();
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && lightbox.classList.contains("open")) {
        closeLightbox();
      }
    });
  }

  // --- 6. CONTROLE DO VÍDEO DO HERO ---
  const heroVideo = document.querySelector(".hero-video");
  if (heroVideo) {
    heroVideo.addEventListener("ended", () => {
      heroVideo.currentTime = 0.05;
      heroVideo.play().catch(() => {});
    });
  }
});
