<?php

namespace App\Http\Controllers\Api\Ticket;


use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Http\Traits\Paginate;
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

    /**
     * Create a new ticket.
     */
    public function store(StoreTicketRequest $request)
    {
        $validatedData = $request->validated();
        $ticket = Ticket::create($validatedData);
        return $this->returnData(TicketResource::make($ticket));
        //return response()->json(['message' => 'Ticket created successfully', 'ticket' => $ticket], 201);
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
