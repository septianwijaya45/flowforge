<?php

declare(strict_types=1);

namespace Modules\Workflow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Modules\Workflow\DTOs\ListWorkflowsDTO;
use Modules\Workflow\Enums\WorkflowStatus;

class ListWorkflowsRequest extends FormRequest
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
            'status' => ['sometimes', 'nullable', Rule::enum(WorkflowStatus::class)],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', 'string', Rule::in(['name', 'slug', 'created_at', 'updated_at', 'status'])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    public function toDto(): ListWorkflowsDTO
    {
        $status = $this->input('status');

        return new ListWorkflowsDTO(
            page: $this->integer('page', 1),
            perPage: $this->integer('per_page', 15),
            status: is_string($status) ? WorkflowStatus::from($status) : null,
            search: $this->input('search'),
            sort: $this->string('sort', 'created_at')->toString(),
            direction: $this->string('direction', 'desc')->toString(),
        );
    }
}
