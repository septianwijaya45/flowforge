<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\DTOs;

use Modules\WorkflowEngine\Exceptions\InvalidWorkflowEdgeException;

final readonly class WorkflowEdgeDTO
{
    public function __construct(
        public string $id,
        public string $source,
        public string $target,
        public ?string $sourceHandle = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        if (! isset($data['id']) || ! is_string($data['id']) || trim($data['id']) === '') {
            throw InvalidWorkflowEdgeException::missingId();
        }

        if (! isset($data['source']) || ! is_string($data['source']) || trim($data['source']) === '') {
            throw InvalidWorkflowEdgeException::missingSource($data['id']);
        }

        if (! isset($data['target']) || ! is_string($data['target']) || trim($data['target']) === '') {
            throw InvalidWorkflowEdgeException::missingTarget($data['id']);
        }

        $sourceHandle = null;

        if (isset($data['source_handle'])) {
            if (! is_string($data['source_handle']) || trim($data['source_handle']) === '') {
                throw InvalidWorkflowEdgeException::invalidSourceHandle($data['id']);
            }

            $sourceHandle = trim($data['source_handle']);
        }

        return new self(
            id: trim($data['id']),
            source: trim($data['source']),
            target: trim($data['target']),
            sourceHandle: $sourceHandle,
        );
    }
}
