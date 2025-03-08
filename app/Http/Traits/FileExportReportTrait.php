<?php

namespace App\Http\Traits;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Exception;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

trait FileExportReportTrait
{

    public function exportData($data, $filename = 'Report.xlsx')
    {
        $userId=auth('sanctum')->id();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Report');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:H1');

        $headerRow = 2;
        $rowNumber = $headerRow + 1;

        if (!empty($data) && is_array($data)) {

            $columnCount = count(array_keys($data[0]));
            $lastColumn = Coordinate::stringFromColumnIndex($columnCount); // الحصول على العمود الأخير بناءً على عدد الأعمدة


            $sheet->fromArray(array_keys($data[0]), null, 'A' . $headerRow);
            $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            foreach ($data as $row) {
                $sheet->fromArray(array_values($row), null, 'A' . $rowNumber++);
                $sheet->getStyle("A" . ($rowNumber - 1) . ":{$lastColumn}" . ($rowNumber - 1))
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }


            for ($col = 1; $col <= $columnCount; $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }
        } else {
            $sheet->setCellValue('A2', 'No data available.');
        }


        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $excelData = ob_get_contents();
        ob_end_clean();

        if ($userId) {
            $date = date('YmdHis');
            $filename = "{$userId}_{$date}_{$filename}";
        }

        $userId = auth('sanctum')->id();
        $exportPath = public_path("exports/{$userId}/");
        if (!file_exists($exportPath)) {
            mkdir($exportPath, 0777, true);
        }
        $filePath = $exportPath . $filename;
        file_put_contents($filePath, $excelData);

        return "exports/{$userId}/{$filename}";
    }


    public function exportDataPdf($data, $filename = 'Report.pdf')
    {
        $directory = 'uploads/exports/';


        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->set_paper('a4', 'portrait');


        $html = '<div style="text-align: center; margin-top: 20px;">';
        $html .= '<table border="1" style="border-collapse: collapse; margin: 0 auto;">';

        $headers = array_keys(reset($data));
        $html .= '<tr>';
        foreach ($headers as $header) {
            $html .= '<th style="padding: 10px; border: 1px solid #ddd; background-color: #f2f2f2; font-weight: bold;">' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr>';


        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td style="padding: 10px; border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</div>';


        $dompdf->loadHtml($html);
        $dompdf->render();
        $output = $dompdf->output();

        file_put_contents($directory . $filename, $output);


        $fileContents = file_get_contents($directory . $filename);
        return base64_encode($fileContents);
    }



    public function exportDynamicTemplate($data, $filename = 'dynamic_template.xlsx', $columns, $title = 'Dynamic Template')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // إعداد العنوان الديناميكي
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->mergeCells('A1:' . Coordinate::stringFromColumnIndex(count($columns)) . '1');

        // كتابة أسماء الأعمدة
        $sheet->fromArray($columns, null, 'A2');
        $sheet->getStyle('A2:' . Coordinate::stringFromColumnIndex(count($columns)) . '2')->getFont()->setBold(true);
        $sheet->getStyle('A2:' . Coordinate::stringFromColumnIndex(count($columns)) . '2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // كتابة البيانات
        $rowNumber = 3; // يبدأ من الصف الثالث بعد العنوان وأسماء الأعمدة
        foreach ($data as $row) {
            $sheet->fromArray($row, null, 'A' . $rowNumber);
            $sheet->getStyle('A' . $rowNumber . ':' . Coordinate::stringFromColumnIndex(count($columns)) . $rowNumber)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $rowNumber++;
        }

        // جعل عرض الأعمدة تلقائيًا
        foreach (range('A', Coordinate::stringFromColumnIndex(count($columns))) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        // إنشاء المجلد إذا لم يكن موجودًا
        $directory = storage_path('app/public/exports');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . '/' . $filename;
        $writer->save($filePath);

        $fileContents = file_get_contents($filePath);
        return base64_encode($fileContents);
    }
}
