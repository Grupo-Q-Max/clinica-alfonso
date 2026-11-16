document.addEventListener('DOMContentLoaded', function () {
    // --- Lógica para o Pop-up de Confirmação de Ação ---
    const confirmModal = document.getElementById('confirmActionModal');
    const closeConfirmModalBtn = document.getElementById('closeConfirmModal');
    const cancelActionBtn = document.getElementById('cancelActionBtn');
    const confirmActionBtn = document.getElementById('confirmActionBtn');
    const confirmActionText = document.getElementById('confirmActionText');
    const actionButtons = document.querySelectorAll('.action-btn');

    let formToSubmit = null;
    let actionToPerform = null;

    if (confirmModal) {
        // Função para fechar o modal
        const closeModal = () => {
            confirmModal.style.display = 'none';
            confirmActionBtn.classList.remove('danger', 'success'); // Limpa classes de cor
        };

        // Adiciona evento de clique para todos os botões de ação (Aprovar/Rejeitar)
        actionButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                event.preventDefault(); // Impede o envio imediato do formulário
                
                const formId = button.dataset.formId;
                actionToPerform = button.dataset.action;
                const question = button.dataset.question;
                
                formToSubmit = document.getElementById(`form-${formId}`);
                
                // Prepara e exibe o modal
                if (formToSubmit) {
                    confirmActionText.textContent = question; // Define o texto da pergunta
                    
                    // Adiciona classe de cor ao botão de confirmação do modal
                    if (actionToPerform === 'rejeitar') {
                        confirmActionBtn.classList.add('danger');
                        confirmActionBtn.textContent = 'Confirmar Rejeição';
                    } else if (actionToPerform === 'aprovar') {
                        confirmActionBtn.classList.add('success');
                        confirmActionBtn.textContent = 'Confirmar Aprovação';
                    }

                    confirmModal.style.display = 'flex';
                }
            });
        });

        // Evento para o botão de confirmação final dentro do modal
        confirmActionBtn.addEventListener('click', () => {
            if (formToSubmit && actionToPerform) {
                // Define o valor da ação no campo hidden e envia o formulário
                const actionInput = formToSubmit.querySelector(`#acao-${formToSubmit.id.split('-')[1]}`);
                if(actionInput) {
                    actionInput.value = actionToPerform;
                    formToSubmit.submit();
                }
            }
        });

        // Eventos para fechar o modal
        closeConfirmModalBtn.addEventListener('click', closeModal);
        cancelActionBtn.addEventListener('click', closeModal);
        confirmModal.addEventListener('click', (event) => {
            if (event.target === confirmModal) {
                closeModal();
            }
        });
    }
});
