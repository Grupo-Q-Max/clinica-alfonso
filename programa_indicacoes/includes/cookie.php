<style>
  .popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 999;
  }

  .popup {
    background-color: #111;
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    width: 100%;
    max-width: 300px;
    max-height: 80%;
    overflow-y: auto;
  }

  .popup p {
    font-size: 16px;
    margin: 10px;
  }

  .cookie-popup-overlay {
    position: fixed;
    bottom: 0;
    left: 20px;
    max-width: 500px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px 0;
    z-index: 999;
  }

  .cookie-popup {
    background-color: #222;
    padding: 15px;
    border-radius: 15px;
    text-align: center;
    width: 100%;
    max-width: 600px;
    color: #fff;
    font-size: 14px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  }

  .cookie-popup p {
    margin: 10px 0;
  }

  .cookie-popup a {
    color: #0f0;
    text-decoration: underline;
  }

  .cookie-category {
    margin: 10px 0;
    text-align: left;
    color: #ccc;
    font-size: 14px;
  }

  .cookie-category input[type="checkbox"] {
    margin-right: 10px;
  }

  .floating-cookie-button {
    position: fixed;
    bottom: 46px;
    left: 40px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    justify-content: center;
    align-items: center;
    text-align: center;
    cursor: pointer;
    z-index: 990;
    transition: all 0.3s ease;
  }

  .floating-cookie-button:hover {
    filter: brightness(1.5);
  }

  .cookie-icon {
    width: 30px;
    height: 30px;
  }

  .accept-btn {
    padding: 10px 18px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
  }

  .manage-btn {
    padding: 10px 18px;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    font-size: 14px;
    background-color: #000;
    color: #fff;
  }

  .accept-btn {
    background-color: #0f0;
    border-radius: 15px;
  }

  .manage-btn:hover {
    background-color: #222;
    color: #fff;
    box-shadow: 0px 0px 10px #000;
  }

  .accept-btn:hover {
    box-shadow: 0px 4px 16px #0f0;
  }

  h3 {
    color: #0f0;
  }

  @media (max-width: 460px) {
    .cookie-popup-overlay {
      left: 0px;
    }
  }
</style>

<div class="cookie-popup-overlay" id="cookiePopupOverlay">
  <div class="cookie-popup">
    <p>Este site utiliza cookies para melhorar a experiência do usuário. Você pode gerenciar suas preferências
      ou ler nossa <a href="#" class="cookie-link" id="show-terms">política de cookies</a>.</p>
    <div class="cookie-buttons">
      <button class="accept-btn" onclick="acceptCookies()">Aceitar</button>
      <button class="manage-btn" onclick="manageCookies()">Gerenciar</button>
    </div>
  </div>
</div>

