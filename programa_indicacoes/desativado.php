<?php
session_start();

require 'vendor/autoload.php';
require 'backend/config.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\GenericProvider;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$page_message = ''; 
$message_class = '';

if (isset($_GET['email']) && isset($_GET['id'])) {
    $email = $_GET['email'];
    $id = $_GET['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $id = $_POST['id'] ?? '';

    if (empty($email) || empty($id)) {
        $page_message = 'Erro: Informações do usuário não encontradas para reenviar o e-mail.';
        $message_class = 'error';
    } else {
        $token = bin2hex(random_bytes(16)); 
        $expirado_em = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conexao->prepare("INSERT INTO usuarios_tokens (email, token, expirado_em, motivo) VALUES (?, ?, ?, 'reativacao') ON DUPLICATE KEY UPDATE token = VALUES(token), expirado_em = VALUES(expirado_em)");
        $stmt->bind_param("sss", $email, $token, $expirado_em);
        $stmt->execute();

        $link_reativacao = "https://qxback.com.br/reativar_conta.php?email=" . urlencode($email) . "&token=" . $token;

        $mail = new PHPMailer(true);
        try {
            $provider = new GenericProvider([
                'clientId'                => $_ENV['OAUTH_CLIENT_ID'],
                'clientSecret'            => $_ENV['OAUTH_CLIENT_SECRET'],
                'redirectUri'             => $_ENV['OAUTH_REDIRECT_URI'],
                'urlAuthorize'            => "https://login.microsoftonline.com/{$_ENV['OAUTH_TENANT_ID']}/oauth2/v2.0/authorize",
                'urlAccessToken'          => "https://login.microsoftonline.com/{$_ENV['OAUTH_TENANT_ID']}/oauth2/v2.0/token",
                'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
                'scopes'                  => 'https://outlook.office.com/SMTP.Send offline_access'
            ]);
            $newAccessToken = $provider->getAccessToken('refresh_token', ['refresh_token' => $_ENV['OAUTH_REFRESH_TOKEN']]);

            $mail->isSMTP();
            $mail->Host = 'smtp.office365.com';
            $mail->Port = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAuth = true;
            $mail->AuthType = 'XOAUTH2';
            $mail->CharSet  = 'UTF-8';

            $mail->setOAuth(new OAuth([
                'provider'     => $provider,
                'clientId'     => $_ENV['OAUTH_CLIENT_ID'],
                'clientSecret' => $_ENV['OAUTH_CLIENT_SECRET'],
                'refreshToken' => $_ENV['OAUTH_REFRESH_TOKEN'],
                'accessToken'  => $newAccessToken->getToken(),
                'userName'     => $_ENV['MAIL_FROM'],
            ]));

            $mail->setFrom($_ENV['MAIL_FROM'], 'QXBack - Suporte');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Reativação de Conta - QXBack';
            $mail->Body    = "Olá,<br><br>Recebemos uma solicitação para reativar sua conta. Clique no link abaixo para confirmar:<br><br><a href='{$link_reativacao}' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reativar Minha Conta</a>";

            $mail->send();
            $page_message = "Um novo e-mail de reativação foi enviado com sucesso!";
            $message_class = 'success';
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail de reativação: " . $mail->ErrorInfo);
            $page_message = "Erro ao enviar o e-mail. Tente novamente mais tarde.";
            $message_class = 'error';
        }
    }
}
if (empty($email) || empty($id)) {
    header("Location: login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conta Desativada - QXBack</title>
    <link rel="icon" href="imagens/qxbackfavicon.webp" class="favicon">
    <style>
        body {
            font-family: sans-serif;
            background-color: #121212;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            text-align: center;
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.1);
            border: 1px solid #0f0;
        }

        .message {
            margin: 20px 0;
            font-size: 1.1em;
        }

        .success {
            color: #00ff00;
        }

        .error {
            color: #ff4d4d;
        }

        button,
        a.btn {
            background-color: #0f0;
            color: #000;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            font-size: 1em;
            transition: background-color 0.3s;
        }

        button:hover,
        a.btn:hover {
            background-color: #00dd00;
        }

        a.btn-cancel {
            background-color: #333;
            color: #fff;
            margin-top: 20px;
            display: inline-block;
        }

        a.btn-cancel:hover {
            background-color: #444;
        }

        form {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Conta Desativada</h1>
        <p class="message">Sua conta foi desativada. Para reativá-la, clique no botão abaixo para receber um link de reativação em seu e-mail.</p>

        <?php if (!empty($page_message)): ?>
            <p class="message <?php echo $message_class; ?>"><?php echo htmlspecialchars($page_message); ?></p>
        <?php endif; ?>

        <form action="desativado.php" method="POST">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
            <button type="submit">Enviar E-mail de Reativação</button>
        </form>

        <a href="login.php" class="btn btn-cancel">Voltar para o Login</a>
    </div>
</body>

</html>