<?php

namespace App\Http\Controllers\Api\Excel;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\ExportExcelJob;
use App\Models\Audit;
use App\Models\Service;
use App\Services\Excel\AuditTransformer;
use App\Services\Excel\ServiceTransformer;
use Carbon\Carbon;
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

            Queue::push(
                ExportExcelJob::dispatch(
                    Service::class,
                    $filters,
                    ['department'],
                    $filename,
                    [$userId],
                    [$transformer, 'transform']
                ));

//            ExportExcelJob::dispatch(
//                Service::class,
//                $filters,
//                ['department'],
//                $filename,
//                [$userId],
//                [$transformer, 'transform']
//            );

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
            // Validate incoming request parameters
            $request->validate([
                'model_type' => 'nullable|string',
                'user_type' => 'nullable|string',
                'event' => 'nullable|in:created,updated,deleted,restored',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'user_id' => 'nullable|string',
            ]);

            // Build filters based on provided request inputs
            $filters = [];

            if ($request->filled('model_type')) {
                $filters['auditable_type'] = $request->input('model_type');
            }
            if ($request->filled('user_type')) {
                $filters['user_type'] = $request->input('user_type');
            }
            if ($request->filled('event')) {
                $filters['event'] = $request->input('event');
            }
            if ($request->filled('user_id')) {
                $filters['user_id'] = $request->input('user_id');
            }


            if ($request->filled('start_date') && $request->filled('end_date')) {
                $filters['created_at'] = [
                    Carbon::parse($request->input('start_date'))->startOfDay(),
                    Carbon::parse($request->input('end_date'))->endOfDay(),
                ];
            } elseif ($request->filled('start_date')) {
                $filters['created_at'] = ['>=', Carbon::parse($request->input('start_date'))->startOfDay()];
            } elseif ($request->filled('end_date')) {
                $filters['created_at'] = ['<=', Carbon::parse($request->input('end_date'))->endOfDay()];
            }
            // Set the filename based on the current date
            $dateNow = date('Ymd');
            $filename = "audit_logs_{$dateNow}.xlsx";

            // Get the current authenticated user ID
            $userId = Auth::id();

            // Transformer for formatting audit log data
            $transform = new AuditTransformer();

            // Dispatch the export job to process in the background
            Queue::push(
            ExportExcelJob::dispatch(
                Audit::class,  // The model to query
                $filters,      // Applied filters
                [],            // No relationships needed
                $filename,     // Generated file name
                [$userId],     // User to notify upon completion
                [$transform, 'transform'] // Data transformation callback
            ));

            return $this->returnSuccessMessage('Export process started. You will receive a notification when it is ready.');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

}
