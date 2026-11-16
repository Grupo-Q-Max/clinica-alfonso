<?php
session_start();
// Define que a resposta será sempre em formato JSON
header('Content-Type: application/json');
require 'backend/config.php';

// Validação de segurança aprimorada
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    http_response_code(403); // Código para "Acesso Proibido"
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}

// Validação do ID da indicação
$indicacao_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$indicacao_id) {
    http_response_code(400); // Código para "Requisição Inválida"
    echo json_encode(['error' => 'ID da indicação inválido.']);
    exit;
}

try {
    // --- CONSULTA SQL CORRIGIDA ---
    // A query agora busca os serviços indicados e junta com as novas regras de cashback
    // da tabela 'servicos', usando o NOME do serviço como chave.
    $query = "
        SELECT 
            i_s.servico,
            s.tipo_cashback,
            s.valor_cashback,
            s.tem_limite,
            s.limite_cashback
        FROM indicacao_servicos AS i_s
        LEFT JOIN servicos AS s ON i_s.servico = s.nome
        WHERE i_s.indicacao_id = ?
    ";

    $stmt = $conexao->prepare($query);
    $stmt->bind_param("i", $indicacao_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $servicos = [];
    while ($row = $result->fetch_assoc()) {
        $servicos[] = $row;
    }

    $stmt->close();

    // Devolve os dados dos serviços em formato JSON para o JavaScript
    echo json_encode($servicos);
} catch (Exception $e) {
    http_response_code(500); // Código para "Erro Interno do Servidor"
    echo json_encode(['error' => 'Erro ao consultar o banco de dados.', 'details' => $e->getMessage()]);
}
