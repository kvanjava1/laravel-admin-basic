<script setup lang="ts">
/**
 * BaseImageCropper.vue — Rewrite v2
 * Pure Vue + Canvas image cropper. No cropperjs.
 * - Image always fills the full workspace
 * - 1:1 aspect ratio strictly enforced
 * - Drag to move crop box, drag corners to resize
 * - Rotate via buttons
 */
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import BaseButton from './BaseButton.vue';

interface Props {
    image: string;
    show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'apply', data: { dataUrl: string; coords: { x: number; y: number; width: number; height: number } }): void;
}>();

// Canvas refs
const canvasRef = ref<HTMLCanvasElement | null>(null);
const containerRef = ref<HTMLDivElement | null>(null);

// Image state
const img = new Image();
const rotation = ref(0); // 0, 90, 180, 270
const zoom = ref(1); // 1 = fitting the container; >1 = zoomed in, <1 = zoomed out
const imgOffset = ref({ x: 0, y: 0 });

// Crop box state (in canvas coordinates)
const cropBox = ref({ x: 0, y: 0, size: 0 });

// Drag & Zoom state
type DragMode = 'move' | 'nw' | 'ne' | 'sw' | 'se' | 'pan' | null;
const drag = ref<{ mode: DragMode; startX: number; startY: number; origBox: typeof cropBox.value; origOffset?: { x: number; y: number } } | null>(null);
const initialPinchDistance = ref<number | null>(null);
const initialZoom = ref<number>(1);

const HANDLE_SIZE = 12;

// ─── Drawing ────────────────────────────────────────────────────────────────

const draw = () => {
    const canvas = canvasRef.value;
    if (!canvas || !img.complete || !img.naturalWidth) return;
    const ctx = canvas.getContext('2d')!;
    const W = canvas.width;
    const H = canvas.height;
    ctx.clearRect(0, 0, W, H);

    // Draw (possibly rotated) image to fill canvas
    ctx.save();
    ctx.translate(W / 2 + imgOffset.value.x, H / 2 + imgOffset.value.y);
    ctx.rotate((rotation.value * Math.PI) / 180);

    const rad = rotation.value;
    const swapped = rad === 90 || rad === 270;
    const naturalW = swapped ? img.naturalHeight : img.naturalWidth;
    const naturalH = swapped ? img.naturalWidth : img.naturalHeight;
    const scale = Math.max(W / naturalW, H / naturalH) * zoom.value;
    const drawW = naturalW * scale;
    const drawH = naturalH * scale;

    ctx.drawImage(img, -drawW / 2, -drawH / 2, drawW, drawH);
    ctx.restore();

    // Darken outside crop box
    ctx.fillStyle = 'rgba(0,0,0,0.55)';
    const b = cropBox.value;
    ctx.fillRect(0, 0, W, b.y);
    ctx.fillRect(0, b.y + b.size, W, H - (b.y + b.size));
    ctx.fillRect(0, b.y, b.x, b.size);
    ctx.fillRect(b.x + b.size, b.y, W - (b.x + b.size), b.size);

    // Crop box border
    ctx.strokeStyle = '#3b82f6';
    ctx.lineWidth = 2;
    ctx.strokeRect(b.x, b.y, b.size, b.size);

    // Grid lines (rule of thirds)
    ctx.strokeStyle = 'rgba(255,255,255,0.3)';
    ctx.lineWidth = 1;
    for (let i = 1; i <= 2; i++) {
        const x = b.x + (b.size / 3) * i;
        const y = b.y + (b.size / 3) * i;
        ctx.beginPath();
        ctx.moveTo(x, b.y);
        ctx.lineTo(x, b.y + b.size);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(b.x, y);
        ctx.lineTo(b.x + b.size, y);
        ctx.stroke();
    }

    // Corner handles
    ctx.fillStyle = '#3b82f6';
    const corners = [
        [b.x, b.y],
        [b.x + b.size, b.y],
        [b.x, b.y + b.size],
        [b.x + b.size, b.y + b.size],
    ];
    corners.forEach(([cx, cy]) => {
        ctx.fillRect(cx - HANDLE_SIZE / 2, cy - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE);
    });
};

// ─── Initialisation ──────────────────────────────────────────────────────────

const initCanvas = () => {
    const canvas = canvasRef.value;
    const container = containerRef.value;
    if (!canvas || !container) return;

    canvas.width = container.offsetWidth;
    canvas.height = container.offsetHeight;

    // Default crop box: centered square, 80% of shorter dimension
    const size = Math.min(canvas.width, canvas.height) * 0.8;
    cropBox.value = {
        x: (canvas.width - size) / 2,
        y: (canvas.height - size) / 2,
        size,
    };

    draw();
};

const loadImage = () => {
    img.crossOrigin = 'anonymous';
    img.onload = () => {
        nextTick(() => initCanvas());
    };
    img.src = props.image;
};

