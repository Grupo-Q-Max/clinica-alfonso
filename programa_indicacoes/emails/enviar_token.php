<?php
require_once '../vendor/autoload.php';
require_once  '../backend/config.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $conexao->prepare("SELECT email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
    exit;
}

$user = $result->fetch_assoc();
$email = $user['email'];

$token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expirado_em = date('Y-m-d H:i:s', strtotime('+30 minutes'));
$motivo = 'editar_perfil';

$stmt = $conexao->prepare("INSERT INTO usuarios_tokens (email, token, expirado_em, motivo) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $token, $expirado_em, $motivo);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Erro ao registrar token no banco de dados.']);
    exit;
}

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USERNAME'];
    $mail->Password   = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['MAIL_PORT'];

    $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
    $mail->addAddress($email);

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = 'Token de Verificação - QXBack';

    $mail->Body = "
        <!DOCTYPE html>
        <html lang='pt-br'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { background-color: #0b0b0b; color: #ffffff; font-family: 'Segoe UI', sans-serif; }
                .container { max-width: 600px; margin: auto; background: #121212; padding: 30px; border-radius: 10px; text-align: center; }
                .token { background: #1e1e1e; color: #00ff00; padding: 15px; font-size: 24px; border-radius: 5px; margin: 20px 0; }
                footer { margin-top: 30px; font-size: 0.9rem; color: #aaa; }
            </style>
        </head>
        <body>
            <div class='container'>
                <img src='https://qxback.com.br/imagens/logobranca1.png' alt='QXBack' style='width: 200px; margin-bottom: 20px;'>
                <h2>Token de Verificação</h2>
                <p>Use o seguinte token para confirmar sua identidade:</p>
                <div class='token'>$token</div>
                <p>Este token expira em 30 minutos.</p>
                <footer>
                    &copy; " . date('Y') . " QXBack. Todos os direitos reservados.
                </footer>
            </div>
        </body>
        </html>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Token enviado com sucesso para $email']);
} catch (Exception $e) {
    error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar e-mail.']);
}
