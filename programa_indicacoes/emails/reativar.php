<?php
session_start();
require '../backend/config.php';
require '../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function enviarEmail($destinatario, $assunto, $mensagem)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['MAIL_PORT'];

        $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
        $mail->addAddress($destinatario);

        $mail->CharSet = 'UTF-8';

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if (isset($_GET['email']) && isset($_GET['id'])) {
    $email = $_GET['email'];
    $id = $_GET['id'];

    $token = bin2hex(random_bytes(16));

    $stmt = $conexao->prepare("INSERT INTO usuarios_tokens (email, token, expirado_em) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();

    $link = "https://qxback.com.br/reativar_conta.php?email=" . urlencode($email) . "&token=" . $token;

    $mensagem = "
<!DOCTYPE html>
            <html lang='pt-br'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                        font-family: 'Segoe UI', sans-serif;
                    }
                    body {
                        background-image: url('https://qxback.com.br/imagens/logobranca1.webp');
                        background-size: cover;
                        background-repeat: no-repeat;
                        background-color: #0b0b0b;
                        color: #ffffff;
                        padding: 0;
                        margin: 0;
                    }
                    .email-container {
                        max-width: 600px;
                        margin: 0 auto;
                        background-color: #060606;
                        border-radius: 10px;
                        padding: 30px;
                        text-align: center;
                    }
                    .email-header {
                        margin-bottom: 20px;
                    }
                    .email-header img {
                        width: 200px;  
                        height: auto;
                        margin-bottom: 20px;
                    }
                    .email-body {
                        margin-bottom: 20px;
                        font-size: 1.2rem;
                        line-height: 1.5;
                        color: #ffffff;
                    }
                    .confirm-link {
                        display: inline-block;
                        background-color: #0f0;
                        color: #000000;
                        font-size: 1.2rem;
                        padding: 12px 30px;
                        text-decoration: none;
                        border-radius: 5px;
                        margin-top: 20px;
                        transition: background-color 0.3s;
                    }
                    .confirm-link:hover {
                        background-color: #19b419;
                    }
                    footer {
                        margin-top: 30px;
                        color: #ffffff;
                        font-size: 0.9rem;
                    }
                    footer a {
                        color: #ffffff;
                        text-decoration: none;
                    }
                    footer a:hover {
                        text-decoration: underline;
                    }
                    a {
                        color: #ffffff;
                    }
                    a:hover {
                        color: #19b419;
                    }
                </style>
                </head>
        <body>
                <div class='email-container'>
                    <div class='email-header'>
                        <img src='https://qxback.com.br/imagens/logobranca1.png' alt='Logo'>
                    </div>
                    <div class='email-body'>
            <p>Olá,</p>
            <p>Você solicitou a reativação da sua conta. Clique no link abaixo para reativá-la:</p>
            <a href='$link' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reativar Conta</a>
  </div>
                    <footer>
                        <p>&copy; 2025 QXBACK. Todos os direitos reservados.</p>
                        <p><a href='https://www.qxback.com.br/politica.html'>Termos de Serviço</a> | <a href='https://www.qxback.com.br/politica.html'>Política de Privacidade</a></p>
                    </footer>
                </div>
            </body>
            </html>
        ";

    if (enviarEmail($email, "Reativação de Conta - QXBACK", $mensagem)) {
        $_SESSION['message'] = "E-mail de reativação enviado com sucesso!";
    } else {
        $_SESSION['message'] = "Erro ao enviar o e-mail de reativação.";
    }

    header("Location: ../login.php");
    exit;
} else {
    $_SESSION['message'] = "Dados inválidos.";
    header("Location: ../login.php");
    exit;
}
