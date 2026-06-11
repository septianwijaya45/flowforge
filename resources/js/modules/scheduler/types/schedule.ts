export interface Schedule {
    id: string;
    workflow_id: number;
    cron_expression: string;
    is_active: boolean;
    next_run_at: string | null;
}
