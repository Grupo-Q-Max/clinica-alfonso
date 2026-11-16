<?php
session_start();
require '../backend/config.php';

// 1. VERIFICAÇÃO DE SEGURANÇA E MÉTODO
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['form_message'] = ['type' => 'error', 'text' => 'Método de requisição inválido.'];
    header("Location: ../cadastro_endereco.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// 2. COLETA E LIMPEZA DOS DADOS DO FORMULÁRIO
$cep = trim(htmlspecialchars($_POST['cep'] ?? ''));
$endereco = trim(htmlspecialchars($_POST['endereco'] ?? ''));
$numero = trim(htmlspecialchars($_POST['numero'] ?? ''));
$bairro = trim(htmlspecialchars($_POST['bairro'] ?? ''));
$cidade = trim(htmlspecialchars($_POST['cidade'] ?? ''));
$estado = trim(htmlspecialchars($_POST['estado'] ?? ''));
$complemento = trim(htmlspecialchars($_POST['complemento'] ?? ''));

// Verificação de campos obrigatórios
if (empty($cep) || empty($endereco) || empty($numero) || empty($bairro) || empty($cidade) || empty($estado)) {
    $_SESSION['form_message'] = ['type' => 'error', 'text' => 'Erro: Por favor, preencha todos os campos de endereço obrigatórios.'];
    header("Location: ../cadastro_endereco.php");
    exit;
}

// 3. ATUALIZAÇÃO SEGURA COM PREPARED STATEMENTS
try {
    $query = "UPDATE usuarios SET cep = ?, endereco = ?, numero = ?, bairro = ?, cidade = ?, estado = ?, complemento = ? WHERE id = ?";

    $stmt = $conexao->prepare($query);
    if ($stmt === false) {
        throw new Exception("Falha na preparação da consulta: " . $conexao->error);
    }

    $stmt->bind_param("sssssssi", $cep, $endereco, $numero, $bairro, $cidade, $estado, $complemento, $usuario_id);

    if ($stmt->execute()) {
        // 4. SUCESSO
        // Redireciona para a página de indicação, pois o perfil está completo
        header("Location: ../indicar.php");
        exit;
    } else {
        throw new Exception("Falha ao executar a consulta: " . $stmt->error);
    }
} catch (Exception $e) {
    // 5. FALHA
    error_log("Erro ao atualizar endereço para o usuário ID $usuario_id: " . $e->getMessage());
    $_SESSION['form_message'] = ['type' => 'error', 'text' => 'Ocorreu um erro ao salvar seu endereço. Detalhe técnico: ' . $e->getMessage()];
    header("Location: ../cadastro_endereco.php");
    exit;
}
