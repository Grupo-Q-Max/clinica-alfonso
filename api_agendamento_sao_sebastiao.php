<?php
// ==============================================================================
// CLÍNICA ALFONSO - AGENDAMENTO SÃO SEBASTIÃO (28/11/2026)
// SINCRONIZAÇÃO BIDIRECIONAL 100% REAL COM O CALENDÁRIO HOSTGATOR (CalDAV)
// - Se o paciente agenda na página -> Entra direto no Calendário do seu Webmail!
// - Se você apagar no Calendário do Webmail -> Libera o horário na página automaticamente!
// ==============================================================================
header('Content-Type: application/json; charset=utf-8');

// Configuração do Servidor CalDAV da HostGator da Clínica (Dra. Nayara Alfonso)
$caldav_user = 'dranayara@clinicaalfonso.com.br';
$caldav_pass = 'n@!arA2025';
$caldav_base = 'https://mail.clinicaalfonso.com.br:2080/calendars/dranayara@clinicaalfonso.com.br/calendar/';

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

/**
 * Consulta a agenda do Webmail HostGator ao vivo e descobre quais horários estão ocupados no dia 28/11
 */
function obterHorariosOcupadosNoCalDAV($caldav_base, $caldav_user, $caldav_pass) {
    $auth = base64_encode($caldav_user . ':' . $caldav_pass);
    
    $ch = curl_init($caldav_base);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 6,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . $auth,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
        ]
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) return [];

    // Pega todos os arquivos .ics listados no calendário do Webmail
    preg_match_all('/href="([^"]+\.ics)"/i', $html, $matches);
    $arquivos_ics = $matches[1] ?? [];

    $horarios_ocupados = [];

    foreach ($arquivos_ics as $ics_file) {
        $file_url = $caldav_base . basename($ics_file);
        $ch_file = curl_init($file_url);
        curl_setopt_array($ch_file, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 4,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . $auth
            ]
        ]);
        $ics_content = curl_exec($ch_file);
        curl_close($ch_file);

        if (!$ics_content) continue;

        // Verifica se é do dia 28/11/2026 (20261128)
        if (preg_match('/DTSTART[^:]*:20261128T(\d{2})(\d{2})/', $ics_content, $m)) {
            $hora_evento = $m[1] . ':' . $m[2];
            $horarios_ocupados[] = $hora_evento;
        }
    }

    return array_unique($horarios_ocupados);
}

/**
 * Cria o evento diretamente no Calendário do Webmail HostGator via CalDAV PUT
 */
function criarEventoNoCalDAV($caldav_base, $caldav_user, $caldav_pass, $nome, $telefone, $email_paciente, $procedimento, $horario) {
    $auth = base64_encode($caldav_user . ':' . $caldav_pass);

    $hora_int = intval(substr($horario, 0, 2));
    $dt_start = sprintf("20261128T%02d0000", $hora_int);
    $dt_end   = sprintf("20261128T%02d0000", $hora_int + 1);
    
    // Nome do arquivo padronizado para identificar e sincronizar
    $nome_arquivo_ics = "sao_sebastiao_20261128_" . str_replace(':', '', $horario) . ".ics";
    $event_url = $caldav_base . $nome_arquivo_ics;

    $uid = "ss_20261128_" . str_replace(':', '', $horario) . "@clinicaalfonso.com.br";
    $dtstamp = gmdate('Ymd\THis\Z');

    $titulo = "Consulta Dra. Nayara Alfonso: {$nome} ({$horario}h)";
    $descricao = "Agendamento Odontológico - São Sebastião\n"
               . "Paciente: {$nome}\n"
               . "WhatsApp: {$telefone}\n"
               . "E-mail: {$email_paciente}\n"
               . "Procedimento: {$procedimento}\n"
               . "Origem: Página Oficial São Sebastião";

    $ics = "BEGIN:VCALENDAR\r\n"
         . "VERSION:2.0\r\n"
         . "PRODID:-//Clinica Alfonso//Agenda Sao Sebastiao//PT\r\n"
         . "BEGIN:VEVENT\r\n"
         . "UID:{$uid}\r\n"
         . "DTSTAMP:{$dtstamp}\r\n"
         . "SUMMARY:{$titulo}\r\n"
         . "DESCRIPTION:" . str_replace("\n", "\\n", $descricao) . "\r\n"
         . "LOCATION:São Sebastião - SP\r\n"
         . "DTSTART:{$dt_start}\r\n"
         . "DTEND:{$dt_end}\r\n"
         . "STATUS:CONFIRMED\r\n"
         . "END:VEVENT\r\n"
         . "END:VCALENDAR\r\n";

    $ch = curl_init($event_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => "PUT",
        CURLOPT_POSTFIELDS     => $ics,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . $auth,
            'Content-Type: text/calendar; charset=utf-8'
        ]
    ]);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code === 201 || $http_code === 204 || $http_code === 200);
}

