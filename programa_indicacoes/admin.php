<?php
session_start();
require 'backend/config.php';

// Verificação de segurança
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] != 'admin') {
    header("Location: login.php");
    exit;
}
$usuario_id = $_SESSION['user_id'];
$stmt = $conexao->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Queries para buscar os dados do dashboard
$queries = [
    'totalIndicacoes' => "SELECT COUNT(DISTINCT id) AS total FROM indicacoes",
    'indicacoesBoas' => "SELECT COUNT(DISTINCT id) AS total FROM indicacoes WHERE status = 'Indicação Boa'",
    'propostasEnviadas' => "SELECT COUNT(DISTINCT id) AS total FROM indicacoes WHERE status = 'Proposta Enviada'",
    'servicosFechados' => "SELECT COUNT(DISTINCT id) AS total FROM indicacoes WHERE status = 'Fechada'",
    'emAvaliaco' => "SELECT COUNT(DISTINCT id) AS total FROM indicacoes WHERE status = 'Ativa'",
    'recusadas' => "SELECT COUNT(DISTINCT id) AS total FROM indicacoes WHERE status = 'Rejeitada'",
    'totalUsuarios' => "SELECT COUNT(*) AS total FROM usuarios WHERE tipo_conta != 'admin'",
    'servicosPopulares' => "SELECT servico, COUNT(*) AS total FROM indicacao_servicos GROUP BY servico ORDER BY total DESC LIMIT 5"
];
$dados = [];
foreach ($queries as $key => $query) {
    $result = $conexao->query($query);
    if ($key === 'servicosPopulares') {
        $dados[$key] = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $row = $result->fetch_assoc();
        $dados[$key] = $row['total'] ?? 0;
    }
}

// Cálculo da taxa de aceitação
$total_aceitas = $dados['indicacoesBoas'] + $dados['servicosFechados'];
$percentual_aceitas = ($dados['totalIndicacoes'] > 0) ? round(($total_aceitas / $dados['totalIndicacoes']) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin | Alfonso Rewards</title>
    <link rel="stylesheet" href="admin.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="includes/admin.js" defer></script>
</head>

<body>

    <?php include 'includes/header_admin.php'; ?>

    <main class="main-content">
        <section class="page-header">
            <h1>Painel Administrativo</h1>
            <p>Visão geral e gerenciamento completo do programa Alfonso Rewards.</p>
        </section>

        <div class="dashboard-grid">
            <div class="main-column">
                <section class="summary-cards">
                    <div class="summary-card"><i class="fas fa-stream"></i>
                        <div>
                            <h3>Total de Indicações</h3><span><?php echo $dados['totalIndicacoes']; ?></span>
                        </div>
                    </div>
                    <div class="summary-card"><i class="fas fa-check-circle"></i>
                        <div>
                            <h3>Indicações Aceitas</h3><span><?php echo $total_aceitas; ?></span>
                        </div>
                    </div>
                    <div class="summary-card"><i class="fas fa-hourglass-half"></i>
                        <div>
                            <h3>Em Análise</h3><span><?php echo $dados['emAvaliaco']; ?></span>
                        </div>
                    </div>
                    <div class="summary-card"><i class="fas fa-users"></i>
                        <div>
                            <h3>Total de Usuários</h3><span><?php echo $dados['totalUsuarios']; ?></span>
                        </div>
                    </div>
                </section>

                <section class="actions-section">
                    <h2>Ações Rápidas</h2>
                    <div class="actions-grid">
                        <a href="gerenciar_indicacoes.php" class="action-card"><i class="fas fa-tasks"></i><span>Gerenciar Indicações</span></a>
                        <a href="usuarios.php" class="action-card"><i class="fas fa-users-cog"></i><span>Gerenciar Usuários</span></a>
                        <a href="admin_logs.php" class="action-card"><i class="fas fa-chart-bar"></i><span>Relatórios</span></a>
                    </div>
                </section>
            </div>

            <div class="sidebar-column">
                <section class="chart-card">
                    <h2>Taxa de Aceitação</h2>
                    <div class="progress-circle" data-percent="<?php echo $percentual_aceitas; ?>">
                        <svg viewBox="0 0 100 100">
                            <circle class="progress-circle-bg" cx="50" cy="50" r="45"></circle>
                            <circle class="progress-circle-fill" cx="50" cy="50" r="45"></circle>
                        </svg>
                        <div class="progress-circle-text"><?php echo $percentual_aceitas; ?>%</div>
                    </div>
                    <p class="chart-subtitle"><?php echo $total_aceitas; ?> de <?php echo $dados['totalIndicacoes']; ?> indicações foram convertidas.</p>
                </section>
                <section class="chart-card">
                    <h2>Tratamentos Populares</h2>
                    <ul class="popular-list">
                        <?php foreach ($dados['servicosPopulares'] as $servico): ?>
                            <li>
                                <span><?php echo htmlspecialchars($servico['servico']); ?></span>
                                <strong><?php echo $servico['total']; ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>
</body>

</html>