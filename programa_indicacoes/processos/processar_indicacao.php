<?php
// ARQUIVO: processos/processar_indicacao.php
session_start();
require '../backend/config.php';

// SISTEMA DE LOG
function gravarLog($mensagem) {
    $arquivo = 'debug_clinicorp.txt';
    $texto = date('d/m/Y H:i:s') . " - " . $mensagem . PHP_EOL;
    file_put_contents($arquivo, $texto, FILE_APPEND);
}

function redirecionarComFeedback($type, $message, $ref_code = null) {
    $_SESSION['form_feedback'] = ['type' => $type, 'text' => $message];
    $_SESSION['form_data'] = $_POST;
    $redirect_url = "../indicar.php";
    if ($ref_code) $redirect_url .= "?ref=" . urlencode($ref_code);
    header("Location: " . $redirect_url);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") exit;

// ==========================================================
// 1. CONFIGURAÇÕES DA API OFICIAL CLINICORP (PERMANENTE)
// ==========================================================
$clinicorp_user  = "alfonso";
$clinicorp_token = "9a341852-3e39-400f-99a2-e8ddcaad247a";
$clinicorp_board = "Clinica Alfonso Rewards";

// ==========================================================
// 2. COLETA DE DADOS DO FORMULÁRIO
// ==========================================================
$ref_code_from_post = $_POST['ref_code'] ?? null;
$indicador_id = null;

// Lógica de Autenticação do Indicador
if (isset($_SESSION['user_id']) && $_SESSION['tipo_conta'] === 'indicador') {
    $indicador_id = $_SESSION['user_id'];
} elseif (!empty($ref_code_from_post)) {
    $stmt_ref = $conexao->prepare("SELECT id FROM usuarios WHERE codigo_indicador = ? AND ativo = 1");
    $stmt_ref->bind_param("s", $ref_code_from_post);
    $stmt_ref->execute();
    $result_ref = $stmt_ref->get_result();
    if ($result_ref->num_rows > 0) {
        $indicador = $result_ref->fetch_assoc();
        $indicador_id = $indicador['id'];
    }
    $stmt_ref->close();
}

if (is_null($indicador_id)) {
    redirecionarComFeedback('error', "Indicador não identificado.", $ref_code_from_post);
}

$nome_paciente = trim(htmlspecialchars($_POST['nome_paciente'] ?? ''));
$cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
$cpf_visual = !empty($cpf) ? $cpf : $nome_paciente;
$telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
$email_input = trim($_POST['email'] ?? '');
$email = filter_var($email_input, FILTER_VALIDATE_EMAIL) ?: null; 
$servicos_interesse = $_POST['servicos_interesse'] ?? [];
$outros_servicos_descricao = $_POST['outros_servicos_descricao'] ?? [];

// Validações
if (empty($nome_paciente)) redirecionarComFeedback('error', 'Nome obrigatório.', $ref_code_from_post);
if (empty($telefone)) redirecionarComFeedback('error', 'Telefone obrigatório.', $ref_code_from_post);
if (empty($servicos_interesse)) redirecionarComFeedback('error', 'Selecione um tratamento.', $ref_code_from_post);

$servicosParaInserir = [];
foreach ($servicos_interesse as $index => $servico) {
    $descricao = ($servico === 'Outros') ? trim($outros_servicos_descricao[$index] ?? '') : null;
    $servicosParaInserir[] = ['servico' => $servico, 'descricao' => $descricao];
}

try {
    // ==========================================================
    // 3. SALVAR NO BANCO LOCAL
    // ==========================================================
    $conexao->begin_transaction();
    $valor_pendente = 0.00;
    
    $queryInsercao = "INSERT INTO indicacoes (nome_paciente, cpf, telefone, email, usuario_id, status, valor_pendente, data_criacao) VALUES (?, ?, ?, ?, ?, 'Ativa', ?, NOW())";
    $stmt = $conexao->prepare($queryInsercao);
    $stmt->bind_param("ssssid", $nome_paciente, $cpf, $telefone, $email, $indicador_id, $valor_pendente);
    $stmt->execute();
    $indicacao_id = $conexao->insert_id;
    $stmt->close();

    if ($indicacao_id === 0) throw new Exception("Falha ao salvar indicação localmente");

    $queryServico = "INSERT INTO indicacao_servicos (indicacao_id, servico, descricao_outros) VALUES (?, ?, ?)";
    $stmtServico = $conexao->prepare($queryServico);
    foreach ($servicosParaInserir as $servico) {
        $stmtServico->bind_param("iss", $indicacao_id, $servico['servico'], $servico['descricao']);
        $stmtServico->execute();
    }
    $stmtServico->close();
    $conexao->commit(); // Salvo com sucesso no banco de dados local!

    // ==========================================================
    // 4. INTEGRAÇÃO CLINICORP (API OFICIAL REST V1 - PERMANENTE)
    // ==========================================================
    try {
        gravarLog("Iniciando Card Oficial para: " . ($cpf ?: $nome_paciente));

        // A. Busca o nome do indicador cadastrado
        $nome_indicador = "Site Alfonso Rewards";
        if ($indicador_id) {
            $stmtInd = $conexao->prepare("SELECT nome, sobrenome FROM usuarios WHERE id = ?");
            $stmtInd->bind_param("i", $indicador_id);
            $stmtInd->execute();
            $resInd = $stmtInd->get_result()->fetch_assoc();
            if ($resInd) {
                $nome_indicador = trim($resInd['nome'] . ' ' . $resInd['sobrenome']);
            }
            $stmtInd->close();
        }

        // B. Monta as observações detalhadas
        if (!is_array($servicos_interesse)) {
            $servicos_interesse = [$servicos_interesse];
        }
        $lista_servicos = implode(', ', $servicos_interesse);
        
        $obs_texto = "👤 NOME: " . $nome_paciente . "\n";
        if (!empty($cpf)) {
            $obs_texto .= "📄 CPF: " . $cpf . "\n";
        }
        $obs_texto .= "🤝 INDICAÇÃO: " . $nome_indicador . "\n";
        $obs_texto .= "📝 INTERESSE: " . $lista_servicos . "\n";
        
        if (!empty($outros_servicos_descricao)) {
            $descricoes_validas = array_filter($outros_servicos_descricao, function($v) { return !empty(trim($v)); });
            if (!empty($descricoes_validas)) {
                $obs_texto .= "ℹ️ DETALHES: " . implode(' | ', $descricoes_validas) . "\n";
            }
        }
        $obs_texto .= "📍 ORIGEM: Site Alfonso Rewards";

        // C. Prepara a carga de dados oficial
        $dados_lead = [
            "subscriber_id" => $clinicorp_user,
            "BoardName"     => $clinicorp_board,
            "Name"          => $cpf_visual,
            "Phone"         => $telefone,
            "Email"         => $email,
            "Notes"         => $obs_texto
        ];

        // D. Autenticação HTTP Basic Oficial (Nunca Expira)
        $auth_basic = base64_encode($clinicorp_user . ":" . $clinicorp_token);

        $ch_crm = curl_init("https://api.clinicorp.com/rest/v1/crm/add_leads");
        curl_setopt_array($ch_crm, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($dados_lead),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Basic " . $auth_basic,
                "Content-Type: application/json;charset=UTF-8"
            ]
        ]);
        
        $res_crm = curl_exec($ch_crm);
        $http_code_crm = curl_getinfo($ch_crm, CURLINFO_HTTP_CODE);
        $curl_err = curl_error($ch_crm);
        curl_close($ch_crm);
        
        gravarLog("Criar Lead Clinicorp - HTTP: $http_code_crm - Resp: " . $res_crm . ($curl_err ? " - CurlErr: $curl_err" : ""));

    } catch (Exception $e) {
        gravarLog("ERRO CRÍTICO API CLINICORP: " . $e->getMessage());
    }

    unset($_SESSION['form_data']);
    redirecionarComFeedback('success', "Sua indicação foi enviada com sucesso!", $ref_code_from_post);

} catch (Exception $e) {
    $conexao->rollback();
    error_log("ERRO BD: " . $e->getMessage());
    redirecionarComFeedback('error', "Ocorreu um erro inesperado ao salvar a indicação.", $ref_code_from_post);
}
?>
