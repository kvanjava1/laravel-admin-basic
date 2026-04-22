<script setup lang="ts">
/**
 * UserEdit.vue
 * Page for editing an existing user.
 */
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useUserForm } from '../../composables/useUserForm';
import { userService } from '../../services/userService';
import { roleService } from '../../services/roleService';
import BasePanel from '../../components/ui/BasePanel.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BaseInput from '../../components/ui/BaseInput.vue';
import BaseSelect from '../../components/ui/BaseSelect.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import MediaImageCropper from '../../components/ui/MediaImageCropper.vue';
import BaseAvatarUpload from '../../components/ui/BaseAvatarUpload.vue';

const router = useRouter();
const route = useRoute();

const {
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
    submit,
    loadUser,
    validationErrors
} = useUserForm();

// Cropper State (UI Specific)
const showCropper = ref(false);
const rawImage = ref('');
const previewImage = ref('');

const availableRoles = ref<{ label: string, value: string }[]>([]);

const userId = parseInt(route.params.id as string, 10);

onMounted(async () => {
    try {
        // availableStatuses removed

        // Fetch Roles (Optimized)
        const rolesData = await roleService.getOptions();
        availableRoles.value = rolesData.data.map((r: any) => ({
            label: r.name,
            value: r.name
        }));

        // Fetch User Data to fuel the form
        await loadUser(userId);
        previewImage.value = typeof avatar.value === 'string' ? avatar.value : '';

    } catch (e) {
        console.error('Failed to load user form data', e);
    }
});

const handleImageSelect = (data: { dataUrl: string; file: File }) => {
    rawImage.value = data.dataUrl;
    previewImage.value = data.dataUrl;
    avatar.value = data.file;
    showCropper.value = true;
};

const handleCropApply = (data: { dataUrl: string; coords: { x: number; y: number; width: number; height: number } }) => {
    crop_x.value = data.coords.x;
    crop_y.value = data.coords.y;
    crop_width.value = data.coords.width;
    crop_height.value = data.coords.height;
    previewImage.value = data.dataUrl;
    showCropper.value = false;
};

const handleCancel = () => {
    router.push({ name: 'users.index' });
};
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader title="Edit Member" back-label="Back to Users" back-route-name="users.index" />

        <div class="form-content-wrapper space-y-6">
            <!-- 1. Profile Identity -->
            <BasePanel title="Profile Photo">
                <div class="flex justify-center py-4">
                    <BaseAvatarUpload :model-value="previewImage" @select="handleImageSelect" />
                </div>
                <p class="text-center text-xs text-text-secondary mt-2 italic">
                    Square portraits (1:1) work best for user avatars.
                </p>
            </BasePanel>

            <!-- 2. Account Details -->
            <BasePanel title="Basic Information">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2">
                    <BaseInput label="Full Name" icon="person" v-model="name" placeholder="e.g. John Doe" :error="validationErrors.name?.[0]" required />
                    <BaseInput label="Email Address" icon="mail" type="email" v-model="email"
                        placeholder="john@example.com" :error="validationErrors.email?.[0]" required />
                </div>
            </BasePanel>

            <!-- 3. Security Settings -->
            <BasePanel title="Security Settings">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2">
                    <BaseInput label="New Password (Optional)" icon="lock" type="password" v-model="password"
                        placeholder="••••••••" :error="validationErrors.password?.[0]" />
                    <BaseInput label="Confirm New Password" icon="lock_reset" type="password"
                        v-model="password_confirmation" placeholder="••••••••" :error="validationErrors.password_confirmation?.[0]" />
                </div>
                <p class="text-xs text-text-secondary mt-2 italic px-1">
                    Leave blank if you don't wish to change the password.
                </p>
            </BasePanel>

            <!-- 4. System Access -->
            <BasePanel title="System Access">
                <div class="grid grid-cols-1 gap-6 py-2">
                    <BaseSelect label="Assigned Role" placeholder="Select Role" v-model="selectedRole"
                        :options="availableRoles" :error="validationErrors.role?.[0]" required />
                </div>

                <template #footer>
                    <div class="flex flex-col sm:flex-row justify-end gap-3 bg-slate-50 p-4 rounded-xl">
                        <BaseButton variant="ghost" class="w-full sm:w-auto" @click="handleCancel">
                            Cancel
                        </BaseButton>
                        <BaseButton icon="save" class="w-full sm:w-auto" @click="submit" :loading="isLoading">
                            Save Changes
                        </BaseButton>
                    </div>
                </template>
            </BasePanel>
        </div>
    </BasePageContainer>

    <!-- Unified 1:1 Cropper -->
    <MediaImageCropper 
        :show="showCropper" 
        :image="rawImage" 
        :aspect-ratio="1"
        aspect-ratio-label="Profile (1:1)"
        @close="showCropper = false" 
        @apply="handleCropApply" 
    />
</template>
