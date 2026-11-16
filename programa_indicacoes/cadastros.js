document.addEventListener("DOMContentLoaded", function () {
  // --- Lógica da Barra de Pesquisa ---
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    const searchTable = () => {
      const filter = searchInput.value.toLowerCase();
      const rows = document.querySelectorAll(".data-table tbody tr");
      rows.forEach((row) => {
        if (row.querySelector(".no-results")) return;

        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
      });
    };
    searchInput.addEventListener("keyup", searchTable);
  }

  const confirmActionModal = document.getElementById("confirmActionModal");
  if (confirmActionModal) {
    const closeConfirmModalBtn = document.getElementById("closeConfirmModal");
    const cancelActionBtn = document.getElementById("cancelActionBtn");
    const confirmActionBtn = document.getElementById("confirmActionBtn");
    const confirmActionText = document.getElementById("confirmActionText");
    const actionButtons = document.querySelectorAll(".action-btn");
    let formToSubmit = null;

    const closeConfirmModal = () => {
      confirmActionModal.style.display = "none";
      formToSubmit = null;
    };

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

    if (confirmActionBtn) {
      confirmActionBtn.addEventListener("click", () => {
        if (formToSubmit) {
          formToSubmit.submit();
        }
      });
    }

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
});
