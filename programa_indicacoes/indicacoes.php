<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// --- FUNÇÕES DE BUSCA DE DADOS (COM CORREÇÃO) ---
function getIndicacoes($conexao, $usuario_id)
{
    // CORRIGIDO: A query agora busca as colunas corretas (nome_paciente, telefone, email)
    // e remove as colunas antigas (cnpj, nome_contato, etc.)
    $query = "SELECT 
                id as indicacao_id, 
                nome_paciente, 
                telefone, 
                email, 
                data_criacao, 
                status, 
                valor_pendente, 
                porcentagem_usuario 
              FROM indicacoes 
              WHERE usuario_id = ? 
              ORDER BY data_criacao DESC";
              
    $stmt = $conexao->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $indicacoes = [];
    while ($row = $result->fetch_assoc()) {
        $indicacoes[] = $row;
    }
    return $indicacoes;
}

function getServicosIndicacao($conexao, $indicacao_id)
{
    $query = "
        SELECT 
            i_s.servico, i_s.descricao_outros, i_s.subtotal, i_s.processado, s.cashback
        FROM indicacao_servicos AS i_s
        LEFT JOIN servicos AS s ON TRIM(UPPER(i_s.servico)) = TRIM(UPPER(s.nome))
        WHERE i_s.indicacao_id = ?
    ";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param("i", $indicacao_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $servicos = [];
    while ($row = $result->fetch_assoc()) {
        $servicos[] = $row;
    }
    return $servicos;
}

$indicacoes = getIndicacoes($conexao, $usuario_id);

// Lógica para contagem dos cards de resumo (permanece a mesma, está correta)
$count_total = count($indicacoes);
$count_pending = 0;
$count_approved = 0;
$count_rejected = 0;
foreach ($indicacoes as $indicacao) {
    if (in_array($indicacao['status'], ['Ativa', 'Orçamento Enviado'])) {
        $count_pending++;
    } elseif (in_array($indicacao['status'], ['Aprovada', 'Fechada', 'Indicação Boa'])) {
        $count_approved++;
    } elseif ($indicacao['status'] === 'Rejeitada') {
        $count_rejected++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Indicações | Alfonso Rewards</title>
    <link rel="stylesheet" href="indicacoes.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <script src="includes/indicacoes.js" defer></script>
</head>
<body>

    <?php include 'includes/header_logado.php'; ?>

    <main class="main-content">
        <section class="page-header">
            <h1>Minhas Indicações</h1>
            <p>Acompanhe em tempo real o status de cada um dos pacientes indicados.</p>
        </section>

        <section class="summary-cards">
            <div class="summary-card">
                <div class="card-icon"><i class="fas fa-stream"></i></div>
                <div class="card-content">
                    <h3>Total de Indicações</h3>
                    <span><?php echo $count_total; ?></span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon warning"><i class="fas fa-hourglass-half"></i></div>
                <div class="card-content">
                    <h3>Em Análise</h3>
                    <span><?php echo $count_pending; ?></span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon success"><i class="fas fa-check-circle"></i></div>
                <div class="card-content">
                    <h3>Aceitas</h3>
                    <span><?php echo $count_approved; ?></span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon error"><i class="fas fa-times-circle"></i></div>
                <div class="card-content">
                    <h3>Recusadas</h3>
                    <span><?php echo $count_rejected; ?></span>
                </div>
            </div>
        </section>

        <section class="indications-list">
            <?php if (count($indicacoes) > 0): ?>
                <?php foreach ($indicacoes as $indicacao): ?>
                    <div class="indication-card">
                        <div class="card-main-info">
                            <div class="info-group">
                                <label>Paciente Indicado</label>
                                <span><?php echo htmlspecialchars($indicacao['nome_paciente']); ?></span>
                            </div>
                            <div class="info-group">
                                <label>Data</label>
                                <span><?php echo date('d/m/Y', strtotime($indicacao['data_criacao'])); ?></span>
                            </div>
                            <div class="info-group">
                                <label>Status</label>
                                <span class="status status-<?php echo strtolower(str_replace(' ', '-', $indicacao['status'])); ?>"><?php echo htmlspecialchars($indicacao['status']); ?></span>
                            </div>
                            <button class="btn-details">
                                Detalhes <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="card-details">
                            <div class="details-content">
                                <h4><i class="fas fa-user"></i> Informações do Paciente</h4>
                                <p><strong>Nome:</strong> <?php echo htmlspecialchars($indicacao['nome_paciente']); ?></p>
                                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($indicacao['telefone']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($indicacao['email']); ?></p>
                                <hr>
                                <h4><i class="fas fa-concierge-bell"></i> Detalhes Financeiros</h4>
                                <div class="table-responsive">
                                    <table class="details-table">
                                        <thead>
                                            <tr>
                                                <th>Serviço</th>
                                                <th>Valor Proposta</th>
                                                <th>Sua %</th>
                                                <th>Valor Potencial</th>
                                                <th>Valor Recebido</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $servicos = getServicosIndicacao($conexao, $indicacao['indicacao_id']);
                                            $bonus_qualificada_potencial = (float)($indicacao['valor_pendente'] ?? 0);
                                            $bonus_qualificada_recebido = 0;
                                            if (in_array($indicacao['status'], ['Indicação Boa', 'Orçamento Enviado', 'Fechada', 'Aprovada'])) {
                                                $bonus_qualificada_recebido = $bonus_qualificada_potencial;
                                            }
                                            $total_potencial = $bonus_qualificada_potencial;
                                            $total_recebido = $bonus_qualificada_recebido;
                                            ?>
                                            <tr>
                                                <td>Bônus por Indicação Qualificada</td>
                                                <td>-</td>
                                                <td>100,00%</td>
                                                <td>R$ <?php echo number_format($bonus_qualificada_potencial, 2, ',', '.'); ?></td>
                                                <td>R$ <?php echo number_format($bonus_qualificada_recebido, 2, ',', '.'); ?></td>
                                            </tr>
                                            <?php foreach ($servicos as $servico):
                                                $subtotal = (float)($servico['subtotal'] ?? 0);
                                                $cashback = (float)($servico['cashback'] ?? $indicacao['porcentagem_usuario']);
                                                $valor_potencial_servico = $subtotal * ($cashback / 100);
                                                $total_potencial += $valor_potencial_servico;
                                                $valor_recebido_servico = 0;
                                                if ($indicacao['status'] === 'Fechada' && $servico['processado'] == 1) {
                                                    $valor_recebido_servico = $valor_potencial_servico;
                                                }
                                                $total_recebido += $valor_recebido_servico;
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($servico['servico']); ?></td>
                                                    <td>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                                                    <td><?php echo number_format($cashback, 2, ',', '.'); ?>%</td>
                                                    <td>R$ <?php echo number_format($valor_potencial_servico, 2, ',', '.'); ?></td>
                                                    <td>R$ <?php echo number_format($valor_recebido_servico, 2, ',', '.'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="total-row">
                                                <td colspan="3">Totais</td>
                                                <td>R$ <?php echo number_format($total_potencial, 2, ',', '.'); ?></td>
                                                <td>R$ <?php echo number_format($total_recebido, 2, ',', '.'); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-indications">
                    <i class="fas fa-folder-open"></i>
                    <h3>Nenhuma indicação encontrada</h3>
                    <p>Você ainda não realizou nenhuma indicação. Clique no botão abaixo para começar a ganhar!</p>
                    <a href="indicar.php" class="btn-primary">Fazer Minha Primeira Indicação</a>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include '../footer.php'; ?>
</body>
</html>