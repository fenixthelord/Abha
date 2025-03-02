<?php

namespace App\Http\Controllers\Api\Ticket;


use App\Http\Controllers\Controller;
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
    public function store(Request $request)
    {
        $request->validate([
            "name" => ["required", "array"],
            "name.en" => ["required", "string", "min:2", "max:255"],
            "name.ar" => ["required", "string", "min:2", "max:255"],
            "department_id" => "required|exists:departments,id",
            "position_id" => "required|exists:positions,id",
            "parent_id" => "nullable|exists:tickets,id",
        ]);

        $ticket = Ticket::create($request->all());
        return $this->returnData($ticket);
        //return response()->json(['message' => 'Ticket created successfully', 'ticket' => $ticket], 201);
    }

    /**
     * Update an existing ticket.
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            "name" => ["required", "array"],
            "name.en" => [
                "required",
                "string",
                "min:2",
                "max:255"
            ],
            "name.ar" => [
                "required",
                "string",
                "min:2",
                "max:255"
            ],
            'department_id' => 'sometimes|required|exists:departments,id',
            'position_id' => 'sometimes|required|exists:positions,id',
            'parent_id' => 'nullable|exists:tickets,id',
        ]);

        $ticket->update($request->all());

        return response()->json(['message' => 'Ticket updated successfully', 'ticket' => $ticket]);
    }
}
