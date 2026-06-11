import { ChevronLeft, ChevronRight } from 'lucide-react';

import { Button } from '@/components/ui/button';
import type { PaginationMeta } from '@/core/api/types/api-response';

interface WorkflowListPaginationProps {
    pagination: PaginationMeta;
    onPageChange: (page: number) => void;
}

export function WorkflowListPagination({
    pagination,
    onPageChange,
}: WorkflowListPaginationProps) {
    const { current_page, last_page, total, per_page } = pagination;
    const from = total === 0 ? 0 : (current_page - 1) * per_page + 1;
    const to = Math.min(current_page * per_page, total);

    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-sm text-muted-foreground">
                Showing {from}–{to} of {total} workflow{total === 1 ? '' : 's'}
            </p>

            <div className="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(current_page - 1)}
                    disabled={current_page <= 1}
                >
                    <ChevronLeft className="size-4" />
                    Previous
                </Button>
                <span className="text-sm text-muted-foreground">
                    Page {current_page} of {last_page || 1}
                </span>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(current_page + 1)}
                    disabled={current_page >= last_page}
                >
                    Next
                    <ChevronRight className="size-4" />
                </Button>
            </div>
        </div>
    );
}
