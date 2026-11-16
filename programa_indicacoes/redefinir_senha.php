<?php
session_start();
require_once 'backend/config.php';

$token_from_url = $_GET['token'] ?? '';
$error_message = '';
$token_is_valid = false;
$email_do_token = '';

if (empty($token_from_url)) {
    $error_message = "Token de redefinição não fornecido. Por favor, utilize o link enviado para o seu e-mail.";
} else {
    // Busca por um token NÃO CONFIRMADO que ainda NÃO EXPIROU
    $stmt = $conexao->prepare("SELECT email, token FROM usuarios_tokens WHERE motivo = 'reset_password' AND confirmado = 0 AND expirado_em > NOW()");
    // Não filtramos pelo token aqui, pegamos todos os tokens de reset válidos.
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Compara o token da URL com cada token hash do banco
        if (password_verify($token_from_url, $row['token'])) {
            $token_is_valid = true;
            $email_do_token = $row['email']; // Guarda o e-mail associado ao token válido
            break; // Para o loop assim que encontrar o token correto
        }
    }

    if (!$token_is_valid) {
        $error_message = "Token inválido, expirado ou já utilizado. Por favor, solicite um novo link de recuperação.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha | Alfonso Rewards</title>
    <link rel="stylesheet" href="redefinir_senha.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <section class="section-container">
            <div class="form-wrapper">
                <div class="form-header">
                    <i class="form-icon fas fa-lock"></i>
                    <h1 class="form-title">Crie sua Nova Senha</h1>
                </div>

                <?php if (isset($_SESSION['reset_error'])): ?>
                    <div class="message-box error"><?php echo $_SESSION['reset_error'];
                                                    unset($_SESSION['reset_error']); ?></div>
                <?php endif; ?>

                <?php if ($token_is_valid): ?>
                    <p class="form-subtitle">Sua identidade foi confirmada. Agora, crie uma nova senha de acesso.</p>
                    <form action="processos/processar_redefinir_senha.php" method="POST" class="form-body">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_from_url); ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_do_token); ?>">

                        <div class="input-group">
                            <label for="nova_senha">Nova Senha</label>
                            <input type="password" id="nova_senha" name="nova_senha" required>
                        </div>
                        <div class="input-group">
                            <label for="confirmar_senha">Confirme a Nova Senha</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn-primary">Salvar Nova Senha</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="message-box error"><?php echo htmlspecialchars($error_message); ?></div>
                    <div class="form-footer">
                        <a href="esqueceu_senha.php" class="back-link">Solicitar Novo Link</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include '../footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.gsap) {
                gsap.from(".form-wrapper", {
                    delay: 0.2,
                    autoAlpha: 0,
                    y: 50,
                    duration: 1.2,
                    ease: "power3.out"
                });
            }
        });
    </script>
</body>

</html>