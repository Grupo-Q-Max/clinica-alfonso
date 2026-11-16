document.addEventListener("DOMContentLoaded", () => {
  // --- ANIMAÇÃO DE ENTRADA DA PÁGINA ---
  gsap.from(".form-wrapper", {
    delay: 0.2,
    autoAlpha: 0,
    y: 50,
    duration: 1.2,
    ease: "power3.out",
  });

  // --- LÓGICA DE BUSCA DE CEP ---
  const cepInput = document.getElementById("cep");
  if (cepInput) {
    // Formata o CEP enquanto digita
    cepInput.addEventListener("input", (e) => {
      let value = e.target.value.replace(/\D/g, "");
      value = value.substring(0, 8);
      value = value.replace(/^(\d{5})(\d)/, "$1-$2");
      e.target.value = value;

      // Se o CEP está completo (9 caracteres com o traço), busca o endereço
      if (value.length === 9) {
        buscarCEP(value);
      }
    });
  }

  async function buscarCEP(cep) {
    const cepLimpo = cep.replace(/\D/g, "");
    if (cepLimpo.length !== 8) return;

    // Seleciona os campos do formulário
    const enderecoInput = document.getElementById("endereco");
    const bairroInput = document.getElementById("bairro");
    const cidadeInput = document.getElementById("cidade");
    const estadoInput = document.getElementById("estado");

    // Adiciona um indicador de carregamento
    cepInput.classList.add("loading");

    try {
      const response = await fetch(
        `https://viacep.com.br/ws/${cepLimpo}/json/`
      );
      if (!response.ok) throw new Error("Falha na resposta da rede");

      const data = await response.json();

      if (!data.erro) {
        // Preenche os campos com os dados retornados
        enderecoInput.value = data.logradouro || "";
        bairroInput.value = data.bairro || "";
        cidadeInput.value = data.localidade || "";
        estadoInput.value = data.uf || "";

        // Foca no campo de número para o próximo passo
        document.getElementById("numero").focus();
      } else {
        alert("CEP não encontrado. Por favor, verifique o número digitado.");
      }
    } catch (error) {
      console.error("Erro ao buscar CEP:", error);
      alert("Ocorreu um erro ao buscar o CEP. Tente novamente.");
    } finally {
      // Remove o indicador de carregamento
      cepInput.classList.remove("loading");
    }
  }
});
