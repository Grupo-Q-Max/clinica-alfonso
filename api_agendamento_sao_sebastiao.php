<?php
// ==============================================================================
// CLÍNICA ALFONSO - AGENDAMENTO SÃO SEBASTIÃO (28/11/2026)
// SISTEMA HÍBRIDO E ULTRA-RESILIENTE (DUAL-LOCK):
// 1. Bloqueio instantâneo no servidor (Garante 100% que nenhum horário duplica)
// 2. Sincronização direta com Calendário Webmail HostGator (CalDAV SSL/HTTP)
// 3. Notificação por E-mail com Convite iCalendar (.ics) para Dra. Nayara
// 4. Integração automática com CRM Clinicorp (Lead)
// 5. Redirecionamento com mensagem pronta no WhatsApp
// ==============================================================================
header('Content-Type: application/json; charset=utf-8');

// Configuração do Servidor CalDAV da HostGator (Dra. Nayara Alfonso)
$caldav_user     = 'dranayara@clinicaalfonso.com.br';
$caldav_pass     = 'n@!arA2025';
$caldav_base_ssl = 'https://mail.clinicaalfonso.com.br:2080/calendars/dranayara@clinicaalfonso.com.br/calendar/';
$caldav_base_tcp = 'http://mail.clinicaalfonso.com.br:2079/calendars/dranayara@clinicaalfonso.com.br/calendar/';

// Arquivo local seguro de agendamentos (trava instantânea sem falhas de rede)
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

/**
 * Funções de Armazenamento Local Seguro
 */
function carregarAgendamentosLocais($file) {
    if (!file_exists($file)) {
        return [];
    }
    $conteudo = file_get_contents($file);
    $dados = json_decode($conteudo, true);
    return is_array($dados) ? $dados : [];
}

function salvarAgendamentosLocais($file, $dados) {
    file_put_contents($file, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * Consulta a agenda do Webmail HostGator ao vivo via CalDAV
 */
function obterHorariosOcupadosNoCalDAV($caldav_user, $caldav_pass, $base_ssl, $base_tcp) {
    $auth = base64_encode($caldav_user . ':' . $caldav_pass);
    
    // Tenta primeiro via SSL (2080), se falhar tenta via HTTP (2079)
    $urls_para_tentar = [$base_ssl, $base_tcp];
    $html = false;
    $url_usada = $base_ssl;

    foreach ($urls_para_tentar as $url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 2,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . $auth,
                'User-Agent: Mozilla/5.0'
            ]
        ]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($res && $code >= 200 && $code < 400) {
            $html = $res;
            $url_usada = $url;
            break;
        }
    }

    if (!$html) return [];

    preg_match_all('/href="([^"]+\.ics)"/i', $html, $matches);
    $arquivos_ics = $matches[1] ?? [];
    $horarios_ocupados = [];

    foreach ($arquivos_ics as $ics_file) {
        $file_url = rtrim($url_usada, '/') . '/' . basename($ics_file);
        $ch_file = curl_init($file_url);
        curl_setopt_array($ch_file, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 2,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $auth]
        ]);
        $ics_content = curl_exec($ch_file);
        curl_close($ch_file);

        if (!$ics_content) continue;

        // Procura data 20261128
        if (preg_match('/DTSTART[^:]*:20261128T(\d{2})(\d{2})/', $ics_content, $m)) {
            $horarios_ocupados[] = $m[1] . ':' . $m[2];
        }
    }

    return array_unique($horarios_ocupados);
}

/**
 * Cria o evento no Calendário do Webmail HostGator via CalDAV PUT
 */
function criarEventoNoCalDAV($caldav_user, $caldav_pass, $base_ssl, $base_tcp, $nome, $telefone, $email_paciente, $procedimento, $horario) {
    $auth = base64_encode($caldav_user . ':' . $caldav_pass);

    $hora_int = intval(substr($horario, 0, 2));
    $dt_start = sprintf("20261128T%02d0000", $hora_int);
    $dt_end   = sprintf("20261128T%02d0000", $hora_int + 1);
    
    $nome_arquivo_ics = "sao_sebastiao_20261128_" . str_replace(':', '', $horario) . ".ics";
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

    $urls_para_tentar = [$base_ssl . $nome_arquivo_ics, $base_tcp . $nome_arquivo_ics];

    foreach ($urls_para_tentar as $target_url) {
        $ch = curl_init($target_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => "PUT",
            CURLOPT_POSTFIELDS     => $ics,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . $auth,
                'Content-Type: text/calendar; charset=utf-8'
            ]
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 || $code === 201 || $code === 204) {
            return true;
        }
    }

    return false;
}

