<?php

namespace App\Http\Controllers\Api\Excel;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\ExportExcelJob;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExcelReportController extends Controller
{
    use ResponseTrait;

    public function exportServicesToExcel()
    {
        try {
            // ✅ Retrieve services from the database
            $services = Service::select('id', 'name', 'details', 'image', 'department_id')->get();

            // ✅ Convert the data into an array suitable for export
            $exportData = $services->map(function ($service) {
                return [
//                    'ID'            => $service->id,
                    'Name (en)'     => $service->getTranslation('name', 'en'),  // English translation
                    'Name (ar)'     => $service->getTranslation('name', 'ar'),  // Arabic translation
                    'Details (en)'  => $service->getTranslation('details', 'en'),  // English translation
                    'Details (ar)'  => $service->getTranslation('details', 'ar'),  // Arabic translation
                    'Department (en)' => $service->department ? $service->department->getTranslation('name', 'en') : null,  // English department name
                    'Department (ar)' => $service->department ? $service->department->getTranslation('name', 'ar') : null,
                ];
            })->toArray();

            // ✅ Define the filename as `services_Ymd.xlsx`
            $dateNow = date('Ymd');
            $filename = "services_{$dateNow}.xlsx";

            // ✅ Get the current user's ID (if available)
            $userId = Auth::id();

            // ✅ Dispatch the job for export
            dispatch(new ExportExcelJob($exportData, $filename, [$userId], $userId));

            // ✅ Send JSON response
            return $this->returnSuccessMessage('Export process started. You will receive a notification when it is ready.');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
