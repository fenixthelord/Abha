<?php

namespace App\Http\Controllers\Api\Event;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\CreateEventRequest;
use App\Http\Requests\Events\ListEventsRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Event;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $permissions = [
            'list'  => ['event.show'],
            'showEvent'    => ['event.show'],
            'createEvent'  => ['event.create'],
            'deleteEvent' => ['event.delete'],
            'updateEvent'   => ['event.update'],
        ];

        foreach ($permissions as $method => $permission) {
            $this->middleware('permission:' . implode('|', $permission))->only($method);
        }
    }
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
                })
                ->when($request->filled("start_date"), function ($q) use ($request) {
                    $q->where("start_date", ">=", Carbon::parse($request->start_date));
                })
                ->when($request->filled("end_date"), function ($q) use ($request) {
                    $q->where("end_date", "<=", Carbon::parse($request->end_date));
                });

            // date range
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
            return $this->returnData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function deleteEvent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:events,id',
            ], [
                'id.required' => __('validation.custom.event.id_required'),
                'id.exists' => __('validation.custom.event.id_exists'),
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }

            $event = Event::withTrashed()->where('id', $request->id)->first();

            if ($event && $event->trashed()) {
                return $this->returnSuccessMessage(__('validation.custom.event.already_deleted'));
            }
//            if (!$event) {
//                return $this->ReturnError(__('validation.custom.event.not_found'));
//            }

            DB::beginTransaction();
//            $event = Event::find($request->id);
            $event->delete();
            DB::commit();
            return $this->returnSuccessMessage(__('validation.custom.event.success_deleted'));
        } catch
        (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function showEvent($id)
    {
        try {
            $validation = Validator::make(
                ["id" => $id],
                ['id' => 'required|exists:events,id']
            );
            if ($validation->fails()) {
                return $this->ReturnError($validation->errors()->first());
            }
            $event = Event::find($id);

            $data["event"] = EventResource::make($event)->allInfo();
            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function updateEvent(UpdateEventRequest $request)
    {
        try {
            DB::beginTransaction();

            $formateStartDate = Carbon::parse($request->start_date)->format("Y-m-d");
            $formateEndDate = Carbon::parse($request->end_date)->format("Y-m-d");

            $validatedData = array_merge($request->validated(), [
                "start_date" => $formateStartDate,
                "end_date" => $formateEndDate,
            ]);

            $event = Event::findOrFail($request->id);
            $event->update($validatedData);
            $data["event"] = EventResource::make($event);
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
