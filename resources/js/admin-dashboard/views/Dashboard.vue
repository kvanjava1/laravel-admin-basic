<script setup lang="ts">
import StatsCard from '../components/dashboard/StatsCard.vue';
import { dashboardStats } from '../mockup/dashboard';
import BaseButton from '../components/ui/BaseButton.vue';
import BasePageContainer from '../components/ui/BasePageContainer.vue';
import { alertService } from '../utils/sweetalert';

const handleAction = (message: string) => {
    alertService.alert('Information', message);
};
</script>

<template>
    <BasePageContainer variant="wide">
        <!-- Welcome Banner -->
        <div class="mb-8 p-6 md:p-10 rounded-2xl bg-primary text-white relative overflow-hidden shadow-lg shadow-primary/20">
            <div class="relative z-10">
                <h2 class="text-3xl font-extrabold mb-2">Welcome back, Alex!</h2>
                <p class="text-blue-100 max-w-md">Here's what's happening with your projects today. You have 3 pending tasks and 2 unread notifications.</p>
                <BaseButton 
                    variant="white"
                    class="mt-6"
                    @click="handleAction('Reports feature coming soon!')"
                >
                    View Reports
                </BaseButton>
            </div>
            <!-- Decorative Circle -->
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        </div>
    </BasePageContainer>
</template>
