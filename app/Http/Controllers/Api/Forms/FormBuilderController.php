<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\CreateFormBuilderRequest;
use App\Http\Requests\Forms\UpdateFormBuilderRequest;
use App\Http\Resources\Forms\FormResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\Event;
use App\Models\Forms\Form;
use App\Models\Forms\FormField;
use App\Models\Forms\FormFieldDataSource;
use App\Models\Forms\FormFieldOption;
use App\Models\Forms\FormType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class FormBuilderController extends Controller
{
    use ResponseTrait;

    public function list(Request $request)
    {
        try {
            $pageNumber = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);
            $forms = Form::with(['type', 'fields.options', 'fields.sources'])
                ->filter($request->only('search', 'event_id', 'category_id'))
                ->orderByAll($request->sortBy, $request->sortType);


            $form = $forms->paginate($perPage, ['*'], 'page', $pageNumber);
            $data['forms'] =  FormResource::collection($form);
            return $this->PaginateData($data, $form);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function show()
    {
        try {
            $validate = Validator::make(request()->all(), [
                'id' => 'required|string|exists:forms,id',
            ]);
            if ($validate->fails()) {
                return $this->returnValidationError($validate);
            }
            $id = request()->input('id');
            $form = Form::with(['type', 'fields.options', 'fields.sources'])->findOrFail($id);
            $data['form'] =  FormResource::make($form);
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function store(CreateFormBuilderRequest $request)
    {
        try {


            DB::beginTransaction();


               $model="App\\Models\\" . $request->form_type;
                if (!class_exists($model)) {
                    return $this->returnError("Invalid form_type: Class '$model' not found.");
                }









                 $form_type=FormType::firstOrCreate([
                     'name'=>$request->form_type,
                     'form_index'=>$request->form_index??null
                 ]);
               $form_id=$form_type->id;




            $form = Form::updateOrCreate(
               [ 'form_type_id' => $form_id],
               [ 'name' => $request->name]

            );

            $field_ids = $request->input('fields.*.id');

            $missing_field_ids = array_diff($form->fields()->pluck('id')->toArray(), $field_ids);

            $res = FormField::whereIn('id', $missing_field_ids)->forceDelete();


            foreach ($request['fields'] as $field_data) {

                $form_field = $form->fields()->updateOrCreate(
                    ['id' => $field_data['id'] ?? null],
                    $field_data
                );


                if (isset($field_data['options']) && in_array($field_data['type'], ['date', 'dropdown', 'radio', 'checkbox'])) {
                    foreach ($field_data['options'] as $option_data) {
                        $form_field->options()->updateOrCreate(
                            ['id' => $option_data['id'] ?? null],
                            $option_data
                        );
                    }
                }


                if (isset($field_data['sources']) && $field_data['type'] == 'dropdown') {
                    foreach ($field_data['sources'] as $data_source) {
                        $form_field->sources()->updateOrCreate(
                            ['id' => $data_source['id'] ?? null],
                            $data_source
                        );
                    }
                }
            }


            $form = Form::with(['type', 'fields.options', 'fields.sources'])->findOrFail($form->id);
            $data['form'] =  FormResource::make($form);
            DB::commit();
            return $this->returnData($data, "Form created successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
        }
    }

    public function update(UpdateFormBuilderRequest $request)
    {
        try {
            $id = request()->input('id');
            DB::beginTransaction();
            $form = Form::findOrFail($id);
            $form->update([
                'name' => $request->name,
            ]);

            $field_ids = $request->input('fields.*.id');

            $missing_field_ids = array_diff($form->fields()->pluck('id')->toArray(), $field_ids);

            $res = FormField::whereIn('id', $missing_field_ids)->forceDelete();
            $option_ids = $request->input('fields.*.options.*.id');
            $source_ids = $request->input('fields.*.sources.*.id');

            foreach ($request->fields as $field_data) {
                if (array_key_exists('id', $field_data)) {
                    $form_field = $form->fields()->find($field_data['id']);
                    if ($form_field) {
                        $form_field->update($field_data);

                        $missing_option_ids = array_diff($form_field->options()->pluck('id')->toArray(), $option_ids);
                        FormFieldOption::whereIn('id', $missing_option_ids)->forceDelete();

                        $missing_source_ids = array_diff($form_field->sources()->pluck('id')->toArray(), $source_ids);
                        FormFieldDataSource::whereIn('id', $missing_source_ids)->forceDelete();

                        if (isset($field_data['options']))
                            foreach ($field_data['options'] as $option_data) {
                                if (array_key_exists('id', $option_data)) {
                                    $existed_option = $form_field->options()->find($option_data['id']);
                                    if ($existed_option)
                                        $existed_option->update($option_data);
                                } else $form_field->options()->create($option_data);
                            }

                        if (isset($field_data['sources']))
                            foreach ($field_data['sources'] as $source_data) {
                                if (array_key_exists('id', $source_data)) {
                                    $existed_source = $form_field->sources()->find($source_data['id']);
                                    if ($existed_source)
                                        $existed_source->update($source_data);
                                } else $form_field->sources()->create($source_data);
                            }
                    }
                } else {
                    $form_field = $form->fields()->create($field_data);
                    if (isset($field_data['options']))
                        foreach ($field_data['options'] as $option_data) {
                            $form_field->options()->create($option_data);
                        }
                    if (isset($field_data['sources']))
                        foreach ($field_data['sources'] as $source_data) {
                            $form_field->sources()->create($source_data);
                        }
                }
            }
            $form = Form::with([ 'fields.options', 'fields.sources'])->findOrFail($id);
            $data['form'] =  FormResource::make($form);
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
        }
    }
    public function ShowByType()
    {

            try {

                $validate = Validator::make(request()->all(), [
                    'type' => 'required|string|exists:form_types,name',
                    'form_index' => [
                        'nullable',
                        'string',
                        'exists:form_types,form_index',
                        request()->input('type') !== 'User' ? 'required' : 'nullable',
                    ],
                ]);


                if ($validate->fails()) {
                    return $this->returnValidationError($validate);
                }


                $form = FormType::where('name', request()->input('type'))
                    ->when(request()->filled('form_index'), function ($query) {
                        $query->where('form_index', request()->input('form_index'));
                    })->first();

                if (!$form) {
                    return $this->badRequest('Form not found');
                }


                $formWithRelations = Form::with(['type', 'fields.options', 'fields.sources'])
                    ->where('form_type_id', $form->id)
                    ->firstOrFail();
                if(!$formWithRelations){
                    return $this->badRequest('Form not found');
                }


                    $data['form']= FormResource::make($formWithRelations);
                return $this->returnData($data);



        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function destroy()
    {
        DB::beginTransaction();
        try {
            $validate = Validator::make(request()->all(), [
                'id' => 'required|string|exists:forms,id',
            ]);
            if ($validate->fails()) {
                return $this->returnValidationError($validate);
            }
            $id = request()->input('id');
            $form = Form::onlyTrashed()->find($id);
            if ($form) {
                return $this->badRequest('Form already deleted.');
            } else {
                $form = Form::findOrFail($id);
                $form->name = $form->name . '-' . $form->id . '-deleted';
                $form->save();
                $form->delete();
                DB::commit();
                return $this->returnSuccessMessage('Form deleted successfully');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
        }
    }
}
