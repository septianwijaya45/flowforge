import { Link } from 'react-router-dom';
import { ArrowLeft, Play, Save } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { appRoutes } from '@/core/constants/routes';

interface BuilderToolbarProps {
    workflowName: string;
    versionNumber?: number;
    isDirty: boolean;
    isSaving: boolean;
    isRunning?: boolean;
    canRun?: boolean;
    onSave: () => void;
    onRun?: () => void;
}

export function BuilderToolbar({
    workflowName,
    versionNumber,
    isDirty,
    isSaving,
    isRunning = false,
    canRun = true,
    onSave,
    onRun,
}: BuilderToolbarProps) {
    return (
        <header className="flex shrink-0 items-center justify-between gap-4 border-b bg-background px-4 py-3">
            <div className="flex min-w-0 flex-1 items-center gap-3">
                <Button variant="ghost" size="sm" asChild>
                    <Link to={appRoutes.workflow.index}>
                        <ArrowLeft className="size-4" />
                        Workflows
                    </Link>
                </Button>
                <div className="min-w-0">
                    <h1 className="truncate text-lg font-semibold">{workflowName}</h1>
                    <p className="text-xs text-muted-foreground">
                        {versionNumber ? `Version ${versionNumber}` : 'New workflow graph'}
                        {isDirty ? ' · Unsaved changes' : ''}
                    </p>
                </div>
            </div>

            <div className="flex shrink-0 items-center gap-2">
                {onRun ? (
                    <Button
                        variant="outline"
                        onClick={onRun}
                        disabled={isSaving || isRunning || !canRun}
                        title={
                            !canRun
                                ? 'Save the workflow before running.'
                                : isDirty
                                  ? 'Save and run the latest graph.'
                                  : undefined
                        }
                    >
                        {isRunning ? <Spinner /> : <Play className="size-4" />}
                        {isRunning ? 'Running…' : 'Run'}
                    </Button>
                ) : null}
                <Button onClick={onSave} disabled={isSaving || !isDirty}>
                    {isSaving ? <Spinner /> : <Save className="size-4" />}
                    Save
                </Button>
            </div>
        </header>
    );
}
