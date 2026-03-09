<?php
require_once __DIR__ . '/../partials/bootstrap.php';
require_role(['pic', 'admin']);

require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set Header Title
$sheet->setTitle('Template Import PC');

// Header Columns
$headers = [
    'A1' => 'Kode PC (Wajib)',
    'B1' => 'Nama PC',
    'C1' => 'Lokasi (Wajib)',
    'D1' => 'Kondisi',
    'E1' => 'Status Check',
    'F1' => 'Internet (Ya/Tidak)',
    'G1' => 'Ready (Ya/Tidak)',
];

// Set Headers
foreach ($headers as $cell => $text) {
    $sheet->setCellValue($cell, $text);
}

// Styling Headers
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFFFF'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FF4CAF50', // Green
        ],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];

$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(25);

// Auto-size columns
foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Add Example Data (Row 2)
$sheet->setCellValue('A2', 'APK/01/1234/56/7890');
$sheet->setCellValue('B2', 'MIP COMLAB 01');
$sheet->setCellValue('C2', 'Komlab 1');
$sheet->setCellValue('D2', 'Baik');
$sheet->setCellValue('E2', 'Sudah');
$sheet->setCellValue('F2', 'Ya');
$sheet->setCellValue('G2', 'Ya');

$sheet->getStyle('A2:G2')->getFont()->setItalic(true);
$sheet->getStyle('A2:G2')->getFont()->getColor()->setARGB('FF666666'); // Gray to indicate example

// Output to Browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="template_import_pc.xlsx"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1'); // If you're serving to IE 9, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
