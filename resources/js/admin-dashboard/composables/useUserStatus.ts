import { ref, computed } from 'vue';
import { userService } from '../services/userService';
import { alertService } from '../utils/sweetalert';

/**
 * useUserStatus.ts
 * Composable to manage user account status and governance history.
 */
export function useUserStatus(userId: number) {
    const user = ref<any>(null);
    const history = ref<any[]>([]);
    const isLoading = ref(true);
    const isSubmitting = ref(false);

    // Filter/Form state
    const banType = ref('permanent');
    const banReason = ref('');
    const deactivateReason = ref('');
    const activateReason = ref('');
    const expiredAt = ref('');
    const unbanReason = ref('');

    const fetchHistory = async () => {
        try {
            isLoading.value = true;
            const response = await userService.getBanHistory(userId);
            user.value = response.data.user;
            history.value = response.data.history;
        } catch (err: any) {
            alertService.errorToast(
                'Failed to load data',
                err.response?.data?.message || 'Please try again later.'
            );
        } finally {
            isLoading.value = false;
        }
    };

    const handleBan = async () => {
        try {
            isSubmitting.value = true;
            await userService.ban(userId, {
                type: banType.value,
                reason: banReason.value,
                expired_at: banType.value === 'temporary' ? expiredAt.value : undefined
            });

            alertService.successToast('User has been banned successfully.');
            banReason.value = '';
            expiredAt.value = '';
            await fetchHistory();
        } catch (err: any) {
            alertService.errorToast(
                'Ban failed',
                err.response?.data?.message || 'Check your inputs and try again.'
            );
        } finally {
            isSubmitting.value = false;
        }
    };

    const handleUnban = async () => {
        try {
            isSubmitting.value = true;
            await userService.unban(userId, {
                reason: unbanReason.value
            });

            alertService.successToast('User status restored to Active.');
            unbanReason.value = '';
            await fetchHistory();
        } catch (err: any) {
            alertService.errorToast(
                'Restore failed',
                err.response?.data?.message || 'Check your inputs and try again.'
            );
        } finally {
            isSubmitting.value = false;
        }
    };

    const handleDeactivate = async () => {
        try {
            isSubmitting.value = true;
            await userService.deactivate(userId, { reason: deactivateReason.value });
            alertService.successToast('User account has been deactivated.');
            deactivateReason.value = '';
            await fetchHistory();
        } catch (err: any) {
            alertService.errorToast(
                'Deactivation failed',
                err.response?.data?.message || 'An error occurred.'
            );
        } finally {
            isSubmitting.value = false;
        }
    };

    const handleActivate = async () => {
        try {
            isSubmitting.value = true;
            await userService.activate(userId, { reason: activateReason.value });
            alertService.successToast('User account has been activated.');
            activateReason.value = '';
            await fetchHistory();
        } catch (err: any) {
            alertService.errorToast(
                'Activation failed',
                err.response?.data?.message || 'An error occurred.'
            );
        } finally {
            isSubmitting.value = false;
        }
    };

    const sortedHistory = computed(() => {
        return [...history.value].sort((a, b) => 
            new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
        );
    });

    return {
        // State
        user,
        history,
        isLoading,
        isSubmitting,
        banType,
        banReason,
        deactivateReason,
        activateReason,
        expiredAt,
        unbanReason,
        sortedHistory,

        // Actions
        fetchHistory,
        handleBan,
        handleUnban,
        handleDeactivate,
        handleActivate
    };
}
