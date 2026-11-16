<?php
session_start();
require 'backend/config.php';
require 'vendor/autoload.php'; // Carrega a biblioteca PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Função para gerar um código de indicador único
function gerarCodigoIndicadorUnico($conexao) {
    do {
        $codigo = substr(str_shuffle(str_repeat('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', 5)), 0, 5);
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE codigo_indicador = ?");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    return $codigo;
}

// Validação de segurança
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Acesso negado.'];
    header("Location: usuarios.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['arquivo_xlsx'])) {
    
    // Validação do upload
    if ($_FILES['arquivo_xlsx']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro no upload do arquivo.'];
        header('Location: usuarios.php');
        exit;
    }

    $senha_padrao = $_POST['senha_padrao'] ?? '';
    if (empty($senha_padrao)) {
        $_SESSION['feedback'] = ['type' => 'error', 'message' => 'A senha padrão é obrigatória.'];
        header('Location: usuarios.php');
        exit;
    }
    
    $senha_hash = password_hash($senha_padrao, PASSWORD_DEFAULT);
    $caminho_arquivo = $_FILES['arquivo_xlsx']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($caminho_arquivo);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $sucesso = 0;
        $erros = 0;
        
        $conexao->begin_transaction();

        $stmt = $conexao->prepare("INSERT INTO usuarios (nome, sobrenome, email, cpf, telefone, data_nascimento, senha, tipo_conta, trocou_senha, ativo, codigo_indicador, data_criacao) VALUES (?, ?, ?, ?, ?, ?, ?, 'indicador', 0, 1, ?, NOW())");
        
        // Loop a partir da linha 2 para pular o cabeçalho
        for ($row = 2; $row <= $highestRow; $row++) {
            $nome = $sheet->getCell('A' . $row)->getValue();
            $sobrenome = $sheet->getCell('B' . $row)->getValue();
            $email = $sheet->getCell('C' . $row)->getValue();
            $cpf = $sheet->getCell('D' . $row)->getValue();
            $telefone = $sheet->getCell('E' . $row)->getValue();
            $data_nascimento_raw = $sheet->getCell('F' . $row)->getValue();
            
            // --- LÓGICA DE CONVERSÃO DE DATA ATUALIZADA ---
            $data_nascimento_mysql = null;
            if (!empty($data_nascimento_raw)) {
                // Tenta converter se for um número (formato padrão do Excel)
                if (is_numeric($data_nascimento_raw)) {
                    try {
                        $data_nascimento_mysql = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data_nascimento_raw)->format('Y-m-d');
                    } catch (Exception $e) {
                        $data_nascimento_mysql = null; // Ignora se for um número inválido
                    }
                // Tenta converter se for texto no formato DD/MM/AAAA
                } else if (is_string($data_nascimento_raw)) {
                    $dateObj = DateTime::createFromFormat('d/m/Y', $data_nascimento_raw);
                    if ($dateObj && $dateObj->format('d/m/Y') === $data_nascimento_raw) {
                        $data_nascimento_mysql = $dateObj->format('Y-m-d');
                    }
                }
            }
            // --- FIM DA LÓGICA DE CONVERSÃO ---

            // Validação simples
            if (empty($nome) || empty($email)) {
                $erros++;
                continue; // Pula para a próxima linha
            }

            $codigo_indicador = gerarCodigoIndicadorUnico($conexao);

            $stmt->bind_param("ssssssss", $nome, $sobrenome, $email, $cpf, $telefone, $data_nascimento_mysql, $senha_hash, $codigo_indicador);
            $stmt->execute();
            $sucesso++;
        }
        
        $conexao->commit();
        $_SESSION['feedback'] = ['type' => 'success', 'message' => "Importação concluída! {$sucesso} usuários cadastrados. {$erros} linhas com erros foram ignoradas."];

    } catch (Exception $e) {
        $conexao->rollback();
        $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao processar a planilha: ' . $e->getMessage()];
    }
}

header('Location: usuarios.php');
exit;
?>