<script setup>
import { computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const name = computed(() => page.props.name || 'Overcloud');
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-slate-950 px-4 py-10"
    >
        <!-- Ambient brand gradient glows -->
        <div
            class="pointer-events-none absolute -top-32 -left-32 h-96 w-96 rounded-full bg-[#7c3aed] opacity-30 blur-3xl"
        ></div>
        <div
            class="pointer-events-none absolute -bottom-40 -right-24 h-[28rem] w-[28rem] rounded-full bg-[#c026d3] opacity-30 blur-3xl"
        ></div>
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(124,58,237,0.18),_transparent_55%)]"
        ></div>

        <div class="relative z-10 w-full max-w-md">
            <!-- Brand header -->
            <div class="mb-8 flex flex-col items-center text-center">
                <Link href="/" class="transition hover:opacity-90">
                    <ApplicationLogo mark-size="h-14 w-14" text-size="text-3xl" />
                </Link>
            </div>

            <!-- Card -->
            <div
                class="w-full rounded-2xl border border-white/10 bg-white/95 p-8 shadow-2xl shadow-purple-950/40 backdrop-blur-sm sm:p-10"
            >
                <slot />
            </div>

            <!-- Footer credit -->
            <p class="mt-8 text-center text-xs text-slate-500">
                {{ name }} &middot; entregado por
                <span class="font-semibold text-slate-400">Overcloud</span>
            </p>
        </div>
    </div>
</template>
