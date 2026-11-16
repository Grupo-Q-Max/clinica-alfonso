<?php
session_start();
require 'backend/config.php';
require_once 'backend/permissoes.php';

// Validação de segurança e permissão de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    header("Location: login.php");
    exit;
}
if (!pode('acesso_logs')) {
    header("Location: admin.php?erro=acesso_negado");
    exit;
}

// --- LÓGICA DE CONTROLE DE VISUALIZAÇÃO ---
$tipo_log = $_GET['tipo'] ?? 'acesso'; // 'acesso' ou 'navegacao'

// --- LÓGICA PARA LOGS DE ACESSO ---
if ($tipo_log === 'acesso') {
    $periodo = $_GET['periodo'] ?? 'hoje';
    $whereClause = '';
    $tituloPeriodo = 'Hoje';

    switch ($periodo) {
        case 'esta_semana':
            $whereClause = "WHERE YEARWEEK(la.data_acesso, 1) = YEARWEEK(CURDATE(), 1)";
            $tituloPeriodo = 'Esta Semana';
            break;
        case 'semana_passada':
            $whereClause = "WHERE YEARWEEK(la.data_acesso, 1) = YEARWEEK(CURDATE() - INTERVAL 1 WEEK, 1)";
            $tituloPeriodo = 'Semana Passada';
            break;
        case 'este_mes':
            $whereClause = "WHERE YEAR(la.data_acesso) = YEAR(CURDATE()) AND MONTH(la.data_acesso) = MONTH(CURDATE())";
            $tituloPeriodo = 'Este Mês';
            break;
        case 'todos':
            $whereClause = "";
            $tituloPeriodo = 'Todos os Períodos';
            break;
        case 'hoje':
        default:
            $whereClause = "WHERE DATE(la.data_acesso) = CURDATE()";
            break;
    }

    // Busca estatísticas de acesso
    $sqlStats = "SELECT COUNT(*) as total_acessos, COUNT(DISTINCT ip) as visitantes_unicos FROM logs_acesso $whereClause";
    $resultStats = $conexao->query(str_replace('la.', '', $sqlStats)); // Remove alias for this specific query
    $stats = $resultStats->fetch_assoc();

    // Busca logs de acesso
    $sqlLogs = "SELECT la.*, u.nome, u.sobrenome FROM logs_acesso la LEFT JOIN usuarios u ON la.usuario_id = u.id $whereClause ORDER BY la.data_acesso DESC LIMIT 200";
    $resultLogs = $conexao->query($sqlLogs);
}

