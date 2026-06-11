<?php

declare(strict_types=1);

namespace Modules\AI\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\AI\DTOs\BuildWorkflowFromPromptDTO;
use Modules\AI\Enums\LlmProvider;

final class BuildWorkflowFromPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'min:3', 'max:5000'],
            'provider' => ['nullable', 'string', Rule::enum(LlmProvider::class)],
        ];
    }

    public function toDto(): BuildWorkflowFromPromptDTO
    {
        $provider = $this->input('provider');

        return new BuildWorkflowFromPromptDTO(
            prompt: $this->string('prompt')->toString(),
            provider: is_string($provider) ? LlmProvider::tryFrom($provider) : null,
        );
    }
}
