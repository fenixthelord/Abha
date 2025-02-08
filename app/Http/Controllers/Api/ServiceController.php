<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Traits\ResponseTrait;
//use App\Http\Traits\PaginateTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Department;
class ServiceController extends Controller {
    use ResponseTrait;

    public function index(Request $request) {
        try {
            $page = intval($request->get('page', 1));
            $perPage = intval($request->get('per_page', 10));
            $search = $request->input('search', null);
            $departmentId = $request->input('department_id', null);

            $query = Service::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'LIKE', '%' . $search . '%')
                        ->orWhere('name->ar', 'LIKE', '%' . $search . '%')
                        ->orWhere('details->en', 'LIKE', '%' . $search . '%')
                        ->orWhere('details->ar', 'LIKE', '%' . $search . '%');
                });
            }

            if ($departmentId) {
                $query->whereHas('department', function ($q) use ($departmentId) {
                    $q->where('id', $departmentId);
                });
            }

            $results = $query->paginate($perPage, ['*'], 'page', $page);

            if ($page > $results->lastPage()) {
                $results = $query->paginate($perPage, ['*'], 'page', $results->lastPage());
            }
            return $this->PaginateData([
                'services' => ServiceResource::collection($results)
            ], $results);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($id) {
        try {
            if ($service = Service::where('id', $id)->first()) {
                $data['service'] = ServiceResource::make($service);
                return $this->returnData($data);
            } else {
                return $this->badRequest(__('validation.custom.service.not_found'));
            }
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'department_id' => ['required', 'exists:departments,id'],
                'name' => ['required', 'array', 'max:255'],
                'name.en' => ['required', 'string', 'max:255', Rule::unique('services', 'name->en')],
                'name.ar' => ['required', 'string', 'max:255', Rule::unique('services', 'name->ar')],
                'details' => ['required', 'array'],
                'details.en' => ['required'],
                'details.ar' => ['required'],
                'image' => ['required', 'string'],

            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $department_id = Department::where('id', $request->department_id)->value('id');

            $service = Service::create([
                'name' => $request->name,
                'details' => $request->details,
                'image' => $request->image,
                'department_id' => $department_id,
            ]);

           $data['service'] = ServiceResource::make($service);
            DB::commit();
            return $this->returnSuccessMessage($data , __('validation.custom.service.created'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function update(Request $request, $id) {
        DB::beginTransaction();
        try {
            $service = Service::where('id', $id)->first();

            if (!$service) {
                return $this->badRequest(__('validation.custom.service.not_found'));
            }

            $validator = Validator::make($request->all(), [
                'name' => ['nullable', 'array'],
                'name.en' => ['nullable', 'max:255'],
                'name.ar' => ['nullable', 'max:255'],
                'details' => ['nullable', 'array'],
                'details.en' => ['nullable', 'max:1000'],
                'details.ar' => ['nullable', 'max:1000'],
                'image' => ['nullable', 'string'],
                'department_id' => ['nullable', 'exists:departments,id'],
            ], messageValidation());

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            if ($request->filled('name')) {
                $exists = Service::where('id', '!=', $service->id)
                    ->where(function ($query) use ($request) {
                        if ($request->has('name.en')) {
                            $query->orWhere('name->en', $request->name['en']);
                        }
                        if ($request->has('name.ar')) {
                            $query->orWhere('name->ar', $request->name['ar']);
                        }
                    })
                    ->where('department_id', $service->department_id)->exists();

                if ($exists) {
                    return $this->badRequest(__('validation.custom.service.name_exists'));
                }
            }

            if ($request->filled('department_id')) {
                $department_id = Department::where('id', $request->department_id)->value('id');
                $service->department_id = $department_id;
            }

            $service->name = $request->name ?? $service->name;
            $service->details = $request->details ?? $service->details;
            $service->image = $request->image ?? $service->image;
            $service->save();

            $data['service'] = ServiceResource::make($service);
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function destroy($id) {
        DB::beginTransaction();
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:services,id',
            ], [
                'id.required' => 'Service id is required.',
                'id.exists' => 'Service id not found.',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            if (Service::where('id', $id)->onlyTrashed()->first()) {
                return $this->badRequest(__('validation.custom.service.deleted'));
            }

            if ($service = Service::where('id', $id)->first()) {
                $name = $service->getTranslations("name");
                $service->name = [
                    'en' => $name['en'] . '-' . $service->id . '-deleted',
                    'ar' => $name['ar'] . '-' . $service->id . '-محذوف',
                ];
                $service->save();
                $service->delete();

                DB::commit();
                return $this->returnSuccessMessage(__('validation.custom.service.deleted'));
            } else {
                return $this->badRequest(__('validation.custom.service.not_found'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
