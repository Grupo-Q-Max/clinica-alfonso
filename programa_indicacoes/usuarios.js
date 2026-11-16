document.addEventListener("DOMContentLoaded", function () {
  // --- LÓGICA PARA O POP-UP DO RELATÓRIO ---
  const openReportBtn = document.getElementById("openReportPopup");
  const reportModal = document.getElementById("reportModal");
  const closeReportBtn = document.getElementById("closeReportModal");
  const cancelReportBtn = document.getElementById("cancelReportModal");
  const selectAllCheckbox = document.getElementById("selectAllColumns");
  const columnCheckboxes = document.querySelectorAll(
    '.column-selector input[name="colunas[]"]'
  );

  if (openReportBtn && reportModal) {
    // Abrir o modal
    openReportBtn.addEventListener("click", () => {
      reportModal.style.display = "flex";
    });

    // Função para fechar o modal
    const closeReportModalFunc = () => {
      reportModal.style.display = "none";
    };

    // Fechar nos botões
    if (closeReportBtn)
      closeReportBtn.addEventListener("click", closeReportModalFunc);
    if (cancelReportBtn)
      cancelReportBtn.addEventListener("click", closeReportModalFunc);

    // Fechar ao clicar fora do conteúdo
    reportModal.addEventListener("click", (event) => {
      if (event.target === reportModal) {
        closeReportModalFunc();
      }
    });

    // Lógica para Marcar/Desmarcar Todos
    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener("change", function () {
        columnCheckboxes.forEach((checkbox) => {
          checkbox.checked = this.checked;
        });
      });
    }
  }

  // --- LÓGICA PARA O POP-UP DE CONFIRMAÇÃO DE AÇÃO (ATIVAR/BLOQUEAR) ---
  const confirmActionModal = document.getElementById("confirmActionModal");
  const closeConfirmModalBtn = document.getElementById("closeConfirmModal");
  const cancelActionBtn = document.getElementById("cancelActionBtn");
  const confirmActionBtn = document.getElementById("confirmActionBtn");
  const confirmActionText = document.getElementById("confirmActionText");
  const actionButtons = document.querySelectorAll(".action-btn");
  let formToSubmit = null;

  if (confirmActionModal) {
    // Função para fechar o modal de confirmação
    const closeConfirmModal = () => {
      confirmActionModal.style.display = "none";
      formToSubmit = null;
    };

    // Abrir o modal de confirmação
    actionButtons.forEach((button) => {
      button.addEventListener("click", function (event) {
        event.preventDefault();
        const question = this.getAttribute("data-question");
        const formId = this.getAttribute("data-form-id");
        formToSubmit = document.getElementById(formId);

        if (confirmActionText) {
          confirmActionText.textContent = question;
        }
        confirmActionModal.style.display = "flex";
      });
    });

    // Evento para o botão de confirmar a ação
    if (confirmActionBtn) {
      confirmActionBtn.addEventListener("click", () => {
        if (formToSubmit) {
          formToSubmit.submit();
        }
      });
    }

    // Eventos para fechar o modal de confirmação
    if (closeConfirmModalBtn)
      closeConfirmModalBtn.addEventListener("click", closeConfirmModal);
    if (cancelActionBtn)
      cancelActionBtn.addEventListener("click", closeConfirmModal);
    confirmActionModal.addEventListener("click", (event) => {
      if (event.target === confirmActionModal) {
        closeConfirmModal();
      }
    });
  }

  // --- LÓGICA DA BARRA DE PESQUISA ---
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("keyup", function () {
      const filter = searchInput.value.toLowerCase();
      const userCards = document.querySelectorAll(".user-card");

      userCards.forEach((card) => {
        const nome = card.dataset.nome.toLowerCase();
        const email = card.dataset.email.toLowerCase();

        if (nome.includes(filter) || email.includes(filter)) {
          card.style.display = "flex";
        } else {
          card.style.display = "none";
        }
      });
    });
  }

  // --- LÓGICA PARA O POP-UP DE IMPORTAÇÃO (CORRIGIDO E INTEGRADO) ---
  const openImportModalBtn = document.getElementById("openImportModal");
  const importModal = document.getElementById("importModal");
  const closeImportModalBtn = document.getElementById("closeImportModal");
  const cancelImportModalBtn = document.getElementById("cancelImportModal");

  if (openImportModalBtn && importModal) {
    openImportModalBtn.addEventListener("click", () => {
      importModal.style.display = "flex";
    });
  }

  const closeImportModal = () => {
    if (importModal) importModal.style.display = "none";
  };

  if (closeImportModalBtn)
    closeImportModalBtn.addEventListener("click", closeImportModal);
  if (cancelImportModalBtn)
    cancelImportModalBtn.addEventListener("click", closeImportModal);

  // Adiciona a funcionalidade de fechar ao clicar fora do modal de importação
  if (importModal) {
    importModal.addEventListener("click", (event) => {
      if (event.target === importModal) {
        closeImportModal();
      }
    });
  }
}); // FIM DO ÚNICO 'DOMContentLoaded'
