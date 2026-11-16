document.addEventListener("DOMContentLoaded", () => {
  // --- SELETORES DE ELEMENTOS ---
  const btnEditar = document.getElementById("btnEditarPerfil");
  const btnDesativarConta = document.getElementById("btnDesativarConta");

  // Modal de Confirmação de Desativação
  const modalConfirmarDesativacao = document.getElementById(
    "modalConfirmarDesativacao"
  );
  const btnCancelarDesativacao = document.getElementById(
    "btnCancelarDesativacao"
  );
  const btnConfirmarDesativacao = document.getElementById(
    "btnConfirmarDesativacao"
  );

  // Modal de Token
  const modalToken = document.getElementById("modalToken");
  const modalTokenTitle = document.getElementById("modalTokenTitle");
  const modalTokenMessage = document.getElementById("modalTokenMessage");
  const btnConfirmarToken = document.getElementById("btnConfirmarToken");
  const btnCancelarToken = document.getElementById("btnCancelarToken");
  const inputToken = document.getElementById("inputToken");

  // Modal de Sucesso
  const modalSucesso = document.getElementById("modalSucesso");
  const modalSucessoMessage = document.getElementById("modalSucessoMessage");

  // Elementos de Feedback (spinners, mensagens de erro/sucesso)
  const spinnerRequest = document.getElementById("loadingSpinnerRequest");
  const spinnerVerify = document.getElementById("loadingSpinnerVerify");
  const tokenErrorP = document.getElementById("tokenError");
  const tokenSuccessP = document.getElementById("tokenSuccess");

  let currentAction = ""; // Variável para saber qual ação (editar ou desativar) disparou o token

  // Funções auxiliares para mostrar/esconder
  const show = (element) => (element.style.display = "flex");
  const hide = (element) => (element.style.display = "none");

  const openModal = (modal) => modal.classList.add("active");
  const closeModal = (modal) => modal.classList.remove("active");

  const resetTokenModal = () => {
    hide(spinnerRequest);
    hide(spinnerVerify);
    tokenErrorP.textContent = "";
    tokenSuccessP.textContent = "";
    inputToken.value = "";
    btnConfirmarToken.disabled = false;
  };

  // --- FLUXO PARA EDITAR PERFIL ---
  if (btnEditar) {
    btnEditar.addEventListener("click", () => {
      currentAction = "editar_perfil";
      resetTokenModal();
      modalTokenTitle.textContent = "Confirmação para Editar Perfil";
      modalTokenMessage.textContent =
        "Um código foi enviado ao seu e-mail. Insira-o para prosseguir com a edição.";
      openModal(modalToken);
      requestToken();
    });
  }

  // --- FLUXO PARA DESATIVAR CONTA ---
  if (btnDesativarConta) {
    btnDesativarConta.addEventListener("click", () => {
      openModal(modalConfirmarDesativacao);
    });
  }
  if (btnCancelarDesativacao) {
    btnCancelarDesativacao.addEventListener("click", () => {
      closeModal(modalConfirmarDesativacao);
    });
  }
  if (btnConfirmarDesativacao) {
    btnConfirmarDesativacao.addEventListener("click", () => {
      closeModal(modalConfirmarDesativacao);
      currentAction = "desativar_conta";
      resetTokenModal();
      modalTokenTitle.textContent = "Confirmação para Desativar Conta";
      modalTokenMessage.textContent =
        "Para sua segurança, insira o código enviado ao seu e-mail para confirmar a desativação.";
      openModal(modalToken);
      requestToken();
    });
  }

  // --- FUNÇÕES DE TOKEN (REUTILIZÁVEIS) ---
  async function requestToken() {
    show(spinnerRequest);
    try {
      const response = await fetch(
        "processos/processar_envio_token_perfil.php",
        { method: "POST" }
      );
      const data = await response.json();
      hide(spinnerRequest);
      if (data.success) {
        tokenSuccessP.textContent =
          "Código enviado com sucesso! Verifique seu e-mail.";
      } else {
        tokenErrorP.textContent = data.message || "Falha ao enviar o código.";
      }
    } catch (error) {
      hide(spinnerRequest);
      tokenErrorP.textContent = "Erro de comunicação com o servidor.";
    }
  }

  async function verifyToken() {
    const token = inputToken.value;
    if (token.length !== 6) {
      tokenErrorP.textContent = "O código deve ter 6 dígitos.";
      return;
    }
    tokenErrorP.textContent = "";
    show(spinnerVerify);
    btnConfirmarToken.disabled = true;

    const formData = new FormData();
    formData.append("token", token);
    formData.append("motivo", currentAction); // Envia a ação atual para o backend

    try {
      // Este script de backend precisa saber o que fazer com base no 'motivo'
      const response = await fetch("processos/verificar_token.php", {
        method: "POST",
        body: formData,
      });
      const data = await response.json();
      hide(spinnerVerify);

      if (data.success && data.redirect) {
        closeModal(modalToken);
        modalSucessoMessage.textContent =
          data.message || "Ação confirmada! Redirecionando...";
        openModal(modalSucesso);
        setTimeout(() => {
          window.location.href = data.redirect;
        }, 2000);
      } else {
        tokenErrorP.textContent =
          data.message || "Código inválido ou expirado.";
        btnConfirmarToken.disabled = false;
      }
    } catch (error) {
      hide(spinnerVerify);
      tokenErrorP.textContent = "Erro ao verificar o código.";
      btnConfirmarToken.disabled = false;
    }
  }

  if (btnConfirmarToken) {
    btnConfirmarToken.addEventListener("click", verifyToken);
  }
  if (btnCancelarToken) {
    btnCancelarToken.addEventListener("click", () => closeModal(modalToken));
  }

  // --- ANIMAÇÕES DE ENTRADA DA PÁGINA ---
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
  gsap.from(".deactivate-section", {
    autoAlpha: 0,
    y: 50,
    duration: 1,
    ease: "power3.out",
    scrollTrigger: { trigger: ".deactivate-section", start: "top 95%" },
  });
});
