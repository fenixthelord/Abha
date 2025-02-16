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

class TypeController extends Controller
{
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
                'name.en' => ['required', 'max:500'],
                'name.ar' => ['required', 'max:500'],
                'service_id' => ['required', 'exists:services,id,deleted_at,NULL'],
                'form_id' => ['required', 'exists:forms,id,deleted_at,NULL'],
            ]);
//dd($request->all());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $service = Service::where('id', $request->service_id)->value('id');
            $form = Form::where('id', $request->form_id)->value('id');

            $type = Type::create([
                'name' => $request->name,
                'service_id' => $service,
                'form_id' => $form,
            ]);

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
                'name' => ['nullable', 'max:500'],
                'name.en' => ['nullable', 'max:500',],
                'name.ar' => ['nullable', 'max:500',],
                'service_id' => ['nullable', 'exists:services,id,deleted_at,NULL'],
                'form_id' => ['nullable', 'exists:forms,id,deleted_at,NULL'],
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $type = Type::findOrFail($request->id);
            $type->update([
                'name' => $request->name ?? $type->name,
                'service_id' => $request->service_id ?? $type->service_id,
                'form_id' => $request->form_id ?? $type->form_id,
            ]);

            DB::commit();
            return $this->returnData(['type' => new TypeResource($type)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

// SoftDelete is Ready, just add Route to this Function When you Want.
    public function destroy(Request $request) {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:type,id',
            ], [
                'id.required' => __('validation.custom.type_controller.id_required'),
                'id.exists' => __('validation.custom.type_controller.id_exists'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            if ($type = Type::withTrashed()->where('id',$request->id)->first()) {
                if ($type->trashed()) {
                    return $this->badRequest(__('validation.custom.type_controller.already_delete'));
                }

                $name = $type->getTranslations("name");
                $type->name = [
                    'en' => $name['en'] . '-' . $type->id . '-deleted',
                    'ar' => $name['ar'] . '-' . $type->id . '-محذوف',
                ];
                $type->save();
                $type->delete();
                DB::commit();
                return $this->returnSuccessMessage(__('validation.custom.type_controller.deleted'));

            } else {
                return $this->badRequest(__('validation.custom.type_controller.not_found'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}