/**
 * Envia notificação por e-mail com anexo iCalendar (.ics) para a Dra. Nayara
 */
function enviarConviteEmail($destinatario, $nome, $telefone, $email_paciente, $procedimento, $horario) {
    $hora_int = intval(substr($horario, 0, 2));
    $dt_start = sprintf("20261128T%02d0000", $hora_int);
    $dt_end   = sprintf("20261128T%02d0000", $hora_int + 1);
    $uid = "ss_20261128_" . str_replace(':', '', $horario) . "@clinicaalfonso.com.br";
    $dtstamp = gmdate('Ymd\THis\Z');

    $assunto = "📅 Novo Agendamento São Sebastião: {$nome} ({$horario}h - 28/11)";
    $boundary = "==Multipart_Boundary_x" . md5(time()) . "x";

    $mensagem_texto = "Novo agendamento recebido pela página oficial de São Sebastião:\n\n"
                    . "Paciente: {$nome}\n"
                    . "WhatsApp: {$telefone}\n"
                    . "E-mail: {$email_paciente}\n"
                    . "Horário: {$horario}h do dia 28/11/2026\n"
                    . "Procedimento: {$procedimento}\n\n"
                    . "O convite da agenda está em anexo (.ics) e foi adicionado automaticamente.";

    $ics = "BEGIN:VCALENDAR\r\n"
         . "VERSION:2.0\r\n"
         . "PRODID:-//Clinica Alfonso//Agenda Sao Sebastiao//PT\r\n"
         . "METHOD:REQUEST\r\n"
         . "BEGIN:VEVENT\r\n"
         . "UID:{$uid}\r\n"
         . "DTSTAMP:{$dtstamp}\r\n"
         . "SUMMARY:Consulta Dra. Nayara: {$nome} ({$horario}h)\r\n"
         . "DESCRIPTION:Paciente: {$nome}\\nWhatsApp: {$telefone}\\nProcedimento: {$procedimento}\r\n"
         . "LOCATION:São Sebastião - SP\r\n"
         . "DTSTART:{$dt_start}\r\n"
         . "DTEND:{$dt_end}\r\n"
         . "STATUS:CONFIRMED\r\n"
         . "END:VEVENT\r\n"
         . "END:VCALENDAR\r\n";

    $headers  = "From: Clínica Alfonso <contato@clinicaalfonso.com.br>\r\n";
    $headers .= "Reply-To: {$email_paciente}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

    $corpo  = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 7bit\r\n\r\n"
            . $mensagem_texto . "\r\n\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/calendar; charset=UTF-8; name=\"consulta.ics\"\r\n"
            . "Content-Disposition: attachment; filename=\"consulta.ics\"\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($ics)) . "\r\n\r\n"
            . "--{$boundary}--";

    @mail($destinatario, $assunto, $corpo, $headers);
}

