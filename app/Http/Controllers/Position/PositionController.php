<?php

namespace App\Http\Controllers\Position;

use App\Http\Controllers\Controller;
use App\Http\Requests\Position\CreatePositionRequest;
use App\Http\Requests\Position\DeletePositionRequest;
use App\Http\Requests\Position\ListOfPositionsRequest;
use App\Http\Requests\Position\UpdatePositionRequest;
use App\Http\Resources\Position\PositionChieldResource;
use App\Http\Resources\Position\PositionChildResource;
use App\Http\Resources\Position\PositionResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    use ResponseTrait;
    /**
     * Display a listing of the resource.
     *
     * @param ListOfPositionsRequest $request
     * @return ResponseTrait
     */
    public function index(ListOfPositionsRequest $request)
    {
        try {
            $perPage = $request->input('per_page', $this->per_page);
            $pageNumber = $request->input('page', $this->pageNumber);

            $query = Position::query();

            if ($request->has("search")) {
                $query->where("name", "LIKE", "%" . $request->search . "%");
            }

            // Do not return his children , if request has id .
            if ($request->has('id')) {
                $query->whereNotIn("id", Position::getChildrenIds($request->id));
            }

            $category = $query->paginate($perPage, ['*'], 'page', $pageNumber);

            $data["positions"] = PositionResource::collection($category);

            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Display a chart of positions.
     *
     * @param Request $request
     * @return ResponseTrait
     */
    public function chart()
    {
        try {
            $headPositionPosition = Position::with("children")->whereNull("parent_id")->firstOrFail();
            $data["positions"] = PositionResource::make($headPositionPosition);
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * creating a new position.
     *
     * @param CreatePositionRequest $request
     * @return ResponseTrait
     */
    public function create(CreatePositionRequest $request)
    {
        try {
            DB::beginTransaction();
            $potions = Position::create($request->validated());
            $data["positions"] = PositionResource::make($potions);
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdatePositionRequest $request
     * @return ResponseTrait
     */
    public function update(UpdatePositionRequest $request)
    {
        try {
            DB::beginTransaction();
            $potions = Position::findOrFail($request->id);
            $potions->update($request->validated());
            $data["positions"] = PositionResource::make($potions);
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    /**
     * Delete the specified resource in storage.
     *
     * @param  DeletePositionRequest $request
     * @return ResponseTrait
     */
    public function delete(DeletePositionRequest $request)
    {
        try {
            DB::beginTransaction();
            $potions = Position::findOrFail($request->id);
            if ($potions->users()->count() > 0 || $potions->children()->count() > 0) {
                $data["chields"] = PositionChildResource::make($potions);
                return $this->returnData($data, "you can not delete this position because it has associated users or sub positions.");
            }
            $potions->delete();
            DB::commit();
            return $this->returnSuccessMessage(__("Position deleted successfully"));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
