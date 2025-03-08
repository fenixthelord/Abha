<?php

namespace App\Http\Controllers\Api\Position;

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Http\Requests\Position\ChartPositionsRequest;
use App\Http\Requests\Position\CreatePositionRequest;
use App\Http\Requests\Position\DeletePositionRequest;
use App\Http\Requests\Position\ListOfPositionsRequest;
use App\Http\Requests\Position\UpdatePositionRequest;
use App\Http\Requests\Position\UpdateUserPositionRequest;
use App\Http\Resources\Position\PositionChildResource;
use App\Http\Resources\Position\PositionResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

            $positions = $query->paginate($perPage, ['*'], 'page', $pageNumber);
            // $positions = $query->get();

            $data["positions"] = PositionResource::collection($positions);

            return $this->PaginateData($data, $positions);
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
    public function chart(/*ChartPositionsRequest $request*/)
    {
        try {
            $relations = ["children"];
            if (request()->has("with_employees") && request()->with_employees) {
                $relations[] = "users";
            }
            $MasterPositionPosition = Position::with($relations)->whereNull("parent_id")->firstOrFail();
            $data["positions"] = PositionResource::make($MasterPositionPosition);
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
            if (is_null($potions->parent_id) && $request->has('parent_id')) {
                return $this->badRequest('Cannot modify parent_id because it is currently null.');
            }
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
            $perPage = $request->input('per_page', $this->per_page);
            $pageNumber = $request->input('page', $this->pageNumber);

            $position = Position::findOrFail($request->id);
            if ($position->users()->count() > 0 || $position->children()->count() > 0) {

                $users = $position->users()->paginate($perPage, ['*'], 'page', $pageNumber);
                $childPositions = $position->children;

                $data = [
                    'position_id' => $position->id,
                    'chields' => [
                        'employees' => [
                            "employees_data" => UserResource::collection($users->items())->each->onlyName(),
                            "meta" => [
                                'next_page' => $users->nextPageUrl(),
                                'current_page' => $users->currentPage(),
                                'previous_page' => $users->previousPageUrl(),
                                'total_pages' => $users->lastPage(),
                            ],
                        ],
                        'child_positions' => PositionResource::collection($childPositions),
                    ],
                ];
                return $this->apiResponse(
                    data: $data,
                    status: true,
                    message: "you can not delete this position because it has associated users or sub positions.",
                    statusCode: 400
                );
            }
            $name = $position->getTranslations("name");
            $position->name = [
                'en' => $name['en'] . '-' . $position->id . '-deleted',
                'ar' => $name['ar'] . '-' . $position->id . '-محذوف'
            ];
            $position->save();
            $position->delete();

            DB::commit();
            return $this->returnSuccessMessage(__("Position deleted successfully"));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    /**
     * 
     * @param UpdateUserPositionRequest $request
     * @return ResponseTrait
     */
    public function updateUserPosition(UpdateUserPositionRequest $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->positions as $newPosition) {
                $user = User::findOrFail($newPosition["user_id"]);
                $user->position_id = $newPosition["position_id"];
                $user->save();
            }

            DB::commit();
            return $this->returnSuccessMessage("position updated sussefully");
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @param Request $request
     * @return ResponseTrait
     */
    public function deleteUser(Request $request)
    {
        try {
            DB::beginTransaction();
            $validation = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'required|exists:users,id,deleted_at,NULL',
            ]);

            if ($validation->fails()) {
                return $this->returnValidationError($validation);
            }

            foreach ($request->user_ids as $user_id) {
                $user = User::findOrFail($user_id);
                $user->position_id = null;
                $user->save();
            }

            DB::commit();
            return $this->returnSuccessMessage("users deleted sussefully");
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function userChart()
    {
        try {
            $MasterPositionPosition = Position::with("children", "users")->whereNull("parent_id")->firstOrFail();
            $data["positions"] = PositionResource::make($MasterPositionPosition);
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
