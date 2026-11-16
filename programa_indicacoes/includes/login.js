document.addEventListener("DOMContentLoaded", function () {
  const checkGSAP = setInterval(() => {
    if (window.gsap) {
      clearInterval(checkGSAP);

      gsap.from(".form-wrapper", {
        delay: 0.2,
        autoAlpha: 0,
        y: 50,
        duration: 1.2,
        ease: "power3.out",
      });
    }
  }, 100);

  const loginForm = document.getElementById("login-form");
  if (!loginForm) return;

  const submitButton = loginForm.querySelector(".btn-primary");

  const popup = document.getElementById("feedback-popup");
  const popupTitle = document.getElementById("popup-title");
  const popupMessage = document.getElementById("popup-message");
  const popupOkBtn = document.getElementById("popup-ok-btn");

  function showPopup(title, message, isError = false) {
    if (!popup || !popupTitle || !popupMessage) return;

    popupTitle.textContent = title;
    popupMessage.textContent = message;

    popupTitle.classList.toggle("popup-title-error", isError);
    popupTitle.classList.toggle("popup-title-success", !isError);

    popup.classList.add("active");
  }

  if (popupOkBtn) {
    popupOkBtn.addEventListener("click", () =>
      popup.classList.remove("active")
    );
  }
  if (popup) {
    popup.addEventListener("click", (event) => {
      if (event.target === popup) {
        popup.classList.remove("active");
      }
    });
  }

  loginForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const originalButtonText = submitButton.textContent;
    submitButton.disabled = true;

    submitButton.classList.add("loading");
    submitButton.innerHTML = '<span class="spinner"></span>';

    try {
      const formData = new FormData(loginForm);
      const response = await fetch("processos/processar_login.php", {
        method: "POST",
        body: formData,
      });
      const data = await response.json();
      submitButton.classList.remove("loading");

      if (data.success && data.redirect) {
        submitButton.innerHTML = `<span>Sucesso!</span>`;
        setTimeout(() => {
          window.location.href = data.redirect;
        }, 800);
      } else {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
        showPopup(
          "Atenção",
          data.message || "Ocorreu um erro desconhecido.",
          true
        );
      }
    } catch (error) {
      submitButton.classList.remove("loading");
      submitButton.disabled = false;
      submitButton.textContent = originalButtonText;
      showPopup(
        "Erro de Rede",
        "Não foi possível conectar ao servidor. Verifique sua conexão.",
        true
      );
      console.error("Fetch Error:", error);
    }
  });


});
