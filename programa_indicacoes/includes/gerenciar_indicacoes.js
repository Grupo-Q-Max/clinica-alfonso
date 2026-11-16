document.addEventListener("DOMContentLoaded", () => {
  let currentIndicacaoId = null;
  let currentAction = null;

  const confirmModal = document.getElementById("modal-confirm");
  const orcamentoModal = document.getElementById("modal-orcamento-enviado");

  const openModal = (modal) => {
    if (modal) modal.style.display = "flex";
  };

  const closeModal = () => {
    document
      .querySelectorAll(".modal-overlay")
      .forEach((modal) => (modal.style.display = "none"));
    if (currentIndicacaoId) {
      const select = document.querySelector(
        `.status-select[data-indicacao-id="${currentIndicacaoId}"]`
      );
      if (select) select.value = "";
    }
  };

  document.querySelectorAll(".close-modal, .btn-secondary").forEach((btn) => {
    btn.addEventListener("click", closeModal);
  });

  // --- LÓGICA PRINCIPAL PARA MUDANÇA DE STATUS ---
  document.querySelectorAll(".status-select").forEach((select) => {
    select.addEventListener("change", async function () {
      currentIndicacaoId = this.dataset.indicacaoId;
      currentAction = this.value;

      if (!currentAction) return;

      if (currentAction === "Orçamento Enviado") {
        try {
          const response = await fetch(
            `get_indicacao_servicos.php?id=${currentIndicacaoId}`
          );
          if (!response.ok) throw new Error("Falha na resposta da rede.");

          const servicos = await response.json();

          const container = document.getElementById("servicos-list-container");
          container.innerHTML = "";

          if (servicos.length > 0) {
            servicos.forEach((servico, index) => {
              const item = document.createElement("div");
              item.className = "servico-orcamento-item";

              // ATUALIZADO: Adicionado um <small> para o aviso de limite
              item.innerHTML = `
                                <div class="checkbox-item">
                                    <input type="checkbox" name="servicos_proposta[]" value="${
                                      servico.servico
                                    }" 
                                           data-tipo="${servico.tipo_cashback}" 
                                           data-valor="${
                                             servico.valor_cashback
                                           }"
                                           data-tem-limite="${
                                             servico.tem_limite
                                           }"
                                           data-limite="${
                                             servico.limite_cashback
                                           }"
                                           id="servico-${index}" checked>
                                    <label for="servico-${index}">${
                servico.servico
              }</label>
                                </div>
                                <div class="valor-final-container" style="display: ${
                                  servico.tipo_cashback === "percentual"
                                    ? "block"
                                    : "none"
                                }">
                                     <label for="valor-final-${index}">Valor Final do Serviço (R$)</label>
                                     <input type="text" name="valor_final_servico[]" id="valor-final-${index}" class="valor-final-input" placeholder="Ex: 500,00">
                                     <small class="limite-aviso" id="limite-aviso-${index}"></small>
                                </div>
                            `;
              container.appendChild(item);
            });

            container.addEventListener("input", updateTotalPendente);
            updateTotalPendente();
          } else {
            container.innerHTML =
              "<p>Nenhum serviço encontrado para esta indicação.</p>";
          }

          openModal(orcamentoModal);
        } catch (error) {
          console.error("Erro ao buscar serviços:", error);
          alert("Não foi possível carregar os serviços da indicação.");
          closeModal();
        }
      } else {
        // ATUALIZADO: Lógica para exibir o valor exato no popup de confirmação
        let text = `Você tem certeza que deseja alterar o status para "${currentAction}"?`;

        if (currentAction === "Aprovado") {
          const tableRow = this.closest("tr");
          const valorPendenteCell = tableRow.querySelector(
            ".valor-pendente-cell"
          );
          const valorPendenteTexto = valorPendenteCell
            ? valorPendenteCell.textContent
            : "R$ 0,00";
          text = `Você confirma a aprovação desta indicação? Um valor de ${valorPendenteTexto} será creditado ao indicador.`;
        }

        document.getElementById("confirm-text").textContent = text;
        openModal(confirmModal);
      }
    });
  });

  // --- FUNÇÃO DE CÁLCULO EM TEMPO REAL (ATUALIZADA) ---
  function updateTotalPendente() {
    let total = 0;
    document
      .querySelectorAll(".servico-orcamento-item")
      .forEach((item, index) => {
        const checkbox = item.querySelector('input[type="checkbox"]');
        const avisoLimite = item.querySelector(`#limite-aviso-${index}`);
        avisoLimite.textContent = ""; // Limpa o aviso

        if (!checkbox.checked) return;

        const tipo = checkbox.dataset.tipo;
        const valor = parseFloat(checkbox.dataset.valor);
        const temLimite = checkbox.dataset.temLimite == "1";
        const limite = parseFloat(checkbox.dataset.limite);
        let cashbackCalculado = 0;

        if (tipo === "valor") {
          cashbackCalculado = valor;
        } else if (tipo === "percentual") {
          const valorFinalInput = item.querySelector(".valor-final-input");
          const valorFinal =
            parseFloat(valorFinalInput.value.replace(",", ".")) || 0;

          cashbackCalculado = valorFinal * (valor / 100);

          // NOVO: Lógica para exibir o aviso de limite
          if (temLimite && limite > 0 && cashbackCalculado > limite) {
            avisoLimite.textContent = `Limite de R$ ${limite
              .toFixed(2)
              .replace(".", ",")} atingido.`;
            cashbackCalculado = limite;
          }
        }
        total += cashbackCalculado;
      });
    document.getElementById("total-pendente-display").textContent = total
      .toFixed(2)
      .replace(".", ",");
  }

  // --- EVENTOS DOS BOTÕES DOS MODAIS ---
  document
    .getElementById("confirm-orcamento-btn")
    .addEventListener("click", () => {
      const form = document.getElementById(`form-${currentIndicacaoId}`);
      form.innerHTML = "";

      form.innerHTML += `<input type="hidden" name="indicacao_id" value="${currentIndicacaoId}">`;
      form.innerHTML += `<input type="hidden" name="status_filtro" value="${
        document
          .querySelector(".filter-button.active")
          ?.getAttribute("href")
          .split("=")[1] || "Ativa"
      }">`;
      form.innerHTML += `<input type="hidden" name="status" value="Orçamento Enviado">`;

      let algumValorInvalido = false;
      document.querySelectorAll(".servico-orcamento-item").forEach((item) => {
        const checkbox = item.querySelector('input[type="checkbox"]');
        if (checkbox.checked) {
          const servicoInput = document.createElement("input");
          servicoInput.type = "hidden";
          servicoInput.name = "servicos_proposta[]";
          servicoInput.value = checkbox.value;
          form.appendChild(servicoInput);

          const valorFinalInput = item.querySelector(".valor-final-input");
          const valorFinalHidden = document.createElement("input");
          valorFinalHidden.type = "hidden";
          valorFinalHidden.name = "valor_final_servico[]";
          valorFinalHidden.value = valorFinalInput.value;
          form.appendChild(valorFinalHidden);

          // ATUALIZADO: Validação reforçada
          if (
            checkbox.dataset.tipo === "percentual" &&
            (valorFinalInput.value.trim() === "" ||
              isNaN(parseFloat(valorFinalInput.value.replace(",", "."))) ||
              parseFloat(valorFinalInput.value.replace(",", ".")) <= 0)
          ) {
            algumValorInvalido = true;
            valorFinalInput.style.border = "2px solid red";
          } else {
            valorFinalInput.style.border = "";
          }
        }
      });

      if (algumValorInvalido) {
        alert(
          "Por favor, preencha um valor final válido (maior que zero) para todos os serviços com cashback percentual."
        );
        return;
      }

      form.submit();
    });

  document
    .getElementById("confirm-action-btn")
    .addEventListener("click", () => {
      if (currentIndicacaoId && currentAction) {
        const form = document.getElementById(`form-${currentIndicacaoId}`);
        document.getElementById(`status-${currentIndicacaoId}`).value =
          currentAction;
        form.submit();
      }
    });
});
