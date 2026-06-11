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
    onSave: (definition: WorkflowDefinition) => void;
    onValidationError: (message: string) => void;
}

function WorkflowCanvasInner({
    workflow,
    initialDefinition,
    versionNumber,
    isSaving,
    onSave,
    onValidationError,
}: WorkflowCanvasProps) {
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

    const handleSave = () => {
        try {
            onSave(flowToDefinition(nodes, edges));
        } catch (error) {
            const message =
                error instanceof GraphSerializationError
                    ? error.message
                    : 'Unable to serialize workflow graph.';

            onValidationError(message);
        }
    };

    return (
        <div className="flex h-full min-h-[600px] flex-col">
            <BuilderToolbar
                workflowName={workflow.name}
                versionNumber={versionNumber}
                isDirty={isDirty}
                isSaving={isSaving}
                onSave={handleSave}
            />

            <div className="flex min-h-0 flex-1">
                <NodePalette />

                <div className="min-w-0 flex-1">
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
                        className="bg-background"
                    >
                        <Background gap={16} size={1} />
                        <Controls />
                        <MiniMap zoomable pannable />
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
