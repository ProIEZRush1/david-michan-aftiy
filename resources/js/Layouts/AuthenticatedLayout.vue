<script setup>
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();

const businessName = computed(() => page.props.name ?? 'David Michan');

const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashError = computed(() => page.props.flash?.error ?? null);

const brandInitials = computed(() => {
    const name = (businessName.value || '').trim();
    if (!name) return 'DM';
    const parts = name.split(/\s+/).filter(Boolean);
    if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase();
    }
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

const userName = computed(() => page.props.auth?.user?.name ?? '');
const userEmail = computed(() => page.props.auth?.user?.email ?? '');

const userInitials = computed(() => {
    const name = (userName.value || '').trim();
    if (!name) return '?';
    const parts = name.split(/\s+/).filter(Boolean);
    if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase();
    }
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

// Menú del giro: todos los módulos del sistema de venta de líneas.
const navItems = [
    { route: 'dashboard', label: 'Inicio', match: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { route: 'planes.index', label: 'Planes', match: 'planes.*', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { route: 'numeros.index', label: 'Números', match: 'numeros.*', icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z' },
    { route: 'pedidos.index', label: 'Pedidos', match: 'pedidos.*', icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293A1 1 0 005.414 17H17M17 17a2 2 0 100 4 2 2 0 000-4zM9 19a2 2 0 11-4 0 2 2 0 014 0z' },
    { route: 'clientes.index', label: 'Clientes', match: 'clientes.*', icon: 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-3-6.65' },
    { route: 'faqs.index', label: 'Preguntas frecuentes', match: 'faqs.*', icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { route: 'conversaciones.index', label: 'Conversaciones', match: 'conversaciones.*', icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' },
    { route: 'conectar', label: 'Conectar WhatsApp', match: 'conectar', icon: 'M3 20l1.3-3.9A8 8 0 1 1 7.9 19.7L3 20z' },
];

const isActive = (match) => route().current(match);
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <!-- Sidebar (desktop) -->
        <aside
            class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200 bg-white lg:flex"
        >
            <!-- Brand -->
            <div class="flex h-20 items-center gap-3 px-6">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#7c3aed] to-[#c026d3] text-sm font-bold text-white shadow-lg shadow-fuchsia-500/20"
                    >
                        {{ brandInitials }}
                    </span>
                    <span
                        class="bg-gradient-to-r from-[#7c3aed] to-[#c026d3] bg-clip-text text-lg font-extrabold leading-tight tracking-tight text-transparent"
                    >
                        {{ businessName }}
                    </span>
                </Link>
            </div>

            <!-- Nav -->
            <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    :class="[
                        'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition',
                        isActive(item.match)
                            ? 'bg-gradient-to-r from-[#7c3aed] to-[#c026d3] text-white shadow-md shadow-fuchsia-500/20'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                    ]"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <!-- Footer credit -->
            <div class="px-6 py-5">
                <p class="text-xs text-slate-400">
                    Desarrollado por
                    <span class="font-semibold text-slate-500">Overcloud</span>
                </p>
            </div>
        </aside>

        <!-- Main column -->
        <div class="lg:pl-64">
            <!-- Top bar -->
            <header
                class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/80 px-4 backdrop-blur sm:px-6 lg:px-8"
            >
                <!-- Mobile brand + hamburger -->
                <div class="flex flex-1 items-center gap-3 lg:hidden">
                    <button
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                        class="inline-flex items-center justify-center rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none"
                    >
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path
                                :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                    <Link :href="route('dashboard')" class="flex items-center gap-2">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#7c3aed] to-[#c026d3] text-xs font-bold text-white"
                        >
                            {{ brandInitials }}
                        </span>
                        <span
                            class="bg-gradient-to-r from-[#7c3aed] to-[#c026d3] bg-clip-text text-base font-extrabold text-transparent"
                        >
                            {{ businessName }}
                        </span>
                    </Link>
                </div>

                <!-- Page heading slot (desktop, inline) -->
                <div class="hidden min-w-0 flex-1 lg:block">
                    <slot name="header" />
                </div>

                <!-- User dropdown -->
                <div class="relative">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-3 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:text-slate-900 focus:outline-none"
                            >
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-[#7c3aed] to-[#c026d3] text-xs font-bold text-white"
                                >
                                    {{ userInitials }}
                                </span>
                                <span class="hidden sm:inline">{{ userName }}</span>
                                <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="text-sm font-semibold text-slate-800">{{ userName }}</p>
                                <p class="truncate text-xs text-slate-500">{{ userEmail }}</p>
                            </div>
                            <DropdownLink :href="route('profile.edit')">Mi perfil</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Cerrar sesión</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Mobile slide-down nav -->
            <div
                :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                class="border-b border-slate-200 bg-white lg:hidden"
            >
                <div class="space-y-1 px-4 py-3">
                    <ResponsiveNavLink
                        v-for="item in navItems"
                        :key="item.route"
                        :href="route(item.route)"
                        :active="isActive(item.match)"
                    >
                        {{ item.label }}
                    </ResponsiveNavLink>
                </div>
                <div class="border-t border-slate-200 px-4 py-4">
                    <p class="text-base font-semibold text-slate-800">{{ userName }}</p>
                    <p class="text-sm text-slate-500">{{ userEmail }}</p>
                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">Mi perfil</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">Cerrar sesión</ResponsiveNavLink>
                    </div>
                </div>
            </div>

            <!-- Mobile page heading -->
            <div v-if="$slots.header" class="border-b border-slate-200 bg-white px-4 py-5 sm:px-6 lg:hidden">
                <slot name="header" />
            </div>

            <!-- Page content -->
            <main class="px-4 py-8 sm:px-6 lg:px-8">
                <!-- Flash messages -->
                <div v-if="flashSuccess || flashError" class="mx-auto mb-6 max-w-7xl space-y-3">
                    <div
                        v-if="flashSuccess"
                        class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ flashSuccess }}
                    </div>
                    <div
                        v-if="flashError"
                        class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"
                    >
                        {{ flashError }}
                    </div>
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
