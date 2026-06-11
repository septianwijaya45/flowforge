<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\WorkflowVersioning\DTOs\ListWorkflowVersionsDTO;

class ListWorkflowVersionsRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDto(): ListWorkflowVersionsDTO
    {
        return new ListWorkflowVersionsDTO(
            page: $this->integer('page', 1),
            perPage: $this->integer('per_page', 15),
        );
    }
}
