<?php

declare(strict_types=1);

namespace Modules\Scheduler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Scheduler\DTOs\CreateScheduleDTO;
use Modules\Tenant\Contracts\TenantContextContract;

class StoreScheduleRequest extends FormRequest
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
            'workflow_id' => [
                'required',
                'uuid',
                Rule::exists('workflows', 'id')->where('tenant_id', $tenantId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'cron_expression' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): CreateScheduleDTO
    {
        return new CreateScheduleDTO(
            workflowId: $this->string('workflow_id')->toString(),
            name: $this->string('name')->toString(),
            cronExpression: $this->string('cron_expression')->toString(),
            isActive: $this->boolean('is_active', true),
            createdBy: $this->user()?->id,
        );
    }
}
