export type UserRole = 'admin' | 'member' | 'viewer';

export interface User {
    uuid: string;
    name: string;
    email: string;
    role: UserRole;
}
