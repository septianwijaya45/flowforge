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
            <aside className="flex w-72 shrink-0 flex-col border-l bg-muted/20 p-4">
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

            <p className="text-xs text-muted-foreground">ID: {node.id}</p>
        </aside>
    );
}
