import { Handle, Position } from '@xyflow/react';
import type { NodeProps } from '@xyflow/react';

import { cn } from '@/lib/utils';
import { nodeTypeMap } from '@/modules/workflow/constants/node-types';
import type { BuilderNode } from '@/modules/workflow/utils/graph-serializer';

export function WorkflowNode({ data, selected }: NodeProps<BuilderNode>) {
    const definition = nodeTypeMap[data.nodeType];
    const Icon = definition.icon;
    const isCondition = data.nodeType === 'condition';

    return (
        <div
            className={cn(
                'min-w-[200px] rounded-lg border bg-card px-3 py-2 shadow-sm',
                selected && 'ring-2 ring-primary',
            )}
        >
            <Handle
                type="target"
                position={Position.Left}
                className="!size-2.5 !border-2 !border-background !bg-primary"
            />

            <div className="flex items-center gap-2">
                <div className="flex size-8 items-center justify-center rounded-md bg-muted">
                    <Icon className="size-4 text-primary" />
                </div>
                <div className="min-w-0">
                    <p className="truncate text-sm font-medium">{data.label}</p>
                    <p className="text-xs text-muted-foreground">{definition.label}</p>
                </div>
            </div>

            {isCondition ? (
                <>
                    <Handle
                        type="source"
                        position={Position.Right}
                        id="true"
                        style={{ top: '30%' }}
                        className="!size-2.5 !border-2 !border-background !bg-green-600"
                    />
                    <Handle
                        type="source"
                        position={Position.Right}
                        id="false"
                        style={{ top: '70%' }}
                        className="!size-2.5 !border-2 !border-background !bg-red-600"
                    />
                    <div className="mt-2 flex justify-end gap-6 pr-1 text-[10px] text-muted-foreground">
                        <span>true</span>
                        <span>false</span>
                    </div>
                </>
            ) : (
                <Handle
                    type="source"
                    position={Position.Right}
                    className="!size-2.5 !border-2 !border-background !bg-primary"
                />
            )}
        </div>
    );
}
