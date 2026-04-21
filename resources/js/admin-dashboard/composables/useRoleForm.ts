import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { roleService } from '../services/roleService';
import { alertService } from '../utils/sweetalert';

/**
 * useRoleForm.ts
 * Composable to manage the state and logic for creating/editing roles.
 */
export function useRoleForm() {
    const router = useRouter();

    // Form State
    const roleId = ref<number | null>(null);
    const name = ref('');
    const permissions = ref<string[]>([]);
    const availablePermissions = ref<Record<string, any>>({});
    const validationErrors = ref<Record<string, string[]>>({});

    // UI State
    const isLoading = ref(false);

    const resetForm = () => {
        roleId.value = null;
        name.value = '';
        permissions.value = [];
    };

    const fetchPermissions = async () => {
        isLoading.value = true;
        try {
            const response = await roleService.getPermissions();
            availablePermissions.value = response.data;
        } catch (err) {
            console.error('Failed to fetch permissions', err);
        } finally {
            isLoading.value = false;
        }
    };

    const togglePermission = (slug: string) => {
        const index = permissions.value.indexOf(slug);
        if (index === -1) {
            permissions.value.push(slug);
        } else {
            permissions.value.splice(index, 1);
        }
    };

    const validate = () => {
        validationErrors.value = {};
        let isValid = true;

        if (!name.value) {
            validationErrors.value.name = ['The role name field is required.'];
            isValid = false;
        }

        if (permissions.value.length === 0) {
            validationErrors.value.permissions = ['Please select at least one permission.'];
            isValid = false;
        }

        return isValid;
    };

    const loadRole = async (id: number) => {
        isLoading.value = true;
        try {
            const response = await roleService.show(id);
            const role = response.data;
            roleId.value = role.id;
            name.value = role.name;
            // Extract only the names/slugs for the checkboxes
            permissions.value = role.permissions.map((p: any) => p.name);
        } catch (err: any) {
            alertService.errorToast('Load Error', 'Could not load role data.');
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
                permissions: permissions.value,
            };

            if (roleId.value) {
                await roleService.update(roleId.value, payload);
                alertService.successToast('Role updated successfully.');
            } else {
                await roleService.store(payload);
                alertService.successToast('Role created successfully.');
            }

            router.push({ name: 'roles.index' });
        } catch (err: any) {
            if (err.response?.status === 422) {
                validationErrors.value = err.response.data.errors;
            }
            const response = err.response?.data;
            alertService.errorToast(
                response?.message || err.message || 'Server Error',
                err.response?.status === 422 ? 'Please check the form for errors.' : (response?.message ? '' : 'Something went wrong while saving the role.')
            );
        } finally {
            isLoading.value = false;
        }
    };

    return {
        // State
        name,
        permissions,
        availablePermissions,
        validationErrors,
        isLoading,

        // Actions
        submit,
        resetForm,
        loadRole,
        fetchPermissions,
        togglePermission
    };
}
