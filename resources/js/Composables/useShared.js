import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Central access to shared Inertia props:
 * - auth user / roles / permissions
 * - hotel settings (currency, tax, loyalty)
 * - notifications meta
 */
export function useShared() {
    const page = usePage();

    const auth = computed(() => page.props.auth || {});
    const user = computed(() => auth.value.user || null);
    const roles = computed(() => auth.value.roles || []);
    const permissions = computed(() => auth.value.permissions || []);

    const settings = computed(() => page.props.settings || {});
    const hotelSettings = computed(() => page.props.hotelSettings || {});

    const currency = computed(() => hotelSettings.value.currency || {});
    const tax = computed(() => hotelSettings.value.tax || {});
    const loyalty = computed(() => hotelSettings.value.loyalty || {});

    const notifications = computed(() => page.props.notifications || { unread_count: 0 });

    const hasRole = (role) => {
        return roles.value.includes(role);
    };

    const can = (permission) => {
        return permissions.value.includes(permission);
    };

    return {
        page,
        auth,
        user,
        roles,
        permissions,
        settings,
        hotelSettings,
        currency,
        tax,
        loyalty,
        notifications,
        hasRole,
        can,
    };
}

