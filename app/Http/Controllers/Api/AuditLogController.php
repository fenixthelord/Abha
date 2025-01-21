<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\User;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Carbon\Carbon;


class AuditLogController extends Controller
{
    use ResponseTrait;
    public function index(Request $request)
    {
        $request->validate([
            'model_type' => 'nullable|string',
            'user_type' => 'nullable|string',
            'user_id' => 'nullable|string',
            'event' => 'nullable|in:created,updated,deleted,restored', // Add event validation
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100', // Allow custom pagination
            'user_uuid' => 'nullable|string', // Add validation for user_uuid
        ]);
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
            $log->uuid = $log->fullName = null;
            if($log->user_type){
                $user = new $log->user_type;
                $details = $user->find($log->user_id)->first();
                $log->uuid = $details->uuid;
                $log->fullName = $details->first_name." ".$details->last_name;
            }

            return $log;
        });

        return $this->returnData('data',$auditLogs);
    }

}
