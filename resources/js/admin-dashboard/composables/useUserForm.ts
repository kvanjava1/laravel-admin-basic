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
    const validationErrors = ref<Record<string, string[]>>({});

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

        if (!userId.value && !password.value) {
            validationErrors.value.password = ['The password field is required for new users.'];
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
        validationErrors.value = {};

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
            if (err.response?.status === 422) {
                validationErrors.value = err.response.data.errors;
            }
            const response = err.response?.data;
            alertService.errorToast(
                response?.message || err.message || 'Server Error',
                err.response?.status === 422 ? 'Please check the form for errors.' : (response?.message ? '' : 'Something went wrong while connecting to the server.')
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
        validationErrors,

        // Actions
        submit,
        resetForm,
        loadUser
    };
}
