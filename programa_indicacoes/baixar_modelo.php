<?php
// Requer o autoload do Composer para usar a biblioteca PhpSpreadsheet
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Cria uma nova planilha
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Modelo de Importação');

// --- ATUALIZADO ---
// Define o cabeçalho com o novo formato de data
$sheet->setCellValue('A1', 'Nome');
$sheet->setCellValue('B1', 'Sobrenome');
$sheet->setCellValue('C1', 'Email');
$sheet->setCellValue('D1', 'CPF');
$sheet->setCellValue('E1', 'Telefone');
$sheet->setCellValue('F1', 'Data_Nascimento (DD/MM/AAAA)'); // Cabeçalho corrigido

// --- ATUALIZADO ---
// Adiciona um exemplo com o novo formato de data
$sheet->setCellValue('A2', 'João');
$sheet->setCellValue('B2', 'Silva');
$sheet->setCellValue('C2', 'joao.silva@exemplo.com');
$sheet->setCellValue('D2', '12345678900');
$sheet->setCellValue('E2', '(11) 98765-4321');
$sheet->setCellValue('F2', '15/05/1990'); // Exemplo corrigido

// --- NOVO ---
// Formata a coluna F como TEXTO para evitar que o Excel converta a data automaticamente
$sheet->getStyle('F')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

// Ajusta a largura das colunas
foreach (range('A', 'F') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Configura os headers para forçar o download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="modelo_importacao_usuarios.xlsx"');
header('Cache-Control: max-age=0');

// Cria o escritor e envia o arquivo para o navegador
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>