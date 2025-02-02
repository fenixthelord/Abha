<?php

namespace App\Http\Controllers\Api\Forms;

use App\Enums\FormFiledType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Forms\FormResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class FormBuilderController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)
    {
        try {
            $pageNumber = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);
            $forms = Form::orderByAll($request->sortBy, $request->sortType)
                ->filter($request->only('search'))
                ->with(['fields']);
            $form = $forms->paginate($perPage, ['*'], 'page', $pageNumber);

            $data['forms'] =  FormResource::collection($form);
            return $this->PaginateData($data, $form);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show() {}

    public function update() {}

    public function destroy() {}

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|array',
                'name.en' => 'required|string|min:2|max:255',
                'name.ar' => 'required|string|min:2|max:255',
                'fields' => 'required|array',
                'fields.*.label.en' => 'required|string',
                'fields.*.label.ar' => 'required|string',
                'fields.*.placeholder.en' => 'required|string',
                'fields.*.placeholder.ar' => 'required|string',
                'fields.*.type' => ['required', new Enum(FormFiledType::class)],
            ]);

            DB::beginTransaction();

            $form = Form::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
            ]);

            foreach ($request->fields as $field) {
                FormField::create([
                    'form_id' => $form->id,
                    'label' => $field['label'],
                    'placeholder' => $field['placeholder'],
                    'type' => $field['type'],
                    'options' => $field['options'] ?? null,
                    'required' => $field['required'] ?? false,
                    'order' => $field['order'] ?? 0,
                ]);
            }

            DB::commit();
            return $this->returnSuccessMessage("Form created successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