// ─── Rotation ────────────────────────────────────────────────────────────────

const rotate = (deg: number) => {
    rotation.value = (rotation.value + deg + 360) % 360;
    imgOffset.value = { x: 0, y: 0 };
    draw();
};

const handleWheel = (e: WheelEvent) => {
    e.preventDefault();
    const factor = 1.1;
    if (e.deltaY < 0) {
        zoom.value = Math.min(zoom.value * factor, 5);
    } else {
        zoom.value = Math.max(zoom.value / factor, 0.1);
    }
    draw();
};

// ─── Mouse / Touch interaction ────────────────────────────────────────────────

const getPos = (e: MouseEvent | TouchEvent): [number, number] => {
    const canvas = canvasRef.value!;
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    return [(clientX - rect.left) * scaleX, (clientY - rect.top) * scaleY];
};

const hitTest = (x: number, y: number): DragMode => {
    const b = cropBox.value;
    const hs = HANDLE_SIZE;
    // Corner handles
    if (Math.abs(x - b.x) < hs && Math.abs(y - b.y) < hs) return 'nw';
    if (Math.abs(x - (b.x + b.size)) < hs && Math.abs(y - b.y) < hs) return 'ne';
    if (Math.abs(x - b.x) < hs && Math.abs(y - (b.y + b.size)) < hs) return 'sw';
    if (Math.abs(x - (b.x + b.size)) < hs && Math.abs(y - (b.y + b.size)) < hs) return 'se';
    // Interior move
    if (x > b.x && x < b.x + b.size && y > b.y && y < b.y + b.size) return 'move';
    return 'pan';
};

const onPointerDown = (e: MouseEvent | TouchEvent) => {
    // Handle pinch start
    if ('touches' in e && e.touches.length === 2) {
        e.preventDefault();
        const dx = e.touches[0].clientX - e.touches[1].clientX;
        const dy = e.touches[0].clientY - e.touches[1].clientY;
        initialPinchDistance.value = Math.sqrt(dx * dx + dy * dy);
        initialZoom.value = zoom.value;
        return;
    }

    const [x, y] = getPos(e);
    const mode = hitTest(x, y);
    if (!mode) return;
    e.preventDefault();
    drag.value = { 
        mode, 
        startX: x, 
        startY: y, 
        origBox: { ...cropBox.value },
        origOffset: { ...imgOffset.value }
    };
};

const onPointerMove = (e: MouseEvent | TouchEvent) => {
    // Handle pinch move
    if ('touches' in e && e.touches.length === 2 && initialPinchDistance.value !== null) {
        e.preventDefault();
        const dx = e.touches[0].clientX - e.touches[1].clientX;
        const dy = e.touches[0].clientY - e.touches[1].clientY;
        const currentDist = Math.sqrt(dx * dx + dy * dy);
        const factor = currentDist / initialPinchDistance.value;
        zoom.value = Math.min(Math.max(initialZoom.value * factor, 0.1), 5);
        draw();
        return;
    }

    const [x, y] = getPos(e);
    const canvas = canvasRef.value!;

    if (!drag.value) {
        // Update cursor
        const mode = hitTest(x, y);
        canvas.style.cursor = mode === 'move' ? 'move' : mode ? 'nwse-resize' : 'default';
        return;
    }
    e.preventDefault();
    const dx = x - drag.value.startX;
    const dy = y - drag.value.startY;
    const orig = drag.value.origBox;
    const W = canvas.width;
    const H = canvas.height;
    let { x: bx, y: by, size } = orig;

    if (drag.value.mode === 'pan') {
        imgOffset.value = {
            x: (drag.value.origOffset?.x || 0) + dx,
            y: (drag.value.origOffset?.y || 0) + dy
        };
    } else if (drag.value.mode === 'move') {
        bx = Math.max(0, Math.min(W - size, bx + dx));
        by = Math.max(0, Math.min(H - size, by + dy));
    } else {
        // Resize — keep 1:1 by using uniform delta
        const delta = (Math.abs(dx) > Math.abs(dy) ? dx : dy);
        if (drag.value.mode === 'nw') {
            const d = Math.min(delta, size - 20);
            bx = orig.x + d;
            by = orig.y + d;
            size = orig.size - d;
        } else if (drag.value.mode === 'ne') {
            const d = -Math.max(-delta, -(size - 20));
            size = orig.size + d;
            by = orig.y - d;
        } else if (drag.value.mode === 'sw') {
            const d = Math.min(delta, size - 20);
            bx = orig.x + d;
            size = orig.size - d;
        } else if (drag.value.mode === 'se') {
            size = Math.max(20, orig.size + delta);
        }
        // Clamp to canvas
        bx = Math.max(0, bx);
        by = Math.max(0, by);
        size = Math.min(size, W - bx, H - by);
    }

    cropBox.value = { x: bx, y: by, size };
    draw();
};

