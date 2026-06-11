<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\WorkflowVersioning\DTOs\CreateWorkflowVersionDTO;

class StoreWorkflowVersionRequest extends FormRequest
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
            'definition' => ['required', 'array'],
            'definition.nodes' => ['required', 'array', 'min:1'],
            'definition.edges' => ['required', 'array'],
            'definition.entry_node_id' => ['required', 'string'],
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toDto(): CreateWorkflowVersionDTO
    {
        /** @var array<string, mixed> $definition */
        $definition = $this->input('definition');

        return new CreateWorkflowVersionDTO(
            definition: $definition,
            changeSummary: $this->input('change_summary'),
            createdBy: $this->user()?->id,
        );
    }
}
