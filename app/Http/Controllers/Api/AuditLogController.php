<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Base query
        $query = Audit::query();

        // Dynamic filters
        $filters = [
            'model_type' => 'auditable_type', // Maps `model_type` to `auditable_type` in the database
            'user_type' => 'user_type',
            'user_id' => 'user_id',
            'event' => 'event',
            'created' => 'created_at',

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

        // Paginate results
        $auditLogs = $query->paginate(10);

        return response()->json($auditLogs);
    }
}
