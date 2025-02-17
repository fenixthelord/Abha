<?php

namespace App\Http\Controllers\Api\Type;

use App\Http\Controllers\Controller;
use App\Http\Resources\Type\TypeResource;
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
            ], __('validation.custom.type_controller'));

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $search = $request->input('search', null);
            $query = Type::query();

            if ($search) {
                $query->whereAny(['name->en', 'name->ar'], $search);
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
            ], __('validation.custom.type_controller'));

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
                'name' => ['required', 'array'],
                'name.en' => ['required', 'max:500', Rule::unique('types', 'name->en')],
                'name.ar' => ['required', 'max:500', Rule::unique('types', 'name->ar')],
                'service_id' => ['required', 'exists:services,id,deleted_at,NULL'],
                'form_id' => ['required', 'exists:forms,id,deleted_at,NULL'],
            ], __('validation.custom.type_controller'));

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $type = Type::create($request->all());
            DB::commit();
            return $this->returnSuccessMessage(['type' => new TypeResource($type)], __('validation.custom.type_controller.created'));
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
                'name' => ['nullable', 'array'],
                'name.en' => ['nullable', 'max:500', Rule::unique('types', 'name->en')],
                'name.ar' => ['nullable', 'max:500', Rule::unique('types', 'name->ar')],
                'service_id' => ['nullable', 'exists:services,id,deleted_at,NULL'],
                'form_id' => ['nullable', 'exists:forms,id,deleted_at,NULL'],
            ], __('validation.custom.type_controller'));

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $type = Type::findOrFail($request->id);
            $type->update($request->all());
            DB::commit();
            return $this->returnData(['type' => new TypeResource($type)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
