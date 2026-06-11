<?php

declare(strict_types=1);

namespace Modules\AI\Contracts;

use Modules\AI\DTOs\LlmCompletionRequestDTO;

interface LlmClientContract
{
    public function complete(LlmCompletionRequestDTO $request): string;
}
