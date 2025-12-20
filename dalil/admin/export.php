<?php
session_start();
include '../includes/db_connect.php';

// Ensure the PhpSpreadsheet library is available
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
} else {
    die("Error: The PhpSpreadsheet library is not found. Please install it via Composer.");
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Security check
if (empty($_SESSION['admin_id']) || empty($_SESSION['permissions']['can_edit_programs'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied. You do not have permission to export data.');
}

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setRightToLeft(true); // Set sheet to RTL for Arabic

    // Define headers (in Arabic for user-friendliness)
    $headers = [
        'title' => 'عنوان البرنامج', 'organizer' => 'الجهة المنظمة', 'description' => 'الوصف',
        'Direction' => 'المنطقة/الاتجاه', 'location' => 'الموقع (الحي)', 'start_date' => 'تاريخ البدء',
        'end_date' => 'تاريخ الانتهاء', 'duration' => 'المدة', 'age_group' => 'الفئة العمرية',
        'price' => 'السعر', 'registration_link' => 'رابط التسجيل', 'google_map' => 'رابط خرائط جوجل',
        'status' => 'الحالة'
    ];

    // Write headers to the first row
    $column = 'A';
    foreach ($headers as $header_text) {
        $sheet->setCellValue($column . '1', $header_text);
        // Auto-size the column for better readability
        $sheet->getColumnDimension($column)->setAutoSize(true);
        $column++;
    }

    // Style the header row
    $header_style = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '8A2BE2']]
    ];
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($header_style);

    // Fetch all programs from the database
    $stmt = $pdo->query("SELECT * FROM programs ORDER BY id DESC");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Write data to the sheet starting from the second row
    $row_index = 2;
    foreach ($programs as $program) {
        $column = 'A';
        foreach ($headers as $db_key => $header_text) {
            $sheet->setCellValue($column . $row_index, $program[$db_key] ?? '');
            $column++;
        }
        $row_index++;
    }

    // Set headers for download
    $filename = 'programs_export_' . date('Y-m-d') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    // Provide a user-friendly error message
    die('Error creating Excel file: ' . $e->getMessage());
}