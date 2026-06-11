export interface Schedule {
    id: string;
    workflow_id: string;
    workflow_name: string | null;
    workflow_slug: string | null;
    name: string;
    cron_expression: string | null;
    is_active: boolean;
    next_run_at: string | null;
    last_triggered_at: string | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface CreateScheduleInput {
    workflow_id: string;
    name: string;
    cron_expression: string;
    is_active?: boolean;
}

export interface UpdateScheduleInput {
    name?: string;
    cron_expression?: string;
    is_active?: boolean;
}
