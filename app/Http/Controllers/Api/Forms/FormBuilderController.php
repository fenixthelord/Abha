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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                ->with(['formable', 'fields.options', 'fields.sources']);
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
            $form = Form::with(['formable', 'fields.options', 'fields.sources'])->findOrFail($id);
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
                'name' => $request->name,
                'formable_type' => $request->formable_type,
                'formable_id' => $request->formable_id
            ]);

            // Get models dynamically
            $models = [
                'category' => Category::class,
                'employee' => User::class,
            ];


            $formableModel = app($models[$request->formable_type])::
                // Insure that the id belongs to an EMPLOYEE
            when($request->formable_type == 'employee', function ($query){
                return $query->where('role', 'employee');
            })
            ->findOrFail($request->formable_id);

            $formableModel->forms()->save($form);

            //Review before deletion
//            if ($request->formable_type === 'category') {
//                $category = Category::find($request->formable_id);
//                $category->forms()->save($form);
//            }elseif ($request->formable_type === 'employee'){
//                $employees = User::find($request->formable_id);
//                $employees->forms()->save($form);
//            } else {
//                $event = Event::findOrfail($request->formable_id);
//                $event->forms()->save($form);
//            }

            foreach ($request['fields'] as $field_data) {
                $form_field = $form->fields()->create($field_data);
                if (isset($field_data['options']) && in_array($field_data['type'], ['date', 'dropdown', 'radio', 'checkbox']))
                    foreach ($field_data['options'] as $option_data) {
                        $form_field->options()->create($option_data);
                    }
                if (isset($field_data['sources']) && $field_data['type'] == 'dropdown')
                    foreach ($field_data['sources'] as $data_source) {
                        $form_field->sources()->create($data_source);
                    }
            }
            $form = Form::with(['formable', 'fields.options', 'fields.sources'])->findOrFail($form->id);
            $data['form'] =  FormResource::make($form);
            DB::commit();
            return $this->returnData($data, "Form created successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function update(UpdateFormBuilderRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $form = Form::findOrFail($id);
            $form->update([
                'name' => $request->name,
                'formable_type' => $request->formable_type,
                'formable_id' => $request->formable_id
            ]);
            if ($request->formable_type === 'category') {
                $category = Category::findOrfail($request->formable_id);
                $category->forms()->save($form);
            } else {
                $event = Event::findOrfail($request->formable_id);
                $event->forms()->save($form);
            }

            $field_ids = $request->input('fields.*.id');
            $missing_field_ids = array_diff($form->fields()->pluck('id')->toArray(), $field_ids);
            FormField::whereIn('id', $missing_field_ids)->forceDelete();

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
            $form = Form::with(['formable', 'fields.options', 'fields.sources'])->findOrFail($id);
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
