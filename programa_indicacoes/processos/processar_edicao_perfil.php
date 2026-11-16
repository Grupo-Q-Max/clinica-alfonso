<?php
// Inicia a sessão para acessar as variáveis
session_start();
// Inclui o arquivo de configuração do banco de dados
require '../backend/config.php';

// --- CORREÇÃO 1: Padronizando a verificação de segurança ---
// Usaremos 'perfil_verificado', que é a variável definida na página anterior.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['perfil_verificado']) || $_SESSION['perfil_verificado'] !== true) {
    // Se não estiver autorizado, define uma mensagem de erro e redireciona
    $_SESSION['profile_update_message'] = ['type' => 'error', 'text' => 'Acesso negado. Sua sessão expirou ou você não tem permissão.'];
    header("Location: ../perfil.php");
    exit;
}

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../editar_perfil.php");
    exit;
}

// --- CORREÇÃO 2: Recebendo TODOS os dados editáveis do formulário ---
// Usamos trim() para remover espaços em branco e htmlspecialchars() para segurança.
$nome = trim(htmlspecialchars($_POST['nome']));
$sobrenome = trim(htmlspecialchars($_POST['sobrenome']));
$data_nascimento = trim(htmlspecialchars($_POST['data_nascimento']));
$telefone = trim(htmlspecialchars($_POST['telefone']));
$genero = trim(htmlspecialchars($_POST['genero']));
$cep = trim(htmlspecialchars($_POST['cep'] ?? '')); // Usamos '??' para campos opcionais
$endereco = trim(htmlspecialchars($_POST['endereco'] ?? ''));
$numero = trim(htmlspecialchars($_POST['numero'] ?? ''));
$bairro = trim(htmlspecialchars($_POST['bairro'] ?? ''));
$estado = trim(htmlspecialchars($_POST['estado'] ?? ''));
$cidade = trim(htmlspecialchars($_POST['cidade'] ?? ''));
$complemento = trim(htmlspecialchars($_POST['complemento'] ?? ''));

// --- CORREÇÃO 3: Usando o nome correto da sessão para o ID do usuário ---
$usuario_id = $_SESSION['user_id'];

// Validação simples dos campos obrigatórios
if (empty($nome) || empty($sobrenome) || empty($data_nascimento) || empty($telefone)) {
    $_SESSION['profile_update_message'] = ['type' => 'error', 'text' => 'Erro: Nome, sobrenome, data de nascimento e telefone são obrigatórios.'];
    header("Location: ../editar_perfil.php");
    exit;
}

// --- CORREÇÃO 4: Montando a query UPDATE apenas com os campos que podem ser alterados ---
// Removemos e-mail e cpf da query, pois eles não devem ser alterados.
$query = "UPDATE usuarios SET 
            nome = ?, 
            sobrenome = ?, 
            data_nascimento = ?, 
            telefone = ?, 
            genero = ?, 
            cep = ?, 
            endereco = ?, 
            numero = ?, 
            bairro = ?, 
            estado = ?, 
            cidade = ?, 
            complemento = ? 
          WHERE id = ?";

$stmt = $conexao->prepare($query);

// A string de tipos deve corresponder aos campos: 12 strings (s) e 1 inteiro (i)
$stmt->bind_param(
    "ssssssssssssi",
    $nome,
    $sobrenome,
    $data_nascimento,
    $telefone,
    $genero,
    $cep,
    $endereco,
    $numero,
    $bairro,
    $estado,
    $cidade,
    $complemento,
    $usuario_id // O ID do usuário para a cláusula WHERE
);

// Executa a query e define a mensagem de sucesso ou erro
if ($stmt->execute()) {
    $_SESSION['profile_update_message'] = ['type' => 'success', 'text' => 'Perfil atualizado com sucesso!'];
} else {
    // Em caso de erro, é bom registrar o erro para depuração
    error_log("Erro ao atualizar perfil: " . $stmt->error);
    $_SESSION['profile_update_message'] = ['type' => 'error', 'text' => 'Erro ao atualizar o perfil. Tente novamente.'];
}

// --- CORREÇÃO 1 (continuação): Remove a autorização após o uso ---
// Limpamos a sessão para que o usuário precise validar novamente no futuro
unset($_SESSION['perfil_verificado']);

// Redireciona de volta para a página de perfil para mostrar a mensagem
header("Location: ../perfil.php");
exit;

?>