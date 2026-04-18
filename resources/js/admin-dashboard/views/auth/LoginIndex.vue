<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { authService } from '../../services/authService';
import { useApi } from '../../composables/useApi';
import { alertService } from '../../utils/sweetalert';
import BaseInput from '../../components/ui/BaseInput.vue';
import BaseButton from '../../components/ui/BaseButton.vue';
import BasePanel from '../../components/ui/BasePanel.vue';

const router = useRouter();
const { isLoading, error } = useApi();
const email = ref('');
const password = ref('');

const handleLogin = async () => {
    try {
        await authService.login({
            email: email.value,
            password: password.value,
        });
        router.push({ name: 'dashboard' });
    } catch (err: any) {
        alertService.errorToast(
            'Login failed',
            err.response?.data?.message || 'Please check your credentials.'
        );
        console.error('Login failed', err);
    }
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-2xl mb-4">
                    <span class="material-symbols-outlined text-primary text-[32px]">admin_panel_settings</span>
                </div>
                <h1 class="text-3xl font-bold text-text-primary">Welcome Back</h1>
                <p class="text-text-secondary mt-2">Log in to manage your system</p>
            </div>

            <BasePanel title="Sign In">
                <form @submit.prevent="handleLogin" class="space-y-6 py-2">
                    <BaseInput label="Email Address" icon="mail" type="email" v-model="email"
                        placeholder="admin@example.com" required />

                    <div class="space-y-1">
                        <BaseInput label="Password" icon="lock" type="password" v-model="password"
                            placeholder="••••••••" required />
                        <div class="flex justify-end">
                            <a href="#" class="text-xs font-bold text-primary hover:underline">Forgot password?</a>
                        </div>
                    </div>

                    <BaseButton type="submit" class="w-full" :loading="isLoading" icon="login">
                        Sign In to Dashboard
                    </BaseButton>
                </form>

                <template #footer>
                    <p class="text-center text-sm text-text-secondary">
                        Don't have an account?
                        <a href="#" class="font-bold text-primary hover:underline">Contact Support</a>
                    </p>
                </template>
            </BasePanel>

            <div class="mt-8 text-center text-xs text-text-secondary">
                &copy; {{ new Date().getFullYear() }} Laravel Admin Basic. All rights reserved.
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Additional local styles if needed */
</style>
