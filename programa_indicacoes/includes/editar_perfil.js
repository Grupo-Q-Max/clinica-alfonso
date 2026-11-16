document.addEventListener("DOMContentLoaded", () => {
  // --- ANIMAÇÃO DE ENTRADA DA PÁGINA COM GSAP ---
  gsap.from(".profile-header", {
    autoAlpha: 0,
    y: -30,
    duration: 1,
    ease: "power3.out",
  });

  gsap.from(".profile-card", {
    autoAlpha: 0,
    y: 50,
    stagger: 0.2,
    duration: 1,
    ease: "power3.out",
    delay: 0.3,
  });
});
