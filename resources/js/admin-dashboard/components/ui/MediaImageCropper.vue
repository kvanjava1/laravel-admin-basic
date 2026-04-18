<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import BaseButton from './BaseButton.vue';

interface CropCoordinates {
    x: number;
    y: number;
    width: number;
    height: number;
}

interface Props {
    image: string;
    show: boolean;
    aspectRatio?: number;
    aspectRatioLabel?: string;
    initialCoords?: CropCoordinates | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'apply', data: { dataUrl: string; coords: CropCoordinates }): void;
}>();

const stageRef = ref<HTMLDivElement | null>(null);
const imageElement = new Image();

const stageSize = ref({ width: 0, height: 0 });
const zoom = ref(1);
const imageLeft = ref(0);
const imageTop = ref(0);
const imageLoaded = ref(false);
const isInteracting = ref(false);

const dragState = ref<{
    startX: number;
    startY: number;
    startLeft: number;
    startTop: number;
} | null>(null);

const pinchState = ref<{
    startDistance: number;
    startZoom: number;
    centerX: number;
    centerY: number;
} | null>(null);

let wheelEndTimer: number | null = null;

const aspectRatio = computed(() => props.aspectRatio || 16 / 9);

const naturalSize = computed(() => ({
    width: imageElement.naturalWidth,
    height: imageElement.naturalHeight,
}));

const viewportRect = computed(() => {
    const { width: stageWidth, height: stageHeight } = stageSize.value;

    if (!stageWidth || !stageHeight) {
        return { x: 0, y: 0, width: 0, height: 0 };
    }

    let width = stageWidth * 0.82;
    let height = width / aspectRatio.value;

    const maxHeight = stageHeight * 0.78;
    if (height > maxHeight) {
        height = maxHeight;
        width = height * aspectRatio.value;
    }

    return {
        x: (stageWidth - width) / 2,
        y: (stageHeight - height) / 2,
        width,
        height,
    };
});

const imageMetrics = computed(() => {
    if (!imageLoaded.value || !naturalSize.value.width || !naturalSize.value.height) {
        return null;
    }

    const viewport = viewportRect.value;
    const baseScale = Math.max(
        viewport.width / naturalSize.value.width,
        viewport.height / naturalSize.value.height
    );
    const scale = baseScale * zoom.value;
    const width = naturalSize.value.width * scale;
    const height = naturalSize.value.height * scale;

    return {
        baseScale,
        scale,
        width,
        height,
        left: imageLeft.value,
        top: imageTop.value,
        right: imageLeft.value + width,
        bottom: imageTop.value + height,
    };
});

const imageStyle = computed(() => {
    const metrics = imageMetrics.value;
    if (!metrics) {
        return {};
    }

    return {
        width: `${metrics.width}px`,
        height: `${metrics.height}px`,
        left: `${metrics.left}px`,
        top: `${metrics.top}px`,
    };
});

const overlayStyle = computed(() => {
    const viewport = viewportRect.value;

    return {
        clipPath: `polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 0% 0%, ${viewport.x}px ${viewport.y}px, ${viewport.x}px ${viewport.y + viewport.height}px, ${viewport.x + viewport.width}px ${viewport.y + viewport.height}px, ${viewport.x + viewport.width}px ${viewport.y}px, ${viewport.x}px ${viewport.y}px)`,
    };
});

const frameStyle = computed(() => {
    const viewport = viewportRect.value;

    return {
        left: `${viewport.x}px`,
        top: `${viewport.y}px`,
        width: `${viewport.width}px`,
        height: `${viewport.height}px`,
    };
});

const getInteractionOverflow = () => {
    const viewport = viewportRect.value;

    return Math.min(viewport.width, viewport.height) * 0.24;
};

const clampImagePosition = (left: number, top: number, overflow = 0) => {
    const metrics = imageMetrics.value;
    if (!metrics) {
        return { left, top };
    }

    const viewport = viewportRect.value;
    const minLeft = viewport.x + viewport.width - metrics.width - overflow;
    const maxLeft = viewport.x + overflow;
    const minTop = viewport.y + viewport.height - metrics.height - overflow;
    const maxTop = viewport.y + overflow;

    return {
        left: Math.min(maxLeft, Math.max(minLeft, left)),
        top: Math.min(maxTop, Math.max(minTop, top)),
    };
};

const applyStrictClamp = () => {
    const clamped = clampImagePosition(imageLeft.value, imageTop.value, 0);
    imageLeft.value = clamped.left;
    imageTop.value = clamped.top;
};

const syncStageSize = () => {
    const stage = stageRef.value;
    if (!stage) {
        return;
    }

    stageSize.value = {
        width: stage.clientWidth,
        height: stage.clientHeight,
    };

    nextTick(() => {
        if (imageLoaded.value) {
            applyStrictClamp();
        }
    });
};

