<?php

declare(strict_types=1);

namespace Modules\ExecutionLog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListExecutionLogsRequest extends FormRequest
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
            'limit' => ['sometimes', 'integer', 'min:1', 'max:500'],
        ];
    }

    public function limit(): ?int
    {
        return $this->has('limit') ? $this->integer('limit') : null;
    }
}