// --- LÓGICA PARA LOGS DE NAVEGAÇÃO ---
if ($tipo_log === 'navegacao') {
    $registros_por_pagina = 20;
    $pagina_atual = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;
    $offset = ($pagina_atual - 1) * $registros_por_pagina;

    $total_sql = "SELECT COUNT(*) FROM navegacao_usuarios";
    $total_result = $conexao->query($total_sql);
    $total_registros = $total_result->fetch_row()[0];
    $total_paginas = ceil($total_registros / $registros_por_pagina);

    $sqlLogsNav = "SELECT u.nome, u.sobrenome, n.pagina_visitada, n.data_hora FROM navegacao_usuarios n JOIN usuarios u ON n.id_usuario = u.id ORDER BY n.data_hora DESC LIMIT ? OFFSET ?";
    $stmt = $conexao->prepare($sqlLogsNav);
    $stmt->bind_param("ii", $registros_por_pagina, $offset);
    $stmt->execute();
    $resultLogsNav = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Monitoramento de Logs - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="logs.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="admin-body">
    <?php include 'includes/header_admin.php'; ?>

    <main class="main-content">
        <section class="page-header">
            <h1>Monitoramento de Logs</h1>
            <p>Alterne entre logs de acesso e de navegação para uma análise completa.</p>
        </section>

        <!-- BARRA DE AÇÕES PRINCIPAL PARA ALTERNAR ENTRE OS TIPOS DE LOG -->
        <section class="actions-bar">
            <div class="filter-buttons main-filters">
                <a href="?tipo=acesso" class="filter-button <?= $tipo_log == 'acesso' ? 'active' : '' ?>"><i class="fas fa-sign-in-alt"></i> Logs de Acesso</a>
                <a href="?tipo=navegacao" class="filter-button <?= $tipo_log == 'navegacao' ? 'active' : '' ?>"><i class="fas fa-route"></i> Logs de Navegação</a>
            </div>
        </section>

        <?php if ($tipo_log === 'acesso'): ?>
            <!-- SEÇÃO DE FILTROS E ESTATÍSTICAS PARA LOGS DE ACESSO -->
            <section class="actions-bar secondary-bar">
                <div class="filter-buttons">
                    <a href="?tipo=acesso&periodo=hoje" class="filter-button <?= $periodo == 'hoje' ? 'active' : '' ?>">Hoje</a>
                    <a href="?tipo=acesso&periodo=esta_semana" class="filter-button <?= $periodo == 'esta_semana' ? 'active' : '' ?>">Esta Semana</a>
                    <a href="?tipo=acesso&periodo=este_mes" class="filter-button <?= $periodo == 'este_mes' ? 'active' : '' ?>">Este Mês</a>
                    <a href="?tipo=acesso&periodo=todos" class="filter-button <?= $periodo == 'todos' ? 'active' : '' ?>">Todos</a>
                </div>
                <form action="gerar_relatorio_logs.php" method="post" target="_blank">
                    <input type="hidden" name="periodo" value="<?= htmlspecialchars($periodo) ?>">
                    <button type="submit" class="btn-primary"><i class="fas fa-file-csv"></i> Gerar Relatório</button>
                </form>
            </section>
            <section class="stats-container">
                <div class="stats-header">
                    <h2>Estatísticas para: <?= htmlspecialchars($tituloPeriodo) ?></h2>
                </div>
                <div class="stats-grid">
                    <div class="stat-card"><i class="fas fa-eye stat-icon"></i>
                        <div class="stat-info">
                            <p><?= $stats['total_acessos'] ?? 0 ?></p>
                            <h3>Total de Acessos</h3>
                        </div>
                    </div>
                    <div class="stat-card"><i class="fas fa-users stat-icon"></i>
                        <div class="stat-info">
                            <p><?= $stats['visitantes_unicos'] ?? 0 ?></p>
                            <h3>Visitantes Únicos (IP)</h3>
                        </div>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <!-- BARRA DE PESQUISA PARA LOGS DE NAVEGAÇÃO -->
            <section class="actions-bar secondary-bar">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Pesquisar por usuário ou página...">
                </div>
            </section>
        <?php endif; ?>

        <!-- TABELA DE DADOS DINÂMICA -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <?php if ($tipo_log === 'acesso'): ?>
                        <tr>
                            <th>Usuário</th>
                            <th>Data de Acesso</th>
                            <th>Endereço IP</th>
                            <th>Dispositivo / Navegador</th>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th>Usuário</th>
                            <th>Página Visitada</th>
                            <th>Data e Hora</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if ($tipo_log === 'acesso'): ?>
                        <?php if ($resultLogs && $resultLogs->num_rows > 0): ?>
                            <?php while ($log = $resultLogs->fetch_assoc()): ?>
                                <tr>
                                    <td><?= ($log['usuario_id'] && $log['nome']) ? htmlspecialchars($log['nome'] . ' ' . $log['sobrenome']) : 'Visitante' ?></td>
                                    <td><?= date('d/m/Y H:i:s', strtotime($log['data_acesso'])) ?></td>
                                    <td><?= htmlspecialchars($log['ip']) ?></td>
                                    <td class="user-agent"><?= htmlspecialchars($log['user_agent']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-results">Nenhum log de acesso encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    <?php else: // Logs de Navegação 
                    ?>
                        <?php if ($resultLogsNav && $resultLogsNav->num_rows > 0): ?>
                            <?php while ($log = $resultLogsNav->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['nome'] . ' ' . $log['sobrenome']) ?></td>
                                    <td><?= htmlspecialchars($log['pagina_visitada']) ?></td>
                                    <td><?= date('d/m/Y H:i:s', strtotime($log['data_hora'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="no-results">Nenhum log de navegação encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINAÇÃO (apenas para logs de navegação) -->
        <?php if ($tipo_log === 'navegacao' && $total_paginas > 1): ?>
            <nav class="pagination">
                <a href="?tipo=navegacao&pagina=<?= max(1, $pagina_atual - 1) ?>" class="<?= ($pagina_atual <= 1) ? 'disabled' : '' ?>">&laquo; Anterior</a>
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="?tipo=navegacao&pagina=<?= $i ?>" class="<?= ($pagina_atual == $i) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="?tipo=navegacao&pagina=<?= min($total_paginas, $pagina_atual + 1) ?>" class="<?= ($pagina_atual >= $total_paginas) ? 'disabled' : '' ?>">Próximo &raquo;</a>
            </nav>
        <?php endif; ?>
    </main>

    <script src="logs.js"></script>
</body>

</html>