const centerImage = () => {
    const metrics = imageMetrics.value;
    if (!metrics) {
        return;
    }

    imageLeft.value = (stageSize.value.width - metrics.width) / 2;
    imageTop.value = (stageSize.value.height - metrics.height) / 2;
    applyStrictClamp();
};

const restoreFromInitialCoords = () => {
    const coords = props.initialCoords;
    const viewport = viewportRect.value;

    if (!coords || !naturalSize.value.width || !naturalSize.value.height || !viewport.width || !viewport.height) {
        centerImage();
        return;
    }

    const baseScale = Math.max(
        viewport.width / naturalSize.value.width,
        viewport.height / naturalSize.value.height
    );
    const scaleFromWidth = viewport.width / Math.max(1, coords.width);
    const scaleFromHeight = viewport.height / Math.max(1, coords.height);
    const targetScale = Math.max(scaleFromWidth, scaleFromHeight);
    const nextZoom = Math.max(1, targetScale / baseScale);

    zoom.value = nextZoom;

    const scaledWidth = naturalSize.value.width * baseScale * nextZoom;
    const scaledHeight = naturalSize.value.height * baseScale * nextZoom;

    imageLeft.value = viewport.x - coords.x * baseScale * nextZoom;
    imageTop.value = viewport.y - coords.y * baseScale * nextZoom;

    const clamped = clampImagePosition(imageLeft.value, imageTop.value, 0);
    imageLeft.value = clamped.left;
    imageTop.value = clamped.top;

    if (scaledWidth < viewport.width || scaledHeight < viewport.height) {
        centerImage();
    }
};

const loadImage = () => {
    if (!props.image) {
        imageLoaded.value = false;
        return;
    }

    imageLoaded.value = false;
    imageElement.crossOrigin = 'anonymous';
    imageElement.onload = async () => {
        imageLoaded.value = true;
        zoom.value = 1;
        await nextTick();
        syncStageSize();
        restoreFromInitialCoords();
    };
    imageElement.src = props.image;
};

const getStagePoint = (clientX: number, clientY: number) => {
    const stage = stageRef.value;
    if (!stage) {
        return { x: 0, y: 0 };
    }

    const rect = stage.getBoundingClientRect();

    return {
        x: clientX - rect.left,
        y: clientY - rect.top,
    };
};

const zoomAroundPoint = (nextZoom: number, pointX: number, pointY: number) => {
    const metrics = imageMetrics.value;
    if (!metrics) {
        return;
    }

    const clampedZoom = Math.min(5, Math.max(1, nextZoom));
    const imageX = (pointX - metrics.left) / metrics.scale;
    const imageY = (pointY - metrics.top) / metrics.scale;
    const nextScale = metrics.baseScale * clampedZoom;
    const nextWidth = naturalSize.value.width * nextScale;
    const nextHeight = naturalSize.value.height * nextScale;
    const nextLeft = pointX - imageX * nextScale;
    const nextTop = pointY - imageY * nextScale;
    const viewport = viewportRect.value;
    const overflow = isInteracting.value ? getInteractionOverflow() : 0;
    const minLeft = viewport.x + viewport.width - nextWidth - overflow;
    const maxLeft = viewport.x + overflow;
    const minTop = viewport.y + viewport.height - nextHeight - overflow;
    const maxTop = viewport.y + overflow;

    zoom.value = clampedZoom;
    imageLeft.value = Math.min(maxLeft, Math.max(minLeft, nextLeft));
    imageTop.value = Math.min(maxTop, Math.max(minTop, nextTop));
};

const beginInteraction = () => {
    isInteracting.value = true;
    if (wheelEndTimer !== null) {
        window.clearTimeout(wheelEndTimer);
        wheelEndTimer = null;
    }
};

const finishInteraction = () => {
    isInteracting.value = false;
    dragState.value = null;
    pinchState.value = null;
    applyStrictClamp();
};

const clearInteractionState = () => {
    isInteracting.value = false;
    dragState.value = null;
    pinchState.value = null;

    if (wheelEndTimer !== null) {
        window.clearTimeout(wheelEndTimer);
        wheelEndTimer = null;
    }
};

const startDrag = (clientX: number, clientY: number) => {
    beginInteraction();
    dragState.value = {
        startX: clientX,
        startY: clientY,
        startLeft: imageLeft.value,
        startTop: imageTop.value,
    };
};

