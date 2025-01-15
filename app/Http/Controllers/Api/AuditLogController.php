<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Carbon\Carbon;


class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Base query
        $query = Audit::query();

        // Dynamic filters
        $filters = [
            'model_type' => 'auditable_type',
            'user_type' => 'user_type',
            'user_id' => 'user_id',
            'event' => 'event',
        ];

        foreach ($filters as $filterKey => $dbColumn) {
            if ($request->filled($filterKey)) {
                $query->where($dbColumn, $request->input($filterKey));
            }
        }

        // Optional: Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        // Order by newest to oldest
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $auditLogs = $query->paginate(25);

        // Format dates
        $auditLogs->getCollection()->transform(function ($log) {
            $log->created_at_readable = Carbon::parse($log->created_at)->format('Y-m-d H:i:s');
            $log->updated_at_readable = Carbon::parse($log->updated_at)->format('Y-m-d H:i:s');
            return $log;
        });

        return response()->json($auditLogs);
    }

}
