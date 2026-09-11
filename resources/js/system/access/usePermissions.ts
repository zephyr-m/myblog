import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();

    return {
        can: (permission: string): boolean =>
            page.props.auth.permissions.includes('*') ||
            page.props.auth.permissions.includes(permission),
    };
}
