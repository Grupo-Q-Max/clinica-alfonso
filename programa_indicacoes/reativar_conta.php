<?php
session_start();
require 'backend/config.php'; 

$page_title = "Reativação de Conta";
$message = '';
$message_class = 'error'; 

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    $stmt = $conexao->prepare("SELECT * FROM usuarios_tokens WHERE email = ? AND token = ? AND motivo = 'reativacao' AND confirmado = 0 AND expirado_em > NOW()");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $conexao->begin_transaction();
        try {
            $stmt_update_user = $conexao->prepare("UPDATE usuarios SET tipo_conta = 'indicador' WHERE email = ?");
            $stmt_update_user->bind_param("s", $email);
            $stmt_update_user->execute();

            $stmt_update_token = $conexao->prepare("UPDATE usuarios_tokens SET confirmado = 1 WHERE token = ?");
            $stmt_update_token->bind_param("s", $token);
            $stmt_update_token->execute();

            $conexao->commit();

            $message = "Sua conta foi reativada com sucesso! Você já pode fazer login.";
            $message_class = 'success';
        } catch (Exception $e) {
            $conexao->rollback();
            $message = "Ocorreu um erro no servidor ao tentar reativar sua conta. Por favor, contate o suporte.";
            error_log("Erro ao reativar conta: " . $e->getMessage());
        }
    } else {
        $message = "Link de reativação inválido, expirado ou já utilizado. Por favor, solicite um novo.";
    }
} else {
    $message = "Parâmetros de reativação ausentes. Por favor, use o link enviado para o seu e-mail.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - QXBack</title>
    <link rel="icon" href="imagens/qxbackfavicon.webp" class="favicon">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0b0b0b;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-image: url('imagens/fundoescuro.webp');
            background-size: cover;
            background-attachment: fixed;
        }

        .container {
            text-align: center;
            background: rgba(0, 0, 0, 0.8);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 255, 0, 0.2);
            border-left: 5px solid #0f0;
            max-width: 500px;
            width: 90%;
        }

        h1 {
            color: #0f0;
            margin-bottom: 20px;
        }

        .message {
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 1.1em;
        }

        .message.success {
            background-color: rgba(0, 255, 0, 0.1);
            border: 1px solid #0f0;
            color: #0f0;
        }

        .message.error {
            background-color: rgba(255, 0, 0, 0.1);
            border: 1px solid #f00;
            color: #f00;
        }

        .btn-login {
            display: inline-block;
            margin-top: 30px;
            background-color: #0f0;
            color: #000;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #fff;
            color: #0f0;
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_class; ?>">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>
        <a href="login.php" class="btn-login">Ir para a Página de Login</a>
    </div>
</body>

</html>