<script setup lang="ts">
import MediaUploadWorkflow from '../../components/media/MediaUploadWorkflow.vue';
import BasePageHeader from '../../components/ui/BasePageHeader.vue';
import BasePageContainer from '../../components/ui/BasePageContainer.vue';
</script>

<template>
    <BasePageContainer variant="narrow">
        <BasePageHeader 
            title="Upload New Media" 
            subtitle="Add images to your library with standard SEO and cropping" 
            back-label="Back to Media" 
            back-route-name="media.index" 
        />

        <MediaUploadWorkflow />
    </BasePageContainer>
</template>
