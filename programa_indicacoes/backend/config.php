<?php
// Descomente as duas linhas abaixo APENAS se precisar depurar erros.
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. URL BASE FINAL DO SEU PROJETO
define('BASE_URL', 'https://www.clinicaalfonso.com.br/programa_indicacoes');

$servidor = "localhost"; 
$banco    = "nayar358_alfonso_rewards";  
$usuario  = "nayar358_alfonso_user";   
$senha    = "su?!Zr7JDLNf";          

$conexao = new mysqli($servidor, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    error_log("Falha na conex���o com o banco de dados HostGator: " . $conexao->connect_error);
    die("Estamos com um problema t���cnico no momento. Por favor, tente novamente mais tarde.");
}

$conexao->set_charset("utf8mb4");

?>