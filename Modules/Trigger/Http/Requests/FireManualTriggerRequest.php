<?php

declare(strict_types=1);

namespace Modules\Trigger\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Trigger\DTOs\DispatchTriggerDTO;

class FireManualTriggerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'input' => ['sometimes', 'nullable', 'array'],
            'trigger_id' => ['sometimes', 'uuid', 'exists:workflow_triggers,id'],
        ];
    }

    public function toDto(): DispatchTriggerDTO
    {
        return new DispatchTriggerDTO(
            input: $this->input('input'),
            triggeredBy: $this->user()?->id,
            triggerId: $this->input('trigger_id'),
        );
    }
}
