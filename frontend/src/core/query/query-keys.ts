export function createQueryKeys<T extends string>(scope: T) {
    return {
        all: [scope] as const,
        lists: () => [scope, 'list'] as const,
        list: <F extends object>(filters: F) => [scope, 'list', filters] as const,
        details: () => [scope, 'detail'] as const,
        detail: (id: string) => [scope, 'detail', id] as const,
    };
}
