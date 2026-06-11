import { cn } from '@/lib/utils';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { nodeTypeMap } from '@/modules/workflow/constants/node-types';
import type { BuilderNode } from '@/modules/workflow/utils/graph-serializer';

interface NodeConfigPanelProps {
    node: BuilderNode | null;
    onUpdate: (nodeId: string, updates: Partial<BuilderNode['data']>) => void;
}

export function NodeConfigPanel({ node, onUpdate }: NodeConfigPanelProps) {
    if (!node) {
        return (
            <aside className="flex w-72 shrink-0 flex-col overflow-y-auto border-l bg-muted/20 p-4">
                <p className="text-sm text-muted-foreground">
                    Select a node to edit its configuration.
                </p>
            </aside>
        );
    }

    const definition = nodeTypeMap[node.data.nodeType];
    const config = node.data.config;

    const updateConfig = (key: string, value: unknown) => {
        onUpdate(node.id, {
            config: { ...config, [key]: value },
        });
    };

    return (
        <aside className="flex w-72 shrink-0 flex-col gap-4 overflow-y-auto border-l bg-muted/20 p-4">
            <div>
                <h3 className="text-sm font-semibold">{definition.label}</h3>
                <p className="text-xs text-muted-foreground">Node configuration</p>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="node-label">Label</Label>
                <Input
                    id="node-label"
                    value={node.data.label}
                    onChange={(event) => onUpdate(node.id, { label: event.target.value })}
                />
            </div>

            {node.data.nodeType === 'http' ? (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="http-url">URL</Label>
                        <Input
                            id="http-url"
                            value={String(config.url ?? '')}
                            onChange={(event) => updateConfig('url', event.target.value)}
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label>Method</Label>
                        <Select
                            value={String(config.method ?? 'GET')}
                            onValueChange={(value) => updateConfig('method', value)}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {['GET', 'POST', 'PUT', 'PATCH', 'DELETE'].map((method) => (
                                    <SelectItem key={method} value={method}>
                                        {method}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="http-timeout">Timeout (seconds)</Label>
                        <Input
                            id="http-timeout"
                            type="number"
                            min={1}
                            value={Number(config.timeout ?? 30)}
                            onChange={(event) => updateConfig('timeout', Number(event.target.value))}
                        />
                    </div>
                </>
            ) : null}

            {node.data.nodeType === 'delay' ? (
                <div className="grid gap-2">
                    <Label htmlFor="delay-seconds">Seconds</Label>
                    <Input
                        id="delay-seconds"
                        type="number"
                        min={0}
                        value={Number(config.seconds ?? 0)}
                        onChange={(event) => updateConfig('seconds', Number(event.target.value))}
                    />
                </div>
            ) : null}

            {node.data.nodeType === 'condition' ? (
                <div className="grid gap-2">
                    <Label>Operator</Label>
                    <Select
                        value={String(config.operator ?? 'truthy')}
                        onValueChange={(value) => updateConfig('operator', value)}
                    >
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="truthy">Truthy</SelectItem>
                            <SelectItem value="equals">Equals</SelectItem>
                            <SelectItem value="contains">Contains</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            ) : null}

            {node.data.nodeType === 'email' ? (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="email-to">To</Label>
                        <Input
                            id="email-to"
                            value={String(config.to ?? '')}
                            onChange={(event) => updateConfig('to', event.target.value)}
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="email-subject">Subject</Label>
                        <Input
                            id="email-subject"
                            value={String(config.subject ?? '')}
                            onChange={(event) => updateConfig('subject', event.target.value)}
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="email-body">Body</Label>
                        <textarea
                            id="email-body"
                            rows={4}
                            value={String(config.body ?? '')}
                            onChange={(event) => updateConfig('body', event.target.value)}
                            className={cn(
                                'border-input placeholder:text-muted-foreground flex min-h-[80px] w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none',
                                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            )}
                        />
                        <p className="text-xs text-muted-foreground">
                            Use {'{{node_id.field}}'} to insert values from prior steps.
                        </p>
                    </div>
                </>
            ) : null}

            {node.data.nodeType === 'database' ? (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="db-query">Query</Label>
                        <textarea
                            id="db-query"
                            rows={4}
                            value={String(config.query ?? '')}
                            onChange={(event) => updateConfig('query', event.target.value)}
                            className={cn(
                                'border-input placeholder:text-muted-foreground flex min-h-[80px] w-full rounded-md border bg-transparent px-3 py-2 font-mono text-sm shadow-xs outline-none',
                                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            )}
                        />
                        <p className="text-xs text-muted-foreground">Read-only SELECT queries only.</p>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="db-connection">Connection (optional)</Label>
                        <Input
                            id="db-connection"
                            placeholder="default"
                            value={String(config.connection ?? '')}
                            onChange={(event) => updateConfig('connection', event.target.value || null)}
                        />
                    </div>
                </>
            ) : null}

            {node.data.nodeType === 'webhook' ? (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="webhook-url">URL</Label>
                        <Input
                            id="webhook-url"
                            value={String(config.url ?? '')}
                            onChange={(event) => updateConfig('url', event.target.value)}
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="webhook-payload-path">Payload path (optional)</Label>
                        <Input
                            id="webhook-payload-path"
                            placeholder="previous_node.body"
                            value={String(config.payload_path ?? '')}
                            onChange={(event) =>
                                updateConfig('payload_path', event.target.value || null)
                            }
                        />
                        <p className="text-xs text-muted-foreground">
                            Leave empty to send the full execution context.
                        </p>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="webhook-timeout">Timeout (seconds)</Label>
                        <Input
                            id="webhook-timeout"
                            type="number"
                            min={1}
                            value={Number(config.timeout ?? 30)}
                            onChange={(event) => updateConfig('timeout', Number(event.target.value))}
                        />
                    </div>
                </>
            ) : null}

            <p className="text-xs text-muted-foreground">ID: {node.id}</p>
        </aside>
    );
}
