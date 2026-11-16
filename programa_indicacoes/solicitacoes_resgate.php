<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $conexao->prepare("SELECT nome, sobrenome, cpf, telefone, saldo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$stmt_resgates = $conexao->prepare("SELECT valor_resgate, metodo_pagamento, dados_pagamento, status_resgate, data_solicitacao FROM solicitacao_resgate WHERE usuario_id = ? ORDER BY data_solicitacao DESC");
$stmt_resgates->bind_param("i", $usuario_id);
$stmt_resgates->execute();
$resgates_result = $stmt_resgates->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Solicitações de Resgate</title>
    <link rel="stylesheet" href="solicitacoes_resgate.css">
    <link rel="icon" href="imagens/qxbackfavicon.png" class="favicon">
    <style>
        .ver-solicitacoes-btn {
            display: inline-block;
            background-color: #0f0;
            color: #000;
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .ver-solicitacoes-btn:hover {
            background-color: #1b7d1b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #222;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            color: #0f0;
        }

        td {
            background-color: #222;
            color: #fff;
        }

        .status-aprovado {
            background-color: green;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .status-pendente {
            background-color: orange;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .status-rejeitado {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: #333;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
        }

        h2 {
            text-align: center;
            color: #0f0;
            font-size: 24px;
        }
    </style>
</head>

<body>
    <div class="main-content">
        <?php include 'includes/header_logado.php'; ?>
        <div class="container">
            <h2>Minhas Solicitações de Resgate</h2>

            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Valor do Resgate</th>
                        <th>Método de Pagamento</th>
                        <th>Detalhes do Pagamento</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($solicitacao = $resgates_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></td>
                            <td>R$ <?= number_format($solicitacao['valor_resgate'], 2, ',', '.') ?></td>
                            <td><?= ucfirst($solicitacao['metodo_pagamento']) ?></td>
                            <td><?= $solicitacao['dados_pagamento'] ?></td>
                            <td>
                                <?php
                                if ($solicitacao['status_resgate'] == 'aprovado') {
                                    echo '<span class="status-aprovado">Aprovado</span>';
                                } elseif ($solicitacao['status_resgate'] == 'pendente') {
                                    echo '<span class="status-pendente">Pendente</span>';
                                } else {
                                    echo '<span class="status-rejeitado">Rejeitado</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'includes/cookie.php'; ?>
    <?php include 'includes/footer.php'; ?>

    <script src="includes/script.js"></script>
</body>

</html>