<?php

declare(strict_types=1);

namespace Modules\Workflow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Tenant\Contracts\TenantContextContract;
use Modules\Workflow\DTOs\UpdateWorkflowDTO;
use Modules\Workflow\Enums\WorkflowStatus;
use Modules\Workflow\Models\Workflow;

class UpdateWorkflowRequest extends FormRequest
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
        /** @var Workflow $workflow */
        $workflow = $this->route('workflow');
        $tenantId = app(TenantContextContract::class)->tenantId();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('workflows', 'slug')
                    ->where('tenant_id', $tenantId)
                    ->ignore($workflow->id),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::enum(WorkflowStatus::class)],
        ];
    }

    public function toDto(): UpdateWorkflowDTO
    {
        $status = $this->input('status');

        return new UpdateWorkflowDTO(
            name: $this->input('name'),
            slug: $this->input('slug'),
            description: $this->has('description') ? $this->input('description') : null,
            status: is_string($status) ? WorkflowStatus::from($status) : null,
        );
    }
}
