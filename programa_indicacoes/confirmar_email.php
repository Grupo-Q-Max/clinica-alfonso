<?php

require_once 'backend/config.php';

$status = 'error'; 
$message = 'Parâmetros de confirmação ausentes. Por favor, use o link enviado para o seu e-mail.';
$icon_class = 'fas fa-exclamation-triangle';
$title_class = 'error-title';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    $stmt = $conexao->prepare("SELECT token FROM usuarios_tokens WHERE email = ? AND expirado_em > NOW()");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $token_info = $result->fetch_assoc();

    if ($token_info && password_verify($token, $token_info['token'])) {
        $conexao->begin_transaction();
        try {
            $updateUserStmt = $conexao->prepare("UPDATE usuarios SET ativo = 1 WHERE email = ?");
            $updateUserStmt->bind_param("s", $email);
            $updateUserStmt->execute();

            $deleteTokenStmt = $conexao->prepare("DELETE FROM usuarios_tokens WHERE email = ?");
            $deleteTokenStmt->bind_param("s", $email);
            $deleteTokenStmt->execute();

            $conexao->commit();

            $status = 'success';
            $message = "Seu e-mail foi confirmado com sucesso! Sua conta no Alfonso Rewards está ativa e pronta para uso.";
            $icon_class = 'fas fa-check-circle';
            $title_class = 'success-title';
        } catch (Exception $e) {
            $conexao->rollback();
            $message = "Ocorreu um erro no servidor ao tentar ativar sua conta. Por favor, contate o suporte.";
            error_log("Erro ao ativar conta: " . $e->getMessage());
        }
    } else {
        $message = "Link de confirmação inválido, expirado ou já utilizado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Conta | Alfonso Rewards</title>
    <link rel="stylesheet" href="confirmar_email.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
</head>

<body>

    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="logo-container">
                <a href="index.php"><img src="../imagens/logo_sem_fundo1.png" alt="Logo Clínica Alfonso"></a>
            </div>
            <div class="card-content">
                <i class="<?php echo $icon_class; ?> <?php echo $title_class; ?>"></i>
                <h1 class="confirmation-title <?php echo $title_class; ?>">
                    <?php echo ($status === 'success') ? 'Conta Ativada!' : 'Ocorreu um Problema'; ?>
                </h1>
                <p class="confirmation-message"><?php echo htmlspecialchars($message); ?></p>
                <a class="btn-primary" href="login.php">Ir para Login</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            gsap.from(".confirmation-card", {
                duration: 1.2,
                y: 50,
                autoAlpha: 0,
                ease: "power3.out",
                delay: 0.3
            });
        });
    </script>
</body>

</html>