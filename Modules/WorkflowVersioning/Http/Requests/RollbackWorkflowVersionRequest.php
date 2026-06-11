<?php

declare(strict_types=1);

namespace Modules\WorkflowVersioning\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\WorkflowVersioning\DTOs\RollbackWorkflowVersionDTO;

class RollbackWorkflowVersionRequest extends FormRequest
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
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toDto(): RollbackWorkflowVersionDTO
    {
        return new RollbackWorkflowVersionDTO(
            changeSummary: $this->input('change_summary'),
            createdBy: $this->user()?->id,
        );
    }
}
