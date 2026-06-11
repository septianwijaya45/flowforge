import type { WorkflowDefinition } from '@/modules/workflow/types/workflow-graph';

export type LlmProvider = 'openai' | 'gemini';

export interface GeneratedWorkflowSchedule {
    cron?: string;
    description?: string;
}

export interface GeneratedWorkflowResult {
    definition: WorkflowDefinition;
    schedule: GeneratedWorkflowSchedule | null;
    provider: LlmProvider;
    attempts: number;
}

export interface BuildWorkflowPayload {
    prompt: string;
    provider?: LlmProvider;
}
