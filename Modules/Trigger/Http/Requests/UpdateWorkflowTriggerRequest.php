<?php

declare(strict_types=1);

namespace Modules\Trigger\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Trigger\DTOs\UpdateWorkflowTriggerDTO;

class UpdateWorkflowTriggerRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'config' => ['sometimes', 'array'],
            'config.expression' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function toDto(): UpdateWorkflowTriggerDTO
    {
        return new UpdateWorkflowTriggerDTO(
            name: $this->input('name'),
            config: $this->has('config') ? $this->input('config') : null,
            isActive: $this->has('is_active') ? $this->boolean('is_active') : null,
        );
    }
}
