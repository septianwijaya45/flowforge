export type UserRole = 'admin' | 'editor' | 'viewer';

export function canWrite(role: UserRole | string | undefined): boolean {
    return role === 'admin' || role === 'editor';
}

export function isViewer(role: UserRole | string | undefined): boolean {
    return role === 'viewer';
}
