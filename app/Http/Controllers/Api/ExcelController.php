<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelController extends Controller
{
    use ResponseTrait;
    public function extractColumn(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
                'column' => 'required|string|min:1|max:2'
            ]);

            $file = $request->file('file')->getPathname();
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $columnLetter = strtoupper($request->input('column'));

            $columnValues = [];
            foreach ($sheet->getColumnIterator($columnLetter) as $column) {
                foreach ($column->getCellIterator() as $cell) {
                    $columnValues[] = $cell->getValue();
                }
            }
            $data["names"] = array_values($columnValues);
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
