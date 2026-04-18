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

    const validate = () => {
        if (!name.value || !email.value) {
            alertService.errorToast('Validation Error', 'Please fill in required fields (Name and Email).');
            return false;
        }

        if (!email.value.includes('@')) {
            alertService.errorToast('Invalid Email', 'Please enter a valid email address.');
            return false;
        }

        if (password.value) {
            if (password.value !== password_confirmation.value) {
                alertService.errorToast('Password Mismatch', 'The password and confirmation do not match.');
                return false;
            }

            if (password.value.length < 8) {
                alertService.errorToast('Weak Password', 'The password must be at least 8 characters long.');
                return false;
            }
        }

        // Image validation
        if (avatar.value instanceof File) {
            const maxSize = 6 * 1024 * 1024; // 6MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];

            if (avatar.value.size > maxSize) {
                alertService.errorToast('File Too Large', 'The selected image exceeds the maximum allowed size of 6MB.');
                return false;
            }

            if (!allowedTypes.includes(avatar.value.type)) {
                alertService.errorToast('Invalid File Type', 'Allowed image types are: JPEG, PNG, GIF, and WEBP.');
                return false;
            }
        }

        return true;
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
            const response = err.response?.data;
            alertService.errorToast(
                response?.message || err.message || 'Server Error',
                response?.errors
                    ? Object.values(response.errors).flat().map(v => typeof v === 'object' ? JSON.stringify(v) : v).join(' ')
                    : (response?.message ? '' : 'Something went wrong while connecting to the server.')
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

        // Actions
        submit,
        loadProfile
    };
}
