<?php

namespace App\Http\Controllers\Api\Ticket;


use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Traits\ResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Http\Traits\Paginate;
use App\Models\Forms\Form;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    use ResponseTrait, Paginate;

    public function index(Request $request)
    {
        try {
            $fields = ['name', 'department_id', 'category_id', 'parent_id'];
            $tickets = $this->allWithSearch(new Ticket(), $fields, $request);
            $data['tickets'] = TicketResource::collection($tickets);
            return $this->PaginateData($data, $tickets);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function show()
    {
        $validate = Validator::make(request()->all(), [
            'id' => 'required|exists:tickets,id'
        ]);
        if ($validate->fails()) {
            return $this->returnValidationError($validate);
        }
        $ticket = Ticket::find(request()->id)?? null;
        if (!$ticket) {
            return $this->returnError('Ticket not found');
        }
        return $this->returnData(TicketResource::make($ticket));
    }

    /**
     * Create a new ticket.
     */
    public function store(StoreTicketRequest $request)
    {

        try {
            DB::beginTransaction();
            $validatedData = $request->validated();
            $ticket = Ticket::create($validatedData);

            $formID = Form::whereHas("type", function ($q) use ($request) {
                $q->where("form_index", $request->category_id);
            })->pluck("id")->first();

            if (!$formID) {
                return $this->returnError("Form not found");
            }

            $data = [
                'submitter_id' => auth()->user()->id,
                'submitter_service' => "user",
                'id' => $formID
            ];

            // add form-id and values-of-form to $data
            $data = array_merge($data, $request->all());

            $response = Http::post(url('/api/v1/forms/submit-customer'), $data)->json();

            if ($response['code'] != 200) {
                return $this->returnError($response["message"]);
            }
            DB::commit();
            return $this->returnData(TicketResource::make($ticket));
            //return response()->json(['message' => 'Ticket created successfully', 'ticket' => $ticket], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    /**
     * Update an existing ticket.
     */
    public function update(UpdateTicketRequest $request)
    {
        $validatedData = $request->validated();
        $ticket = Ticket::findOrFail($request['id']);
        $ticket->update($validatedData);
        return $this->returnData(TicketResource::make($ticket));
    }
}
