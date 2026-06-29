<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    markSize: {
        type: String,
        default: 'h-10 w-10',
    },
    textSize: {
        type: String,
        default: 'text-2xl',
    },
    showText: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();
const name = computed(() => page.props.name || 'Overcloud');
const initial = computed(() => name.value.trim().charAt(0).toUpperCase() || 'O');
</script>

<template>
    <div class="flex items-center gap-3">
        <span
            :class="markSize"
            class="relative inline-flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#7c3aed] to-[#c026d3] shadow-lg shadow-fuchsia-500/30"
        >
            <svg
                viewBox="0 0 40 40"
                class="h-3/5 w-3/5 text-white"
                fill="none"
                aria-hidden="true"
            >
                <circle cx="15" cy="20" r="9" fill="currentColor" fill-opacity="0.55" />
                <circle cx="25" cy="20" r="9" fill="currentColor" fill-opacity="0.9" />
            </svg>
        </span>

        <span
            v-if="showText"
            :class="textSize"
            class="bg-gradient-to-r from-[#7c3aed] to-[#c026d3] bg-clip-text font-extrabold tracking-tight text-transparent"
        >
            {{ name }}
        </span>
    </div>
</template>