const moveDrag = (clientX: number, clientY: number) => {
    if (!dragState.value) {
        return;
    }

    const overflow = getInteractionOverflow();
    const nextLeft = dragState.value.startLeft + (clientX - dragState.value.startX);
    const nextTop = dragState.value.startTop + (clientY - dragState.value.startY);
    const clamped = clampImagePosition(nextLeft, nextTop, overflow);

    imageLeft.value = clamped.left;
    imageTop.value = clamped.top;
};

const getTouchDistance = (event: TouchEvent) => {
    if (event.touches.length < 2) {
        return 0;
    }

    const dx = event.touches[0].clientX - event.touches[1].clientX;
    const dy = event.touches[0].clientY - event.touches[1].clientY;

    return Math.sqrt(dx * dx + dy * dy);
};

const handleMouseDown = (event: MouseEvent) => {
    event.preventDefault();
    startDrag(event.clientX, event.clientY);
};

const handleMouseMove = (event: MouseEvent) => {
    moveDrag(event.clientX, event.clientY);
};

const handleMouseUp = () => {
    if (dragState.value || pinchState.value || isInteracting.value) {
        finishInteraction();
    }
};

const handleWheel = (event: WheelEvent) => {
    event.preventDefault();
    beginInteraction();

    const factor = event.deltaY < 0 ? 1.12 : 1 / 1.12;
    const point = getStagePoint(event.clientX, event.clientY);
    zoomAroundPoint(zoom.value * factor, point.x, point.y);

    wheelEndTimer = window.setTimeout(() => {
        finishInteraction();
    }, 160);
};

const handleTouchStart = (event: TouchEvent) => {
    if (event.touches.length === 2) {
        beginInteraction();
        const centerX = (event.touches[0].clientX + event.touches[1].clientX) / 2;
        const centerY = (event.touches[0].clientY + event.touches[1].clientY) / 2;
        const point = getStagePoint(centerX, centerY);

        pinchState.value = {
            startDistance: getTouchDistance(event),
            startZoom: zoom.value,
            centerX: point.x,
            centerY: point.y,
        };
        dragState.value = null;
        return;
    }

    if (event.touches.length === 1) {
        startDrag(event.touches[0].clientX, event.touches[0].clientY);
    }
};

const handleTouchMove = (event: TouchEvent) => {
    if (event.touches.length === 2 && pinchState.value) {
        event.preventDefault();

        const centerX = (event.touches[0].clientX + event.touches[1].clientX) / 2;
        const centerY = (event.touches[0].clientY + event.touches[1].clientY) / 2;
        const point = getStagePoint(centerX, centerY);
        const nextZoom = pinchState.value.startZoom * (getTouchDistance(event) / pinchState.value.startDistance);

        pinchState.value.centerX = point.x;
        pinchState.value.centerY = point.y;
        zoomAroundPoint(nextZoom, point.x, point.y);
        return;
    }

    if (event.touches.length === 1) {
        event.preventDefault();
        moveDrag(event.touches[0].clientX, event.touches[0].clientY);
    }
};

const handleTouchEnd = (event: TouchEvent) => {
    if (event.touches.length === 1) {
        pinchState.value = null;
        startDrag(event.touches[0].clientX, event.touches[0].clientY);
        return;
    }

    if (event.touches.length === 0) {
        finishInteraction();
    }
};

const buildCropRect = (): CropCoordinates | null => {
    const metrics = imageMetrics.value;
    if (!metrics) {
        return null;
    }

    const viewport = viewportRect.value;
    const x = (viewport.x - metrics.left) / metrics.scale;
    const y = (viewport.y - metrics.top) / metrics.scale;
    const width = viewport.width / metrics.scale;
    const height = viewport.height / metrics.scale;
    const clampedX = Math.max(0, Math.round(x));
    const clampedY = Math.max(0, Math.round(y));

    return {
        x: clampedX,
        y: clampedY,
        width: Math.max(1, Math.min(naturalSize.value.width - clampedX, Math.round(width))),
        height: Math.max(1, Math.min(naturalSize.value.height - clampedY, Math.round(height))),
    };
};

const renderPreview = (coords: CropCoordinates) => {
    const canvas = document.createElement('canvas');
    canvas.width = 1200;
    canvas.height = Math.round(1200 / aspectRatio.value);

    const context = canvas.getContext('2d');
    if (!context) {
        return null;
    }

    context.drawImage(
        imageElement,
        coords.x,
        coords.y,
        coords.width,
        coords.height,
        0,
        0,
        canvas.width,
        canvas.height
    );

    return canvas.toDataURL('image/jpeg', 0.9);
};

const handleApply = () => {
    clearInteractionState();

    const coords = buildCropRect();
    if (!coords) {
        return;
    }

    const dataUrl = renderPreview(coords);
    if (!dataUrl) {
        return;
    }

    emit('apply', {
        dataUrl,
        coords,
    });
};

