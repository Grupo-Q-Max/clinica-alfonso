document.addEventListener("DOMContentLoaded", function () {
  if (window.gsap) {
    gsap.from(".form-wrapper", {
      delay: 0.2,
      autoAlpha: 0,
      y: 50,
      duration: 1.2,
      ease: "power3.out",
    });
  }

  const resetForm = document.getElementById("reset-form");
  if (resetForm) {
    const button = document.getElementById("submit-btn");
    const messageArea = document.getElementById("message-area");

    resetForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const originalButtonText = button.textContent;
      button.disabled = true;
      button.textContent = "Enviando...";
      messageArea.style.display = "none";

      try {
        const response = await fetch("processos/processar_esqueceu_senha.php", {
          method: "POST",
          body: new FormData(resetForm),
        });
        const data = await response.json();

        messageArea.textContent = data.message;
        messageArea.className = data.success
          ? "message-box success"
          : "message-box error";
        messageArea.style.display = "block";

        if (data.success) {
          resetForm.querySelector('input[name="email"]').value = "";
        }
      } catch (error) {
        messageArea.textContent = "Erro de comunicação. Tente novamente.";
        messageArea.className = "message-box error";
        messageArea.style.display = "block";
        console.error("Erro no fetch:", error);
      } finally {
        button.disabled = false;
        button.textContent = originalButtonText;
      }
    });
  }
});
