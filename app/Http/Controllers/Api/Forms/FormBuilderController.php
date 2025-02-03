<?php

namespace App\Http\Controllers\Api\Forms;

use App\Enums\FormFiledType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFormBuilderRequest;
use App\Http\Requests\Forms\UpdateFormBuilderRequest;
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

    public function store(CreateFormBuilderRequest $request)
    {
        try {
            DB::beginTransaction();
            $form = Form::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
            ]);

            foreach ($request['fields'] as $field_data) {
                $form_field = $form->fields()->create($field_data);
                foreach ($field_data['options'] as $option_data) {
                    $form_field->options()->create($option_data);
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

    public function update(UpdateFormBuilderRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $form = Form::with('fields.options')->findOrFail($id);

            $form->update(['category_id' => $request->category_id, 'name' => $request->name]);

            $field_ids = $request->input('fields.*.id');
            $missing_field_ids = array_diff($form->fields()->pluck('id')->toArray(), $field_ids);
            FormField::whereIn('id', $missing_field_ids)->delete();

            $option_ids = $request->input('fields.*.options.*.id');
            $missing_option_ids = array_diff($form->fields()->pluck('id')->toArray(), $option_ids);
            FormFieldOption::whereIn('id', $missing_option_ids)->delete();

            foreach ($request->fields as $field_data) {
                if (array_key_exists('id', $field_data)) {
                    $existed_field = $form->fields()->find($field_data['id']);
                    if ($existed_field)
                        $existed_field->update($field_data);
                } else {
                    $form_field = FormField::create([
                        'form_id' => $form->id,
                        'label' => $field_data['label'],
                        'placeholder' => $field_data['placeholder'],
                        'type' => $field_data['type'],
                        'required' => $field_data['required'] ?? false,
                        'order' => $field_data['order'] ?? 0,
                    ]);
                    foreach ($field_data['options'] as $option) {
                        FormFieldOption::create([
                            'form_field_id' => $form_field->id,
                            'label' => $option['label'],
                            'selected' => $option['selected'] ?? false,
                            'order' => $option['order'] ?? 0,
                        ]);
                    }
                }
            }
            $form = Form::with('fields.options')->findOrFail($id);
            $data['form'] =  FormResource::make($form);
            DB::commit();
            return $this->returnData($data);
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
                $form->name = $form->name . '-' . $form->id . '-deleted';
                $form->save();
                $form->forceDelete();
                DB::commit();
                return $this->returnSuccessMessage('Form deleted successfully');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
