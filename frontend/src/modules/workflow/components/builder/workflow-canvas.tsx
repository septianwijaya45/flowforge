import '@xyflow/react/dist/style.css';

import {
    Background,
    Controls,
    MiniMap,
    ReactFlow,
    ReactFlowProvider,
    addEdge,
    useEdgesState,
    useNodesState,
    useReactFlow
    
} from '@xyflow/react';
import type {Connection} from '@xyflow/react';
import { useCallback, useMemo, useState } from 'react';

import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { BuilderToolbar } from '@/modules/workflow/components/builder/builder-toolbar';
import { NodeConfigPanel } from '@/modules/workflow/components/builder/node-config-panel';
import { NodePalette } from '@/modules/workflow/components/builder/node-palette';
import { WorkflowNode } from '@/modules/workflow/components/builder/workflow-node';
import { DRAG_NODE_TYPE_KEY, nodeTypeMap } from '@/modules/workflow/constants/node-types';
import type { Workflow } from '@/modules/workflow/types/workflow';
import type { WorkflowDefinition } from '@/modules/workflow/types/workflow-graph';
import type { WorkflowNodeType } from '@/modules/workflow/types/workflow-graph';
import {
    createBuilderNode,
    createEdgeId,
    definitionToFlow,
    flowToDefinition,
    GraphSerializationError,
    isValidConnection
    
    
} from '@/modules/workflow/utils/graph-serializer';
import type {BuilderEdge, BuilderNode} from '@/modules/workflow/utils/graph-serializer';

const nodeTypes = { workflow: WorkflowNode };

interface WorkflowCanvasProps {
    workflow: Workflow;
    initialDefinition: WorkflowDefinition;
    versionNumber?: number;
    isSaving: boolean;
    isRunning?: boolean;
    canRun?: boolean;
    onSave: (definition: WorkflowDefinition) => void;
    onRun?: (definition: WorkflowDefinition, needsSave: boolean) => void;
    onValidationError: (message: string) => void;
}

function WorkflowCanvasInner({
    workflow,
    initialDefinition,
    versionNumber,
    isSaving,
    isRunning = false,
    canRun = true,
    onSave,
    onRun,
    onValidationError,
}: WorkflowCanvasProps) {
    const { resolvedAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';
    const { screenToFlowPosition } = useReactFlow();
    const initialFlow = useMemo(() => definitionToFlow(initialDefinition), [initialDefinition]);
    const [nodes, setNodes, onNodesChange] = useNodesState<BuilderNode>(initialFlow.nodes);
    const [edges, setEdges, onEdgesChange] = useEdgesState<BuilderEdge>(initialFlow.edges);
    const [selectedNodeId, setSelectedNodeId] = useState<string | null>(null);

    const [isDirty, setIsDirty] = useState(false);

    const selectedNode = nodes.find((node) => node.id === selectedNodeId) ?? null;

    const onConnect = useCallback(
        (connection: Connection) => {
            if (!isValidConnection(connection, nodes)) {
                return;
            }

            setIsDirty(true);
            setEdges((currentEdges) =>
                addEdge(
                    {
                        ...connection,
                        id: createEdgeId(
                            connection.source!,
                            connection.target!,
                            connection.sourceHandle,
                        ),
                    },
                    currentEdges,
                ),
            );
        },
        [nodes, setEdges],
    );

    const onDragOver = useCallback((event: React.DragEvent) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }, []);

    const onDrop = useCallback(
        (event: React.DragEvent) => {
            event.preventDefault();

            const type = event.dataTransfer.getData(DRAG_NODE_TYPE_KEY) as WorkflowNodeType;
            const definition = nodeTypeMap[type];

            if (!definition) {
                return;
            }

            const position = screenToFlowPosition({
                x: event.clientX,
                y: event.clientY,
            });

            setIsDirty(true);
            setNodes((currentNodes) => [
                ...currentNodes,
                createBuilderNode(type, position, definition.defaultConfig),
            ]);
        },
        [screenToFlowPosition, setNodes],
    );

    const updateNodeData = useCallback(
        (nodeId: string, updates: Partial<BuilderNode['data']>) => {
            setIsDirty(true);
            setNodes((currentNodes) =>
                currentNodes.map((node) =>
                    node.id === nodeId
                        ? {
                              ...node,
                              data: {
                                  ...node.data,
                                  ...updates,
                                  config: updates.config
                                      ? { ...node.data.config, ...updates.config }
                                      : node.data.config,
                              },
                          }
                        : node,
                ),
            );
        },
        [setNodes],
    );

    const serializeDefinition = (): WorkflowDefinition | null => {
        try {
            return flowToDefinition(nodes, edges);
        } catch (error) {
            const message =
                error instanceof GraphSerializationError
                    ? error.message
                    : 'Unable to serialize workflow graph.';

            onValidationError(message);

            return null;
        }
    };

    const handleSave = () => {
        const definition = serializeDefinition();

        if (definition) {
            onSave(definition);
        }
    };

    const handleRun = () => {
        if (!onRun) {
            return;
        }

        const definition = serializeDefinition();

        if (definition) {
            onRun(definition, isDirty);
        }
    };

    return (
        <div className="flex h-full min-h-0 flex-col overflow-hidden">
            <BuilderToolbar
                workflowName={workflow.name}
                versionNumber={versionNumber}
                isDirty={isDirty}
                isSaving={isSaving}
                isRunning={isRunning}
                canRun={canRun}
                onSave={handleSave}
                onRun={onRun ? handleRun : undefined}
            />

            <div className="flex min-h-0 min-w-0 flex-1 overflow-hidden">
                <NodePalette />

                <div className="relative min-h-0 min-w-0 flex-1 overflow-hidden">
                    <ReactFlow
                        nodes={nodes}
                        edges={edges}
                        nodeTypes={nodeTypes}
                        onNodesChange={(changes) => {
                            if (changes.some((change) => change.type !== 'select')) {
                                setIsDirty(true);
                            }

                            onNodesChange(changes);
                        }}
                        onEdgesChange={(changes) => {
                            if (changes.length > 0) {
                                setIsDirty(true);
                            }

                            onEdgesChange(changes);
                        }}
                        onConnect={onConnect}
                        onDrop={onDrop}
                        onDragOver={onDragOver}
                        onNodeClick={(_, node) => setSelectedNodeId(node.id)}
                        onPaneClick={() => setSelectedNodeId(null)}
                        isValidConnection={(connection) => isValidConnection(connection, nodes)}
                        fitView
                        deleteKeyCode={['Backspace', 'Delete']}
                        className={cn('h-full w-full bg-background', isDark && 'dark')}
                    >
                        <Background gap={16} size={1} />
                        <Controls
                            showInteractive={false}
                            className="overflow-hidden rounded-md border border-border bg-card shadow-sm"
                        />
                        <MiniMap
                            zoomable
                            pannable
                            className="overflow-hidden rounded-md border border-border bg-card shadow-sm"
                            style={{ width: 150, height: 110 }}
                            nodeColor="var(--muted-foreground)"
                            maskColor={
                                isDark ? 'rgba(20, 20, 20, 0.65)' : 'rgba(240, 240, 240, 0.65)'
                            }
                        />
                    </ReactFlow>
                </div>

                <NodeConfigPanel node={selectedNode} onUpdate={updateNodeData} />
            </div>
        </div>
    );
}

export function WorkflowCanvas(props: WorkflowCanvasProps) {
    return (
        <ReactFlowProvider>
            <WorkflowCanvasInner {...props} />
        </ReactFlowProvider>
    );
}
