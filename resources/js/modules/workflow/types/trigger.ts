export type TriggerType = 'manual' | 'cron' | 'webhook';

export interface WorkflowTrigger {
    id: string;
    workflow_id: string;
    type: TriggerType;
    name: string;
    is_active: boolean;
    config: {
        expression?: string;
    } | null;
    webhook_token?: string;
    webhook_url?: string;
    last_triggered_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface CreateTriggerInput {
    type: TriggerType;
    name: string;
    is_active?: boolean;
    config?: {
        expression?: string;
    };
}

export interface UpdateTriggerInput {
    name?: string;
    is_active?: boolean;
    config?: {
        expression?: string;
    };
}
