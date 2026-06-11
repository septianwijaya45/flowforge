import { Clock, Code, Database, GitBranch, Globe, Mail, Webhook } from 'lucide-react';
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
    {
        type: 'email',
        label: 'Send Email',
        description: 'Send an email notification',
        icon: Mail,
        defaultConfig: {
            label: 'Send Email',
            to: 'user@example.com',
            subject: 'Workflow notification',
            body: 'Hello from FlowForge.',
        },
    },
    {
        type: 'database',
        label: 'Database Query',
        description: 'Run a read-only SELECT query',
        icon: Database,
        defaultConfig: {
            label: 'Database Query',
            query: 'SELECT 1 AS value',
            bindings: [],
        },
    },
    {
        type: 'webhook',
        label: 'Webhook',
        description: 'POST data to an external webhook URL',
        icon: Webhook,
        defaultConfig: {
            label: 'Webhook',
            url: 'https://example.com/webhook',
            payload_path: null,
            timeout: 30,
        },
    },
];

export const nodeTypeMap = Object.fromEntries(
    nodeTypeDefinitions.map((definition) => [definition.type, definition]),
) as Record<WorkflowNodeType, NodeTypeDefinition>;

export const DRAG_NODE_TYPE_KEY = 'application/flowforge-node-type';
