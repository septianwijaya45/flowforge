<?php

declare(strict_types=1);

use Modules\AI\Services\WorkflowDefinitionPromptTemplate;

function workflowPromptTemplate(): WorkflowDefinitionPromptTemplate
{
    return new WorkflowDefinitionPromptTemplate;
}

describe('WorkflowDefinitionPromptTemplate', function (): void {
    it('includes schema guidance and the user prompt', function (): void {
        $messages = workflowPromptTemplate()->initialMessages('Every hour check website.');

        expect($messages)->toHaveCount(2)
            ->and($messages[0]->role)->toBe('system')
            ->and($messages[0]->content)->toContain('entry_node_id')
            ->and($messages[0]->content)->toContain('schedule')
            ->and($messages[1]->role)->toBe('user')
            ->and($messages[1]->content)->toBe('Every hour check website.');
    });

    it('builds correction messages with validation errors', function (): void {
        $message = workflowPromptTemplate()->correctionMessage('entry_node_id is missing');

        expect($message->role)->toBe('user')
            ->and($message->content)->toContain('entry_node_id is missing')
            ->and($message->content)->toContain('corrected JSON');
    });
});