const onPointerUp = (e?: MouseEvent | TouchEvent) => {
    drag.value = null;
    if (e && 'touches' in e && e.touches.length < 2) {
        initialPinchDistance.value = null;
    }
};

// ─── Apply crop ───────────────────────────────────────────────────────────────

const handleApply = () => {
    const canvas = canvasRef.value;
    if (!canvas) return;
    const b = cropBox.value;

    // Create output canvas at 400×400
    const out = document.createElement('canvas');
    out.width = 400;
    out.height = 400;
    const ctx = out.getContext('2d')!;
    ctx.drawImage(canvas, b.x, b.y, b.size, b.size, 0, 0, 400, 400);

    // Emit both the preview (optional, for UI) and the COORDS relative to the image
    // To get coordinates relative to the ORIGINAL image:
    // 1. Find the current scale of the image on canvas
    const rad = rotation.value;
    const swapped = rad === 90 || rad === 270;
    const naturalW = swapped ? img.naturalHeight : img.naturalWidth;
    const naturalH = swapped ? img.naturalWidth : img.naturalHeight;
    const W = canvas.width;
    const H = canvas.height;
    const displayScale = Math.max(W / naturalW, H / naturalH) * zoom.value;

    // 2. The image is centered on canvas. Its top-left on canvas is:
    const imgXOnCanvas = (W - naturalW * displayScale) / 2 + imgOffset.value.x;
    const imgYOnCanvas = (H - naturalH * displayScale) / 2 + imgOffset.value.y;

    // 3. Map cropBox (canvas coords) to image coords
    const x = (b.x - imgXOnCanvas) / displayScale;
    const y = (b.y - imgYOnCanvas) / displayScale;
    const size = b.size / displayScale;

    emit('apply', {
        dataUrl: out.toDataURL('image/jpeg', 0.9),
        coords: {
            x: Math.round(x),
            y: Math.round(y),
            width: Math.round(size),
            height: Math.round(size)
        }
    });
};

// ─── Lifecycle ────────────────────────────────────────────────────────────────

watch(() => props.show, (visible) => {
    if (visible && props.image) {
        imgOffset.value = { x: 0, y: 0 };
        zoom.value = 1;
        nextTick(() => loadImage());
    } else {
        rotation.value = 0;
    }
});

watch(() => props.image, (src) => {
    if (src && props.show) {
        rotation.value = 0;
        loadImage();
    }
});

// Resize observer for responsive canvas
let resizeObserver: ResizeObserver | null = null;
onMounted(() => {
    if (containerRef.value) {
        resizeObserver = new ResizeObserver(() => {
            if (props.show) initCanvas();
        });
        resizeObserver.observe(containerRef.value);
    }
    if (props.show && props.image) loadImage();
});

onUnmounted(() => {
    resizeObserver?.disconnect();
});
</script>

<template>
    <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[110] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="emit('close')"></div>

        <!-- Modal -->
        <div class="relative w-full max-w-3xl max-h-[90vh] bg-white rounded-2xl md:rounded-3xl overflow-hidden shadow-2xl flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Crop Profile Picture</h3>
                    <p class="text-xs text-slate-500">Drag the box to select, drag corners to resize (1:1 enforced).</p>
                </div>
                <button @click="emit('close')" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-50 text-slate-400 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Canvas workspace -->
            <div
                ref="containerRef"
                class="bg-slate-900 w-full shrink flex-1 relative min-h-[50vh] md:min-h-[520px]"
            >
                <canvas
                    ref="canvasRef"
                    class="block w-full h-full touch-none"
                    @mousedown="onPointerDown"
                    @mousemove="onPointerMove"
                    @mouseup="onPointerUp"
                    @mouseleave="onPointerUp"
                    @wheel="handleWheel"
                    @touchstart.prevent="onPointerDown"
                    @touchmove.prevent="onPointerMove"
                    @touchend.prevent="onPointerUp"
                ></canvas>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-slate-50 flex items-center justify-between shrink-0">
                <div class="flex gap-2">
                    <button
                        @click="rotate(-90)"
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-primary hover:border-primary transition-all shadow-sm"
                        title="Rotate left"
                    >
                        <span class="material-symbols-outlined">rotate_90_degrees_ccw</span>
                    </button>
                    <button
                        @click="rotate(90)"
                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-primary hover:border-primary transition-all shadow-sm"
                        title="Rotate right"
                    >
                        <span class="material-symbols-outlined">rotate_90_degrees_cw</span>
                    </button>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <BaseButton class="flex-1 md:flex-none justify-center" variant="ghost" @click="emit('close')">Cancel</BaseButton>
                    <BaseButton class="flex-1 md:flex-none justify-center" icon="crop" @click="handleApply">Apply</BaseButton>
                </div>
            </div>
        </div>
    </div>
    </Teleport>
</template>
