<?php
session_start();
require 'backend/config.php';
require 'vendor/autoload.php'; // Carrega a biblioteca PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Validação de segurança
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Acesso negado.'];
    header("Location: cadastros.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['arquivo_xlsx'])) {

    // Validação do upload
    if ($_FILES['arquivo_xlsx']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro no upload do arquivo.'];
        header('Location: cadastros.php');
        exit;
    }

    $caminho_arquivo = $_FILES['arquivo_xlsx']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($caminho_arquivo);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $sucesso = 0;
        $erros = 0;

        $conexao->begin_transaction();

        $stmt = $conexao->prepare("INSERT INTO servicos (nome, tipo_cashback, valor_cashback, tem_limite, limite_cashback) VALUES (?, ?, ?, ?, ?)");

        // Loop a partir da linha 2 para pular o cabeçalho
        for ($row = 2; $row <= $highestRow; $row++) {
            $nome = trim($sheet->getCell('A' . $row)->getValue());
            $tipo_cashback = strtolower(trim($sheet->getCell('B' . $row)->getValue()));
            $valor_cashback = (float)str_replace(',', '.', trim($sheet->getCell('C' . $row)->getValue()));
            $tem_limite_str = strtoupper(trim($sheet->getCell('D' . $row)->getValue()));
            $limite_cashback_raw = trim($sheet->getCell('E' . $row)->getValue());

            // Validações e conversões
            if (empty($nome) || !in_array($tipo_cashback, ['valor', 'percentual'])) {
                $erros++;
                continue; // Pula linha com dados essenciais faltando
            }

            $tem_limite = ($tem_limite_str === 'SIM') ? 1 : 0;
            $limite_cashback = null;
            if ($tem_limite && !empty($limite_cashback_raw)) {
                $limite_cashback = (float)str_replace(',', '.', $limite_cashback_raw);
            }

            $stmt->bind_param("ssdid", $nome, $tipo_cashback, $valor_cashback, $tem_limite, $limite_cashback);
            if ($stmt->execute()) {
                $sucesso++;
            } else {
                $erros++;
            }
        }

        $conexao->commit();
        $_SESSION['feedback'] = ['type' => 'success', 'message' => "Importação concluída! {$sucesso} serviços cadastrados. {$erros} linhas com erros foram ignoradas."];
    } catch (Exception $e) {
        $conexao->rollback();
        $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro ao processar a planilha: ' . $e->getMessage()];
    }
}

header('Location: cadastros.php');
exit;
