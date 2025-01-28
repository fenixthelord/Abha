<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteCategoryRequest;
use App\Http\Requests\DeleteCatigoryRequest;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\IndexCategoryRequest;
use App\Http\Requests\SaveCategoriesRequest;
use App\Http\Requests\ShowCategoriesRequest;
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
                return $this->badRequest("Wrong page . total pages is " . $categories->lastPage() . " you sent " . $pageNumber);
            }
            $data = CategoryResource::collection($categories);

            return $this->PaginateData(
                data: $data,
                object: $categories,
                key: "categories"
            );
        } catch (\Throwable $e) {
            return $this->badRequest($e->getMessage());
        }
    }

    public function filter(FilterRequest $request)
    {
        try {
            $query = Department::query()
                ->when($request->has("department_uuid"), function ($q) use ($request) {
                    $q->where("uuid", $request->department_uuid);
                });

            $departments = $query->get();

            if ($departments->isEmpty()) {
                return $this->notFound('No departments found.');
            }

            $data['department'] = DepartmentResource::collection($departments);

            if ($request->has('department_uuid')) {
                $categories = Category::where('department_id', $departments->pluck("id")->first())->get();
                $data['categories'] = CategoryResource::collection($categories);
            }

            return $this->returnData('data', $data);
        } catch (\Throwable $e) {
            return $this->badRequest($e->getMessage());
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
            return $this->badRequest($e->getMessage());
        }
    }


    public function show(ShowCategoriesRequest $request, $department_uuid)
    {
        try {

            $department = Department::where('uuid', $department_uuid)->firstOrFail();
            $data["department"] = DepartmentResource::make($department->load("allChildren"));

            return $this->returnData("data", $data);
        } catch (\Exception $e) {
            return $this->badRequest($e->getMessage());
        }
    }

    public function add(SaveCategoriesRequest $request)
    {
        try {
            DB::beginTransaction();
            $department = Department::where('uuid', $request->department_uuid)->firstOrFail();
            $this->addCategories(
                department: $department,
                categories: $request->validated('chields'),
                parentId: null
            );
            DB::commit();
            return $this->returnSuccessMessage("Categories updated successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->badRequest($e->getMessage());
        }
    }




    public function save(SaveCategoriesRequest $request)
    {
        try {

            DB::beginTransaction();

            $department = Department::where('uuid', $request->department_uuid)->firstOrFail();

            $this->addCategories(
                department: $department,
                categories: $request->validated('chields'),
                parentId: null
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->badRequest($e->getMessage());
        }
    }

    private function addCategories($department, $categories, $parentId = null)
    {
        foreach ($categories as $categoryData) {
            $category = Category::firstOrCreate(
                [
                    'department_id' => $parentId ?  null : $department->id,
                    'parent_id' => $parentId,
                    'name' => $categoryData['name']
                ],
                ['name' => $categoryData['name']]
            );

            // Recursively handle children
            if (!empty($categoryData['chields'])) {
                $this->addCategories(
                    department: $department,
                    categories: $categoryData['chields'],
                    parentId: $category->id
                );
            }
        }
    }

    private function pruneDeletedCategories(
        Department $department,
        array $currentCategories,
        ?int $parentId = null
    ) {
        $currentNames = collect($currentCategories)->pluck('name')->toArray();

        $obsoleteCategories = Category::where('department_id', $department->id)
            ->where('parent_id', $parentId)
            ->whereNotIn('name', $currentNames)
            ->get();

        foreach ($obsoleteCategories as $category) {
            $category->deleteWithChildren();
        }
    }
}
