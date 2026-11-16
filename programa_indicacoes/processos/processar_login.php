<?php
// Inicia o buffer de saída para capturar qualquer erro inesperado
ob_start();

// Define o cabeçalho de resposta como JSON desde o início
header('Content-Type: application/json');

// --- SISTEMA DE SEGURANÇA À PROVA DE FALHAS ---
// Função para enviar uma resposta JSON formatada e encerrar o script
function responderJson($success, $message, $extra = [])
{
    // Limpa qualquer saída inesperada que possa ter sido gerada
    ob_end_clean();
    // Monta a resposta
    $response = ['success' => $success, 'message' => $message];
    // Envia a resposta e encerra
    echo json_encode(array_merge($response, $extra));
    exit();
}

// Define um manipulador de erros customizado para capturar qualquer erro PHP
set_error_handler(function ($severity, $message, $file, $line) {
    // Registra o erro real no log do servidor para você poder depurar
    error_log("PHP Error: [$severity] $message in $file on line $line");
    // Envia uma resposta amigável para o usuário
    responderJson(false, 'Ocorreu um erro inesperado no servidor. Por favor, tente novamente.');
});

// 1. VERIFICAÇÃO DE DEPENDÊNCIAS E INICIALIZAÇÃO
try {
    // Inicia a sessão se ainda não houver uma
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Inclui os arquivos essenciais
    require_once '../backend/config.php';
    require_once '../vendor/autoload.php';
    require_once '../backend/funcoes_email.php';
} catch (Throwable $e) {
    // Captura erros fatais se os arquivos require não forem encontrados
    error_log("Erro Crítico de Inclusão: " . $e->getMessage());
    responderJson(false, 'Ocorreu um erro crítico na configuração do servidor.');
}

// 2. LÓGICA DE LOGIN PRINCIPAL DENTRO DE UM TRY-CATCH
try {
    // Garante que o método da requisição seja POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responderJson(false, 'Método não permitido.');
    }

    // Validação dos dados de entrada
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $senha = $_POST['senha'] ?? '';

    // Pequena correção na mensagem de erro
    if (!$email || empty($senha)) {
        responderJson(false, 'Por favor, preencha o e-mail e a senha.');
    }

    // Consulta ao banco de dados
    $stmt = $conexao->prepare("SELECT id, nome, senha, ativo, tipo_conta, trocou_senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Lógica de Autenticação
    if (!$user || !password_verify($senha, $user['senha'])) {
        responderJson(false, 'E-mail ou senha inválidos.');
    }

    if ($user['tipo_conta'] == 'desativado') {
        responderJson(false, 'Sua conta foi desativada. Entre em contato com o suporte.', ['redirect' => 'desativado.php']);
    }

    if ($user['ativo'] == 0) {
        if (enviarEmailReativacao($email, $user['nome'], $conexao)) {
            responderJson(false, 'Sua conta está inativa. Um novo e-mail de confirmação foi enviado para você.', ['inactive' => true, 'email' => $email]);
        } else {
            responderJson(false, 'Sua conta está inativa e não conseguimos reenviar o e-mail. Contate o suporte.', ['inactive' => true, 'email' => $email]);
        }
    }

    if ($user['trocou_senha'] == 0) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['tipo_conta'] = $user['tipo_conta'];
        $_SESSION['precisa_trocar_senha'] = true;
        responderJson(true, 'Redirecionando...', ['redirect' => 'trocar_senha_obrigatorio.php']);
    }

    // SUCESSO NO LOGIN
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nome'];
    $_SESSION['tipo_conta'] = $user['tipo_conta'];

    $redirect_url = ($user['tipo_conta'] == 'admin') ? 'admin.php' : 'indicador.php';
    responderJson(true, 'Login bem-sucedido! Redirecionando...', ['redirect' => $redirect_url]);
} catch (Exception $e) {
    // Captura qualquer outra exceção que possa ocorrer durante a execução
    error_log("Erro de Execução em processar_login.php: " . $e->getMessage());
    responderJson(false, 'Ocorreu um erro durante o processamento. Tente novamente.');
}

// Limpa o buffer de saída no final, caso tudo tenha corrido bem.
ob_end_flush();