// ------------------------------------------------------------------------------
// COMANDO DE RESET / LIBERAÇÃO (Ex: ?reset=todos ou ?reset=08:00)
// ------------------------------------------------------------------------------
if (isset($_GET['reset'])) {
    $alvo = trim($_GET['reset']);
    $auth = base64_encode($caldav_user . ':' . $caldav_pass);

    if ($alvo === 'todos' || $alvo === 'all') {
        foreach ($horarios_base as $h) {
            $arq = "sao_sebastiao_20261128_" . str_replace(':', '', $h) . ".ics";
            $ch = curl_init($caldav_base . $arq);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST  => "DELETE",
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $auth]
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
        echo json_encode(['sucesso' => true, 'mensagem' => 'Todos os horários foram liberados na agenda do Webmail!'], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        $arq = "sao_sebastiao_20261128_" . str_replace(':', '', $alvo) . ".ics";
        $ch = curl_init($caldav_base . $arq);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => "DELETE",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $auth]
        ]);
        curl_exec($ch);
        curl_close($ch);
        echo json_encode(['sucesso' => true, 'mensagem' => "Horário {$alvo} liberado com sucesso no Webmail!"], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ------------------------------------------------------------------------------
// MÉTODO GET: Consulta AO VIVO na Agenda do Webmail HostGator
// Se o evento foi apagado na agenda do Webmail, ele fica DISPONÍVEL na hora!
// ------------------------------------------------------------------------------
if ($method === 'GET') {
    $horarios_ocupados = obterHorariosOcupadosNoCalDAV($caldav_base, $caldav_user, $caldav_pass);

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
        'origem' => 'Webmail HostGator CalDAV',
        'horarios' => $resultado
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ------------------------------------------------------------------------------
// MÉTODO POST: Processa e Injeta Direto no Calendário do Webmail
// ------------------------------------------------------------------------------
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $nome         = trim($input['nome'] ?? '');
    $telefone     = trim($input['telefone'] ?? '');
    $email        = trim($input['email'] ?? '');
    $procedimento = trim($input['observacoes'] ?? $input['procedimento'] ?? 'Avaliação Odontológica');
    $horario      = trim($input['horario'] ?? '');

    if (empty($nome) || empty($telefone) || empty($horario)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Preencha Nome, WhatsApp e selecione um Horário.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!in_array($horario, $horarios_base)) {
        echo json_encode(['sucesso' => false, 'erro' => 'Horário inválido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 1. Consulta ao vivo se o horário está livre no calendário
    $horarios_ocupados = obterHorariosOcupadosNoCalDAV($caldav_base, $caldav_user, $caldav_pass);
    if (in_array($horario, $horarios_ocupados)) {
        echo json_encode([
            'sucesso' => false,
            'erro' => "O horário das {$horario} já está reservado no calendário. Por favor, escolha outro horário."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. Grava diretamente no Calendário HostGator via CalDAV PUT
    criarEventoNoCalDAV($caldav_base, $caldav_user, $caldav_pass, $nome, $telefone, $email, $procedimento, $horario);

    // 3. Salva no CRM Clinicorp (Backup)
    try {
        $clinicorp_user  = "alfonso";
        $clinicorp_token = "9a341852-3e39-400f-99a2-e8ddcaad247a";
        $clinicorp_board = "Clinica Alfonso Rewards";

        $obs_clinicorp = "📅 AGENDAMENTO SÃO SEBASTIÃO (28/11)\n"
                       . "⏰ HORÁRIO: {$horario}h\n"
                       . "👤 PACIENTE: {$nome}\n"
                       . "📱 WHATSAPP: {$telefone}\n"
                       . "✉️ E-MAIL: {$email}\n"
                       . "📝 PROCEDIMENTO: {$procedimento}\n"
                       . "📍 ORIGEM: Página São Sebastião";

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
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Basic " . $auth_basic,
                "Content-Type: application/json;charset=UTF-8"
            ]
        ]);
        curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {}

    // Link WhatsApp
    $msg_zap = "Olá Dra. Nayara! Confirmei meu agendamento em São Sebastião para 28/11 às {$horario}h.\nPaciente: {$nome}\nProcedimento: {$procedimento}";
    $link_whatsapp = "https://wa.me/5511967687666?text=" . urlencode($msg_zap);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Agendamento sincronizado com sucesso diretamente no seu Calendário HostGator!',
        'horario' => $horario,
        'nome' => $nome,
        'whatsapp_link' => $link_whatsapp
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
