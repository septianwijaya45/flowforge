import '@xyflow/react/dist/style.css';

import { Background, Controls, MiniMap, ReactFlow, ReactFlowProvider } from '@xyflow/react';
import { useMemo } from 'react';

import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { WorkflowNode } from '@/modules/workflow/components/builder/workflow-node';
import type { WorkflowDefinition } from '@/modules/workflow/types/workflow-graph';
import { applyDefinitionLayout } from '@/modules/ai/utils/apply-definition-layout';
import { definitionToFlow } from '@/modules/workflow/utils/graph-serializer';

const nodeTypes = { workflow: WorkflowNode };

interface WorkflowDagPreviewProps {
    definition: WorkflowDefinition;
    className?: string;
}

function WorkflowDagPreviewInner({ definition, className }: WorkflowDagPreviewProps) {
    const { resolvedAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';

    const { nodes, edges } = useMemo(() => {
        const layoutDefinition = applyDefinitionLayout(definition);

        return definitionToFlow(layoutDefinition);
    }, [definition]);

    return (
        <div className={cn('relative min-h-[360px] overflow-hidden rounded-lg border', className)}>
            <ReactFlow
                nodes={nodes}
                edges={edges}
                nodeTypes={nodeTypes}
                nodesDraggable={false}
                nodesConnectable={false}
                elementsSelectable={false}
                panOnDrag
                zoomOnScroll
                fitView
                fitViewOptions={{ padding: 0.2 }}
                proOptions={{ hideAttribution: true }}
                className={cn('h-full w-full bg-background', isDark && 'dark')}
            >
                <Background gap={16} size={1} />
                <Controls showInteractive={false} className="rounded-md border border-border bg-card shadow-sm" />
                <MiniMap
                    zoomable
                    pannable
                    className="rounded-md border border-border bg-card shadow-sm"
                    style={{ width: 130, height: 90 }}
                    nodeColor="var(--muted-foreground)"
                    maskColor={
                        isDark ? 'rgba(20, 20, 20, 0.65)' : 'rgba(240, 240, 240, 0.65)'
                    }
                />
            </ReactFlow>
        </div>
    );
}

export function WorkflowDagPreview(props: WorkflowDagPreviewProps) {
    return (
        <ReactFlowProvider>
            <WorkflowDagPreviewInner {...props} />
        </ReactFlowProvider>
    );
}
