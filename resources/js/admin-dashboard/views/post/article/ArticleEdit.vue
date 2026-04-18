<script setup lang="ts">
import BasePageHeader from '../../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../../components/ui/BasePageContainer.vue';
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader title="Edit Article" back-label="Back to Articles" back-route-name="articles.index" />
        <div class="py-20 text-center bg-white rounded-2xl border border-dashed border-slate-300">
            <span class="material-symbols-outlined text-5xl text-slate-300">edit</span>
            <p class="mt-2 text-slate-500 font-medium">Article Edit Form is under construction.</p>
        </div>
    </BasePageContainer>
</template>
