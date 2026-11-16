document.addEventListener("DOMContentLoaded", function () {
  // --- LÓGICA DOS MODAIS (Pop-ups) ---
  const modals = document.querySelectorAll(".modal-overlay");

  window.openModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add("active");
      document.body.style.overflow = "hidden";
    }
  };

  window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove("active");
      document.body.style.overflow = "";
    }
  };

  modals.forEach((modal) => {
    modal.addEventListener("click", (event) => {
      if (event.target === modal) {
        closeModal(modal.id);
      }
    });
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      modals.forEach((modal) => closeModal(modal.id));
    }
  });

  // --- LÓGICA PARA COPIAR E COMPARTILHAR O LINK (ADICIONADA) ---
  window.copyLink = function () {
    const linkInput = document.getElementById("referralLinkInput");
    const successMessage = document.getElementById("copy-success");
    if (!linkInput || !successMessage) return;

    linkInput.select();
    linkInput.setSelectionRange(0, 99999); // Para mobile

    try {
      document.execCommand("copy");
      successMessage.classList.add("visible");
      setTimeout(() => {
        successMessage.classList.remove("visible");
      }, 2000);
    } catch (err) {
      console.error("Falha ao copiar o link: ", err);
    }
  };

  window.shareLink = function (platform) {
    const linkInput = document.getElementById("referralLinkInput");
    if (!linkInput) return;

    const link = encodeURIComponent(linkInput.value);
    const text = encodeURIComponent(
      "Olá! Conheça a Clínica Alfonso, um lugar de excelência em cuidado odontológico. Use meu link para saber mais:"
    );
    const subject = encodeURIComponent(
      "Uma indicação especial para você: Clínica Alfonso"
    );

    let shareUrl;
    switch (platform) {
      case "whatsapp":
        shareUrl = `https://api.whatsapp.com/send?text=${text}%20${link}`;
        break;
      case "telegram":
        shareUrl = `https://t.me/share/url?url=${link}&text=${text}`;
        break;
      case "email":
        shareUrl = `mailto:?subject=${subject}&body=${text}%20${link}`;
        break;
      default:
        return;
    }
    window.open(shareUrl, "_blank", "noopener,noreferrer");
  };

  // --- ANIMAÇÕES GSAP PARA A PÁGINA (COM SEÇÃO DE LINK) ---
  gsap.registerPlugin(ScrollTrigger);

  gsap.from(".welcome-content > *", {
    delay: 0.3,
    autoAlpha: 0,
    y: 50,
    stagger: 0.2,
    duration: 1,
    ease: "power3.out",
  });

  // Animação para a seção do link de indicação, se ela existir
  if (document.querySelector(".referral-link-section")) {
    gsap.from(".referral-link-section > *", {
      autoAlpha: 0,
      y: 50,
      stagger: 0.1,
      duration: 1,
      ease: "power3.out",
      scrollTrigger: {
        trigger: ".referral-link-section",
        start: "top 85%",
        toggleActions: "play none none reverse",
      },
    });
  }

  gsap.from(".summary-card", {
    autoAlpha: 0,
    y: 50,
    stagger: 0.1,
    duration: 0.8,
    ease: "power3.out",
    scrollTrigger: {
      trigger: ".summary-section",
      start: "top 85%",
      toggleActions: "play none none reverse",
    },
  });

  gsap.from(".financial-section > *", {
    autoAlpha: 0,
    y: 50,
    stagger: 0.2,
    duration: 1,
    ease: "power3.out",
    scrollTrigger: {
      trigger: ".financial-section",
      start: "top 85%",
      toggleActions: "play none none reverse",
    },
  });
});
