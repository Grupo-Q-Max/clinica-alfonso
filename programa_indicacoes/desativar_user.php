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

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$stmt = $conexao->prepare("SELECT email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: perfil.php?erro=usuario_nao_encontrado");
    exit;
}

$email = $user['email'];

$token = rand(100000, 999999);
$criado_em = date('Y-m-d H:i:s');
$expirado_em = date('Y-m-d H:i:s', strtotime('+30 minutes'));

$stmt_find = $conexao->prepare("SELECT id FROM usuarios_tokens WHERE email = ? AND motivo = 'desativacao' AND confirmado = 0");
$stmt_find->bind_param("s", $email);
$stmt_find->execute();
$result_find = $stmt_find->get_result();

if ($result_find->num_rows > 0) {
    $stmt_update = $conexao->prepare("UPDATE usuarios_tokens SET token = ?, criado_em = ?, expirado_em = ? WHERE email = ? AND motivo = 'desativacao' AND confirmado = 0");
    $stmt_update->bind_param("ssss", $token, $criado_em, $expirado_em, $email);
    $stmt_update->execute();
} else {
    $stmt_insert = $conexao->prepare("INSERT INTO usuarios_tokens (email, token, criado_em, expirado_em, confirmado, motivo) VALUES (?, ?, ?, ?, 0, 'desativacao')");
    $stmt_insert->bind_param("ssss", $email, $token, $criado_em, $expirado_em);
    $stmt_insert->execute();
}

$email_enviado = false;
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
    $mail->Subject = "Código de Confirmação - Desativação de Conta";
    $mail->Body    = "<p>Seu código para confirmar a desativação da conta é: <strong>$token</strong>.</p><p>Ele expira em 30 minutos. Se você não solicitou essa desativação, ignore este e-mail.</p>";

    $mail->send();
    $email_enviado = true;
} catch (Exception $e) {
    error_log("Erro ao enviar e-mail de desativação: " . $mail->ErrorInfo);
    $email_enviado = false;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desativação de Conta - QXBack</title>
    <link rel="icon" href="imagens/qxbackfavicon.webp" class="favicon">
    <link rel="stylesheet" href="desativar_user.css">
</head>

<body>
    <div class="main-container">
        <div class="content">
            <div class="container">
                <h2 class="title">Desativação de Conta</h2>

                <?php
                if (isset($_GET['erro'])) {
                    echo '<p class="error">' . htmlspecialchars($_GET['erro']) . '</p>';
                }

                if ($email_enviado): ?>
                    <p class="success">Um código de verificação foi enviado para <strong><?php echo htmlspecialchars($email); ?></strong>.</p>
                <?php else: ?>
                    <p class="error">Erro ao enviar o e-mail. Tente reenviar ou contate o suporte.</p>
                <?php endif; ?>

                <p>Digite o código recebido para confirmar a desativação da sua conta.</p>

                <form action="confirmar_desativacao.php" method="POST" class="form-container">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <input type="text" name="token" placeholder="Digite o código" required>
                    <button type="submit" class="btn btn-confirm">Confirmar Desativação</button>
                </form>

                <p>Caso não tenha recebido o e-mail, clique abaixo para reenviar:</p>
                <form action="desativar_user.php" method="POST">
                    <button type="submit" class="btn btn-resend">Reenviar Código</button>
                </form>

                <p class="info">
                    Sua conta será desativada, mas você poderá reativá-la a qualquer momento acessando novamente com seu e-mail e senha.
                </p>
                <p><a href="perfil.php" class="btn btn-cancel">Cancelar</a></p>
            </div>
        </div>
    </div>
</body>

</html>