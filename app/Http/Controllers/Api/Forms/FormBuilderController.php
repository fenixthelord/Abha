<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormBuilderController extends Controller
{
    use ResponseTrait;

    public function index() {}

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
                'fields.*.type' => 'required|string|in:',
            ]);

            DB::beginTransaction();
            // $category = Category::where('id', $request->category_id)->first();

            $form = Form::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
            ]);

            foreach ($request->fields as $field) {
                FormField::create([
                    'form_id' => $form->id,
                    'label' => $field['label'],
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
