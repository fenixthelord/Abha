<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\Paginate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceController extends Controller {
    use ResponseTrait, PaginateTrait;

    public function index(Request $request) {
        try {
            $fields = ['name->ar', 'name->en', 'details->ar', 'details->en'];
            $services = $this->allWithSearch(new Service(), $fields, $request, 'department_id', $request->input('department_uuid'), '=');

            $data['services'] = ServiceResource::collection($services);
            return $this->PaginateData($data, $services);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($uuid) {
        try {
            if ($service = Service::where('id', $uuid)->first()) {
                $data['service'] = ServiceResource::make($service);
                return $this->returnData($data);
            } else {
                return $this->badRequest(__('validation.custom.service.notfound'));
            }
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'array', 'max:254'],
                'name.en' => ['required', 'max:255', Rule::unique('services', 'name->en')],
                'name.ar' => ['required', 'max:255', Rule::unique('services', 'name->ar')],
                'details' => ['nullable', 'array'],
                'details.en' => ['nullable', 'max:1000'],
                'details.ar' => ['nullable', 'max:1000'],
                'image' => ['nullable', 'string'],
                'department_uuid' => ['required', 'exists:departments,uuid'],
            ], messageValidation());

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $department_id = Department::where('uuid', $request->department_uuid)->value('id');

            $service = Service::create([
                'name' => $request->name,
                'details' => $request->details,
                'image' => $request->image,
                'department_id' => $department_id,
            ]);

            $data['service'] = ServiceResource::make($service);
            DB::commit();
            return $this->returnData($data, __('validation.custom.service.created'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function update(Request $request, $uuid)
    {
        DB::beginTransaction();
        try {
            if ($service = Service::where('id', $uuid)->first()) {
                $validator = Validator::make($request->all(), [
                    'name' => ['nullable', 'array'],
                    'name.en' => ['required_with:name', 'max:255', Rule::unique('services', 'name->en')->ignore($service->id)],
                    'name.ar' => ['required_with:name', 'max:255', Rule::unique('services', 'name->ar')->ignore($service->id)],
                    'details' => ['nullable', 'array'],
                    'details.en' => ['nullable', 'max:1000'],
                    'details.ar' => ['nullable', 'max:1000'],
                    'image' => ['nullable', 'string'],
                    'department_uuid' => ['nullable', 'exists:departments,uuid'],
                ], messageValidation());

                if ($validator->fails()) {
                    return $this->returnValidationError($validator);
                }

                if ($request->filled('department_uuid')) {
                    $department_id = Department::where('uuid', $request->department_uuid)->value('id');$service->department_id = $department_id;
                }

                $service->name = $request->name ?? $service->name;
                $service->details = $request->details ?? $service->details;
                $service->image = $request->image ?? $service->image;
                $service->save();

                $data['service'] = ServiceResource::make($service);
                DB::commit();
                return $this->returnData($data);
            } else {
                return $this->badRequest(__('validation.custom.service.notfound'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function destroy($uuid)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make(['uuid' => $uuid], [
                'uuid' => 'required|exists:services,id',
            ], [
                'uuid.required' => 'Service uuid is required.',
                'uuid.exists' => 'Service uuid not found.',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            if (Service::where('id', $uuid)->onlyTrashed()->first()) {
                return $this->badRequest(__('validation.custom.service.deleted'));
            }

            if ($service = Service::where('id', $uuid)->first()) {
                $name = $service->getTranslations("name");
                $service->name = [
                    'en' => $name['en'] . '-' . $service->id . '-deleted',
                    'ar' => $name['ar'] . '-' . $service->id . '-محذوف',
                ];
                $service->save();
                $service->delete();

                DB::commit();
                return $this->returnSuccessMessage(__('validation.custom.service.delete'));
            } else {
                return $this->badRequest(__('validation.custom.service.notfound'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
