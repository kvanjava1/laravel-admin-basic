<script setup lang="ts">
/**
 * UserCreate.vue
 * Page for creating a new user.
 */
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUserForm } from '../../composables/useUserForm';
import { userService } from '../../services/userService';
import { roleService } from '../../services/roleService';
import BasePanel from '../../components/ui/BasePanel.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BaseInput from '../../components/ui/BaseInput.vue';
import BaseSelect from '../../components/ui/BaseSelect.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import BaseImageCropper from '../../components/ui/BaseImageCropper.vue';
import BaseAvatarUpload from '../../components/ui/BaseAvatarUpload.vue';

const router = useRouter();

// Layered Architecture: Use the Composable for form logic
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
    submit
} = useUserForm();

// Cropper State (Specific to UI view)
const showCropper = ref(false);
const rawImage = ref('');
const previewImage = ref(''); // Keep a preview for the UI

const availableRoles = ref<{ label: string, value: string }[]>([]);

onMounted(async () => {
    try {
        // availableStatuses removed
        // Fetch Roles (Optimized)
        const rolesData = await roleService.getOptions();
        availableRoles.value = rolesData.data.map((r: any) => ({
            label: r.name,
            value: r.name
        }));
    } catch (e) {
        console.error('Failed to load user form data', e);
    }
});

const handleImageSelect = (data: { dataUrl: string; file: File }) => {
    rawImage.value = data.dataUrl;
    previewImage.value = data.dataUrl; // Initial preview
    avatar.value = data.file; // Store the raw file for multipart upload
    showCropper.value = true;
};

const handleCropApply = (data: { dataUrl: string; coords: { x: number; y: number; width: number; height: number } }) => {
    // Store coordinates
    crop_x.value = data.coords.x;
    crop_y.value = data.coords.y;
    crop_width.value = data.coords.width;
    crop_height.value = data.coords.height;

    // UI preview
    previewImage.value = data.dataUrl;

    showCropper.value = false;
};

const handleCancel = () => {
    router.push({ name: 'users.index' });
};
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader title="Add New Member" back-label="Back to Users" back-route-name="users.index" />

        <div class="form-content-wrapper space-y-6">
            <!-- 1. Profile Identity -->
            <BasePanel title="Profile Photo">
                <div class="flex justify-center py-4">
                    <BaseAvatarUpload :model-value="previewImage" @select="handleImageSelect" />
                </div>
                <p class="text-center text-xs text-text-secondary mt-2 italic">
                    Images will be automatically cropped to a square (1:1 aspect ratio).
                </p>
            </BasePanel>

            <!-- 2. Basic Information -->
            <BasePanel title="Basic Information">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2">
                    <BaseInput label="Full Name" icon="person" v-model="name" placeholder="e.g. John Doe" />
                    <BaseInput label="Email Address" icon="mail" type="email" v-model="email"
                        placeholder="john@example.com" />
                </div>
            </BasePanel>

            <!-- 3. Security Settings -->
            <BasePanel title="Security Settings">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2">
                    <BaseInput label="Password" icon="lock" type="password" v-model="password" placeholder="••••••••" />
                    <BaseInput label="Confirm Password" icon="lock_reset" type="password"
                        v-model="password_confirmation" placeholder="••••••••" />
                </div>
            </BasePanel>

            <!-- 4. Account Permissions -->
            <BasePanel title="Account Permissions">
                <div class="grid grid-cols-1 gap-6 py-2">
                    <BaseSelect label="Assigned Role" placeholder="Select Role" v-model="selectedRole"
                        :options="availableRoles" />
                </div>

                <template #footer>
                    <div class="flex flex-col sm:flex-row justify-end gap-3 bg-slate-50 p-4 rounded-xl">
                        <BaseButton variant="ghost" class="w-full sm:w-auto" @click="handleCancel">
                            Cancel
                        </BaseButton>
                        <BaseButton icon="person_add" class="w-full sm:w-auto" @click="submit" :loading="isLoading">
                            Create Account
                        </BaseButton>
                    </div>
                </template>
            </BasePanel>
        </div>
    </BasePageContainer>

    <!-- Unified 1:1 Cropper (outside the max-w-4xl constraint) -->
    <BaseImageCropper :show="showCropper" :image="rawImage" @close="showCropper = false" @apply="handleCropApply" />
</template>
