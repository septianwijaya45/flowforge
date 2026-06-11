<?php

declare(strict_types=1);

namespace Modules\Trigger\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Trigger\DTOs\CreateWorkflowTriggerDTO;
use Modules\Trigger\Enums\TriggerType;

class StoreWorkflowTriggerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $type = $this->input('type');

        return [
            'type' => ['required', Rule::enum(TriggerType::class)],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'config' => ['nullable', 'array'],
            'config.expression' => [
                Rule::requiredIf($type === TriggerType::Cron->value),
                'string',
                'max:255',
            ],
        ];
    }

    public function toDto(): CreateWorkflowTriggerDTO
    {
        return new CreateWorkflowTriggerDTO(
            type: TriggerType::from($this->string('type')->toString()),
            name: $this->string('name')->toString(),
            config: $this->input('config'),
            isActive: $this->boolean('is_active', true),
            createdBy: $this->user()?->id,
        );
    }
}
