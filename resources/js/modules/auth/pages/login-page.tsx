import { PageHeader } from '@/shared/components/page-header';

export function LoginPage() {
    return (
        <div className="mx-auto w-full max-w-md p-6">
            <PageHeader
                title="Sign in"
                description="Access your FlowForge workspace."
            />
        </div>
    );
}
