import { Link } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { appRoutes } from '@/core/constants/routes';

interface BuilderToolbarProps {
    workflowName: string;
    versionNumber?: number;
    isDirty: boolean;
    isSaving: boolean;
    onSave: () => void;
}

export function BuilderToolbar({
    workflowName,
    versionNumber,
    isDirty,
    isSaving,
    onSave,
}: BuilderToolbarProps) {
    return (
        <header className="flex items-center justify-between gap-4 border-b bg-background px-4 py-3">
            <div className="flex min-w-0 items-center gap-3">
                <Button variant="ghost" size="sm" asChild>
                    <Link href={appRoutes.workflow.index}>
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

            <Button onClick={onSave} disabled={isSaving || !isDirty}>
                {isSaving ? <Spinner /> : <Save className="size-4" />}
                Save
            </Button>
        </header>
    );
}
