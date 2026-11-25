<?php
header('Content-Type: application/json; charset=utf-8');

$user = 'dranayara@clinicaalfonso.com.br';
$pass = 'n@!arA2025';
$auth = base64_encode($user . ':' . $pass);

$urls = [
    'mail_ssl_2080'      => 'https://mail.clinicaalfonso.com.br:2080/calendars/dranayara@clinicaalfonso.com.br/calendar/',
    'mail_http_2079'     => 'http://mail.clinicaalfonso.com.br:2079/calendars/dranayara@clinicaalfonso.com.br/calendar/',
    'br272_ssl_2080'     => 'https://br272.hostgator.com.br:2080/calendars/dranayara@clinicaalfonso.com.br/calendar/',
    'br272_http_2079'    => 'http://br272.hostgator.com.br:2079/calendars/dranayara@clinicaalfonso.com.br/calendar/',
    'localhost_ssl_2080' => 'https://127.0.0.1:2080/calendars/dranayara@clinicaalfonso.com.br/calendar/',
    'localhost_http_2079'=> 'http://127.0.0.1:2079/calendars/dranayara@clinicaalfonso.com.br/calendar/'
];

$results = [];

foreach ($urls as $name => $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 3,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . $auth,
            'User-Agent: Mozilla/5.0'
        ]
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $results[$name] = [
        'code' => $code,
        'error' => $err,
        'body' => substr((string)$resp, 0, 80)
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
