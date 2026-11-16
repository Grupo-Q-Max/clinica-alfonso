<?php
require_once '../vendor/autoload.php';
require_once '../backend/config.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


ini_set('display_errors', 0);
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

if (!isset($_POST['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'E-mail não fornecido']);
    exit;
}

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

$stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'E-mail não encontrado']);
    exit;
}

$token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expirado_em = date('Y-m-d H:i:s', strtotime('+1 hour'));
$motivo = 'redefinir_senha';

$stmt = $conexao->prepare("DELETE FROM usuarios_tokens WHERE email = ? AND motivo = ?");
$stmt->bind_param("ss", $email, $motivo);
$stmt->execute();

$stmt = $conexao->prepare("INSERT INTO usuarios_tokens (email, token, expirado_em, motivo) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $token, $expirado_em, $motivo);

if (!$stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao registrar token']);
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
    $mail->CharSet = 'UTF-8'; 

    $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Token de Redefinição de Senha';
    $mail->Body    = "Seu token para redefinição de senha é: <strong>$token</strong><br><br>Este token expira em 1 hora.";

    $mail->send();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Token enviado com sucesso']);
} catch (Exception $e) {
    error_log('Erro ao enviar email: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar email. Tente novamente mais tarde.']);
}
?>