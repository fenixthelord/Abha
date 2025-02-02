<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\OrgFilterRequest;
use App\Http\Resources\OrganizationResource;
use App\Http\Traits\Paginate;
use App\Http\Traits\ResponseTrait;
use App\Models\Department;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpParser\Node\Expr\Cast\Object_;

class OrganizationController extends Controller
{
    use ResponseTrait;
    public function index(OrgFilterRequest $request)
    {
        try {
            $perPage = $request->input('per_page', $this->per_page);
            $pageNumber = $request->input('page', $this->pageNumber);
            $department_uuid = $request->input('department_uuid');
            $manger_uuid = $request->input('manger_uuid');
            $query  = Organization::query()
                ->when(
                    $department_uuid || $manger_uuid,
                    function ($q) use ($request) {
                        if ($request->department_uuid) {
                            $department =  Department::where('uuid', $request->department_uuid)->pluck('id')->first();
                            $q->where("department_id", $department);
                        }
                        if ($request->manger_uuid) {
                            $manger =  User::where('uuid', $request->manger_uuid)->pluck('id')->first();
                            $q->where("manger_id", $manger);
                        }
                    },
                )
                ;
            $organization = $query->paginate($perPage, ['*'], 'page', $pageNumber);

            if ($request->page > $organization->lastPage()) {
                $organization = Organization::paginate($perPage, ['*'], 'page', $pageNumber);
            }


            $data["organizations"] = OrganizationResource::collection($organization);

            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function filter(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'per_page' => 'nullable|integer|min:1',
                'page' => 'nullable|integer|min:1',

            ]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'uuid' => ['required', 'uuid', Rule::exists('organizations', 'uuid')->where("deleted_at", null)],
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $org = Organization::where('uuid', $request->uuid)->first();
            $org->delete();
            DB::commit();
            return $this->returnSuccessMessage('Organization deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
