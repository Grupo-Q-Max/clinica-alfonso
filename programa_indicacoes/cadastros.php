<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    $_SESSION['login_error_message'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit;
}

// Lógica de exclusão (simplificada para apenas serviços)
if (isset($_GET['excluir'])) {
    $idExcluir = intval($_GET['excluir']);

    $delete = $conexao->prepare("DELETE FROM servicos WHERE id = ?");
    $delete->bind_param("i", $idExcluir);
    if ($delete->execute()) {
        $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Serviço excluído com sucesso!'];
    } else {
        $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao excluir o registro.'];
    }
    header("Location: cadastros.php");
    exit;
}

// CORRIGIDO: A query agora busca as novas colunas da tabela de serviços
$query = "SELECT id, nome, tipo_cashback, valor_cashback FROM servicos ORDER BY id DESC";

$result = $conexao->query($query);
if (!$result) {
    // Isso ajuda a ver o erro do banco de dados se algo der errado
    die("Erro ao buscar cadastros: " . $conexao->error);
}

if (isset($_SESSION['feedback'])) {
    $feedback = $_SESSION['feedback'];
    unset($_SESSION['feedback']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Cadastros de Serviços</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cadastros.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="admin-body">
    <?php include 'includes/header_admin.php'; ?>
    <main class="main-content">
        <section class="page-header">
            <h1>Gerenciar Cadastros</h1>
            <p>Visualize, edite ou exclua os cadastros de serviços.</p>
        </section>

        <?php if (isset($feedback)): ?>
            <div class="alert <?= $feedback['type'] === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($feedback['message']) ?>
            </div>
        <?php endif; ?>

        <section class="actions-bar">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar na tabela...">
            </div>
            <div class="add-buttons">
                <button id="openImportModal" class="btn-secondary"><i class="fas fa-file-import"></i> Importar Serviços</button>
                <a href="cadastro_servico.php" class="btn-primary"><i class="fas fa-plus"></i> Novo Serviço</a>
            </div>
        </section>

        <div id="tabela-cadastros" class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Serviço</th>
                        <th>Regra de Cashback</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($cadastro = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($cadastro['id']) ?></td>
                                <td><?= htmlspecialchars($cadastro['nome']) ?></td>
                                <td>
                                    <?php
                                    // CORRIGIDO: Lógica para exibir o cashback corretamente
                                    if ($cadastro['tipo_cashback'] == 'valor') {
                                        echo "R$ " . htmlspecialchars(number_format($cadastro['valor_cashback'], 2, ',', '.'));
                                    } elseif ($cadastro['tipo_cashback'] == 'percentual') {
                                        echo htmlspecialchars(number_format($cadastro['valor_cashback'], 2, ',', '.')) . " %";
                                    } else {
                                        echo "Não definido";
                                    }
                                    ?>
                                </td>
                                <td class="actions-cell">
                                    <a href="editar_servico.php?id=<?= $cadastro['id'] ?>" class="btn-secondary"><i class="fas fa-edit"></i> Editar</a>
                                    <form method="GET" action="cadastros.php" id="form-excluir-<?= $cadastro['id'] ?>" style="display:inline;">
                                        <input type="hidden" name="excluir" value="<?= $cadastro['id'] ?>">
                                        <button type="button" class="btn-primary danger action-btn" data-form-id="form-excluir-<?= $cadastro['id'] ?>" data-question="Tem certeza que deseja excluir este serviço? A ação não pode ser desfeita.">
                                            <i class="fas fa-trash"></i> Excluir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-results">Nenhum serviço encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="modal-overlay" id="importModal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-file-import"></i> Importar Serviços em Massa</h3>
                    <button class="close-modal" id="closeImportModal">&times;</button>
                </div>
                <form action="importar_servicos.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <p>Baixe a planilha modelo, preencha com os dados dos serviços e faça o upload do arquivo .xlsx para cadastrar múltiplos serviços de uma vez.</p>

                        <div class="form-group" style="margin-top: 1rem; margin-bottom: 1.5rem;">
                            <a href="baixar_modelo_servicos.php" class="btn-secondary" download><i class="fas fa-download"></i> Baixar Modelo da Planilha</a>
                        </div>

                        <div class="form-group">
                            <label for="arquivo_xlsx">Arquivo da Planilha (.xlsx) *</label>
                            <input type="file" name="arquivo_xlsx" id="arquivo_xlsx" class="form-control" accept=".xlsx" required>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" id="cancelImportModal">Cancelar</button>
                        <button type="submit" class="btn-primary">Iniciar Importação</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="confirmActionModal" style="display: none;">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmação</h3>
                <button class="close-modal" id="closeConfirmModal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmActionText">Você tem certeza?</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancelActionBtn">Cancelar</button>
                <button type="button" class="btn-primary danger" id="confirmActionBtn">Confirmar Exclusão</button>
            </div>
        </div>
    </div>

    <script src="cadastros.js"></script>
</body>

</html>