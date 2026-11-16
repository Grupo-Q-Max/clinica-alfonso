<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$permissoes_planos = [
    'basico' => [
        'acesso_crm' => false,
        'relatorios_avancados' => false,
        'ver_painel_admin' => true,
        'acesso_logs' => false,
    ],
    'intermediario' => [
        'acesso_crm' => false,
        'relatorios_avancados' => true,
        'ver_painel_admin' => true,
        'acesso_logs' => true,
    ],
    'premium' => [
        'acesso_crm' => true,
        'relatorios_avancados' => true,
        'ver_painel_admin' => true,
        'acesso_logs' => true,
    ]
];

function pode(string $funcionalidade): bool
{
    global $permissoes_planos;

    $plano_atual = $_SESSION['plano_cliente'] ?? 'intermediario';

    if (isset($permissoes_planos[$plano_atual][$funcionalidade])) {
        return $permissoes_planos[$plano_atual][$funcionalidade];
    }

    return false;
}
?>