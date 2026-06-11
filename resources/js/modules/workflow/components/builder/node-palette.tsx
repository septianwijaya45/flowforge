import { DRAG_NODE_TYPE_KEY, nodeTypeDefinitions } from '@/modules/workflow/constants/node-types';
import type { WorkflowNodeType } from '@/modules/workflow/types/workflow-graph';

export function NodePalette() {
    const onDragStart = (event: React.DragEvent, type: WorkflowNodeType) => {
        event.dataTransfer.setData(DRAG_NODE_TYPE_KEY, type);
        event.dataTransfer.effectAllowed = 'move';
    };

    return (
        <aside className="flex w-56 shrink-0 flex-col gap-2 border-r bg-muted/30 p-4">
            <div>
                <h3 className="text-sm font-semibold">Nodes</h3>
                <p className="text-xs text-muted-foreground">Drag onto the canvas</p>
            </div>

            <ul className="flex flex-col gap-2">
                {nodeTypeDefinitions.map((definition) => {
                    const Icon = definition.icon;

                    return (
                        <li key={definition.type}>
                            <button
                                type="button"
                                draggable
                                onDragStart={(event) => onDragStart(event, definition.type)}
                                className="flex w-full cursor-grab items-start gap-3 rounded-lg border bg-card p-3 text-left shadow-xs transition hover:border-primary/40 active:cursor-grabbing"
                            >
                                <Icon className="mt-0.5 size-4 shrink-0 text-primary" />
                                <span>
                                    <span className="block text-sm font-medium">{definition.label}</span>
                                    <span className="block text-xs text-muted-foreground">
                                        {definition.description}
                                    </span>
                                </span>
                            </button>
                        </li>
                    );
                })}
            </ul>
        </aside>
    );
}
