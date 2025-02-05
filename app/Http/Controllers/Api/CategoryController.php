<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\ListOfCategoriesRequest;
use App\Http\Requests\Categories\CreateCategoriesRequest;
use App\Http\Requests\Categories\DeleteCategoryRequest;
use App\Http\Requests\Categories\FilterRequest;
use App\Http\Requests\Categories\UpdateCategoriesRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\Department;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\Constraints\CountInDatabase;

class CategoryController extends Controller
{
    use ResponseTrait;


    /**
     * List of categories , All parent categories That the parentID is null
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(ListOfCategoriesRequest $request) {
        try {

            $perPage = $request->input('per_page', $this->per_page);
            $pageNumber = $request->input('page', $this->pageNumber);

            $query = Category::query()
                ->when($request->has("search"),  function ($q) use ($request) {
                    $q->WithSearch($request->search);
                })
                ->where("parent_id", null)
                ->when(
                    $request->has("department_uuid"),
                    function ($q) use ($request) {
                        $department = Department::where("uuid", $request->department_uuid)->firstOrFail();
                        $q->where("department_id", $department->id);
                    }
                )
                ->when(
                    $request->has("categories_uuid"),
                    function ($q) use ($request) {
                        $q->Where("uuid", $request->categories_uuid);
                    }
                );

            $category = $query->paginate($perPage, ['*'], 'page', $pageNumber);
            if ($category->lastPage() < $pageNumber) {
                $category = $query->paginate($perPage, ['*'], 'page', 1);
            }
            $data["categories"] = CategoryResource::collection(
                $category->load("children")
            )->each->withDeparted();

            return $this->PaginateData($data, $category);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Filters for search to category
     *
     * @param  FilterRequest $request for validation
     * @return \Illuminate\Http\Response
     */
    public function filter(FilterRequest $request)
    {
        try {
            $departmentQuery = Department::where("uuid", $request->department_uuid)->firstOrFail();

            $categories = Category::query()
                ->where("department_id", $departmentQuery->id)
                ->whereNull("parent_id")
                ->get();

            $data["categories"] = CategoryResource::collection($categories);
            return $this->returnData($data);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }


    /**
     * Delete category with all children
     *
     * @param  DeleteCategoryRequest  $request for validation and control Roles
     * @return \Illuminate\Http\Response
     */
    public function delete(DeleteCategoryRequest $request)
    {
        try {
            DB::beginTransaction();

            $category = Category::where('uuid', $request->uuid)->firstOrFail();
            $category->deleteWithChildren();

            DB::commit();
            return $this->returnSuccessMessage(__('validation.custom.category.category_deleted'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }


    /**
     * This function is used to update categories and sub-categories
     * @param UpdateCategoriesRequest $request for validation and control Roles
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCategoriesRequest $request)
    {
        try {
            DB::beginTransaction();

            $department = Department::where('uuid', $request->department_uuid)->first();
            $category = Category::where("uuid", $request->category_uuid)->firstOrFail();

            $category->update([
                'name' => $request->name,
            ]);

            $this->updateCategories(
                department: $department,
                categories: $request->chields,
                parentId: $category->id
            );

            DB::commit();
            return $this->returnSuccessMessage(__('validation.custom.category.category_updated'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    /**
     * This function is used to update categories and sub-categories
     * @param $departmentId
     * @param $categories
     * @param $parentId
     * @return void
     */
    private function updateCategories($department, $categories, $parentId = null)
    {

        $currentUuids = collect($categories)->pluck('category_uuid')->filter()->toArray();

        // Delete existing children not present in the current request
        Category::where('parent_id', $parentId)
            ->where('department_id', $department->id)
            ->whereNotIn('uuid', $currentUuids)
            ->each(function ($category) {
                $category->deleteWithChildren();
            });

        foreach ($categories as $categoryData) {

            // Create the new categories
            if (!isset($categoryData["category_uuid"])) {
                $this->createCategories(
                    departmentId: $department->id,
                    categories: [$categoryData],
                    parentId: $parentId,
                    status: 'update'
                );
                continue;
            }

            $category = Category::where("uuid", $categoryData["category_uuid"])->firstOrFail();

            // update the parent category
            $category->update([
                'name' => $categoryData['name'],
                "parent_id" => $parentId,
                "department_id" => $department->id,
            ]);

            // update the chields all categories
            $this->updateCategories(
                department: $department,
                categories: $categoryData['chields'],
                parentId: $category->id
            );
        }
    }

    /**
     * This function is used to create categories and sub-categories
     * @param CreateCategoriesRequest $request for validation and control Roles
     * @return \Illuminate\Http\JsonResponse
     */

    public function create(CreateCategoriesRequest $request)
    {
        try {
            DB::beginTransaction();
            $department = Department::where('uuid', $request->department_uuid)->first();


            $category = Category::create([
                'name' => $request->name,
                "parent_id" => null,
                "department_id" => $department->id,
            ]);

            $this->createCategories(
                departmentId: $department->id,
                categories: $request->chields,
                parentId: $category->id
            );

            DB::commit();
            return $this->returnSuccessMessage(__('validation.custom.category.category_created'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    /**
     * @param $departmentId
     * @param $categories
     * @param $parentId
     * @return void
     */
    private function createCategories($departmentId, $categories, $parentId = null, $status = 'create')
    {
        try {
            foreach ($categories as $categoryData) {
                // dd($categories);
                // log::info($categoryData['category_uuid']);
                // continue;
                if ($status == "update") {
                    if (isset($categoryData["category_uuid"])) {
                        $isCategoryExists = Category::where("uuid", $categoryData["category_uuid"])->first();
                        if ($isCategoryExists->exists()) {
                            continue;
                        }
                    }
                }

                $category = Category::Create([
                    'department_id' => $departmentId,
                    'parent_id' => $parentId,
                    'name' => $categoryData['name']
                ]);

                if (!empty($categoryData['chields'])) {
                    $this->createCategories(
                        departmentId: $departmentId,
                        categories: $categoryData['chields'],
                        parentId: $category->id,
                        status: $status
                    );
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
