import { Clock, Code, GitBranch, Globe } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

import type { WorkflowNodeType } from '@/modules/workflow/types/workflow-graph';

export interface NodeTypeDefinition {
    type: WorkflowNodeType;
    label: string;
    description: string;
    icon: LucideIcon;
    defaultConfig: Record<string, unknown>;
}

export const nodeTypeDefinitions: NodeTypeDefinition[] = [
    {
        type: 'http',
        label: 'HTTP Request',
        description: 'Call an external HTTP endpoint',
        icon: Globe,
        defaultConfig: {
            label: 'HTTP Request',
            url: 'https://example.com',
            method: 'GET',
            timeout: 30,
        },
    },
    {
        type: 'delay',
        label: 'Delay',
        description: 'Pause execution for a number of seconds',
        icon: Clock,
        defaultConfig: {
            label: 'Delay',
            seconds: 5,
        },
    },
    {
        type: 'condition',
        label: 'Condition',
        description: 'Branch execution based on a boolean result',
        icon: GitBranch,
        defaultConfig: {
            label: 'Condition',
            operator: 'truthy',
            path: null,
        },
    },
    {
        type: 'script',
        label: 'Script',
        description: 'Run a lightweight script step',
        icon: Code,
        defaultConfig: {
            label: 'Script',
        },
    },
];

export const nodeTypeMap = Object.fromEntries(
    nodeTypeDefinitions.map((definition) => [definition.type, definition]),
) as Record<WorkflowNodeType, NodeTypeDefinition>;

export const DRAG_NODE_TYPE_KEY = 'application/flowforge-node-type';
