<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

$stmt = $conexao->prepare("SELECT nome, saldo, codigo_indicador, origem_cadastro FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

function buscarIndicacoes($conexao, $usuario_id, $status)
{
    try {
        $query = "SELECT i.nome_indicado, i.data_criacao, i.status, mr.motivo FROM indicacoes i LEFT JOIN motivos_rejeicao mr ON i.id = mr.indicacao_id WHERE i.usuario_id = ?";

        if ($status === 'aceitas') {
            $query .= " AND (i.status = 'Aprovada' OR i.status = 'Fechada')";
        } elseif ($status === 'em_avaliacao') {
            $query .= " AND i.status = 'Ativa'";
        } elseif ($status === 'rejeitadas') {
            $query .= " AND i.status = 'Rejeitada'";
        }

        $query .= " ORDER BY i.data_criacao DESC";

        $stmt = $conexao->prepare($query);
        if ($stmt === false) {
            throw new Exception('Falha na preparação da consulta: ' . $conexao->error);
        }

        $stmt->bind_param("i", $usuario_id);

        if (!$stmt->execute()) {
            throw new Exception('Falha na execução da consulta: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $indicacoes = [];
        while ($row = $result->fetch_assoc()) {
            $indicacoes[] = $row;
        }
        $stmt->close();
        return $indicacoes;
    } catch (Exception $e) {
        error_log("Erro na função buscarIndicacoes: " . $e->getMessage());
        return [];
    }
}

$queries = [
    'totalIndicacoes' => "SELECT COUNT(*) AS total FROM indicacoes WHERE usuario_id = ?",
    'servicosAceitos' => "SELECT COUNT(*) AS total FROM indicacoes WHERE usuario_id = ? AND (status = 'Aprovada' OR status = 'Fechada')",
    'emAvaliacao' => "SELECT COUNT(*) AS total FROM indicacoes WHERE usuario_id = ? AND status = 'Ativa'",
    'recusadas' => "SELECT COUNT(*) AS total FROM indicacoes WHERE usuario_id = ? AND status = 'Rejeitada'",
    'pendencias' => "SELECT SUM(valor_pendente) AS total FROM indicacoes WHERE usuario_id = ? AND status = 'Ativa'",
    'ganhosTotais' => "SELECT SUM(valor) AS total FROM ganhos WHERE usuario_id = ?"
];
$dados = [];
foreach ($queries as $key => $query) {
    $stmt = $conexao->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $dados[$key] = $row['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Painel | Alfonso Rewards</title>
    <link rel="stylesheet" href="indicador.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>
    <script src="includes/indicador.js" defer></script>
</head>

<body>

    <?php include __DIR__ . '/includes/header_logado.php'; ?>

    <main class="dashboard-main">
        <section class="welcome-section">
            <div class="welcome-content">
                <h1>Olá, <?php echo htmlspecialchars($user['nome']); ?>! <span class="wave">👋</span></h1>
                <p>Este é o seu centro de controle no Alfonso Rewards. Acompanhe suas indicações e veja suas recompensas crescerem.</p>

                <a href="indicar.php" class="btn-primary btn-indicate"><i class="fas fa-plus"></i> Fazer Nova Indicação</a>
            </div>
        </section>

        <?php if ($user['origem_cadastro'] !== 'link_indicacao'): // Mostra apenas se não for um convidado 
        ?>
            <section class="referral-link-section">
                <h2><i class="fas fa-share-alt"></i> Seu Link Mágico de Indicação</h2>
                <p>Compartilhe este link com seus amigos. Cada novo paciente que se cadastrar através dele será automaticamente vinculado a você!</p>
                <div class="link-box">
                    <input type="text" value="https://clinicaalfonso.com.br/programa_indicacoes/indicar.php?ref=<?php echo htmlspecialchars($user['codigo_indicador']); ?>" id="referralLinkInput" readonly>
                    <button onclick="copyLink()" class="copy-button">
                        <i class="far fa-copy"></i> <span>Copiar</span>
                    </button>
                </div>
                <div id="copy-success">Link copiado com sucesso!</div>
                <div class="share-buttons">
                    <p>Ou compartilhe diretamente:</p>
                    <div class="share-btn-group">
                        <a href="javascript:void(0);" onclick="shareLink('whatsapp')" class="share-btn whatsapp" aria-label="Compartilhar no WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="javascript:void(0);" onclick="shareLink('telegram')" class="share-btn telegram" aria-label="Compartilhar no Telegram"><i class="fab fa-telegram-plane"></i></a>
                        <a href="javascript:void(0);" onclick="shareLink('email')" class="share-btn email" aria-label="Compartilhar por E-mail"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="summary-section">
            <div class="summary-card" onclick="openModal('modalTodas')">
                <div class="card-icon">
                    <i class="fas fa-stream"></i>
                </div>
                <div class="card-content">
                    <h3>Total de Indicações</h3>
                    <span><?php echo $dados['totalIndicacoes']; ?></span>
                </div>
            </div>

            <div class="summary-card" onclick="openModal('modalEmAvaliacao')">
                <div class="card-icon warning">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="card-content">
                    <h3>Em Avaliação</h3>
                    <span><?php echo $dados['emAvaliacao']; ?></span>
                </div>
            </div>

            <div class="summary-card" onclick="openModal('modalAceitas')">
                <div class="card-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="card-content">
                    <h3>Aceitas</h3>
                    <span><?php echo $dados['servicosAceitos']; ?></span>
                </div>
            </div>

            <div class="summary-card" onclick="openModal('modalRejeitadas')">
                <div class="card-icon error">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="card-content">
                    <h3>Recusadas</h3>
                    <span><?php echo $dados['recusadas']; ?></span>
                </div>
            </div>
        </section>

        <section class="financial-section">
            <div class="financial-card">
                <h3><i class="fas fa-wallet"></i> Valores Pendentes</h3>
                <div class="amount">R$ <?php echo number_format($dados['pendencias'], 2, ',', '.'); ?></div>
                <p class="description">Ganhos de indicações em avaliação.</p>
            </div>
            <div class="financial-card">
                <h3><i class="fas fa-money-bill-wave"></i> Saldo Disponível</h3>
                <div class="amount highlight">R$ <?php echo number_format($user['saldo'], 2, ',', '.'); ?></div>
                <a href="resgate.php" class="action-link">Solicitar Resgate <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="financial-card">
                <h3><i class="fas fa-trophy"></i> Ganhos Totais</h3>
                <div class="amount">R$ <?php echo number_format($dados['ganhosTotais'], 2, ',', '.'); ?></div>
                <a href="indicacoes.php" class="action-link">Ver Histórico Completo <i class="fas fa-arrow-right"></i></a>
            </div>
        </section>

        <div class="modal-overlay" id="modalTodas">
            <div class="modal-content">
                <span class="close-modal" onclick="closeModal('modalTodas')">&times;</span>
                <h3 class="modal-title"><i class="fas fa-list"></i> Todas as Indicações</h3>
                <div class="modal-body">
                    <?php $indicacoes = buscarIndicacoes($conexao, $usuario_id, 'todas'); ?>
                    <?php if (count($indicacoes) > 0): ?>
                        <div class="table-responsive">
                            <table class="indications-table">
                                <thead>
                                    <tr>
                                        <th>Indicado</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($indicacoes as $indicacao): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($indicacao['nome_indicado']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($indicacao['data_criacao'])); ?></td>
                                            <td><span class="status status-<?php echo strtolower(str_replace(' ', '-', $indicacao['status'])); ?>"><?php echo htmlspecialchars($indicacao['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="no-indications">Você ainda não fez nenhuma indicação.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main> 

    <?php include __DIR__ . '/../footer.php'; ?>

</body>

</html>