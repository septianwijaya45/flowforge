<?php

declare(strict_types=1);

namespace Modules\Monitoring\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Modules\Monitoring\DTOs\ListWorkflowRunsDTO;
use Modules\WorkflowEngine\Enums\WorkflowRunStatus;

class ListWorkflowRunsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string|Enum>>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'nullable', Rule::enum(WorkflowRunStatus::class)],
            'active_only' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): ListWorkflowRunsDTO
    {
        $status = $this->input('status');

        return new ListWorkflowRunsDTO(
            page: $this->integer('page', 1),
            perPage: $this->integer('per_page', 15),
            status: is_string($status) ? WorkflowRunStatus::from($status) : null,
            activeOnly: $this->boolean('active_only'),
        );
    }
}
