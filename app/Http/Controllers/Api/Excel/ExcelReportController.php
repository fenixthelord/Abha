<?php

namespace App\Http\Controllers\Api\Excel;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Jobs\ExportExcelJob;
use App\Models\Audit;
use App\Models\Event;
use App\Models\Position;
use App\Models\Service;
use App\Services\Excel\AuditTransformer;
use App\Services\Excel\PositionTransformer;
use App\Services\Excel\ServiceTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

                case "type":
                    return $this->exportTypesToExcel($request);
                    break;

                case "position":
                    return $this->exportPositionsToExcel($request);
                    break;

                case "event":
                    return $this->exportEventsToExcel($request);
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
            $fields = ['name->ar', 'name->en', 'details->en', 'details->ar'];
            // Set the file name based on the current date
            $dateNow = date('Ymd');


            // Get the current user's ID for notification purposes.
            $userId = auth('sanctum')->user()->id;

            $filename = "services_{$dateNow}_{$userId}.xlsx";
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
                $fields,
                ['department'],
                $filename,
                [$userId],
                $userId,
                [$transformer, 'transform']
            );


            // Return a successful JSON response.
            return $this->returnSuccessMessage('Export process started. You will receive a notification when it is ready.');
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response.
            return $this->handleException($e);
        }
    }

    public function exportTypesToExcel(Request $request)
    {
        try {

            $filters = $request->only(['service_id', 'form_id', 'search']);
            $fields = ['name->ar', 'name->en', 'details->en', 'details->ar'];
            $dateNow = date('Ymd');
            $userId = auth('sanctum')->user()->id;
            $filename = "types_{$dateNow}_{$userId}.xlsx";

            $transformer = new \App\Services\Excel\TypeTransformer();

            ExportExcelJob::dispatch(
                \App\Models\Type::class,
                $filters,
                $fields,
                ['service', 'form'],
                $filename,
                [$userId],
                $userId,
                [$transformer, 'transform']
            );

            return $this->returnSuccessMessage('Export process started. You will receive a notification when it is ready.');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function exportPositionsToExcel(Request $request)
    {
        try {
            $request->validate([
                'search' => 'nullable|string',
            ]);

            $query = Position::query();

            if ($request->has("search")) {
                $query->where("name", "LIKE", "%" . $request->search . "%");
            }

            $dateNow = date('Ymd');
            $userId = auth('sanctum')->user()->id;
            $filename = "positions_{$dateNow}_{$userId}.xlsx";

            $positions = $query->get();


            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Name');
            $sheet->setCellValue('C1', 'Parent ID');

            $row = 2;
            foreach ($positions as $position) {
                $sheet->setCellValue('A' . $row, $position->id);
                $sheet->setCellValue('B' . $row, $position->name);
                $sheet->setCellValue('C' . $row, $position->parent_id);

                $row++;
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $response = response()->stream(
                function() use ($writer) {
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                ]
            );

            return $response;
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function exportEventsToExcel(Request $request)
    {
        try {
            $request->validate([
                'service_id' => 'nullable|exists:services,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $filters = [];
            if ($request->filled('service_id')) {
                $filters['service_id'] = $request->input('service_id');
            }
            if ($request->filled('start_date')) {
                $filters['start_date'] = Carbon::parse($request->input('start_date'))->startOfDay();
            }
            if ($request->filled('end_date')) {
                $filters['end_date'] = Carbon::parse($request->input('end_date'))->endOfDay();
            }
            $fields = ['name->ar', 'name->en'];
            $dateNow = date('Ymd');
            $userId = auth('sanctum')->user()->id;
            $filename = "events_{$dateNow}_{$userId}.xlsx";

            $transformer = new \App\Services\Excel\EventTransformer();

            ExportExcelJob::dispatch(
                Event::class,
                $filters,
                $fields,
                [],
                $filename,
                [$userId],
                $userId,
                [$transformer, 'transform']
            );

            return $this->returnSuccessMessage('Export process started. You will receive a notification when it is ready.');
        } catch (\Exception $e) {
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
                'end_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:start_date',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value && !$request->filled('start_date')) {
                            $fail('The start_date field is required when end_date is provided.');
                        }
                    },
                ],
                'user_id' => 'nullable|string',
            ]);

            // Build filters based on provided request inputs
            $filters = [];
            $fields = ['auditable_type','user_type','event','user_id'];
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

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
            if ($request->filled('search')) {
                $filters['search'] = $request->input('search');
            }


            if ($request->filled('start_date') && $request->filled('end_date')) {
                $filters['created_at'] = [
                    Carbon::parse($request->input('start_date'))->startOfDay(),
                    Carbon::parse($request->input('end_date'))->endOfDay(),
                ];
            }
            // Set the filename based on the current date
            $dateNow = date('Ymd');


            // Get the current authenticated user ID
            $userId = auth('sanctum')->user()->id;

            $filename = "audit_logs_{$dateNow}_{$userId}.xlsx";

            // Transformer for formatting audit log data
            $transform = new AuditTransformer();

            // Dispatch the export job to process in the background
//            Queue::push(
            ExportExcelJob::dispatch(
                Audit::class,  // The model to query
                $filters,      // Applied filters
                $fields,
                [],            // No relationships needed
                $filename,     // Generated file name
                [$userId],
                $userId, // User to notify upon completion
                [$transform, 'transform'] // Data transformation callback
            );
            Log::error($userId);
            return $this->returnSuccessMessage('Export process started. You will receive a notification when it is ready.');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

}