watch(() => props.show, async (show) => {
    if (!show) {
        finishInteraction();
        return;
    }

    await nextTick();
    loadImage();
});

watch(() => props.image, () => {
    if (props.show) {
        loadImage();
    }
});

watch(() => props.initialCoords, async () => {
    if (!props.show || !imageLoaded.value) {
        return;
    }

    await nextTick();
    restoreFromInitialCoords();
});

watch(() => props.aspectRatio, async () => {
    await nextTick();
    syncStageSize();
    restoreFromInitialCoords();
});

let resizeObserver: ResizeObserver | null = null;

onMounted(() => {
    if (stageRef.value) {
        resizeObserver = new ResizeObserver(() => syncStageSize());
        resizeObserver.observe(stageRef.value);
    }

    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('mouseup', handleMouseUp);
});

onUnmounted(() => {
    clearInteractionState();

    resizeObserver?.disconnect();
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('mouseup', handleMouseUp);
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[110] flex items-center justify-center p-3 md:p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="emit('close')"></div>

            <div class="relative flex max-h-[95vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="relative z-10 flex items-center justify-between border-b border-slate-100 bg-white px-5 py-4 md:px-8 md:py-6">
                    <div class="flex min-w-0 items-center gap-3 md:gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary md:h-12 md:w-12">
                            <span class="material-symbols-outlined text-[26px] md:text-[28px]">
                                {{ aspectRatioLabel === '16:9' ? 'crop_16_9' : 'crop_landscape' }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="truncate text-lg font-bold text-slate-800 md:text-xl">Crop Image</h3>
                            <div class="mt-0.5 flex flex-wrap items-center gap-2">
                                <span class="rounded-md bg-primary px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-white">
                                    Target: {{ aspectRatioLabel }}
                                </span>
                                <span class="text-[11px] font-bold uppercase tracking-tight text-slate-400">
                                    {{ aspectRatioLabel === '16:9' ? 'Headline / Discover' : 'Thumbnail / List' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <button
                        class="group flex h-11 w-11 items-center justify-center rounded-2xl border border-transparent text-slate-400 transition-all hover:border-rose-100 hover:bg-slate-100 hover:text-rose-500"
                        @click="emit('close')"
                    >
                        <span class="material-symbols-outlined transition-transform duration-300 group-hover:rotate-90">close</span>
                    </button>
                </div>

                <div
                    ref="stageRef"
                    class="relative min-h-[52vh] flex-1 overflow-hidden bg-slate-950 touch-none md:min-h-[520px]"
                    @mousedown="handleMouseDown"
                    @wheel="handleWheel"
                    @touchstart="handleTouchStart"
                    @touchmove="handleTouchMove"
                    @touchend="handleTouchEnd"
                    @touchcancel="finishInteraction"
                >
                    <img
                        v-if="imageLoaded"
                        :src="image"
                        :style="imageStyle"
                        class="pointer-events-none absolute max-w-none select-none"
                        :class="{ 'transition-[left,top,width,height] duration-200 ease-out': !isInteracting }"
                        alt="Crop preview"
                        draggable="false"
                    >

                    <div class="pointer-events-none absolute inset-0 bg-black/60" :style="overlayStyle"></div>

                    <div class="pointer-events-none absolute border-2 border-blue-500" :style="frameStyle">
                        <div class="absolute left-1/3 top-0 h-full w-px bg-white/30"></div>
                        <div class="absolute left-2/3 top-0 h-full w-px bg-white/30"></div>
                        <div class="absolute left-0 top-1/3 h-px w-full bg-white/30"></div>
                        <div class="absolute left-0 top-2/3 h-px w-full bg-white/30"></div>
                    </div>

                    <div class="pointer-events-none absolute inset-x-0 bottom-4 flex justify-center px-4 md:hidden">
                        <div class="rounded-full bg-slate-900/80 px-3 py-1.5 text-[11px] font-semibold text-white/90 backdrop-blur">
                            Geser gambar. Cubit untuk zoom ke area yang diinginkan.
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 flex-col gap-3 bg-slate-50 px-4 py-4 md:flex-row md:items-center md:justify-between md:px-6">
                    <div class="hidden text-xs font-semibold text-slate-500 md:block">
                        Geser gambar. Gunakan wheel mouse atau pinch untuk zoom ke titik yang dipilih.
                    </div>

                    <div class="flex w-full gap-2 md:w-auto">
                        <BaseButton class="flex-1 justify-center md:flex-none" variant="ghost" @click="emit('close')">
                            Cancel
                        </BaseButton>
                        <BaseButton class="flex-1 justify-center md:flex-none" icon="crop" @click="handleApply">
                            Apply Crop
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
