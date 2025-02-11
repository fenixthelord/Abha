<?php

namespace App\Http\Requests\Workflows;

use App\Http\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class WorkflowRequest extends FormRequest
{
    use ResponseTrait;
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $is_store = request()->route()->getName() === 'workflows.store';
        $is_update = request()->route()->getName() === 'workflows.update';
        if ($is_store)
            return [
                'name' => 'required|array',
                'description' => 'nullable|array',
                'blocks' => 'required|array',
                'blocks.*.type' => 'required|in:start,end,action',
                'blocks.*.config' => 'required_if:blocks.*.type,start|array',
            ];
        else if ($is_update)
            return [
                'name' => 'sometimes|array',
                'description' => 'nullable|array',
                'blocks' => 'sometimes|array',
                'blocks.*.type' => 'sometimes|required|in:start,end,action',
                'blocks.*.config' => 'sometimes|required_if:blocks.*.type,start|array',
            ];
        else return [];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateBlocks($validator);
        });
    }

    protected function validateBlocks($validator)
    {
        $blocks = $this->input('blocks');

        foreach ($blocks as $index => $block) {
            if ($block['type'] === 'start') {
                $trigger = $block['config']['trigger'] ?? null;

                switch ($trigger) {
                    case 'action_based':
                        $this->validateActionBasedTrigger($validator, $block['config'], $index);
                        break;

                    case 'scheduled':
                        $this->validateScheduledTrigger($validator, $block['config'], $index);
                        break;

                    case 'system_event':
                        $this->validateSystemEventTrigger($validator, $block['config'], $index);
                        break;

                    case 'conditional':
                        $this->validateConditionalTrigger($validator, $block['config'], $index);
                        break;

                    default:
                        $validator->errors()->add("blocks.{$index}.config.trigger", "Invalid trigger type: {$trigger}");
                }
            }
        }
    }

    protected function validateActionBasedTrigger($validator, $config, $index)
    {
        $actionType = $config['action_type'] ?? null;

        if (!in_array($actionType, ['data_creation', 'data_deletion', 'field_change'])) {
            $validator->errors()->add("blocks.{$index}.config.action_type", "Invalid action type: {$actionType}");
        }
    }

    protected function validateScheduledTrigger($validator, $config, $index)
    {
        $scheduledType = $config['scheduled_type'] ?? null;

        if (!in_array($scheduledType, ['time_based', 'recurring'])) {
            $validator->errors()->add("blocks.{$index}.config.scheduled_type", "Invalid scheduled type: {$scheduledType}");
        }

        if ($scheduledType === 'time_based' && empty($config['date'])) {
            $validator->errors()->add("blocks.{$index}.config.date", "Missing date for time-based trigger.");
        }

        if ($scheduledType === 'recurring') {
            if (empty($config['start_date']) || empty($config['start_time'])) {
                $validator->errors()->add("blocks.{$index}.config.start_date", "Missing start date or time for recurring trigger.");
            }

            $recurrence = $config['recurrence'] ?? null;
            if (!in_array($recurrence, ['daily', 'weekly', 'monthly', 'annual'])) {
                $validator->errors()->add("blocks.{$index}.config.recurrence", "Invalid recurrence type: {$recurrence}");
            }
        }
    }

    protected function validateSystemEventTrigger($validator, $config, $index)
    {
        $eventType = $config['event_type'] ?? null;

        if (!in_array($eventType, ['system_error'])) {
            $validator->errors()->add("blocks.{$index}.config.event_type", "Invalid system event type: {$eventType}");
        }
    }

    protected function validateConditionalTrigger($validator, $config, $index)
    {
        $conditionalType = $config['conditional_type'] ?? null;

        if (!in_array($conditionalType, ['threshold_reached', 'condition_met', 'combination_of_events'])) {
            $validator->errors()->add("blocks.{$index}.config.conditional_type", "Invalid conditional type: {$conditionalType}");
        }
    }

    public function failedValidation($validator)
    {
        throw new HttpResponseException($this->returnValidationError($validator));
    }
}
