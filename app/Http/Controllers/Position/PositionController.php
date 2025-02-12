<?php

namespace App\Http\Controllers\Position;

use App\Http\Controllers\Controller;
use App\Http\Requests\Position\CreatePositionRequest;
use App\Http\Resources\Position\PositionResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    use ResponseTrait;
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', $this->per_page);
            $pageNumber = $request->input('page', $this->pageNumber);

            $query = Position::query();

            if ($request->has("search")) {
                $query->where("name", "LIKE", "%" . $request->search . "%");
            }

            $category = $query->paginate($perPage, ['*'], 'page', $pageNumber);

            $data["positions"] = PositionResource::collection($category);

            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
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

    public function update()
    {
        try {
            DB::beginTransaction();

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
