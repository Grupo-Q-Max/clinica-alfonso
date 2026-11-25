<?php
// ==============================================================================
// CLÍNICA ALFONSO - AGENDAMENTO SÃO SEBASTIÃO (28/11/2026)
// Integração Direta com a Agenda de E-mail HostGator (iCalendar / CalDAV / VEVENT)
// + Bloqueio de Horário + Registro no Clinicorp + Notificação
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
    if (!file_exists($file)) return [];
    $conteudo = file_get_contents($file);
    $dados = json_decode($conteudo, true);
    return is_array($dados) ? $dados : [];
}

function salvarAgendamentos($file, $dados) {
    file_put_contents($file, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ------------------------------------------------------------------------------
// FUNÇÃO: Envia Convite de Calendário Oficial (.ICS) para o E-mail da HostGator
// Isso faz o compromisso entrar AUTOMATICAMENTE na agenda do Webmail (Roundcube / Horde / CalDAV)
// ------------------------------------------------------------------------------
function enviarConviteCalendarioHostgator($nome, $telefone, $email_paciente, $procedimento, $horario) {
    $email_clinica = 'contato@clinicaalfonso.com.br';
    
    // Calcula horários de início e término no dia 28/11/2026
    $hora_int = intval(substr($horario, 0, 2));
    $dt_start = sprintf("20261128T%02d0000", $hora_int);
    $dt_end   = sprintf("20261128T%02d0000", $hora_int + 1);
    $uid      = uniqid('alfonso_ss_') . '@clinicaalfonso.com.br';
    $dtstamp  = gmdate('Ymd\THis\Z');

    $titulo_evento = "Consulta Dra. Nayara Alfonso: {$nome} ({$horario}h)";
    $local_evento  = "São Sebastião - SP";
    $descricao_evento = "Agendamento Odontológico Exclusivo - São Sebastião\\n"
                      . "Paciente: {$nome}\\n"
                      . "WhatsApp: {$telefone}\\n"
                      . "E-mail: {$email_paciente}\\n"
                      . "Procedimento: {$procedimento}\\n"
                      . "Origem: Página Oficial São Sebastião";

    // Estrutura padrão iCalendar (RFC 5545) compatível com Webmail HostGator, Outlook, Apple Calendar e Google Agenda
    $ics_content  = "BEGIN:VCALENDAR\r\n";
    $ics_content .= "PRODID:-//Clinica Alfonso//Agenda Sao Sebastiao//PT\r\n";
    $ics_content .= "VERSION:2.0\r\n";
    $ics_content .= "CALSCALE:GREGORIAN\r\n";
    $ics_content .= "METHOD:REQUEST\r\n";
    $ics_content .= "BEGIN:VEVENT\r\n";
    $ics_content .= "UID:{$uid}\r\n";
    $ics_content .= "DTSTAMP:{$dtstamp}\r\n";
    $ics_content .= "ORGANIZER;CN=\"Clinica Alfonso\":mailto:{$email_clinica}\r\n";
    $ics_content .= "ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;CN=\"Dra. Nayara Alfonso\":mailto:{$email_clinica}\r\n";
    if (!empty($email_paciente)) {
        $ics_content .= "ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;CN=\"{$nome}\":mailto:{$email_paciente}\r\n";
    }
    $ics_content .= "SUMMARY:{$titulo_evento}\r\n";
    $ics_content .= "DESCRIPTION:{$descricao_evento}\r\n";
    $ics_content .= "LOCATION:{$local_evento}\r\n";
    $ics_content .= "DTSTART:{$dt_start}\r\n";
    $ics_content .= "DTEND:{$dt_end}\r\n";
    $ics_content .= "STATUS:CONFIRMED\r\n";
    $ics_content .= "SEQUENCE:0\r\n";
    $ics_content .= "TRANSP:OPAQUE\r\n";
    $ics_content .= "BEGIN:VALARM\r\n";
    $ics_content .= "TRIGGER:-PT60M\r\n";
    $ics_content .= "ACTION:DISPLAY\r\n";
    $ics_content .= "DESCRIPTION:Lembrete de Consulta em São Sebastião\r\n";
    $ics_content .= "END:VALARM\r\n";
    $ics_content .= "END:VEVENT\r\n";
    $ics_content .= "END:VCALENDAR\r\n";

    // Envio MIME multipart/alternative com text/calendar para o Webmail HostGator reconhecer o evento nativamente
    $boundary = "----=_Part_" . md5(time());
    
    $headers  = "From: Clinica Alfonso <{$email_clinica}>\r\n";
    $headers .= "Reply-To: {$email_clinica}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";

    $corpo  = "--{$boundary}\r\n";
    $corpo .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $corpo .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $corpo .= "NOVO AGENDAMENTO EM SÃO SEBASTIÃO (28/11)\r\n";
    $corpo .= "Horário: {$horario}h às " . sprintf("%02d:00", $hora_int + 1) . "h\r\n";
    $corpo .= "Paciente: {$nome}\r\n";
    $corpo .= "WhatsApp: {$telefone}\r\n";
    $corpo .= "E-mail: {$email_paciente}\r\n";
    $corpo .= "Procedimento: {$procedimento}\r\n\r\n";

    $corpo .= "--{$boundary}\r\n";
    $corpo .= "Content-Type: text/calendar; charset=UTF-8; method=REQUEST; name=\"convite_sao_sebastiao.ics\"\r\n";
    $corpo .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $corpo .= $ics_content . "\r\n";
    $corpo .= "--{$boundary}--\r\n";

    // Envia tanto para a clínica (para cair na agenda do Webmail) quanto para o paciente (se informou e-mail)
    @mail($email_clinica, "📅 [NOVO AGENDAMENTO] {$nome} - 28/11 às {$horario}h", $corpo, $headers);
    if (!empty($email_paciente) && filter_var($email_paciente, FILTER_VALIDATE_EMAIL)) {
        @mail($email_paciente, "Confirmação de Agendamento: 28/11 às {$horario}h • Dra. Nayara Alfonso", $corpo, $headers);
    }
}

// ------------------------------------------------------------------------------
// COMANDO DE RESET / LIBERAÇÃO (Ex: ?reset=todos ou ?reset=08:00)
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

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

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
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

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

    $agendamentos = carregarAgendamentos($appointments_file);

    // Valida se horário já foi reservado
    foreach ($agendamentos as $ag) {
        if ($ag['horario'] === $horario) {
            echo json_encode([
                'sucesso' => false,
                'erro' => "O horário das {$horario} já foi reservado. Por favor, escolha outro horário."
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
        'procedimento' => $procedimento,
        'criado_em' => date('Y-m-d H:i:s')
    ];

    $agendamentos[] = $novo_agendamento;
    salvarAgendamentos($appointments_file, $agendamentos);

    // 2. DISPARA CONVITE NATIVO ICALENDAR PARA O EMAIL / AGENDA HOSTGATOR
    enviarConviteCalendarioHostgator($nome, $telefone, $email, $procedimento, $horario);

    // 3. TAMBÉM SALVA NO CLINICORP (BACKUP AUTOMÁTICO)
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

    // Link para mensagem no WhatsApp da clínica
    $msg_zap = "Olá Dra. Nayara! Confirmei meu agendamento em São Sebastião para 28/11 às {$horario}h.\nPaciente: {$nome}\nProcedimento: {$procedimento}";
    $link_whatsapp = "https://wa.me/5511967687666?text=" . urlencode($msg_zap);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Agendamento confirmado com sucesso e adicionado à agenda!',
        'horario' => $horario,
        'nome' => $nome,
        'whatsapp_link' => $link_whatsapp
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
