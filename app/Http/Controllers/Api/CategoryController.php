<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\ListOfCategoriesRequest;
use App\Http\Requests\CreateCategoriesRequest;
use App\Http\Requests\DeleteCategoryRequest;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\UpdateCategoriesRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    use ResponseTrait;


    /**
     * List of categories , All parent categories That the parentID is null
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(ListOfCategoriesRequest $request)
    {
        try {

            $perPage = $request->input('per_page', $this->per_page);
            $pageNumber = $request->input('page', $this->pageNumber);

            $query = Category::query()
                ->where("parent_id", null)
                
                ->when($request->has("search"),  function ($q) use ($request) {
                    $q->where("name", "like", "%" . $request->search . "%");
                });

            $category = $query->paginate($perPage, ['*'], 'page', $pageNumber);
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
            return $this->returnSuccessMessage("Category and all related sup-categories deleted successfully");
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
            return $this->returnSuccessMessage("Categories updated successfully");
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
        foreach ($categories as $categoryData) {

            $category = Category::where("uuid", $categoryData["category_uuid"])->firstOrFail();

            $category->update([
                'name' => $categoryData['name'],
                "parent_id" => $parentId,
                "department_id" => $department->id,
            ]);

            if (!empty($categoryData['chields'])) {
                $this->updateCategories(
                    department: $department,
                    categories: $categoryData['chields'],
                    parentId: $category->id
                );
            }
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
            return $this->returnSuccessMessage("Categories created successfully");
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
    private function createCategories($departmentId, $categories, $parentId = null)
    {
        try {
            foreach ($categories as $categoryData) {

                $category = Category::Create([
                    'department_id' => $departmentId,
                    'parent_id' => $parentId,
                    'name' => $categoryData['name']
                ]);

                if (!empty($categoryData['chields'])) {
                    $this->createCategories(
                        departmentId: $departmentId,
                        categories: $categoryData['chields'],
                        parentId: $category->id
                    );
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
