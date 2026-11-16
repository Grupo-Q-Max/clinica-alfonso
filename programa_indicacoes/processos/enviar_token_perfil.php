<?php
session_start();
ob_start(); 

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../backend/config.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\GenericProvider;

function send_json_response($success, $message)
{
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    error_log("Erro no .env: " . $e->getMessage());
    send_json_response(false, 'Erro de configuração do servidor.');
}

if (!isset($_SESSION['usuario_id'])) {
    send_json_response(false, 'Acesso negado. Por favor, faça login novamente.');
}

$usuario_id = $_SESSION['usuario_id'];
$stmt = $conexao->prepare("SELECT email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    send_json_response(false, 'Usuário não encontrado.');
}
$user = $result->fetch_assoc();
$email = $user['email'];

$token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expirado_em = date('Y-m-d H:i:s', strtotime('+15 minutes'));
$motivo = 'editar_perfil';

$stmt_insert = $conexao->prepare("INSERT INTO usuarios_tokens (email, token, expirado_em, motivo) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token), expirado_em = VALUES(expirado_em), confirmado = 0");
$stmt_insert->bind_param("ssss", $email, $token, $expirado_em, $motivo);
if (!$stmt_insert->execute()) {
    error_log('Falha ao inserir token para editar perfil: ' . $email);
    send_json_response(false, 'Erro ao registrar o token no banco de dados.');
}

$mail = new PHPMailer(true);
try {
    $provider = new GenericProvider(['clientId' => $_ENV['OAUTH_CLIENT_ID'], 'clientSecret' => $_ENV['OAUTH_CLIENT_SECRET'], 'redirectUri' => $_ENV['OAUTH_REDIRECT_URI'], 'urlAuthorize' => "https://login.microsoftonline.com/{$_ENV['OAUTH_TENANT_ID']}/oauth2/v2.0/authorize", 'urlAccessToken' => "https://login.microsoftonline.com/{$_ENV['OAUTH_TENANT_ID']}/oauth2/v2.0/token", 'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me', 'scopes' => 'https://outlook.office.com/SMTP.Send offline_access']);
    $newAccessToken = $provider->getAccessToken('refresh_token', ['refresh_token' => $_ENV['OAUTH_REFRESH_TOKEN']]);
    $mail->isSMTP();
    $mail->Host = 'smtp.office365.com';
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPAuth = true;
    $mail->AuthType = 'XOAUTH2';
    $mail->CharSet  = 'UTF-8';
    $mail->setOAuth(new OAuth(['provider' => $provider, 'clientId' => $_ENV['OAUTH_CLIENT_ID'], 'clientSecret' => $_ENV['OAUTH_CLIENT_SECRET'], 'refreshToken' => $_ENV['OAUTH_REFRESH_TOKEN'], 'accessToken'  => $newAccessToken->getToken(), 'userName' => $_ENV['MAIL_FROM'],]));
    $mail->setFrom($_ENV['MAIL_FROM'], 'QXBack - Suporte');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Token de Verificação para Edição de Perfil';
    $mail->Body    = "Use o seguinte token para confirmar sua identidade e editar seu perfil: <strong>{$token}</strong>";
    $mail->send();
    send_json_response(true, 'Token enviado com sucesso!');
} catch (Exception $e) {
    error_log("Erro ao enviar token de perfil: " . $mail->ErrorInfo);
    send_json_response(false, 'Não foi possível enviar o token. Tente novamente.');
}
