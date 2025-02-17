<?php

namespace App\Http\Controllers\Api\Excel;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\ExportExcelJob;
use App\Models\Audit;
use App\Models\Service;
use App\Services\Excel\AuditTransformer;
use App\Services\Excel\ServiceTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;

class ExcelReportController extends Controller
{
    use ResponseTrait;

    public function export(Request $request)
    {
        try {
            switch ($request->export_type) {
                case "service":
                    return $this->exportServicesToExcel($request);
                    break;

                case "audit":
                    return $this->exportAuditLogsToExcel($request);
                    break;

                default:
                    return $this->badRequest($request->export_type . ' not found 😅');
            }
        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * Export Services to Excel.
     *
     * This method dispatches a job to export Service data into an Excel file.
     * It gathers filtering criteria from the request, defines a transformation
     * callback for each service, and then dispatches the ExportGenericJob.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse  JSON response indicating that the export process has started.
     */
    public function exportServicesToExcel(Request $request)
    {
        try {
            // Retrieve filtering criteria from the request (e.g., department_id, search term)
            $filters = $request->only(['department_id', 'search']);

            // Set the file name based on the current date
            $dateNow = date('Ymd');
            $filename = "services_{$dateNow}.xlsx";

            // Get the current user's ID for notification purposes.
            $userId = Auth::id();

            $transformer = new ServiceTransformer();

//            Queue::push(
//                ExportExcelJob::dispatch(
//                    Service::class,
//                    $filters,
//                    ['department'],
//                    $filename,
//                    [$userId],
//                    [$transformer, 'transform']
//                ));

            ExportExcelJob::dispatch(
                Service::class,
                $filters,
                ['department'],
                $filename,
                [$userId],
                [$transformer, 'transform']
            );

            // Return a successful JSON response.
            return $this->returnSuccessMessage('Export process started. You will receive a notification when it is ready.');
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response.
            return $this->handleException($e);
        }
    }

    /**
     * Export Audit Logs to Excel.
     *
     * This method dispatches a job to export Audit Log data into an Excel file.
     * It gathers filtering criteria from the request, defines a transformation
     * callback for each audit record, and then dispatches the ExportGenericJob.
     *
     * @param Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse  JSON response indicating that the export process has started.
     */
    public function exportAuditLogsToExcel(Request $request)
    {
        try {
            // Retrieve filtering or search criteria from the request (e.g., search term)
            $filters = $request->only(['search']); // You can add additional filters if needed.

            // Set the file name based on the current date.
            $dateNow = date('Ymd');
            $filename = "audit_logs_{$dateNow}.xlsx";

            // Get the current user's ID for notification purposes.
            $userId = Auth::id();

            // Define a callback to transform each Audit record into an array for export.
            $transform = new AuditTransformer();

            // Dispatch the generic export job for audit logs.
            dispatch(new ExportExcelJob(
                Audit::class,   // The model class to query.
                $filters,       // Filtering and search criteria.
                [],             // No extra relationships are required.
                $filename,      // Name of the output Excel file.
                [$userId],      // User IDs to be notified when export is complete.
                [$transform, 'transform']     // Transformation callback for each audit record.
            ));

            // Return a successful JSON response.
            return $this->returnSuccessMessage('Export process started. You will receive a notification when it is ready.');
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response.
            return $this->handleException($e);
        }
    }
}
