<?php

declare(strict_types=1);

namespace Modules\WorkflowEngine\DTOs;

use Modules\WorkflowEngine\Enums\WorkflowNodeType;
use Modules\WorkflowEngine\Exceptions\InvalidWorkflowNodeException;

final readonly class WorkflowNodeDTO
{
    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>|null  $position
     */
    public function __construct(
        public string $id,
        public WorkflowNodeType $type,
        public array $config = [],
        public ?array $position = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        if (! isset($data['id']) || ! is_string($data['id']) || trim($data['id']) === '') {
            throw InvalidWorkflowNodeException::missingId();
        }

        if (! isset($data['type']) || ! is_string($data['type'])) {
            throw InvalidWorkflowNodeException::missingType($data['id']);
        }

        $type = WorkflowNodeType::tryFrom($data['type']);

        if ($type === null) {
            throw InvalidWorkflowNodeException::unsupportedType($data['id'], $data['type']);
        }

        if (isset($data['config']) && ! is_array($data['config'])) {
            throw InvalidWorkflowNodeException::invalidConfig($data['id']);
        }

        if (isset($data['position']) && ! is_array($data['position'])) {
            throw InvalidWorkflowNodeException::invalidPosition($data['id']);
        }

        return new self(
            id: trim($data['id']),
            type: $type,
            config: $data['config'] ?? [],
            position: $data['position'] ?? null,
        );
    }
}
