document.addEventListener("DOMContentLoaded", function () {
  // --- Elementos do DOM ---
  const metodoSelect = document.getElementById("metodo_pagamento");
  const valorInput = document.getElementById("valor_resgate");
  const submitBtn = document.getElementById("submit-btn");
  const successModal = document.getElementById("successModal");
  const closeSuccessModalBtn = document.getElementById("closeSuccessModalBtn");

  // --- Lógica do Formulário ---
  if (valorInput && document.querySelector(".saldo-box strong")) {
    // Garante que os elementos existem
    const userSaldo = parseFloat(
      document
        .querySelector(".saldo-box strong")
        .innerText.replace("R$", "")
        .replace(/\./g, "")
        .replace(",", ".")
    );
    const minimoResgate = parseFloat(valorInput.min);

    function showPaymentFields() {
      if (!metodoSelect) return;
      const metodo = metodoSelect.value;
      document
        .getElementById("pix-fields")
        .classList.toggle("hidden", metodo !== "pix");
      document
        .getElementById("bancaria-fields")
        .classList.toggle("hidden", metodo !== "conta_bancaria");
    }

    function atualizarEstadoBotao() {
      const valor = parseFloat(valorInput.value) || 0;

      if (userSaldo < minimoResgate) {
        submitBtn.disabled = true;
        submitBtn.title = `Saldo insuficiente para resgate mínimo de R$ ${minimoResgate
          .toFixed(2)
          .replace(".", ",")}`;
      } else if (valor > userSaldo) {
        submitBtn.disabled = true;
        submitBtn.title =
          "O valor do resgate não pode ser maior que o saldo disponível.";
      } else {
        submitBtn.disabled = false;
        submitBtn.title = "";
      }
    }

    if (metodoSelect) {
      metodoSelect.addEventListener("change", showPaymentFields);
    }
    valorInput.addEventListener("input", atualizarEstadoBotao);

    // Executa na carga da página
    showPaymentFields();
    atualizarEstadoBotao();
  }

  // --- Lógica para o Modal de Sucesso ---
  window.openModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = "flex";
    }
  };

  window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = "none";
    }
  };

  if (successModal) {
    if (closeSuccessModalBtn) {
      closeSuccessModalBtn.addEventListener("click", () =>
        closeModal("successModal")
      );
    }
    successModal.addEventListener("click", function (event) {
      if (event.target === successModal) {
        closeModal("successModal");
      }
    });
  }
});
