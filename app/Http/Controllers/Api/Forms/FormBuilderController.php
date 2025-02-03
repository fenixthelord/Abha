<?php

namespace App\Http\Controllers\Api\Forms;

use App\Enums\FormFiledType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFormBuilderRequest;
use App\Http\Resources\Forms\FormResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Forms\Form;
use App\Models\Forms\FormField;
use App\Models\Forms\FormFieldOption;
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
                ->with(['fields.options']);
            $form = $forms->paginate($perPage, ['*'], 'page', $pageNumber);

            $data['forms'] =  FormResource::collection($form);
            return $this->PaginateData($data, $form);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($id)
    {
        try {
            $form = Form::with('fields.options')->findOrFail($id);
            $data['form'] =  FormResource::make($form);
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'category_id' => 'required|numeric',
                'name' => 'required|array',
                'name.en' => 'required|string|min:2|max:255',
                'name.ar' => 'required|string|min:2|max:255',
                'fields' => 'required|array',
                'fields.*.id' => 'nullable|string',
                'fields.*.label.en' => 'required|string',
                'fields.*.label.ar' => 'required|string',
                'fields.*.placeholder.en' => 'required|string',
                'fields.*.placeholder.ar' => 'required|string',
                'fields.*.type' => ['required', new Enum(FormFiledType::class)],
                'fields.*.required' => 'nullable|boolean',
                'fields.*.order' => 'nullable|numeric',
            ]);

            $form = Form::with('fields')->findOrFail($id);

            $form->name = $request->name ?? $form->name;
            $form->category_id = $request->category_id ?? $form->category_id;
            $form->save();
            $idsFromRequest = $request->input('fields.*.id');
            $missingIds = array_diff($form->fields()->pluck('id')->toArray(), $idsFromRequest);
            FormField::whereIn('id', $missingIds)->delete();

            foreach ($request->fields as $fieldData) {
                if (array_key_exists('id', $fieldData)) {
                    $child = $form->fields()->find($fieldData['id']);
                    if ($child)
                        $child->update($fieldData);
                } else {
                    $form_field = FormField::create([
                        'form_id' => $form->id,
                        'label' => $fieldData['label'],
                        'placeholder' => $fieldData['placeholder'],
                        'type' => $fieldData['type'],
                        'required' => $fieldData['required'] ?? false,
                        'order' => $fieldData['order'] ?? 0,
                    ]);
                }
            }
            $form = Form::with('fields')->findOrFail($id);
            $data['form'] =  FormResource::make($form);
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function store(CreateFormBuilderRequest $request)
    {
        // dd(config('app.supported_locales'));
        // $rules = [];

        // foreach (config('app.supported_locales') as $locale) {
        //     $rules["name.$locale"] = [
        //         'required|string|min:2|max:255',
        //         Rule::unique('forms', "name->$locale")
        //     ];
        // }

        // $request->validate($rules);
        try {
            // $request->validate([
            //     'category_id' => 'required|numeric',
            //     'name' => 'required|array',
            //     'name.en' => 'required|string|min:2|max:255',
            //     'name.ar' => 'required|string|min:2|max:255',
            //     'fields' => 'required|array',
            //     'fields.*.label.en' => 'required|string',
            //     'fields.*.label.ar' => 'required|string',
            //     'fields.*.placeholder.en' => 'required|string',
            //     'fields.*.placeholder.ar' => 'required|string',
            //     'fields.*.type' => ['required', new Enum(FormFiledType::class)],
            //     'fields.*.required' => 'nullable|boolean',
            //     'fields.*.order' => 'nullable|numeric',
            // ]);
            $request->validated();
            DB::beginTransaction();

            $form = Form::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
            ]);

            foreach ($request->fields as $field) {
                $formField = FormField::create([
                    'form_id' => $form->id,
                    'label' => $field['label'],
                    'placeholder' => $field['placeholder'],
                    'type' => $field['type'],
                    // 'options' => $field['options'] ?? null,
                    'required' => $field['required'] ?? false,
                    'order' => $field['order'] ?? 0,
                ]);
                foreach ($field['options'] as $option) {
                    FormFieldOption::create([
                        'form_field_id' => $formField->id,
                        'label' => $option['label'],
                        'selected' => $option['selected'] ?? false,
                        'order' => $option['order'] ?? 0,
                    ]);
                }
            }

            DB::commit();
            $form = Form::with('fields.options')->findOrFail($form->id);
            $data['form'] =  FormResource::make($form);
            return $this->returnData($data, "Form created successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $form = Form::onlyTrashed()->find($id);
            if ($form) {
                return $this->badRequest('Form already deleted.');
            } else {
                $form = Form::findOrFail($id);
                $form->fields()->delete();
                $form->submissions()->delete();
                $form->name = $form->name . '-' . $form->id . '-deleted';
                $form->save();
                $form->delete();
                DB::commit();
                return $this->returnSuccessMessage('Form deleted successfully');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
