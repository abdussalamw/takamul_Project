<?php
// export_report.php - Export reports to Excel

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Initialize
include_once '../includes/db_connect.php';
include_once 'AdminController.php';
$adminController = new AdminController($pdo);
$adminController->requirePermission('can_manage_settings');

// Fetch data
$reports_data = $adminController->getReportsData();

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(11);

// Header styling
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8A2BE2']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$dataStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

// Sheet 1: الجهات
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('الجهات المشاركة');
$sheet->setCellValue('A1', 'الجهة');
$sheet->setCellValue('B1', 'عدد البرامج');
$sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

$row = 2;
foreach ($reports_data['organizers'] as $item) {
    $sheet->setCellValue('A' . $row, $item['organizer']);
    $sheet->setCellValue('B' . $row, (int)$item['count']);
    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray($dataStyle);
    $row++;
}
$sheet->getColumnDimension('A')->setAutoSize(true);
$sheet->getColumnDimension('B')->setAutoSize(true);

// Sheet 2: الأقسام
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('الأقسام');
$sheet2->setCellValue('A1', 'القسم');
$sheet2->setCellValue('B1', 'عدد البرامج');
$sheet2->getStyle('A1:B1')->applyFromArray($headerStyle);

$row = 2;
foreach ($reports_data['directions'] as $item) {
    $sheet2->setCellValue('A' . $row, $item['Direction']);
    $sheet2->setCellValue('B' . $row, (int)$item['count']);
    $sheet2->getStyle('A' . $row . ':B' . $row)->applyFromArray($dataStyle);
    $row++;
}
$sheet2->getColumnDimension('A')->setAutoSize(true);
$sheet2->getColumnDimension('B')->setAutoSize(true);

// Sheet 3: الأماكن
$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('الأماكن');
$sheet3->setCellValue('A1', 'المكان');
$sheet3->setCellValue('B1', 'عدد البرامج');
$sheet3->getStyle('A1:B1')->applyFromArray($headerStyle);

$row = 2;
foreach ($reports_data['locations'] as $item) {
    $sheet3->setCellValue('A' . $row, $item['location']);
    $sheet3->setCellValue('B' . $row, (int)$item['count']);
    $sheet3->getStyle('A' . $row . ':B' . $row)->applyFromArray($dataStyle);
    $row++;
}
$sheet3->getColumnDimension('A')->setAutoSize(true);
$sheet3->getColumnDimension('B')->setAutoSize(true);

// Sheet 4: جميع البرامج
$sheet4 = $spreadsheet->createSheet();
$sheet4->setTitle('جميع البرامج');

$stmt = $pdo->query("SELECT * FROM programs ORDER BY id DESC");
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$headers = ['#', 'العنوان', 'الجهة', 'القسم', 'الموقع', 'تاريخ البدء', 'المدة', 'الفئة العمرية', 'السعر', 'الحالة'];
$col = 'A';
foreach ($headers as $header) {
    $sheet4->setCellValue($col . '1', $header);
    $col++;
}
$sheet4->getStyle('A1:J1')->applyFromArray($headerStyle);

$row = 2;
foreach ($programs as $p) {
    $sheet4->setCellValue('A' . $row, $p['id']);
    $sheet4->setCellValue('B' . $row, $p['title']);
    $sheet4->setCellValue('C' . $row, $p['organizer']);
    $sheet4->setCellValue('D' . $row, $p['Direction']);
    $sheet4->setCellValue('E' . $row, $p['location']);
    $sheet4->setCellValue('F' . $row, $p['start_date']);
    $sheet4->setCellValue('G' . $row, $p['duration']);
    $sheet4->setCellValue('H' . $row, $p['age_group']);
    $sheet4->setCellValue('I' . $row, $p['price']);
    $sheet4->setCellValue('J' . $row, $p['status']);
    $sheet4->getStyle('A' . $row . ':J' . $row)->applyFromArray($dataStyle);
    $row++;
}

foreach (range('A', 'J') as $col) {
    $sheet4->getColumnDimension($col)->setAutoSize(true);
}

// Output
$filename = 'تقرير_البرامج_' . date('Y-m-d_H-i') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;