<div class="popup-overlay" id="popup-overlay">
  <div class="popup">
    <h2>Política de Cookies da QXBACK.</h2>
    <p>A QXBACK valoriza a privacidade e a segurança dos seus dados pessoais.
      A QXBACK utiliza cookies e tecnologias similares para proporcionar uma melhor experiência ao usuário,
      personalizar conteúdos e anúncios, analisar o tráfego em nosso site e aprimorar nossos serviços. Este
      documento explica como utilizamos cookies e como você pode gerenciá-los.</p>
    <h3>1. O que são Cookies?</h3>
    <p>Cookies são pequenos arquivos de texto armazenados no seu dispositivo (computador, tablet ou smartphone)
      quando você acessa nosso site. Eles nos ajudam a reconhecer seu navegador, lembrar suas preferências e
      melhorar sua experiência de navegação.</p>
    <h3>2. Tipos de Cookies Utilizados</h3>
    <p>Cookies Necessários:

      Essenciais para o funcionamento do site, permitindo a navegação e acesso a funcionalidades seguras.
      Exemplo: Cookies que mantêm você conectado à sua conta.
      Cookies de Desempenho:

      Coletam informações sobre como você utiliza nosso site, como páginas acessadas e tempo de navegação.
      Utilizados para melhorar a funcionalidade e desempenho do site.
      Cookies Funcionais:

      Permitem que o site lembre suas escolhas, como idioma ou região, para oferecer uma experiência
      personalizada.
      Cookies de Publicidade:

      Usados para apresentar anúncios relevantes e medir a eficácia das campanhas publicitárias.
      Podem ser configurados por parceiros como as empresas do grupo Q-Max.</p>
    <h3>3. Como Usamos os Cookies?</h3>
    <p>Os cookies nos ajudam a:

      Garantir o funcionamento correto do site;
      Lembrar suas preferências e configurações;
      Analisar o tráfego e o comportamento dos usuários no site;
      Oferecer conteúdos personalizados e relevantes;
      Melhorar continuamente nossos serviços.</p>
    <h3>4. Gerenciamento de Cookies</h3>
    <p>Você pode aceitar, recusar ou gerenciar os cookies através das configurações do seu navegador ou no nosso
      painel de preferências de cookies. A desativação de alguns cookies pode impactar negativamente sua
      experiência no site.</p>
    <h3>5. Cookies de Terceiros</h3>
    <p>Alguns cookies podem ser configurados por terceiros confiáveis, como empresas do grupo Q-Max, para
      oferecer funcionalidades adicionais ou serviços analíticos. Recomendamos revisar as políticas de
      privacidade desses terceiros para entender como utilizam seus dados.</p>
    <h3>6. Alterações na Política de Cookies</h3>
    <p>A QXBACK pode atualizar esta Política de Cookies periodicamente para refletir
      mudanças em nossos serviços ou regulamentos. Certifique-se de revisar esta página regularmente.</p>
    <h3>7. Contato</h3>
    <p>Caso tenha dúvidas ou preocupações sobre esta Política de Privacidade, entre em contato conosco:
      E-mail: contato@grupoqmax.com.br
      Telefone: (11) 2897-6514 -
      Endereço: Rua Frei Gaspar 941, Centro - 09720-440 - São Bernardo do Campo - São Paulo.
      Ao continuar navegando em nosso site, você concorda com o uso de cookies conforme descrito nesta
      Política.</p>
    <button id="close-popup" class="accept-btn">Fechar</button>
  </div>
</div>

<div class="cookie-popup-overlay" id="acceptPopupOverlay" style="display: none;">
  <div class="cookie-popup">
    <p>Obrigado por aceitar os cookies! Sua experiência será aprimorada.</p>
    <button class="accept-btn" onclick="closeAcceptPopup()">Fechar</button>
  </div>
</div>

<div class="cookie-popup-overlay" id="managePopupOverlay" style="display: none;">
  <div class="cookie-popup">
    <p>Gerencie suas preferências de cookies aqui. Escolha as categorias que deseja ativar.</p>
    <form id="cookiePreferencesForm">
      <div class="cookie-category">
        <label>
          <input type="checkbox" id="performanceCookies">
          <strong>Cookies de Desempenho</strong> - Coletam dados para melhorar o desempenho do site.
        </label>
      </div>
      <div class="cookie-category">
        <label>
          <input type="checkbox" id="functionalityCookies">
          <strong>Cookies de Funcionalidade</strong> - Permitem personalizar sua experiência.
        </label>
      </div>
      <div class="cookie-category">
        <label>
          <input type="checkbox" id="advertisingCookies">
          <strong>Cookies de Publicidade</strong> - Usados para oferecer anúncios personalizados.
        </label>
      </div>
    </form>
    <div class="cookie-buttons">
      <button class="accept-btn" onclick="savePreferences()">Salvar e Fechar</button>
    </div>
  </div>
</div>

<div class="cookie-popup-overlay" id="savedPreferencesPopupOverlay" style="display: none;">
  <div class="cookie-popup">
    <p>Suas preferências de cookies foram salvas com sucesso! Você pode alterá-las a qualquer momento clicando no botão
      de cookies que fica no canto inferior esquerdo do site.</p>
    <button class="accept-btn" onclick="closeSavedPreferencesPopup()">Fechar</button>
  </div>
</div>

<div id="floatingCookieButton" class="floating-cookie-button" onclick="manageCookies()" style="display: none;">
  <img src="imagens/food_15639699.webp" alt="Gerenciar cookies" class="cookie-icon">
</div>