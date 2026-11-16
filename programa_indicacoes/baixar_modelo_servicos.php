<?php
// Requer o autoload do Composer para usar a biblioteca PhpSpreadsheet
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Modelo Importação Serviços');

// Define o cabeçalho
$sheet->setCellValue('A1', 'Nome_Servico');
$sheet->setCellValue('B1', 'Tipo_Cashback (valor ou percentual)');
$sheet->setCellValue('C1', 'Valor_Cashback');
$sheet->setCellValue('D1', 'Tem_Limite (SIM ou NAO)');
$sheet->setCellValue('E1', 'Limite_Cashback (R$)');

// Adiciona exemplos para guiar o usuário
$sheet->setCellValue('A2', 'Restauração Simples');
$sheet->setCellValue('B2', 'valor');
$sheet->setCellValue('C2', '25.00');
$sheet->setCellValue('D2', 'NAO');
$sheet->setCellValue('E2', ''); // Vazio, pois não tem limite

$sheet->setCellValue('A3', 'Implante Unitário');
$sheet->setCellValue('B3', 'percentual');
$sheet->setCellValue('C3', '10'); // Apenas o número 10 para 10%
$sheet->setCellValue('D3', 'SIM');
$sheet->setCellValue('E3', '250.00');

$sheet->setCellValue('A4', 'Clareamento Completo');
$sheet->setCellValue('B4', 'percentual');
$sheet->setCellValue('C4', '15');
$sheet->setCellValue('D4', 'NAO'); // Sem limite
$sheet->setCellValue('E4', '');

// Formata colunas de valor como texto para evitar conversão automática do Excel
$sheet->getStyle('C')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
$sheet->getStyle('E')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

// Ajusta a largura das colunas
foreach (range('A', 'E') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Força o download do arquivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="modelo_importacao_servicos.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>