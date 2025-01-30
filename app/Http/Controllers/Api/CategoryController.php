<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoriesRequest;
use App\Http\Requests\DeleteCategoryRequest;
use App\Http\Requests\DeleteCatigoryRequest;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\IndexCategoryRequest;
use App\Http\Requests\SaveCategoriesRequest;
use App\Http\Requests\ShowCategoriesRequest;
use App\Http\Requests\ShowCategoryRequest;
use App\Http\Requests\UpdateCategoriesRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DepartmentResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use ResponseTrait;

    public function index(IndexCategoryRequest $request)
    {
        try {
            $perPage = $request->input('per_page', $this->per_page);
            $pageNumber = $request->input('page', $this->pageNumber);

            $categoriesQuery = Category::query()
                ->when($request->has('department_uuid') &&  !$request->has('parent_category_uuid'), function ($q) use ($request) {
                    $q->where(
                        "department_id",
                        Department::where('uuid', $request->department_uuid)
                            ->pluck('id')->firstOrFail()
                    );
                })
                ->when($request->has('parent_category_uuid'), function ($q) use ($request) {
                    $q->where(
                        "parent_id",
                        Category::where('uuid', $request->parent_category_uuid)
                            ->pluck('id')->firstOrFail()
                    );
                });

            $categories = $categoriesQuery->paginate($perPage, ['*'], 'page', $pageNumber);

            if ($request->page > $categories->lastPage()) {
                if ($request->has("department_uuid") || $request->has("parent_category_uuid")) {
                    $pageNumber =  1;
                    $categories = $categoriesQuery->paginate($perPage, ['*'], 'page', $pageNumber);
                } else {
                    return $this->badRequest("Wrong page . total pages is " . $categories->lastPage() . " you sent " . $pageNumber);
                }
            }

            $data["categories"] = CategoryResource::collection($categories);

            return $this->PaginateData(
                data: $data,
                object: $categories,
            );
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function showCategory(ShowCategoryRequest $request)
    {
        try {
            $category = Category::where('uuid', $request->category_uuid)->firstOrFail();
            $categoryInfo = $category->department()->first();
            // dd($categoryInfo->name);
            $data["test"] = DepartmentResource::make($categoryInfo->load("categories"));
            // dd($data);
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function filter(FilterRequest $request)
    {
        try {
            $departmentQuery = Department::query()
                ->when($request->has("department_uuid"), function ($q) use ($request) {
                    $q->where("uuid", $request->department_uuid);
                });

            $departments = $departmentQuery->get();

            $categoryQuery = Category::query()
                ->when($request->has("department_uuid") && !$request->has("category_uuid"),  function ($q) use ($departments) {
                    $q->where("parent_id", null)->where("department_id", $departments->first()->id);
                })
                ->when($request->has("category_uuid"), function ($q) use ($request) {
                    $q->where("uuid", $request->category_uuid);
                });

            $data['department'] = DepartmentResource::collection($departments);
            $data['categories'] = null;
            if ($request->has("department_uuid")) {
                $categories = $request->has("category_uuid") ?  $categoryQuery->firstOrFail()->children : $categoryQuery->get();
                $data['categories'] =  CategoryResource::collection($categories);
            }

            return $this->returnData($data);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function delete(DeleteCategoryRequest $request)
    {
        try {
            DB::beginTransaction();

            $category = Category::where('uuid', $request->uuid)->firstOrFail();
            $category->deleteWithChildren();

            DB::commit();
            return $this->returnSuccessMessage("Category and all related sup-categories deleted successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }


    public function show(ShowCategoriesRequest $request, $department_uuid)
    {
        try {
            $department = Department::where('uuid', $department_uuid)->firstOrFail();
            $data["department"] = DepartmentResource::make($department->load("categories"));

            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(UpdateCategoriesRequest $request)
    {
        try {
            DB::beginTransaction();

            $department = Department::where('uuid', $request->department_uuid)->first();

            $this->updateCategories(
                department: $department,
                categories: $request->chields,
                parentId: null
            );

            DB::commit();
            return $this->returnSuccessMessage("Categories updated successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
    public function create(CreateCategoriesRequest $request)
    {
        try {
            DB::beginTransaction();
            $department = Department::where('uuid', $request->department_uuid)->first();

            $this->createCategories(
                department: $department,
                categories: $request->chields,
                parentId: null
            );

            DB::commit();
            return $this->returnSuccessMessage("Categories created successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    private function updateCategories($department, $categories, $parentId = null)
    {
        foreach ($categories as $categoryData) {

            $category = Category::where("uuid", $categoryData["uuid"])->firstOrFail();
            $category->update([
                'name' => $categoryData['name'],
                "parent_id" => $parentId,
                "department_id" => $department->id,
            ]);
            // if ($categoryData['name'] != "hi") {
            //     dd([
            //         $parentId,
            //         $categoryData['name']
            //     ]);
            // }

            if (!empty($categoryData['chields'])) {
                $this->updateCategories(
                    department: $department,
                    categories: $categoryData['chields'],
                    parentId: $category->id
                );
            }
        }
    }
    private function createCategories($department, $categories, $parentId = null)
    {
        foreach ($categories as $categoryData) {
            if (!isset($categoryData['uuid'])) {
                $category = Category::Create([
                    'department_id' => $department->id,
                    'parent_id' => $parentId,
                    'name' => $categoryData['name']
                ]);
            } else {
                $category = Category::where("uuid", $categoryData)->firstOrFail();
            }
            if (!empty($categoryData['chields'])) {
                $this->createCategories(
                    department: $department,
                    categories: $categoryData['chields'],
                    parentId: $category->id
                );
            }
        }
    }
}
