import { Sparkles } from 'lucide-react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';

import { useAuth } from '@/app/providers/auth-provider';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { canWrite } from '@/core/auth/permissions';
import { cn } from '@/lib/utils';
import { ImportWorkflowDialog } from '@/modules/ai/components/import-workflow-dialog';
import { WorkflowDagPreview } from '@/modules/ai/components/workflow-dag-preview';
import { useGenerateWorkflow } from '@/modules/ai/hooks/use-generate-workflow';
import type { GeneratedWorkflowResult } from '@/modules/ai/types/generated-workflow';
import { PageHeader } from '@/shared/components/page-header';

const EXAMPLE_PROMPT =
    'Every hour check website. If status code is 500 send email.';

function suggestWorkflowName(prompt: string): string {
    const trimmed = prompt.trim();

    if (!trimmed) {
        return 'AI Generated Workflow';
    }

    const firstSentence = trimmed.split(/[.!?\n]/)[0]?.trim() ?? trimmed;

    return firstSentence.length > 60 ? `${firstSentence.slice(0, 57)}...` : firstSentence;
}

export function AiWorkflowGeneratorPage() {
    const { user } = useAuth();
    const userCanWrite = canWrite(user?.role as string | undefined);

    const [prompt, setPrompt] = useState('');
    const [result, setResult] = useState<GeneratedWorkflowResult | null>(null);
    const [importOpen, setImportOpen] = useState(false);

    const generateWorkflow = useGenerateWorkflow();

    const suggestedName = useMemo(
        () => suggestWorkflowName(result ? prompt : prompt || EXAMPLE_PROMPT),
        [prompt, result],
    );

    const handleGenerate = () => {
        const trimmedPrompt = prompt.trim();

        if (trimmedPrompt.length < 3) {
            toast.error('Prompt too short', {
                description: 'Describe your automation in at least a few words.',
            });

            return;
        }

        generateWorkflow.mutate(
            { prompt: trimmedPrompt },
            {
                onSuccess: (generated) => {
                    setResult(generated);
                    toast.success('Workflow generated', {
                        description: `Validated in ${generated.attempts} attempt${generated.attempts === 1 ? '' : 's'} via ${generated.provider}.`,
                    });
                },
                onError: (error) => {
                    toast.error('Generation failed', { description: error.message });
                },
            },
        );
    };

    return (
        <div className="flex flex-col gap-6 p-4 md:p-6">
            <PageHeader
                title="AI Workflow Generator"
                description="Describe an automation in plain language and preview the generated DAG before importing it into the builder."
            />

            {!userCanWrite ? (
                <Alert>
                    <AlertTitle>View only</AlertTitle>
                    <AlertDescription>
                        You need editor or admin access to generate and import workflows.
                    </AlertDescription>
                </Alert>
            ) : null}

            <div className="grid gap-6 lg:grid-cols-[minmax(0,380px)_1fr]">
                <Card>
                    <CardHeader>
                        <CardTitle>Prompt</CardTitle>
                        <CardDescription>
                            Describe triggers, steps, conditions, and actions. Scheduling hints are
                            extracted separately from the graph.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        <textarea
                            value={prompt}
                            onChange={(event) => setPrompt(event.target.value)}
                            placeholder={EXAMPLE_PROMPT}
                            rows={10}
                            disabled={!userCanWrite || generateWorkflow.isPending}
                            className={cn(
                                'border-input placeholder:text-muted-foreground min-h-[220px] w-full resize-y rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none',
                                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                'disabled:cursor-not-allowed disabled:opacity-50',
                            )}
                        />

                        <div className="flex flex-wrap items-center gap-2">
                            <Button
                                onClick={handleGenerate}
                                disabled={!userCanWrite || generateWorkflow.isPending || !prompt.trim()}
                            >
                                {generateWorkflow.isPending ? <Spinner /> : <Sparkles />}
                                Generate
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                disabled={!userCanWrite || generateWorkflow.isPending}
                                onClick={() => setPrompt(EXAMPLE_PROMPT)}
                            >
                                Use example
                            </Button>
                        </div>

                        {generateWorkflow.isError ? (
                            <Alert variant="destructive">
                                <AlertTitle>Generation failed</AlertTitle>
                                <AlertDescription>
                                    {generateWorkflow.error.message}
                                </AlertDescription>
                            </Alert>
                        ) : null}
                    </CardContent>
                </Card>

                <Card className="min-h-[480px]">
                    <CardHeader className="flex flex-row items-start justify-between gap-4">
                        <div>
                            <CardTitle>DAG Preview</CardTitle>
                            <CardDescription>
                                Read-only preview of the validated workflow graph.
                            </CardDescription>
                        </div>
                        {result ? (
                            <div className="flex flex-wrap items-center gap-2">
                                {result.schedule?.cron ? (
                                    <Badge variant="secondary">
                                        {result.schedule.description ?? result.schedule.cron}
                                    </Badge>
                                ) : null}
                                <Badge variant="outline">{result.provider}</Badge>
                            </div>
                        ) : null}
                    </CardHeader>
                    <CardContent className="flex h-full flex-col gap-4">
                        {result ? (
                            <>
                                <WorkflowDagPreview
                                    definition={result.definition}
                                    className="min-h-[380px] flex-1"
                                />
                                <div className="flex justify-end">
                                    <Button onClick={() => setImportOpen(true)} disabled={!userCanWrite}>
                                        Import into Workflow Builder
                                    </Button>
                                </div>
                            </>
                        ) : (
                            <div className="flex min-h-[380px] flex-1 items-center justify-center rounded-lg border border-dashed bg-muted/30 p-6 text-center text-sm text-muted-foreground">
                                {generateWorkflow.isPending
                                    ? 'Generating workflow...'
                                    : 'Generate a workflow to preview the DAG here.'}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {result ? (
                <ImportWorkflowDialog
                    open={importOpen}
                    onOpenChange={setImportOpen}
                    definition={result.definition}
                    suggestedName={suggestedName}
                />
            ) : null}
        </div>
    );
}
