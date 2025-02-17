<?php

namespace App\Http\Controllers\Api\Type;

use App\Http\Controllers\Controller;
use App\Http\Resources\Type\TypeResource;
use App\Http\Resources\ServiceResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Forms\Form;
use App\Models\Service;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TypeController extends Controller {
    use ResponseTrait;

    public function index(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'page' => ['nullable', 'integer', 'min:1'],
                'per_page' => ['nullable', 'integer', 'min:1'],
                'search' => ['nullable', 'string'],
                'service_id' => ['nullable', 'exists:services,id,deleted_at,NULL'],
                'form_id' => ['nullable', 'exists:forms,id,deleted_at,NULL'],
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $query = Type::query();

            if ($request->filled('search')) {
                $query->where('name', 'LIKE', "%{$request->search}%");
            }

            if ($request->filled('service_id')) {
                $query->where('service_id', $request->service_id);
            }

            if ($request->filled('form_id')) {
                $query->where('form_id', $request->form_id);
            }

            $results = $query->paginate($request->per_page ?? 10);

            return $this->PaginateData(['types' => TypeResource::collection($results)], $results);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Request $request) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:types,id',
            ], [
                'id.required' => __('validation.custom.type_controller.id_required'),
                'id.exists' => __('validation.custom.type_controller.id_exists'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $type = Type::findOrFail($request->id);
            DB::commit();
            return $this->returnData(['type' => new TypeResource($type)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function store(Request $request) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'max:500'],
                'name.en' => ['required', 'max:500', Rule::unique('types', 'name->en')],
                'name.ar' => ['required', 'max:500', Rule::unique('types', 'name->ar')],
                'service_id' => ['required', 'exists:services,id,deleted_at,NULL'],
                'form_id' => ['required', 'exists:forms,id,deleted_at,NULL'],
            ], [
                'name.required' => __('validation.custom.type_controller.name_required'),
                'name.en.required' => __('validation.custom.type_controller.name_en_required'),
                'name.ar.required' => __('validation.custom.type_controller.name_ar_required'),
                'name.en.unique' => __('validation.custom.type_controller.name_en_unique'),
                'name.ar.unique' => __('validation.custom.type_controller.name_ar_unique'),
                'service_id.required' => __('validation.custom.type_controller.service_id_required'),
                'service_id.exists' => __('validation.custom.type_controller.service_id_exists'),
                'form_id.required' => __('validation.custom.type_controller.form_id_required'),
                'form_id.exists' => __('validation.custom.type_controller.form_id_exists'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $type = Type::create([
                'name' => $request->name,
                'service_id' => $request->service_id,
                'form_id' => $request->form_id,
            ]);

            DB::commit();
            return $this->returnSuccessMessage(['type' => new TypeResource($type)], __('type_controller.created'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function update(Request $request) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:types,id',
                'name' => ['nullable', 'max:500'],
                'name.en' => ['nullable', 'max:500', Rule::unique('types', 'name->en')],
                'name.ar' => ['nullable', 'max:500', Rule::unique('types', 'name->ar')],
                'service_id' => ['nullable', 'exists:services,id,deleted_at,NULL'],
                'form_id' => ['nullable', 'exists:forms,id,deleted_at,NULL'],
            ], [
                'id.required' => __('validation.custom.type_controller.id_required'),
                'id.exists' => __('validation.custom.type_controller.id_exists'),
                'name.en.unique' => __('validation.custom.type_controller.name_en_unique'),
                'name.ar.unique' => __('validation.custom.type_controller.name_ar_unique'),
                'service_id.exists' => __('validation.custom.type_controller.service_id_exists'),
                'form_id.exists' => __('validation.custom.type_controller.form_id_exists'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $type = Type::findOrFail($request->id);
            $type->update($request->only([
                'name',
                'service_id',
                'form_id',
            ]));


            DB::commit();
            return $this->returnData(['type' => new TypeResource($type)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function getServiceByType(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => ['required', 'exists:types,id'],
            ], [
                'id.required' => __('validation.custom.type_controller.id_required'),
                'id.exists' => __('validation.custom.type_controller.id_exists'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $type = Type::with('service')->findOrFail($request->id);

            if (!$type->service) {
                return $this->NotFound(__('validation.custom.type_controller.service_not_found'));
            }

            return $this->returnData(['service' => new ServiceResource($type->service)]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
