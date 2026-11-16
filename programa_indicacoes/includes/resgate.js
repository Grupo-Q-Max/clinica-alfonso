document.addEventListener("DOMContentLoaded", () => {
  const checkGSAP = setInterval(() => {
    if (window.gsap) {
      clearInterval(checkGSAP);

      gsap.from(".resgate-wrapper", {
        delay: 0.2,
        autoAlpha: 0,
        y: 50,
        duration: 1.2,
        ease: "power3.out",
      });
    }
  }, 100);
});
