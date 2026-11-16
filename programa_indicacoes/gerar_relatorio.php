<?php
session_start();
require 'backend/config.php';

// Apenas administradores podem gerar relatórios
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    http_response_code(403);
    exit('Acesso negado.');
}

// Pega o filtro de status do formulário do pop-up
$filtro_status = $_POST['filtro_status'] ?? 'todos';

// 1. SELEÇÃO DE CAMPOS RELEVANTES DO BANCO DE DADOS
$sql = "SELECT 
            id, nome, sobrenome, nome_social, cpf, email, telefone,
            data_nascimento, genero, saldo, codigo_indicador, indicado_por,
            endereco, numero, bairro, cidade, estado, cep,
            ativo, tipo_conta, origem_cadastro, data_criacao 
        FROM usuarios";

$params = [];
$types = '';

// Aplica o filtro de status na consulta SQL, se necessário
if ($filtro_status === 'ativos') {
    $sql .= " WHERE ativo = ?";
    $params[] = 1;
    $types .= 'i';
} elseif ($filtro_status === 'inativos') {
    $sql .= " WHERE ativo = ?";
    $params[] = 0;
    $types .= 'i';
}
$sql .= " ORDER BY id ASC";

// Prepara e executa a consulta de forma segura
$stmt = $conexao->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Define o nome do arquivo que será baixado
$filename = "relatorio_usuarios_" . date('Y-m-d_H-i-s') . ".csv";

// Define os cabeçalhos HTTP para forçar o download do arquivo
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Cria um ponteiro para a saída do PHP, que será o nosso arquivo
$output = fopen('php://output', 'w');

// Adiciona o BOM (Byte Order Mark) para garantir a correta exibição de acentos no Excel
fwrite($output, "\xEF\xBB\xBF");

// 2. CABEÇALHOS DAS COLUNAS (TÍTULOS ORGANIZADOS)
fputcsv($output, [
    'ID',
    'Nome Completo',
    'Nome Social',
    'CPF',
    'Email',
    'Telefone',
    'Status',
    'Tipo de Conta',
    'Saldo (R$)',
    'Data de Nascimento',
    'Gênero',
    'Endereço Completo',
    'CEP',
    'Código Indicador',
    'Indicado Por (Código)',
    'Origem do Cadastro',
    'Data de Cadastro'
], ';'); // ALTERADO: Usando ponto e vírgula como delimitador

// 3. PROCESSAMENTO E FORMATAÇÃO DOS DADOS
while ($row = $result->fetch_assoc()) {
    // Formata o status para um texto legível
    $status_texto = ($row['ativo'] == 1) ? 'Ativo' : 'Inativo';

    // Formata o saldo como moeda brasileira
    $saldo_formatado = number_format($row['saldo'], 2, ',', '.');

    // Concatena o endereço em uma única coluna para melhor visualização
    $endereco_parts = array_filter([
        $row['endereco'],
        $row['numero'] ? 'Nº ' . $row['numero'] : '',
        $row['bairro'],
        $row['cidade'],
        $row['estado']
    ]);
    $endereco_completo = implode(', ', $endereco_parts);

    // Formata as datas para o padrão brasileiro (ou deixa em branco se for nula)
    $data_nasc_formatada = !empty($row['data_nascimento']) ? date('d/m/Y', strtotime($row['data_nascimento'])) : '';
    $data_cadastro_formatada = !empty($row['data_criacao']) ? date('d/m/Y H:i', strtotime($row['data_criacao'])) : '';

    // Monta a linha com os dados formatados na ordem correta do cabeçalho
    fputcsv($output, [
        $row['id'],
        $row['nome'] . ' ' . $row['sobrenome'],
        $row['nome_social'] ?? '',
        $row['cpf'] ?? '',
        $row['email'],
        $row['telefone'] ?? '',
        $status_texto,
        ucfirst($row['tipo_conta']), // Deixa a primeira letra maiúscula
        $saldo_formatado,
        $data_nasc_formatada,
        $row['genero'] ?? '',
        $endereco_completo,
        $row['cep'] ?? '',
        $row['codigo_indicador'] ?? '',
        $row['indicado_por'] ?? '',
        $row['origem_cadastro'],
        $data_cadastro_formatada
    ], ';'); // ALTERADO: Usando ponto e vírgula como delimitador
}

fclose($output);
exit;
