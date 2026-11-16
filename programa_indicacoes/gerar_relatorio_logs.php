<?php
session_start();
require 'backend/config.php';

// Verificação de segurança
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    http_response_code(403);
    exit('Acesso negado.');
}

// Pega o filtro de período do formulário
$periodo = $_POST['periodo'] ?? 'hoje';
$whereClause = '';

switch ($periodo) {
    case 'esta_semana':
        $whereClause = "WHERE YEARWEEK(data_acesso, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'semana_passada':
        $whereClause = "WHERE YEARWEEK(data_acesso, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)";
        break;
    case 'este_mes':
        $whereClause = "WHERE YEAR(data_acesso) = YEAR(CURDATE()) AND MONTH(data_acesso) = MONTH(CURDATE())";
        break;
    case 'todos':
        $whereClause = "";
        break;
    case 'hoje':
    default:
        $whereClause = "WHERE DATE(data_acesso) = CURDATE()";
        break;
}

// Busca todos os logs para o relatório (sem limite)
$sql = "SELECT la.*, u.nome, u.sobrenome 
        FROM logs_acesso la 
        LEFT JOIN usuarios u ON la.usuario_id = u.id 
        $whereClause 
        ORDER BY data_acesso DESC";
$result = $conexao->query($sql);

// Define o nome do arquivo
$filename = "relatorio_logs_acesso_" . date('Y-m-d') . ".csv";

// Define os cabeçalhos para forçar o download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // BOM para compatibilidade com Excel

// Escreve o cabeçalho do CSV
fputcsv($output, ['ID Log', 'Usuário', 'Data de Acesso', 'Endereço IP', 'User Agent'], ';');

// Escreve os dados no CSV
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['usuario_id'] ? $row['nome'] . ' ' . $row['sobrenome'] : 'Visitante',
            date('d/m/Y H:i:s', strtotime($row['data_acesso'])),
            $row['ip'],
            $row['user_agent']
        ], ';');
    }
}

fclose($output);
exit;
