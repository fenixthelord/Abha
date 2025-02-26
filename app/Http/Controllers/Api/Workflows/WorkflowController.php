<?php

namespace App\Http\Controllers\Api\Workflows;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflows\WorkflowRequest;
use App\Http\Resources\Workflows\WorkflowResource;
use App\Http\Traits\HasPermissionTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Workflows\Workflow;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    use ResponseTrait, HasPermissionTrait;


    public function index(Request $request)
    {
        try {
            $this->authorizePermission('workflow.show');

            $pageNumber = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);
            $forms = Workflow::orderByAll($request->sortBy, $request->sortType)
                ->filter($request->only('search'))
                ->with(['blocks']);
            $form = $forms->paginate($perPage, ['*'], 'page', $pageNumber);

            $data['workflows'] =  WorkflowResource::collection($form);
            return $this->PaginateData($data, $form);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show($id)
    {
        try {
            $this->authorizePermission('workflow.show');

            $workflow = Workflow::with('blocks')->findOrFail($id);
            $data['workflow'] =  WorkflowResource::make($workflow);
            return $this->returnData($data);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(WorkflowRequest $request)
    {
        try {
            $this->authorizePermission('workflow.create');

            DB::beginTransaction();
            $validated = $request->validated();
            $workflow = Workflow::create($validated);
            foreach ($validated['blocks'] as $block) {
                $workflow->blocks()->create($block);
            }
            $workflow = Workflow::with('blocks')->findOrFail($workflow->id);
            $data['workflow'] =  WorkflowResource::make($workflow);
            DB::commit();
            return $this->returnData($data, "Workflow created successfully");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function update(WorkflowRequest $request, $id)
    {
        try {
            $this->authorizePermission('workflow.update');

            DB::beginTransaction();
            $validated = $request->validated();
            $workflow = Workflow::findOrFail($id);
            $workflow->update($validated);
            if ($request->has('blocks')) {
                $workflow->blocks()->forceDelete();
                foreach ($request->blocks as $block) {
                    $workflow->blocks()->create($block);
                }
            }
            $workflow = Workflow::with('blocks')->findOrFail($workflow->id);
            $data['workflow'] =  WorkflowResource::make($workflow);
            DB::commit();
            return $this->returnData($data, "Form created successfully");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
        $validated = $request->validated();
        $workflow->update($validated);
        return response()->json($workflow->load('blocks'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->authorizePermission('workflow.delete');

            $workflow = Workflow::findOrFail($id);
            $workflow->forceDelete();
            DB::commit();
            return $this->returnSuccessMessage('Workflow deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function testSytemError()
    {
        throw new Exception("This is a test system error.");
    }
}
