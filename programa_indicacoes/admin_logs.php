<?php
session_start();
require 'backend/config.php';

// Verificação de segurança e permissão de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Lógica de Paginação
$registros_por_pagina = 20;
$pagina_atual = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Contagem total de registros para a paginação
$total_sql = "SELECT COUNT(*) FROM navegacao_usuarios";
$total_result = $conexao->query($total_sql);
$total_registros = $total_result->fetch_row()[0];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Busca os logs da página atual
$sql = "
    SELECT u.nome, u.sobrenome, n.pagina_visitada, n.data_hora
    FROM navegacao_usuarios n
    JOIN usuarios u ON n.id_usuario = u.id
    ORDER BY n.data_hora DESC
    LIMIT ? OFFSET ?
";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("ii", $registros_por_pagina, $offset);
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Logs de Navegação - Admin</title>
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
            <h1>Logs de Navegação</h1>
            <p>Acompanhe a atividade e as páginas visitadas pelos usuários no sistema.</p>
        </section>

        <section class="actions-bar">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Pesquisar por usuário ou página...">
            </div>
        </section>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Página Visitada</th>
                        <th>Data e Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="3" class="no-results">Nenhum registro de navegação encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['nome'] . ' ' . $log['sobrenome']) ?></td>
                                <td><?= htmlspecialchars($log['pagina_visitada']) ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($log['data_hora'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_paginas > 1): ?>
            <nav class="pagination">
                <a href="?pagina=<?= max(1, $pagina_atual - 1) ?>" class="<?= ($pagina_atual <= 1) ? 'disabled' : '' ?>">&laquo; Anterior</a>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="?pagina=<?= $i ?>" class="<?= ($pagina_atual == $i) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <a href="?pagina=<?= min($total_paginas, $pagina_atual + 1) ?>" class="<?= ($pagina_atual >= $total_paginas) ? 'disabled' : '' ?>">Próximo &raquo;</a>
            </nav>
        <?php endif; ?>
    </main>

    <script src="logs.js"></script>
</body>

</html>