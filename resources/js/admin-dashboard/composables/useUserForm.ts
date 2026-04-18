import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { userService } from '../services/userService';
import { alertService } from '../utils/sweetalert';

/**
 * useUserForm.ts
 * Composable to manage the state and logic for creating/editing users.
 */
export function useUserForm() {
    const router = useRouter();

    // Form State
    const userId = ref<number | null>(null);
    const name = ref('');
    const email = ref('');
    const password = ref('');
    const password_confirmation = ref('');
    const selectedRole = ref('');
    const avatar = ref<string | File | undefined>(undefined);
    const crop_x = ref<number | undefined>(undefined);
    const crop_y = ref<number | undefined>(undefined);
    const crop_width = ref<number | undefined>(undefined);
    const crop_height = ref<number | undefined>(undefined);

    // UI State
    const isLoading = ref(false);

    const resetForm = () => {
        userId.value = null;
        name.value = '';
        email.value = '';
        password.value = '';
        password_confirmation.value = '';
        selectedRole.value = '';
        avatar.value = undefined;
        crop_x.value = undefined;
        crop_y.value = undefined;
        crop_width.value = undefined;
        crop_height.value = undefined;
    };

    const validate = () => {
        if (!name.value || !email.value) {
            alertService.errorToast('Validation Error', 'Please fill in required fields (Name and Email).');
            return false;
        }

        if (!userId.value && !password.value) {
            alertService.errorToast('Validation Error', 'Please provide a password for the new user.');
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

    const loadUser = async (id: number) => {
        isLoading.value = true;
        try {
            const response = await userService.show(id);

            const user = response.data;
            userId.value = user.id;
            name.value = user.name;
            email.value = user.email;
            selectedRole.value = user.roles && user.roles.length > 0 ? user.roles[0].name : '';
            // The avatar preview URL should load existing raw path
            if (user.avatar) {
                avatar.value = user.avatar.startsWith('http') || user.avatar.startsWith('/storage')
                    ? user.avatar
                    : '/storage/' + user.avatar;
            } else {
                avatar.value = undefined;
            }
        } catch (err: any) {
            alertService.errorToast('Load Error', 'Could not load user data to edit.');
            console.error('Failed to load user', err);
        } finally {
            isLoading.value = false;
        }
    };

    const submit = async () => {
        if (!validate()) return;

        isLoading.value = true;

        try {
            const payload = {
                name: name.value,
                email: email.value,
                password: password.value,
                password_confirmation: password_confirmation.value,
                role: selectedRole.value,
                avatar: avatar.value,
                crop_x: crop_x.value,
                crop_y: crop_y.value,
                crop_width: crop_width.value,
                crop_height: crop_height.value
            };

            if (userId.value) {
                await userService.update(userId.value, payload);
                alertService.successToast('User profile updated successfully.');
            } else {
                await userService.store(payload);
                alertService.successToast('User created successfully.');
            }

            router.push({ name: 'users.index' });
        } catch (err: any) {
            const response = err.response?.data;
            alertService.errorToast(
                response?.message || err.message || 'Server Error',
                response?.errors
                    ? Object.values(response.errors).flat().map(v => typeof v === 'object' ? JSON.stringify(v) : v).join(' ')
                    : (response?.message ? '' : 'Something went wrong while connecting to the server.')
            );
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
        selectedRole,
        avatar,
        crop_x,
        crop_y,
        crop_width,
        crop_height,
        isLoading,

        // Actions
        submit,
        resetForm,
        loadUser
    };
}
