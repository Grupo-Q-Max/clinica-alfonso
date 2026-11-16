<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    $_SESSION['login_error_message'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit;
}

$order = $_GET['ordem'] ?? 'DESC';
$order = in_array(strtoupper($order), ['ASC', 'DESC']) ? strtoupper($order) : 'DESC';

$usuariosQuery = "SELECT id, nome, sobrenome, email, tipo_conta, ativo, data_criacao FROM usuarios ORDER BY id $order";
$usuariosResult = $conexao->query($usuariosQuery);
$usuarios = $usuariosResult->fetch_all(MYSQLI_ASSOC);

// Para exibir mensagens de feedback do processamento
if (isset($_SESSION['feedback'])) {
    $feedback = $_SESSION['feedback'];
    unset($_SESSION['feedback']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários | Admin</title>
    <link rel="stylesheet" href="usuarios.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* Estilos para o novo seletor de colunas */
        .column-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
            margin-bottom: 1.5rem;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            padding: 1rem;
            border-radius: 8px;
            background-color: var(--section-bg);
        }

        .column-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .column-item input[type="checkbox"] {
            width: 1.2em;
            height: 1.2em;
            accent-color: var(--dark-teal);
        }

        .select-all-container {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body class="admin-body">
    <?php include 'includes/header_admin.php'; ?>
    <main class="main-content">
        <section class="page-header">
            <h1>Gerenciar Usuários</h1>
            <p>Ative, bloqueie, edite e gere relatórios de todos os usuários do sistema.</p>
        </section>

        <?php if (isset($feedback)): ?>
            <div class="alert <?= $feedback['type'] === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($feedback['message']) ?>
            </div>
        <?php endif; ?>

        <section class="actions-bar">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Pesquisar por nome ou email...">
            </div>
            <div class="action-buttons">
                <div class="sort-buttons">
                    <span>Ordenar por ID:</span>
                    <a href="?ordem=asc" class="sort-btn <?= $order == 'ASC' ? 'active' : '' ?>"><i class="fas fa-arrow-up"></i></a>
                    <a href="?ordem=desc" class="sort-btn <?= $order == 'DESC' ? 'active' : '' ?>"><i class="fas fa-arrow-down"></i></a>
                </div>
                <button id="openImportModal" class="btn-secondary"><i class="fas fa-file-import"></i> Importar Usuários</button>
                <button id="openReportPopup" class="btn-primary"><i class="fas fa-file-excel"></i> Gerar Relatório</button>
            </div>
        </section>

        <section class="users-list">
            <?php if (empty($usuarios)): ?>
                <div class="no-results">
                    <h3>Nenhum usuário encontrado.</h3>
                </div>
            <?php else: ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <div class="user-card" data-nome="<?= htmlspecialchars(strtolower($usuario['nome'] . ' ' . $usuario['sobrenome'])) ?>" data-email="<?= htmlspecialchars(strtolower($usuario['email'])) ?>">
                        <div class="user-info">
                            <div class="info-group">
                                <label>Nome do Usuário</label>
                                <span><?php echo htmlspecialchars($usuario['nome'] . ' ' . $usuario['sobrenome']); ?></span>
                            </div>
                            <div class="info-group">
                                <label>Email</label>
                                <span><?php echo htmlspecialchars($usuario['email']); ?></span>
                            </div>
                            <div class="info-group">
                                <label>Status</label>
                                <span class="status-badge <?= $usuario['ativo'] == 1 ? 'ativo' : 'inativo' ?>">
                                    <?= $usuario['ativo'] == 1 ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </div>
                        </div>
                        <div class="user-actions">
                            <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn-secondary"><i class="fas fa-edit"></i> Editar</a>

                            <?php if ($usuario['ativo'] == 1): ?>
                                <form method="POST" action="processos/processar_usuario.php" style="display: inline;" onsubmit="return confirm('Você tem certeza que deseja bloquear este usuário?');">
                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                    <button type="submit" name="action" value="desativar_status" class="btn-primary danger">Bloquear</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="processos/processar_usuario.php" style="display: inline;" onsubmit="return confirm('Você tem certeza que deseja ativar este usuário?');">
                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                    <button type="submit" name="action" value="ativar_status" class="btn-primary">Ativar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <!-- MODAL ATUALIZADO PARA GERAR RELATÓRIO -->
        <div class="modal-overlay" id="reportModal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><i class="fas fa-file-excel"></i> Gerar Relatório de Usuários</h3>
                    <button class="close-modal" id="closeReportModal">&times;</button>
                </div>
                <form action="gerar_relatorio.php" method="POST" id="reportForm">
                    <div class="form-group">
                        <label for="filtro_status">Filtrar por Status (Opcional):</label>
                        <select id="filtro_status" name="filtro_status" class="form-control">
                            <option value="todos">Todos os Usuários</option>
                            <option value="ativos">Apenas Ativos</option>
                            <option value="inativos">Apenas Inativos</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Selecione as Colunas para Incluir:</label>
                        <div class="select-all-container">
                            <label class="column-item">
                                <input type="checkbox" id="selectAllColumns">
                                <strong>Marcar/Desmarcar Todos</strong>
                            </label>
                        </div>
                        <div class="column-selector">
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="id" checked> ID</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="nome_completo" checked> Nome Completo</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="email" checked> Email</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="status" checked> Status</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="cpf"> CPF</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="telefone"> Telefone</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="tipo_conta"> Tipo de Conta</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="saldo"> Saldo (R$)</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="data_nascimento"> Data de Nascimento</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="endereco_completo"> Endereço Completo</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="codigo_indicador"> Código Indicador</label>
                            <label class="column-item"><input type="checkbox" name="colunas[]" value="data_criacao" checked> Data de Cadastro</label>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" id="cancelReportModal">Cancelar</button>
                        <button type="submit" class="btn-primary">Gerar e Baixar</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <div class="modal-overlay" id="importModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-file-import"></i> Importar Usuários</h3>
                <button class="close-modal" id="closeImportModal">&times;</button>
            </div>
            <form action="importar_usuarios.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <p>Para importar usuários em massa, baixe nossa planilha modelo, preencha os dados e faça o upload do arquivo no formato .xlsx.</p>

                    <div class="form-group" style="margin-top: 1rem; margin-bottom: 1rem;">
                        <a href="baixar_modelo.php" class="btn-secondary" download><i class="fas fa-download"></i> Baixar Modelo da Planilha</a>
                    </div>

                    <div class="form-group">
                        <label for="senha_padrao">Senha Padrão para Novos Usuários *</label>
                        <input type="password" name="senha_padrao" id="senha_padrao" class="form-control" required>
                        <small>Esta será a senha temporária para todos os usuários importados.</small>
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

    <?php include '../footer.php'; ?>
    <script src="usuarios.js" defer></script>
</body>

</html>