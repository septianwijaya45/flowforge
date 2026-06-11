<?php

declare(strict_types=1);

namespace Modules\Scheduler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Scheduler\DTOs\UpdateScheduleDTO;

class UpdateScheduleRequest extends FormRequest
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
            'cron_expression' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): UpdateScheduleDTO
    {
        return new UpdateScheduleDTO(
            name: $this->input('name'),
            cronExpression: $this->input('cron_expression'),
            isActive: $this->has('is_active') ? $this->boolean('is_active') : null,
        );
    }
}
