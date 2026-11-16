<?php

// Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// --- FUNÇÃO CENTRAL DE ENVIO DE E-MAIL ---
// Esta função contém toda a configuração do servidor SMTP.
// As outras funções irão chamá-la.
function enviarEmail($para_email, $para_nome, $assunto, $corpo_html, $corpo_texto) {
    // Carrega as variáveis de ambiente do arquivo .env
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURAÇÃO PARA SMTP DA HOSTGATOR (LIDO DO .ENV) ---
        // $mail->SMTPDebug = 2; // Descomente esta linha para ver logs de erro detalhados
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int)$_ENV['SMTP_PORT'];
        $mail->CharSet    = 'UTF-8';

        // Remetente e Destinatário
        $mail->setFrom($_ENV['SMTP_USER'], 'Alfonso Rewards'); // O nome que aparece como remetente
        $mail->addAddress($para_email, $para_nome);

        // Conteúdo do E-mail
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $corpo_html;
        $mail->AltBody = $corpo_texto;

        $mail->send();
        return true; // Sucesso

    } catch (Exception $e) {
        // Grava o erro no log do servidor para você poder investigar
        error_log('PHPMailer Error (SMTP HostGator): ' . $mail->ErrorInfo);
        return false; // Falha
    }
}

// --- FUNÇÕES ESPECÍFICAS (AGORA MUITO MAIS SIMPLES) ---

function enviarEmailDeCadastro($email, $nome, $token) {
$link_confirmacao = "https://www.clinicaalfonso.com.br/programa_indicacoes/confirmar_email.php?email=" . urlencode($email) . "&token=" . $token;

    $htmlBody = file_get_contents(__DIR__ . '/../emails/template_cadastro.html');
    $htmlBody = str_replace('{{NOME_USUARIO}}', htmlspecialchars($nome), $htmlBody);
$htmlBody = str_replace('{{LINK_VERIFICACAO}}', $link_confirmacao, $htmlBody);
    
    $altBody = "Olá, " . htmlspecialchars($nome) . "! Bem-vindo ao Alfonso Rewards. Confirme seu e-mail: " . $link_confirmacao;
    $assunto = 'Confirme seu Cadastro no Alfonso Rewards';
    
    // Chama a função central para fazer o envio
    return enviarEmail($email, $nome, $assunto, $htmlBody, $altBody);
}

function enviarEmailReativacao($email, $nome, $conexao) {
    // 1. GERAÇÃO DE UM NOVO TOKEN SEGURO
    // Usamos random_bytes para gerar um token criptograficamente seguro.
    // bin2hex converte o token para um formato que pode ser usado em URLs.
    $token = bin2hex(random_bytes(32)); 
    
    // 2. CRIAÇÃO DO LINK DE CONFIRMAÇÃO
    // Este link conterá o token original (não o hash).
    $link_confirmacao = "https://www.clinicaalfonso.com.br/programa_indicacoes/confirmar_email.php?email=" . urlencode($email) . "&token=" . $token;

    // 3. ARMAZENAMENTO SEGURO DO TOKEN NO BANCO DE DADOS
    try {
        // Fazemos o hash do token. Nunca guardamos tokens em texto puro no banco.
        $token_hash = password_hash($token, PASSWORD_DEFAULT);
        
        // Definimos a data de expiração do token (ex: 24 horas a partir de agora).
        $expiracao = date('Y-m-d H:i:s', time() + 86400); // 86400 segundos = 24 horas

        // Preparamos a query SQL para inserir ou atualizar o token.
        // A lógica "INSERT ... ON DUPLICATE KEY UPDATE" é ótima aqui.
        // Se o usuário já tiver um token antigo, ele será substituído pelo novo.
        // Para isso, a coluna 'email' na tabela 'usuarios_tokens' deve ser uma chave única (UNIQUE).
        $stmt = $conexao->prepare(
            "INSERT INTO usuarios_tokens (email, token, expirado_em) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE token = ?, expirado_em = ?"
        );
        $stmt->bind_param("sssss", $email, $token_hash, $expiracao, $token_hash, $expiracao);
        
        // Se a execução da query falhar, registramos o erro e retornamos false.
        if (!$stmt->execute()) {
            error_log("Falha ao salvar o token de reativação para o e-mail: " . $email . " - " . $stmt->error);
            return false;
        }
        $stmt->close();

    } catch (Exception $e) {
        // Se ocorrer qualquer outro erro, registramos e retornamos false.
        error_log("Exceção ao processar token de reativação: " . $e->getMessage());
        return false;
    }

    // 4. PREPARAÇÃO E ENVIO DO E-MAIL
    // Carrega o template HTML do e-mail.
    $htmlBody = file_get_contents(__DIR__ . '/../emails/template_reativacao.html');
    
    // Substitui os placeholders pelos dados reais.
    $htmlBody = str_replace('{{NOME_USUARIO}}', htmlspecialchars($nome), $htmlBody);
    $htmlBody = str_replace('{{LINK_VERIFICACAO}}', $link_confirmacao, $htmlBody);

    // Corpo do e-mail em texto puro, para clientes de e-mail que não suportam HTML.
    $altBody = "Olá, " . htmlspecialchars($nome) . "! Para reativar sua conta, acesse o link: " . $link_confirmacao;
    $assunto = 'Ative sua conta no Alfonso Rewards';

    // 5. CHAMADA DA FUNÇÃO CENTRAL DE ENVIO
    // Só tentamos enviar o e-mail se o token foi salvo com sucesso no banco.
    return enviarEmail($email, $nome, $assunto, $htmlBody, $altBody);
}

function enviarEmailTokenAcao($email, $nome, $token) {
    $htmlBody = file_get_contents(__DIR__ . '/../emails/template_token.html');
    $htmlBody = str_replace('{{NOME_USUARIO}}', htmlspecialchars($nome), $htmlBody);
    $htmlBody = str_replace('{{TOKEN}}', $token, $htmlBody);

    $altBody = "Olá, " . htmlspecialchars($nome) . "! Seu código de verificação é: " . $token;
    $assunto = 'Seu Código de Verificação - Alfonso Rewards';

    return enviarEmail($email, $nome, $assunto, $htmlBody, $altBody);
}

function enviarEmailRecuperacao($email, $nome, $token) {
    $link_recuperacao = "https://www.clinicaalfonso.com.br/programa_indicacoes/redefinir_senha.php?token=" . $token;

    $htmlBody = file_get_contents(__DIR__ . '/../emails/template_recuperacao.html');
    $htmlBody = str_replace('{{NOME_USUARIO}}', htmlspecialchars($nome), $htmlBody);
    $htmlBody = str_replace('{{LINK_RECUPERACAO}}', $link_recuperacao, $htmlBody);
    
    $altBody = "Olá, " . htmlspecialchars($nome) . "! Para redefinir sua senha, acesse o link: " . $link_recuperacao;
    $assunto = 'Recuperação de Senha - Alfonso Rewards';

    return enviarEmail($email, $nome, $assunto, $htmlBody, $altBody);
}

?>