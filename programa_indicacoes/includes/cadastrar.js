document.addEventListener("DOMContentLoaded", function () {
  // --- ANIMAÇÃO DE ENTRADA DA PÁGINA ---
  // Garante que o GSAP esteja pronto antes de usar
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

  // --- SELETORES DOS ELEMENTOS DO FORMULÁRIO ---
  const form = document.getElementById("form-cadastro");
  const cpfInput = document.getElementById("cpf");
  const telefoneInput = document.getElementById("telefone");
  const senhaInput = document.getElementById("senha");
  const confirmarSenhaInput = document.getElementById("confirmar-senha");
  const errorSenhaDiv = document.getElementById("error-senha");
  const errorConfirmSenhaDiv = document.getElementById("error-confirmSenha");

  // --- MÁSCARAS INTELIGENTES (MAIS ROBUSTAS) ---

  function formatarTelefone(event) {
    let input = event.target;
    let value = input.value.replace(/\D/g, "");
    value = value.substring(0, 11); // Limita a 11 dígitos (celular com 9)
    if (value.length > 10) {
      value = value.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1) $2-$3");
    } else if (value.length > 5) {
      value = value.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "($1) $2-$3");
    } else if (value.length > 2) {
      value = value.replace(/^(\d\d)(\d{0,5}).*/, "($1) $2");
    } else {
      value = value.replace(/^(\d*)/, "($1");
    }
    input.value = value;
  }

  function formatarCPF(event) {
    let input = event.target;
    let value = input.value.replace(/\D/g, "");
    value = value.substring(0, 11);
    value = value.replace(/(\d{3})(\d)/, "$1.$2");
    value = value.replace(/(\d{3})(\d)/, "$1.$2");
    value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    input.value = value;
  }

  // --- VALIDAÇÃO EM TEMPO REAL ---

  function validarSenhas() {
    const senha = senhaInput.value;
    const confirmSenha = confirmarSenhaInput.value;
    let senhasCoincidem = true;
    let senhaForte = true;

    // 1. Validação de Força da Senha
    if (senha.length > 0) {
      const forcaValida =
        senha.length >= 7 &&
        /[A-Z]/.test(senha) &&
        /[0-9]/.test(senha) &&
        /[!@#$%^&*(),.?":{}|<>]/.test(senha);
      if (!forcaValida) {
        errorSenhaDiv.textContent =
          "Requisitos: Mínimo 7 caracteres, 1 maiúscula, 1 número e 1 símbolo.";
        senhaInput.classList.add("error"); // Adiciona uma classe de erro para CSS
        senhaForte = false;
      } else {
        errorSenhaDiv.textContent = "";
        senhaInput.classList.remove("error");
      }
    } else {
      errorSenhaDiv.textContent = "";
      senhaInput.classList.remove("error");
    }

    // 2. Validação de Coincidência
    if (confirmSenha.length > 0 && senha !== confirmSenha) {
      errorConfirmSenhaDiv.textContent = "As senhas não coincidem.";
      confirmarSenhaInput.classList.add("error");
      senhasCoincidem = false;
    } else {
      errorConfirmSenhaDiv.textContent = "";
      confirmarSenhaInput.classList.remove("error");
    }

    return senhasCoincidem && senhaForte;
  }

  // --- EVENT LISTENERS (OUVINTES DE EVENTOS) ---

  if (form) {
    // Validação final ao tentar enviar o formulário
    form.addEventListener("submit", function (event) {
      if (!validarSenhas()) {
        event.preventDefault(); // Impede o envio do formulário se houver erro

        // Exibe um pop-up de erro se a função existir
        if (typeof showPopup === "function") {
          showPopup(
            "Erro no Formulário",
            "Por favor, corrija os erros destacados antes de enviar.",
            true
          );
        }
      }
    });
  }

  // Adiciona as máscaras e validações em tempo real
  if (cpfInput) cpfInput.addEventListener("input", formatarCPF);
  if (telefoneInput) telefoneInput.addEventListener("input", formatarTelefone);
  if (senhaInput) senhaInput.addEventListener("input", validarSenhas);
  if (confirmarSenhaInput)
    confirmarSenhaInput.addEventListener("input", validarSenhas);
});
