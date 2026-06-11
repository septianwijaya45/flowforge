import { PageHeader } from '@/shared/components/page-header';

export function AiAssistantPage() {
    return (
        <div className="flex flex-col gap-6 p-6">
            <PageHeader
                title="AI Assistant"
                description="Generate workflow ideas and explain execution steps."
            />
        </div>
    );
}
