import { ref } from 'vue';
import { useAuth } from './useAuth';
import { profileService } from '../services/profileService';
import { alertService } from '../utils/sweetalert';

/**
 * useProfileForm.ts
 * Composable to manage the state and logic for updating the authenticated user's profile.
 */
export function useProfileForm() {
    const { setAuth } = useAuth();

    // Form State
    const name = ref('');
    const email = ref('');
    const password = ref('');
    const password_confirmation = ref('');
    const avatar = ref<string | File | undefined>(undefined);
    const crop_x = ref<number | undefined>(undefined);
    const crop_y = ref<number | undefined>(undefined);
    const crop_width = ref<number | undefined>(undefined);
    const crop_height = ref<number | undefined>(undefined);

    // UI State
    const isLoading = ref(false);
    const validationErrors = ref<Record<string, string[]>>({});

    const validate = () => {
        validationErrors.value = {};
        let isValid = true;

        if (!name.value) {
            validationErrors.value.name = ['The name field is required.'];
            isValid = false;
        }

        if (!email.value) {
            validationErrors.value.email = ['The email field is required.'];
            isValid = false;
        } else if (!email.value.includes('@')) {
            validationErrors.value.email = ['Please enter a valid email address.'];
            isValid = false;
        }

        if (password.value) {
            if (password.value !== password_confirmation.value) {
                validationErrors.value.password_confirmation = ['The password confirmation does not match.'];
                isValid = false;
            }

            if (password.value.length < 8) {
                validationErrors.value.password = ['The password must be at least 8 characters long.'];
                isValid = false;
            }
        }

        return isValid;
    };

    const loadProfile = async () => {
        isLoading.value = true;
        try {
            const response = await profileService.show();
            const user = response.data;
            name.value = user.name;
            email.value = user.email;

            if (user.avatar) {
                avatar.value = user.avatar.startsWith('http') || user.avatar.startsWith('/storage')
                    ? user.avatar
                    : '/storage/' + user.avatar;
            } else {
                avatar.value = undefined;
            }
        } catch (err: any) {
            alertService.errorToast('Load Error', 'Could not load profile data.');
            console.error('Failed to load profile', err);
        } finally {
            isLoading.value = false;
        }
    };

    const submit = async () => {
        if (!validate()) return;

        isLoading.value = true;
        validationErrors.value = {};

        try {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('name', name.value);
            formData.append('email', email.value);

            if (password.value) {
                formData.append('password', password.value);
                formData.append('password_confirmation', password_confirmation.value);
            }

            if (avatar.value instanceof File) {
                formData.append('avatar', avatar.value);
                if (crop_x.value !== undefined) formData.append('crop_x', String(crop_x.value));
                if (crop_y.value !== undefined) formData.append('crop_y', String(crop_y.value));
                if (crop_width.value !== undefined) formData.append('crop_width', String(crop_width.value));
                if (crop_height.value !== undefined) formData.append('crop_height', String(crop_height.value));
            }

            const response = await profileService.update(formData);

            if (response.success) {
                // Update Global Auth State to sync UI (Sidebar name/avatar)
                const token = localStorage.getItem('auth_token') || '';
                setAuth(response.data, token);
                alertService.successToast('Profile updated successfully.');
            }

            return response;
        } catch (err: any) {
            if (err.response?.status === 422) {
                validationErrors.value = err.response.data.errors;
            }
            const response = err.response?.data;
            alertService.errorToast(
                response?.message || err.message || 'Server Error',
                err.response?.status === 422 ? 'Please check the form for errors.' : (response?.message ? '' : 'Something went wrong while connecting to the server.')
            );
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    return {
        // State
        name,
        email,
        password,
        password_confirmation,
        avatar,
        crop_x,
        crop_y,
        crop_width,
        crop_height,
        isLoading,
        validationErrors,

        // Actions
        submit,
        loadProfile
    };
}
