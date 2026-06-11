export type UserRole = 'admin' | 'editor' | 'viewer';

export interface User {
    uuid: string;
    name: string;
    email: string;
    role: UserRole;
}
