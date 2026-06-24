import { useAuth } from './useAuth';

export function usePermission() {
    const { user } = useAuth();

    const can = (permission: string): boolean => {
        return user.value?.permissions?.some(
            (p: any) => p.name === permission
        ) ?? false;
    };

    const canAny = (...permissions: string[]): boolean => {
        return permissions.some(p => can(p));
    };

    return { can, canAny };
}
