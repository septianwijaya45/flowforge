import type { WorkflowDefinition } from '@/modules/workflow/types/workflow-graph';

const HORIZONTAL_GAP = 280;
const VERTICAL_GAP = 140;
const ORIGIN_X = 80;
const ORIGIN_Y = 80;

export function applyDefinitionLayout(definition: WorkflowDefinition): WorkflowDefinition {
    const needsLayout = definition.nodes.some((node) => !node.position);

    if (!needsLayout) {
        return definition;
    }

    const layers = computeLayers(definition);
    const layerCounts = new Map<number, number>();

    const nodes = definition.nodes.map((node) => {
        if (node.position) {
            return node;
        }

        const layer = layers.get(node.id) ?? 0;
        const indexInLayer = layerCounts.get(layer) ?? 0;
        layerCounts.set(layer, indexInLayer + 1);

        return {
            ...node,
            position: {
                x: ORIGIN_X + layer * HORIZONTAL_GAP,
                y: ORIGIN_Y + indexInLayer * VERTICAL_GAP,
            },
        };
    });

    return {
        ...definition,
        nodes,
    };
}

function computeLayers(definition: WorkflowDefinition): Map<string, number> {
    const adjacency = new Map<string, string[]>();

    for (const node of definition.nodes) {
        adjacency.set(node.id, []);
    }

    for (const edge of definition.edges) {
        adjacency.get(edge.source)?.push(edge.target);
    }

    const layers = new Map<string, number>();
    const queue = [definition.entry_node_id];
    layers.set(definition.entry_node_id, 0);

    while (queue.length > 0) {
        const current = queue.shift()!;
        const currentLayer = layers.get(current) ?? 0;

        for (const target of adjacency.get(current) ?? []) {
            const nextLayer = currentLayer + 1;
            const existingLayer = layers.get(target);

            if (existingLayer === undefined || nextLayer > existingLayer) {
                layers.set(target, nextLayer);
                queue.push(target);
            }
        }
    }

    for (const node of definition.nodes) {
        if (!layers.has(node.id)) {
            layers.set(node.id, 0);
        }
    }

    return layers;
}
