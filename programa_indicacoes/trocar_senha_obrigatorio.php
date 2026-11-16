<?php
session_start();
require 'backend/config.php';

// --- CONTROLE DE ACESSO ---
// 1. Garante que o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$usuario_id = $_SESSION['user_id'];

// 2. Garante que o usuário PRECISA trocar a senha. Se ele já trocou, manda para o painel.
$stmt = $conexao->prepare("SELECT trocou_senha FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if ($usuario && $usuario['trocou_senha'] == 1) {
    header("Location: indicador.php"); // Redireciona para o painel principal se já trocou a senha
    exit;
}
$stmt->close();


// --- PROCESSAMENTO DO FORMULÁRIO (QUANDO ENVIADO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $genero = $_POST['genero'] ?? '';

    // Validações
    if (empty($nova_senha) || empty($confirmar_senha) || empty($genero)) {
        $feedback = ['type' => 'error', 'message' => 'Todos os campos são obrigatórios.'];
    } elseif ($nova_senha !== $confirmar_senha) {
        $feedback = ['type' => 'error', 'message' => 'As senhas não coincidem.'];
    } elseif (strlen($nova_senha) < 6) {
        $feedback = ['type' => 'error', 'message' => 'A nova senha deve ter pelo menos 6 caracteres.'];
    } elseif (!in_array($genero, ['masculino', 'feminino', 'nao_declarar'])) {
        $feedback = ['type' => 'error', 'message' => 'Opção de gênero inválida.'];
    } else {
        // Se tudo estiver OK, atualiza o banco de dados
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $trocou_senha = 1; // Marca que a senha foi trocada

        $stmt_update = $conexao->prepare("UPDATE usuarios SET senha = ?, genero = ?, trocou_senha = ? WHERE id = ?");
        $stmt_update->bind_param("ssii", $senha_hash, $genero, $trocou_senha, $usuario_id);

        if ($stmt_update->execute()) {
            // Sucesso! Redireciona para o painel com uma mensagem de boas-vindas.
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'Sua senha foi atualizada com sucesso. Bem-vindo(a)!'];
            header("Location: index.php");
            exit;
        } else {
            $feedback = ['type' => 'error', 'message' => 'Ocorreu um erro ao atualizar seus dados. Tente novamente.'];
        }
        $stmt_update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Atualize suas Informações</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --dark-teal: #30414B;
            --ivory-white: #FCFCFC;
            --soft-gold: #C0B283;
            --section-bg: #f5f7f9;
            --border-color: #eaeaea;
            --text-primary: #2c2c2c;
            --text-secondary: #5c5c5c;
            --heading-font: 'Playfair Display', serif;
            --body-font: 'Lato', sans-serif;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: var(--body-font);
            background-color: var(--section-bg);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-card {
            background-color: var(--ivory-white);
            padding: 2.5rem 3rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h1 {
            font-family: var(--heading-font);
            color: var(--dark-teal);
            margin: 0 0 0.5rem 0;
        }

        .form-header p {
            color: var(--text-secondary);
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-group input[type="password"] {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: normal;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            border: none;
            background-color: var(--dark-teal);
            color: var(--ivory-white);
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--soft-gold);
            color: var(--dark-teal);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .alert.success {
            background-color: #e8f5e9;
            color: var(--success-color);
        }

        .alert.error {
            background-color: #ffebee;
            color: var(--danger-color);
        }
    </style>
</head>

<body>
    <div class="form-card">
        <div class="form-header">
            <h1>Atualize suas Informações</h1>
            <p>Por segurança, por favor, crie uma nova senha e complete seu cadastro.</p>
        </div>

        <?php if (isset($feedback)): ?>
            <div class="alert <?= $feedback['type']; ?>">
                <?= htmlspecialchars($feedback['message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="trocar_senha_obrigatorio.php">
            <div class="form-group">
                <label for="nova_senha">Nova Senha</label>
                <input type="password" id="nova_senha" name="nova_senha" required>
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirme a Nova Senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
            </div>
            <div class="form-group">
                <label>Gênero</label>
                <div class="radio-group">
                    <label><input type="radio" name="genero" value="masculino" required> Masculino</label>
                    <label><input type="radio" name="genero" value="feminino"> Feminino</label>
                    <label><input type="radio" name="genero" value="nao_declarar"> Prefiro não declarar</label>
                </div>
            </div>
            <button type="submit" class="btn-primary">Salvar e Continuar</button>
        </form>
    </div>
</body>

</html>