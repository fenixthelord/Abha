<?php

namespace App\Http\Controllers\Api\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\CreateEventRequest;
use App\Http\Requests\Events\ListEventsRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    use ResponseTrait;

    public function list(ListEventsRequest $request)
    {
        try {
            $perPage = $request->input('per_page', $this->per_page);
            $pageNumber = $request->input('page', $this->pageNumber);

            $query = Event::query()
                ->when($request->has("search"), function ($q) use ($request) {
                    $q->search($request->search);
                })
                ->when($request->has("service_id"), function ($q) use ($request) {
                    $q->Filter($request->service_id);
                });

            $events = $query->paginate($perPage, ['*'], 'page', $pageNumber);
            if ($events->lastPage() < $pageNumber) {
                $events = $query->paginate($perPage, ['*'], 'page', 1);
            }
            $data["events"] = EventResource::collection($events);
            return $this->PaginateData($data, $events);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function createEvent(CreateEventRequest $request)
    {
        try {
            DB::beginTransaction();
            $formateStartDate = Carbon::parse($request->start_date)->format("Y-m-d");
            $formateEndDate = Carbon::parse($request->end_date)->format("Y-m-d");
            
            $validatedData = array_merge($request->validated(), [
                "start_date" => $formateStartDate,
                "end_date" => $formateEndDate,
            ]);
            
            $event = Event::create($validatedData);
            $data["event"] = EventResource::make($event);
            DB::commit();
            // return $this->returnData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function deleteEvent($id)
    {
        try {
            $validation = Validator::make(
                ["id" => $id],
                ['id' => 'required|exists:events,id',]
            );
            if ($validation->fails()) {
                return $this->ReturnError($validation->errors()->first());
            }
            DB::beginTransaction();
            $event = Event::find($id);
            $event->delete();
            DB::commit();
            return $this->returnSuccessMessage("Event Deleted successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    // public function showEvent($id)
    // {
    //     try {
    //         $validation = Validator::make(
    //             ["id" => $id],
    //             ['id' => 'required|exists:events,id',]
    //         );
    //         if ($validation->fails()) {
    //             return $this->ReturnError($validation->errors()->first());
    //         }
    //         $event = Event::find($id);

    //         $data["event"] = EventResource::make($event)->allInfo();
    //         return $this->returnData($data);
    //     } catch (\Exception $e) {
    //         return $this->handleException($e);
    //     }
    // }
    public function updateEvent(UpdateEventRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $formateStartDate = Carbon::parse($request->start_date)->format("Y-m-d");
            $formateEndDate = Carbon::parse($request->end_date)->format("Y-m-d");
            
            $validatedData = array_merge($request->validated(), [
                "start_date" => $formateStartDate,
                "end_date" => $formateEndDate,
            ]);
            
            $event = Event::find($id);
            $event->update($validatedData);
            
            DB::commit();
            return $this->returnSuccessMessage("Event Updated successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
