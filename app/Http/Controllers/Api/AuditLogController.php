<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Carbon\Carbon;


class AuditLogController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
       /* $permissions = [
            //To be reviewed
            'index'  => ['audit.show'],
        ];

        foreach ($permissions as $method => $permissionGroup) {
            foreach ($permissionGroup as $permission) {
                $this->middleware("permission:{$permission}")->only($method);
            }
        }*/
    }

    public function index(Request $request)
    {
        $user = auth()->user();

     if (!$user->hasPermissionTo('audit.show')) {
            return $this->Forbidden("you don't have permission to access this page");
       }
        $request->validate([
            'model_type' => 'nullable|string',
            'user_type' => 'nullable|string',

            'event' => 'nullable|in:created,updated,deleted,restored', // Add event validation
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100', // Allow custom pagination
            'user_id' => 'nullable|string', // Add validation for user_id
        ]);
        // Base query
        $query = Audit::query();

        // Dynamic filters
        $filters = [
            'model_type' => 'auditable_type',
            'user_type' => 'user_type',

            'event' => 'event',
        ];

        foreach ($filters as $filterKey => $dbColumn) {
            if ($request->filled($filterKey)) {
                $query->where($dbColumn, $request->input($filterKey));
            }
        }

        if ($request->filled('user_id')) {


            $query->where('user_id', $request->input('user_id'));
        }

        // Optional: Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->input('start_date')),
                Carbon::parse($request->input('end_date')),
            ]);
        } else if ($request->filled('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->input('start_date')));
        } else if ($request->filled('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->input('end_date')));
        }

        // Order by newest to oldest
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $perPage = isset($request->per_page) ? $request->per_page : 10;
        $auditLogs = $query->paginate($perPage);

        // Format dates
        $auditLogs->getCollection()->transform(function ($log) {
            $log->created_at_readable = Carbon::parse($log->created_at)->format('Y-m-d H:i:s');
            $log->updated_at_readable = Carbon::parse($log->updated_at)->format('Y-m-d H:i:s');
            $log->id = $log->fullName = null;
            if ($log->user_type) {
                $user = new $log->user_type;
                $details = $user->find($log->user_id);

                // Check if user details were found
                if ($details) {
                    $log->id = $details->id;
                    $log->fullName = $details->first_name . " " . $details->last_name;
                }
            }

            return $log;
        });

        return $this->returnData($auditLogs);
    }
}
