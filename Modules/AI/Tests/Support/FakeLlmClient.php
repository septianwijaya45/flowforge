<?php

declare(strict_types=1);

namespace Modules\AI\Tests\Support;

use Modules\AI\Contracts\LlmClientContract;
use Modules\AI\DTOs\LlmCompletionRequestDTO;
use Modules\AI\Exceptions\LlmClientException;

final class FakeLlmClient implements LlmClientContract
{
    /**
     * @param  list<string>  $responses
     */
    public function __construct(
        private array $responses = [],
    ) {}

    public function complete(LlmCompletionRequestDTO $request): string
    {
        if ($this->responses === []) {
            throw LlmClientException::requestFailed('fake', 'No canned responses remain.');
        }

        return array_shift($this->responses);
    }

    public function pushResponse(string $response): void
    {
        $this->responses[] = $response;
    }
}
