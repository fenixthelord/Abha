<?php

namespace App\Http\Controllers\Api\Workflows;

use App\Http\Controllers\Controller;
use App\Models\Workflows\Workflow;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index()
    {
        return Workflow::with('blocks')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'description' => 'nullable|array',
            'blocks' => 'required|array',
        ]);

        $workflow = Workflow::create($validated);

        foreach ($request->blocks as $block) {
            $workflow->blocks()->create($block);
        }

        return response()->json($workflow->load('blocks'), 201);
    }

    public function show(Workflow $workflow)
    {
        return $workflow->load('blocks');
    }

    public function update(Request $request, Workflow $workflow)
    {
        $validated = $request->validate([
            'name' => 'sometimes|array',
            'description' => 'nullable|array',
            'blocks' => 'sometimes|array',
        ]);

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
