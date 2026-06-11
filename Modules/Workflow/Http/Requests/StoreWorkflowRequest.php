<?php

declare(strict_types=1);

namespace Modules\Workflow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Workflow\DTOs\CreateWorkflowDTO;
use Modules\Workflow\Enums\WorkflowStatus;

class StoreWorkflowRequest extends FormRequest
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
        $tenantId = app(TenantContextContract::class)->tenantId();

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('workflows', 'slug')->where('tenant_id', $tenantId),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::enum(WorkflowStatus::class)],
        ];
    }

    public function toDto(): CreateWorkflowDTO
    {
        $status = $this->input('status', WorkflowStatus::Draft->value);

        return new CreateWorkflowDTO(
            name: $this->string('name')->toString(),
            slug: $this->input('slug'),
            description: $this->input('description'),
            status: WorkflowStatus::from((string) $status),
            createdBy: $this->user()?->id,
        );
    }
}
