<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\Cast\Object_;

class OrganizationController extends Controller
{
    use ResponseTrait;
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'per_page' => 'nullable|integer|min:1',
                'page' => 'nullable|integer|min:1',
            ]);
            $perPage = $request->input('per_page', $this->per_page);
            $pageNumber = $request->input('page', $this->pageNumber);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $organization = Organization::paginate($perPage, ['*'], 'page', $pageNumber);

            if ($request->page > $organization->lastPage()) {
                $organization = Organization::paginate($perPage, ['*'], 'page', $pageNumber);
            }

            $data["organizations"] = OrganizationResource::collection($organization);

            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}

