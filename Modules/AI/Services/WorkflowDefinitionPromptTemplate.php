<?php

declare(strict_types=1);

namespace Modules\AI\Services;

use Modules\AI\Contracts\WorkflowDefinitionPromptTemplateContract;
use Modules\AI\DTOs\LlmChatMessageDTO;

final class WorkflowDefinitionPromptTemplate implements WorkflowDefinitionPromptTemplateContract
{
    private const string SYSTEM_PROMPT = <<<'PROMPT'
You are a workflow architect for FlowForge. Convert natural language automation requests into valid workflow DAG JSON.

Return ONLY a single JSON object. Do not wrap the JSON in markdown fences or add commentary.

JSON schema:
{
  "entry_node_id": "string",
  "nodes": [
    {
      "id": "string",
      "type": "http" | "delay" | "condition" | "script" | "email" | "database" | "webhook",
      "config": { }
    }
  ],
  "edges": [
    {
      "id": "string",
      "source": "string",
      "target": "string",
      "source_handle": "true" | "false" (optional, required for condition branches)
    }
  ],
  "schedule": {
    "cron": "string (5-field cron expression, optional)",
    "description": "string (optional)"
  }
}

Node types:
- http: outbound HTTP request. config keys: label, url, method (GET|POST|PUT|PATCH|DELETE), headers (object), body (object), timeout (seconds).
- delay: pause execution. config keys: label, seconds (integer).
- condition: branch on a predicate. config keys: label, operator (equals|not_equals|greater_than|less_than|contains|truthy|falsy), path, value.
- script: lightweight no-op or placeholder step. config keys: label.
- email: send an email notification. config keys: label, to, subject, body (supports {{node_id.field}} placeholders).
- database: read-only SELECT query. config keys: label, query, bindings (array, optional), connection (optional).
- webhook: POST JSON to an external URL. config keys: label, url, payload_path (optional context path), headers (object, optional), timeout (seconds).

DAG rules:
- entry_node_id must reference an existing node with zero incoming edges and be the only root.
- Every node id must be unique. Use snake_case ids.
- Every edge id must be unique.
- No cycles. All nodes must be reachable from entry_node_id.
- At least one terminal node (no outgoing edges) is required.
- Condition nodes must have distinct true/false outgoing edges using source_handle "true" and "false".
- When only one branch needs work, attach a script no-op node to the other branch.

Execution context:
- Prior node outputs are available at paths like "{node_id}.status", "{node_id}.body", "{node_id}.headers".
- HTTP nodes expose response status at "{node_id}.status".

Scheduling:
- Recurring schedules (e.g. hourly, daily) belong in the top-level schedule object, not as workflow nodes.
- Use standard 5-field cron syntax (minute hour day month weekday). Example hourly: "0 * * * *".

Email or notifications:
- Prefer the email node for email delivery. Use webhook for Slack/Discord-style integrations.

Use sensible placeholder URLs and labels when the user omits details.
PROMPT;

    /**
     * @return list<LlmChatMessageDTO>
     */
    public function initialMessages(string $userPrompt): array
    {
        return [
            new LlmChatMessageDTO('system', self::SYSTEM_PROMPT),
            new LlmChatMessageDTO('user', trim($userPrompt)),
        ];
    }

    public function correctionMessage(string $errorMessage): LlmChatMessageDTO
    {
        return new LlmChatMessageDTO(
            'user',
            'The previous response was invalid. Fix the workflow JSON and return ONLY the corrected JSON object.'."\n"
            .'Validation error: '.trim($errorMessage),
        );
    }
}