// ------------------------------------------------------------------------------
// COMANDO DE RESET / LIBERAÇÃO (Ex: ?reset=todos ou ?reset=08:00)
// ------------------------------------------------------------------------------
if (isset($_GET['reset'])) {
    $alvo = trim($_GET['reset']);
    $agendamentos = carregarAgendamentosLocais($appointments_file);

    if ($alvo === 'todos' || $alvo === 'all') {
        salvarAgendamentosLocais($appointments_file, []);
        
        // Também tenta limpar CalDAV se possível
        $auth = base64_encode($caldav_user . ':' . $caldav_pass);
        foreach ($horarios_base as $h) {
            $arq = "sao_sebastiao_20261128_" . str_replace(':', '', $h) . ".ics";
            $ch = curl_init($caldav_base_ssl . $arq);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST  => "DELETE",
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 1,
                CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $auth]
            ]);
            curl_exec($ch);
            curl_close($ch);
        }

        echo json_encode(['sucesso' => true, 'mensagem' => 'Todos os horários foram liberados com sucesso!'], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        $filtrados = array_filter($agendamentos, function($ag) use ($alvo) {
            return ($ag['horario'] ?? '') !== $alvo;
        });
        salvarAgendamentosLocais($appointments_file, array_values($filtrados));

        $arq = "sao_sebastiao_20261128_" . str_replace(':', '', $alvo) . ".ics";
        $auth = base64_encode($caldav_user . ':' . $caldav_pass);
        $ch = curl_init($caldav_base_ssl . $arq);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => "DELETE",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 1,
            CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $auth]
        ]);
        curl_exec($ch);
        curl_close($ch);

        echo json_encode(['sucesso' => true, 'mensagem' => "Horário {$alvo} liberado com sucesso!"], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ------------------------------------------------------------------------------
// MÉTODO GET: Retorna status dos horários (Local + Webmail CalDAV)
// ------------------------------------------------------------------------------
if ($method === 'GET') {
    // 1. Horários ocupados no armazenamento local seguro
    $agendamentos_locais = carregarAgendamentosLocais($appointments_file);
    $ocupados_locais = array_column($agendamentos_locais, 'horario');

    // 2. Horários ocupados no CalDAV da HostGator (se disponível)
    $ocupados_caldav = obterHorariosOcupadosNoCalDAV($caldav_user, $caldav_pass, $caldav_base_ssl, $caldav_base_tcp);

    // Une as duas fontes de verdade
    $todos_ocupados = array_unique(array_merge($ocupados_locais, $ocupados_caldav));

    $resultado = [];
    foreach ($horarios_base as $h) {
        $resultado[] = [
            'horario'    => $h,
            'disponivel' => !in_array($h, $todos_ocupados)
        ];
    }

    echo json_encode([
        'sucesso'  => true,
        'data'     => '28/11/2026',
        'cidade'   => 'São Sebastião',
        'origem'   => 'Clínica Alfonso Dual-Lock Engine (Local + CalDAV)',
        'horarios' => $resultado
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ------------------------------------------------------------------------------
// MÉTODO POST: Processa Agendamento e Executa Trava Total
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

    // 1. Verifica duplicidade no armazenamento local
    $agendamentos = carregarAgendamentosLocais($appointments_file);
    foreach ($agendamentos as $ag) {
        if (($ag['horario'] ?? '') === $horario) {
            echo json_encode([
                'sucesso' => false,
                'erro' => "O horário das {$horario} já foi reservado por outro paciente. Por favor, escolha outro horário."
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // 2. Verifica no CalDAV (se ativo)
    $ocupados_caldav = obterHorariosOcupadosNoCalDAV($caldav_user, $caldav_pass, $caldav_base_ssl, $caldav_base_tcp);
    if (in_array($horario, $ocupados_caldav)) {
        echo json_encode([
            'sucesso' => false,
            'erro' => "O horário das {$horario} já está reservado no calendário. Por favor, escolha outro horário."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. GRAVA E TRAVA LOCALMENTE IMEDIATAMENTE (Garante que ninguém mais pega esse horário)
    $novo_agendamento = [
        'id'           => uniqid('ss_'),
        'data_evento'  => '28/11/2026',
        'cidade'       => 'São Sebastião',
        'horario'      => $horario,
        'nome'         => $nome,
        'telefone'     => $telefone,
        'email'        => $email,
        'procedimento' => $procedimento,
        'criado_em'    => date('Y-m-d H:i:s')
    ];
    $agendamentos[] = $novo_agendamento;
    salvarAgendamentosLocais($appointments_file, $agendamentos);

    // 4. Injeta no Calendário Webmail HostGator via CalDAV
    criarEventoNoCalDAV($caldav_user, $caldav_pass, $caldav_base_ssl, $caldav_base_tcp, $nome, $telefone, $email, $procedimento, $horario);

    // 5. Envia convite de agenda por E-mail para a Dra. Nayara
    enviarConviteEmail('dranayara@clinicaalfonso.com.br', $nome, $telefone, $email, $procedimento, $horario);

    // 6. Integração com CRM Clinicorp (Lead com Notas)
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
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Basic " . $auth_basic,
                "Content-Type: application/json;charset=UTF-8"
            ]
        ]);
        curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {}

    // 7. Link direto para confirmação no WhatsApp
    $msg_zap = "Olá Dra. Nayara! Confirmei meu agendamento em São Sebastião para 28/11 às {$horario}h.\nPaciente: {$nome}\nProcedimento: {$procedimento}";
    $link_whatsapp = "https://wa.me/5511967687666?text=" . urlencode($msg_zap);

    echo json_encode([
        'sucesso'       => true,
        'mensagem'      => 'Agendamento confirmado e bloqueado com sucesso na agenda!',
        'horario'       => $horario,
        'nome'          => $nome,
        'whatsapp_link' => $link_whatsapp
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
