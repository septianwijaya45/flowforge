<?php

declare(strict_types=1);

namespace Modules\AI\Services;

use Illuminate\Support\Str;
use Modules\AI\Exceptions\LlmResponseParseException;
use Modules\WorkflowEngine\Enums\WorkflowNodeType;

final class WorkflowDefinitionSanitizer
{
    private const int MAX_STRING_LENGTH = 10_000;

    private const int MAX_NODES = 100;

    private const int MAX_EDGES = 200;

    /**
     * @param  array<string, mixed>  $payload
     * @return array{definition: array<string, mixed>, schedule: array<string, mixed>|null}
     */
    public function sanitize(array $payload): array
    {
        $schedule = $this->sanitizeSchedule($payload['schedule'] ?? null);

        if (! isset($payload['nodes']) || ! is_array($payload['nodes'])) {
            throw LlmResponseParseException::invalidStructure('nodes must be an array.');
        }

        if (! isset($payload['edges']) || ! is_array($payload['edges'])) {
            throw LlmResponseParseException::invalidStructure('edges must be an array.');
        }

        if (! isset($payload['entry_node_id']) || ! is_string($payload['entry_node_id'])) {
            throw LlmResponseParseException::invalidStructure('entry_node_id must be a string.');
        }

        $nodes = $this->sanitizeNodes(array_values($payload['nodes']));
        $edges = $this->sanitizeEdges(array_values($payload['edges']));

        return [
            'definition' => [
                'entry_node_id' => $this->sanitizeIdentifier($payload['entry_node_id']),
                'nodes' => $nodes,
                'edges' => $edges,
            ],
            'schedule' => $schedule,
        ];
    }

    /**
     * @param  list<mixed>  $nodes
     * @return list<array<string, mixed>>
     */
    private function sanitizeNodes(array $nodes): array
    {
        if (count($nodes) > self::MAX_NODES) {
            throw LlmResponseParseException::invalidStructure('Too many nodes in generated workflow.');
        }

        $sanitized = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if (! isset($node['id'], $node['type']) || ! is_string($node['id']) || ! is_string($node['type'])) {
                continue;
            }

            $type = WorkflowNodeType::tryFrom($node['type']);

            if ($type === null) {
                continue;
            }

            $config = is_array($node['config'] ?? null) ? $this->sanitizeConfig($node['config']) : [];
            $sanitizedNode = [
                'id' => $this->sanitizeIdentifier($node['id']),
                'type' => $type->value,
                'config' => $config,
            ];

            if (isset($node['position']) && is_array($node['position'])) {
                $sanitizedNode['position'] = $this->sanitizePosition($node['position']);
            }

            $sanitized[] = $sanitizedNode;
        }

        if ($sanitized === []) {
            throw LlmResponseParseException::invalidStructure('At least one valid node is required.');
        }

        return $sanitized;
    }

    /**
     * @param  list<mixed>  $edges
     * @return list<array<string, mixed>>
     */
    private function sanitizeEdges(array $edges): array
    {
        if (count($edges) > self::MAX_EDGES) {
            throw LlmResponseParseException::invalidStructure('Too many edges in generated workflow.');
        }

        $sanitized = [];
        $edgeIndex = 1;

        foreach ($edges as $edge) {
            if (! is_array($edge)) {
                continue;
            }

            if (! isset($edge['source'], $edge['target']) || ! is_string($edge['source']) || ! is_string($edge['target'])) {
                continue;
            }

            $edgeId = isset($edge['id']) && is_string($edge['id']) && trim($edge['id']) !== ''
                ? $this->sanitizeIdentifier($edge['id'])
                : 'e'.$edgeIndex;

            $sanitizedEdge = [
                'id' => $edgeId,
                'source' => $this->sanitizeIdentifier($edge['source']),
                'target' => $this->sanitizeIdentifier($edge['target']),
            ];

            if (isset($edge['source_handle']) && is_string($edge['source_handle']) && trim($edge['source_handle']) !== '') {
                $sanitizedEdge['source_handle'] = $this->sanitizeScalarString($edge['source_handle']);
            }

            $sanitized[] = $sanitizedEdge;
            $edgeIndex++;
        }

        return $sanitized;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function sanitizeConfig(array $config): array
    {
        $sanitized = [];

        foreach ($config as $key => $value) {
            $sanitizedKey = Str::snake($this->sanitizeScalarString((string) $key));

            if ($sanitizedKey === '') {
                continue;
            }

            $sanitized[$sanitizedKey] = $this->sanitizeValue($value);
        }

        return $sanitized;
    }

    /**
     * @param  array<string, mixed>  $position
     * @return array{x: float, y: float}
     */
    private function sanitizePosition(array $position): array
    {
        return [
            'x' => isset($position['x']) && is_numeric($position['x']) ? (float) $position['x'] : 0.0,
            'y' => isset($position['y']) && is_numeric($position['y']) ? (float) $position['y'] : 0.0,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function sanitizeSchedule(mixed $schedule): ?array
    {
        if (! is_array($schedule)) {
            return null;
        }

        $sanitized = [];

        if (isset($schedule['cron']) && is_string($schedule['cron']) && trim($schedule['cron']) !== '') {
            $sanitized['cron'] = $this->sanitizeScalarString($schedule['cron']);
        }

        if (isset($schedule['description']) && is_string($schedule['description']) && trim($schedule['description']) !== '') {
            $sanitized['description'] = $this->sanitizeScalarString($schedule['description']);
        }

        return $sanitized === [] ? null : $sanitized;
    }

    private function sanitizeIdentifier(string $value): string
    {
        $normalized = Str::snake(trim($value));
        $normalized = preg_replace('/[^a-z0-9_]+/', '_', $normalized) ?? '';
        $normalized = trim($normalized, '_');

        return $normalized !== '' ? $normalized : 'node';
    }

    private function sanitizeScalarString(string $value): string
    {
        $trimmed = trim($value);

        if (strlen($trimmed) > self::MAX_STRING_LENGTH) {
            return substr($trimmed, 0, self::MAX_STRING_LENGTH);
        }

        return $trimmed;
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->sanitizeScalarString($value);
        }

        if (is_int($value) || is_float($value) || is_bool($value) || $value === null) {
            return $value;
        }

        if (! is_array($value)) {
            return null;
        }

        $isList = array_is_list($value);
        $sanitized = [];

        foreach ($value as $key => $nestedValue) {
            if ($isList) {
                $sanitized[] = $this->sanitizeValue($nestedValue);

                continue;
            }

            if (! is_string($key)) {
                continue;
            }

            $sanitized[Str::snake($this->sanitizeScalarString($key))] = $this->sanitizeValue($nestedValue);
        }

        return $sanitized;
    }
}
