<?php
// ==============================================================================
// CLÍNICA ALFONSO - AGENDAMENTO ESPECIAL SÃO SEBASTIÃO (28/11)
// API de Verificação de Horários e Gravação no Clinicorp
// ==============================================================================
header('Content-Type: application/json; charset=utf-8');

$appointments_file = __DIR__ . '/sao_sebastiao_agendamentos.json';

// Horários do dia 28/11 (08h às 16h, de hora em hora)
$horarios_base = [
    '08:00',
    '09:00',
    '10:00',
    '11:00',
    '12:00',
    '13:00',
    '14:00',
    '15:00',
    '16:00'
];

function carregarAgendamentos($file) {
    if (!file_exists($file)) {
        return [];
    }
    $conteudo = file_get_contents($file);
    $dados = json_decode($conteudo, true);
    return is_array($dados) ? $dados : [];
}

function salvarAgendamentos($file, $dados) {
    file_put_contents($file, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ------------------------------------------------------------------------------
// COMANDO DE RESET / LIBERAÇÃO (Ex: ?reset=08:00 ou ?reset=todos)
// ------------------------------------------------------------------------------
if (isset($_GET['reset'])) {
    $alvo = trim($_GET['reset']);
    if ($alvo === 'todos' || $alvo === 'all') {
        salvarAgendamentos($appointments_file, []);
        echo json_encode(['sucesso' => true, 'mensagem' => 'Todos os horários foram liberados com sucesso!'], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        $agendamentos = carregarAgendamentos($appointments_file);
        $filtrados = array_values(array_filter($agendamentos, function($a) use ($alvo) {
            return $a['horario'] !== $alvo;
        }));
        salvarAgendamentos($appointments_file, $filtrados);
        echo json_encode(['sucesso' => true, 'mensagem' => "Horário {$alvo} liberado com sucesso!"], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ------------------------------------------------------------------------------
// MÉTODO GET: Retorna os horários com status (disponível ou ocupado)
// ------------------------------------------------------------------------------
if ($method === 'GET') {
    $ocupados = carregarAgendamentos($appointments_file);
    $horarios_ocupados = array_column($ocupados, 'horario');

    $resultado = [];
    foreach ($horarios_base as $h) {
        $resultado[] = [
            'horario' => $h,
            'disponivel' => !in_array($h, $horarios_ocupados)
        ];
    }

    echo json_encode([
        'sucesso' => true,
        'data' => '28/11/2026',
        'cidade' => 'São Sebastião',
        'horarios' => $resultado
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ------------------------------------------------------------------------------
// MÉTODO POST: Processa o agendamento
// ------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $nome     = trim($input['nome'] ?? '');
    $telefone = trim($input['telefone'] ?? '');
    $email    = trim($input['email'] ?? '');
    $horario  = trim($input['horario'] ?? '');
    $obs      = trim($input['observacoes'] ?? '');

    if (empty($nome) || empty($telefone) || empty($horario)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Preencha Nome, WhatsApp e selecione um Horário.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!in_array($horario, $horarios_base)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Horário inválido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $agendamentos = carregarAgendamentos($appointments_file);

    // Valida se horário já foi reservado
    foreach ($agendamentos as $ag) {
        if ($ag['horario'] === $horario) {
            echo json_encode([
                'sucesso' => false,
                'erro' => "O horário das {$horario} acabou de ser preenchido por outro paciente. Por favor, escolha outro horário."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // 1. REGISTRA LOCALMENTE (trava imediatamente o horário)
    $novo_agendamento = [
        'id' => uniqid('ss_'),
        'data_evento' => '28/11/2026',
        'cidade' => 'São Sebastião',
        'horario' => $horario,
        'nome' => $nome,
        'telefone' => $telefone,
        'email' => $email,
        'observacoes' => $obs,
        'criado_em' => date('Y-m-d H:i:s'),
        'clinicorp_status' => 'PENDENTE'
    ];

    // 2. INTEGRAÇÃO COM CLINICORP (API REST V1 OFICIAL)
    $clinicorp_user  = "alfonso";
    $clinicorp_token = "9a341852-3e39-400f-99a2-e8ddcaad247a";
    $clinicorp_board = "Clinica Alfonso Rewards";

    $obs_clinicorp = "📅 AGENDAMENTO CONFIRMADO: SÃO SEBASTIÃO (28/11)\n";
    $obs_clinicorp .= "⏰ HORÁRIO: " . $horario . "h\n";
    $obs_clinicorp .= "👤 PACIENTE: " . $nome . "\n";
    $obs_clinicorp .= "📱 WHATSAPP: " . $telefone . "\n";
    if (!empty($email)) {
        $obs_clinicorp .= "✉️ E-MAIL: " . $email . "\n";
    }
    if (!empty($obs)) {
        $obs_clinicorp .= "📝 OBS: " . $obs . "\n";
    }
    $obs_clinicorp .= "📍 ORIGEM: Página Exclusiva São Sebastião (Landing Page)\n";
    $obs_clinicorp .= "⚠️ ATENÇÃO: Horário reservado automaticamente na agenda!";

    $dados_lead = [
        "subscriber_id" => $clinicorp_user,
        "BoardName"     => $clinicorp_board,
        "Name"          => $nome,
        "Phone"         => $telefone,
        "Email"         => $email,
        "Notes"         => $obs_clinicorp
    ];

    $auth_basic = base64_encode($clinicorp_user . ":" . $clinicorp_token);

    $ch = curl_init("https://api.clinicorp.com/rest/v1/crm/add_leads");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($dados_lead),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Basic " . $auth_basic,
            "Content-Type: application/json;charset=UTF-8"
        ]
    ]);

    $resp_clinicorp = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err = curl_error($ch);
    curl_close($ch);

    $novo_agendamento['clinicorp_http'] = $http_code;
    $novo_agendamento['clinicorp_resp'] = $resp_clinicorp;
    $novo_agendamento['clinicorp_status'] = ($http_code >= 200 && $http_code < 300) ? 'ENVIADO' : 'ERRO';

    // Salva na lista
    $agendamentos[] = $novo_agendamento;
    salvarAgendamentos($appointments_file, $agendamentos);

    // Link para mensagem no WhatsApp da clínica para notificação
    $msg_zap = "Olá, acabei de agendar meu horário em São Sebastião para 28/11 às {$horario}h! Meu nome é {$nome}.";
    $link_whatsapp = "https://wa.me/5511967687666?text=" . urlencode($msg_zap);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Agendamento confirmado com sucesso!',
        'horario' => $horario,
        'nome' => $nome,
        'whatsapp_link' => $link_whatsapp
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
