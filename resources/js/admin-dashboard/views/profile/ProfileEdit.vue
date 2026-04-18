<script setup lang="ts">
/**
 * ProfileEdit.vue
 * Page for authenticated user to edit their own profile.
 * Adapted from UserEdit.vue.
 */
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useProfileForm } from '../../composables/useProfileForm';
import BasePanel from '../../components/ui/BasePanel.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BaseInput from '../../components/ui/BaseInput.vue';
import BaseSelect from '../../components/ui/BaseSelect.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
import MediaImageCropper from '../../components/ui/MediaImageCropper.vue';
import BaseAvatarUpload from '../../components/ui/BaseAvatarUpload.vue';

const router = useRouter();

const {
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
    loadProfile,
    submit: submitProfile
} = useProfileForm();

// Cropper State (UI Specific)
const showCropper = ref(false);
const rawImage = ref('');
const previewImage = ref('');

onMounted(async () => {
    try {
        await loadProfile();
        previewImage.value = typeof avatar.value === 'string' ? avatar.value : '';
    } catch (e) {
        console.error('Failed to load profile data', e);
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
    router.push({ name: 'dashboard' });
};

const handleSave = async () => {
    try {
        await submitProfile();
        router.push({ name: 'dashboard' });
    } catch (err) {
        // Error reported via toast in composable
    }
};
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader title="Edit My Profile" back-label="Back to Dashboard" back-route-name="dashboard" />

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
                    <BaseInput label="New Password (Optional)" icon="lock" type="password" v-model="password"
                        placeholder="••••••••" />
                    <BaseInput label="Confirm New Password" icon="lock_reset" type="password"
                        v-model="password_confirmation" placeholder="••••••••" />
                </div>
                <p class="text-xs text-text-secondary mt-2 italic px-1">
                    Leave blank if you don't wish to change your password.
                </p>

                <template #footer>
                    <div class="flex flex-col sm:flex-row justify-end gap-3 bg-slate-50 p-4 rounded-xl">
                        <BaseButton variant="ghost" class="w-full sm:w-auto" @click="handleCancel">
                            Cancel
                        </BaseButton>
                        <BaseButton icon="save" class="w-full sm:w-auto" @click="handleSave" :loading="isLoading">
                            Update Profile
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
