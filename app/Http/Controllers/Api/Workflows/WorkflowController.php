<?php

namespace App\Http\Controllers\Api\Workflows;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflows\StoreWorkflowRequest;
use App\Http\Requests\Workflows\WorkflowRequest;
use App\Http\Traits\ResponseTrait;
use App\Models\Workflows\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        return Workflow::with('blocks')->get();
    }

    public function store(WorkflowRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();

            $workflow = Workflow::create($validated);

            foreach ($validated['blocks'] as $block) {
                $workflow->blocks()->create($block);
            }
            $data['workflow'] =  $workflow->load('blocks');
            DB::commit();
            return $this->returnData($data, "Form created successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }


        return response()->json($workflow->load('blocks'), 201);
    }

    public function show(Workflow $workflow)
    {
        return $workflow->load('blocks');
    }

    public function update(WorkflowRequest $request, Workflow $workflow)
    {
        $validated = $request->validated();

        $workflow->update($validated);

        if ($request->has('blocks')) {
            $workflow->blocks()->delete();
            foreach ($request->blocks as $block) {
                $workflow->blocks()->create($block);
            }
        }

        return response()->json($workflow->load('blocks'));
    }

    public function destroy(Workflow $workflow)
    {
        $workflow->delete();
        return response()->json(null, 204);
    }
}
