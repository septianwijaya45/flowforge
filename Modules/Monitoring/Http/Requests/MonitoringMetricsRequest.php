<?php

declare(strict_types=1);

namespace Modules\Monitoring\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MonitoringMetricsRequest extends FormRequest
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
            'days' => ['sometimes', 'integer', 'min:1', 'max:90'],
        ];
    }

    public function days(): int
    {
        return $this->integer('days', 30);
    }
}
