<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../vendor/autoload.php';
require '../backend/config.php';
require '../backend/funcoes_email.php'; 

function redirecionarComErro($mensagem, $campo = null)
{
    if ($campo === 'email') {
        $_SESSION['email_error_message'] = $mensagem;
    } elseif ($campo === 'cpf') {
        $_SESSION['cpf_error_message'] = $mensagem;
    } else {
        $_SESSION['form_error_message'] = $mensagem;
    }

    $_SESSION['form_data'] = $_POST;
    unset($_SESSION['form_data']['senha'], $_SESSION['form_data']['confirmar-senha']);
    header('Location: ../cadastrar.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}

$nome = trim(htmlspecialchars($_POST['nome'] ?? ''));
$sobrenome = trim(htmlspecialchars($_POST['sobrenome'] ?? ''));
$cpf = trim(htmlspecialchars($_POST['cpf'] ?? ''));
$telefone = trim(htmlspecialchars($_POST['telefone'] ?? ''));
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar-senha'] ?? '';
$privacy = isset($_POST['privacy']);

if (empty($nome) || empty($sobrenome) || empty($cpf) || empty($telefone) || !$email || empty($senha) || !$privacy) {
    redirecionarComErro("Por favor, preencha todos os campos obrigatórios e aceite os termos.");
}
if ($senha !== $confirmar_senha) {
    redirecionarComErro("As senhas não coincidem. Por favor, tente novamente.");
}
if (strlen($senha) < 7 || !preg_match('/[A-Z]/', $senha) || !preg_match('/[0-9]/', $senha) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $senha)) {
    redirecionarComErro("A senha não atende aos requisitos de segurança.");
}

try {
    $stmt = $conexao->prepare("SELECT email, cpf FROM usuarios WHERE email = ? OR cpf = ?");
    $stmt->bind_param("ss", $email, $cpf);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $existente = $resultado->fetch_assoc();
        if ($existente['email'] === $email) {
            redirecionarComErro("Este endereço de e-mail já está cadastrado.", "email");
        }
        if ($existente['cpf'] === $cpf) {
            redirecionarComErro("Este CPF já está cadastrado.", "cpf");
        }
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Erro no DB (verificação de duplicidade): " . $e->getMessage());
    redirecionarComErro("Ocorreu um erro no servidor. Tente novamente.");
}


$senha_hash = password_hash($senha, PASSWORD_BCRYPT);
$token = bin2hex(random_bytes(32));
$token_hash = password_hash($token, PASSWORD_DEFAULT); 

$conexao->begin_transaction();
try {
    $stmt_user = $conexao->prepare("INSERT INTO usuarios (nome, sobrenome, nome_social, cpf, data_nascimento, genero, email, telefone, senha) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_user->bind_param("sssssssss", $nome, $sobrenome, $_POST['nome_social'], $cpf, $_POST['data_nascimento'], $_POST['genero'], $email, $telefone, $senha_hash);
    $stmt_user->execute();
    $stmt_user->close();

$stmt_token = $conexao->prepare("INSERT INTO usuarios_tokens (email, token, expirado_em) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
$stmt_token->bind_param("ss", $email, $token_hash);
    $stmt_token->execute();
    $stmt_token->close();

    $conexao->commit();
} catch (Exception $e) {
    $conexao->rollback();
    error_log("Erro no DB (inserção): " . $e->getMessage());
    redirecionarComErro("Ocorreu um erro interno ao criar sua conta. Por favor, tente novamente.");
}

if (enviarEmailDeCadastro($email, $nome, $token)) {
    unset($_SESSION['form_data']);
    $_SESSION['login_success_message'] = "Cadastro realizado com sucesso! Um e-mail de confirmação foi enviado para você.";
    header("Location: ../login.php");
    exit();
} else {
    redirecionarComErro("Seu cadastro foi realizado, mas houve uma falha ao enviar o e-mail de confirmação. Por favor, entre em contato com o suporte para ativar sua conta.");
}
