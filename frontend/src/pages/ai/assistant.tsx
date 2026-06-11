import { Head } from '@inertiajs/react';

import { AiAssistantPage } from '@/modules/ai/pages/ai-assistant-page';

export default function AiAssistant() {
    return (
        <>
            <Head title="AI Assistant" />
            <AiAssistantPage />
        </>
    );
}

AiAssistant.layout = {
    breadcrumbs: [{ title: 'AI Assistant', href: '/ai/assistant' }],
};
