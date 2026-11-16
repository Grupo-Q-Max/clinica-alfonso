document.addEventListener("DOMContentLoaded", function () {
  // --- VARIÁVEIS E ELEMENTOS DO DOM ---
  const {
    enderecoCompleto,
    modoConvidado,
    servicosDisponiveis,
    servicosSelecionados,
    outrosDescricoes,
    feedbackData,
  } = window.config;

  const feedbackPopup = document.getElementById("feedbackPopup");
  const addressPopup = document.getElementById("addressPopup");
  const form = document.getElementById("formIndicacao");
  const servicosContainer = document.getElementById("servicos-container");
  const btnAdicionar = document.getElementById("btnAdicionarServico");
  let servicoCounter = 0;

  // --- LÓGICA DOS POP-UPS (ATUALIZADA) ---

  // Função para mostrar o pop-up de feedback (sucesso/erro)
  const showFeedbackPopup = (type, message) => {
    if (!feedbackPopup) return;

    const icon = feedbackPopup.querySelector("#popup-icon");
    const title = feedbackPopup.querySelector("#popup-title");
    const p = feedbackPopup.querySelector("#popup-message");
    const okBtn = feedbackPopup.querySelector("#ok-btn");
    const novaIndicacaoBtn = feedbackPopup.querySelector("#nova-indicacao-btn");
    // NOVO: Pega a referência do novo botão
    const verIndicacoesBtn = feedbackPopup.querySelector("#ver-indicacoes-btn");

    // Garante que todos os botões comecem escondidos
    okBtn.style.display = "none";
    novaIndicacaoBtn.style.display = "none";
    verIndicacoesBtn.style.display = "none";

    if (type === "success") {
      icon.className = "fas fa-check-circle success";
      title.textContent = "Indicação Enviada!";

      // ATUALIZADO: Mostra os botões de sucesso
      novaIndicacaoBtn.style.display = "inline-block";
      verIndicacoesBtn.style.display = "inline-block"; // Mostra o novo botão
    } else {
      // Erro
      icon.className = "fas fa-exclamation-triangle error";
      title.textContent = "Ocorreu um Erro";
      okBtn.style.display = "inline-block";
    }
    p.textContent = message;
    feedbackPopup.classList.add("active");
  };

  // Função para esconder o pop-up de feedback
  const hideFeedbackPopup = () => {
    if (feedbackPopup) feedbackPopup.classList.remove("active");
  };

  // Adiciona eventos aos botões do pop-up
  document
    .getElementById("ok-btn")
    ?.addEventListener("click", hideFeedbackPopup);
  document
    .getElementById("nova-indicacao-btn")
    ?.addEventListener("click", () => {
      form.reset();
      servicosContainer.innerHTML = "";
      adicionarServico();
      hideFeedbackPopup();
    });

  // NOVO: Fecha o popup ao clicar no overlay (fundo)
  feedbackPopup?.addEventListener("click", function (event) {
    // Se o elemento clicado for o próprio fundo do popup...
    if (event.target === feedbackPopup) {
      hideFeedbackPopup();
    }
  });

  // NOVO: Fecha o popup ao pressionar a tecla ESC
  window.addEventListener("keydown", function (event) {
    // Se a tecla for 'Escape' e o popup estiver ativo...
    if (event.key === "Escape" && feedbackPopup.classList.contains("active")) {
      hideFeedbackPopup();
    }
  });

  // Verifica e exibe feedback da sessão (se houver)
  if (feedbackData) {
    showFeedbackPopup(feedbackData.type, feedbackData.text);
  }

  // Verifica e exibe pop-up de endereço incompleto
  if (!enderecoCompleto && !modoConvidado && addressPopup) {
    addressPopup.classList.add("active");
    form.style.pointerEvents = "none";
    form.style.opacity = "0.5";
  }

  // --- LÓGICA DO FORMULÁRIO DINÂMICO DE SERVIÇOS ---
  // (O restante do seu código permanece o mesmo, sem alterações)

  const adicionarServico = (servicoSelecionado = "", outraDescricao = "") => {
    servicoCounter++;
    const novoItem = document.createElement("div");
    novoItem.className = "servico-item";

    let opcoesHTML =
      '<option value="" disabled selected>Selecione um tratamento...</option>';
    servicosDisponiveis.forEach((servico) => {
      const isSelected = servico === servicoSelecionado ? "selected" : "";
      opcoesHTML += `<option value="${servico}" ${isSelected}>${servico}</option>`;
    });
    const isOutrosSelected = servicoSelecionado === "Outros" ? "selected" : "";
    opcoesHTML += `<option value="Outros" ${isOutrosSelected}>Outro...</option>`;

    novoItem.innerHTML = `
            <div class="servico-select-wrapper">
                <select name="servicos_interesse[]" required>${opcoesHTML}</select>
                ${
                  servicoCounter > 1
                    ? `<button type="button" class="remove-servico-btn"><i class="fas fa-times"></i></button>`
                    : ""
                }
            </div>
            <div class="outros-servicos-container" style="display: ${
              servicoSelecionado === "Outros" ? "block" : "none"
            }">
                <textarea name="outros_servicos_descricao[]" placeholder="Descreva o tratamento de interesse...">${outraDescricao}</textarea>
            </div>
        `;
    servicosContainer.appendChild(novoItem);
  };

  servicosContainer.addEventListener("change", function (e) {
    if (e.target.tagName === "SELECT") {
      const select = e.target;
      const outrosContainer = select
        .closest(".servico-item")
        .querySelector(".outros-servicos-container");
      const textarea = outrosContainer.querySelector("textarea");
      if (select.value === "Outros") {
        outrosContainer.style.display = "block";
        textarea.setAttribute("required", "required");
      } else {
        outrosContainer.style.display = "none";
        textarea.removeAttribute("required");
      }
    }
  });

  servicosContainer.addEventListener("click", function (e) {
    const removeBtn = e.target.closest(".remove-servico-btn");
    if (removeBtn) {
      removeBtn.closest(".servico-item").remove();
      servicoCounter--;
    }
  });

  btnAdicionar?.addEventListener("click", () => adicionarServico());

  if (servicosSelecionados && servicosSelecionados.length > 0) {
    servicosSelecionados.forEach((serv, index) => {
      adicionarServico(serv, outrosDescricoes[index] || "");
    });
  } else {
    adicionarServico();
  }
});
