document.addEventListener("DOMContentLoaded", function () {
  // --- LÓGICA DO ACORDEÃO PARA OS CARDS DE INDICAÇÃO ---
  const indicationCards = document.querySelectorAll(".indication-card");

  indicationCards.forEach((card) => {
    const mainInfo = card.querySelector(".card-main-info");
    const details = card.querySelector(".card-details");

    mainInfo.addEventListener("click", () => {
      // Fecha todos os outros cards abertos
      indicationCards.forEach((otherCard) => {
        if (otherCard !== card && otherCard.classList.contains("open")) {
          otherCard.classList.remove("open");
          otherCard.querySelector(".card-details").style.maxHeight = null;
        }
      });

      // Abre ou fecha o card clicado
      const isOpen = card.classList.toggle("open");
      if (isOpen) {
        details.style.maxHeight = details.scrollHeight + "px";
      } else {
        details.style.maxHeight = null;
      }
    });
  });

  // --- ANIMAÇÕES GSAP PARA A PÁGINA ---
  gsap.from(".page-header > *", {
    delay: 0.2,
    autoAlpha: 0,
    y: 30,
    stagger: 0.2,
    duration: 1,
    ease: "power3.out",
  });

  gsap.from(".summary-card", {
    autoAlpha: 0,
    y: 50,
    stagger: 0.1,
    duration: 0.8,
    ease: "power3.out",
    scrollTrigger: {
      trigger: ".summary-cards",
      start: "top 90%",
      toggleActions: "play none none reverse",
    },
  });

  gsap.from(".indication-card", {
    autoAlpha: 0,
    y: 50,
    stagger: 0.1,
    duration: 0.8,
    ease: "power3.out",
    scrollTrigger: {
      trigger: ".indications-list",
      start: "top 85%",
      toggleActions: "play none none reverse",
    },
  });
});
