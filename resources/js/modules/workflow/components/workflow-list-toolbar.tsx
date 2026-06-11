import { Search } from 'lucide-react';

import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { workflowStatusOptions } from '@/modules/workflow/constants/workflow-status';
import type { WorkflowStatus } from '@/modules/workflow/types/workflow';

interface WorkflowListToolbarProps {
    search: string;
    status: WorkflowStatus | '';
    onSearchChange: (value: string) => void;
    onStatusChange: (value: WorkflowStatus | '') => void;
}

export function WorkflowListToolbar({
    search,
    status,
    onSearchChange,
    onStatusChange,
}: WorkflowListToolbarProps) {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div className="relative flex-1">
                <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    value={search}
                    onChange={(event) => onSearchChange(event.target.value)}
                    placeholder="Search workflows by name or slug…"
                    className="pl-9"
                />
            </div>

            <Select
                value={status || 'all'}
                onValueChange={(value) =>
                    onStatusChange(value === 'all' ? '' : (value as WorkflowStatus))
                }
            >
                <SelectTrigger className="w-full sm:w-44">
                    <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                    {workflowStatusOptions.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
