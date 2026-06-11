<?php

declare(strict_types=1);

namespace Modules\Trigger\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Trigger\DTOs\DispatchTriggerDTO;

class WebhookTriggerRequest extends FormRequest
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
            'input' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function toDto(): DispatchTriggerDTO
    {
        $payload = $this->except(['input']);

        return new DispatchTriggerDTO(
            input: $this->input('input') ?? ($payload === [] ? null : $payload),
            payload: [
                'headers' => $this->headers->all(),
            ],
        );
    }
}
