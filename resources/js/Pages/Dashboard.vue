<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    metrics: { type: Object, default: () => ({}) },
    pedidosPorEstado: { type: Object, default: () => ({}) },
    ultimosPedidos: { type: Array, default: () => [] },
});

const page = usePage();
const businessName = computed(() => page.props.name ?? 'David Michan');
const userFirstName = computed(() => {
    const name = (page.props.auth?.user?.name ?? '').trim();
    return name ? name.split(/\s+/)[0] : '';
});

const money = (cents) =>
    '$' + Number((cents ?? 0) / 100).toLocaleString('es-MX', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' MXN';

// Tarjetas-resumen por módulo (conteos reales del controlador).
const cards = computed(() => [
    {
        label: 'Planes activos', value: props.metrics.planes ?? 0, hint: 'Líneas a la venta',
        href: route('planes.index'), gradient: 'from-[#7c3aed] to-[#a855f7]',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    },
    {
        label: 'Números disponibles', value: props.metrics.numeros_disponibles ?? 0, hint: `${props.metrics.numeros_total ?? 0} en inventario`,
        href: route('numeros.index'), gradient: 'from-[#a21caf] to-[#c026d3]',
        icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
    },
    {
        label: 'Pedidos', value: props.metrics.pedidos ?? 0, hint: `${props.metrics.pedidos_abiertos ?? 0} en proceso`,
        href: route('pedidos.index'), gradient: 'from-[#7c3aed] to-[#c026d3]',
        icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293A1 1 0 005.414 17H17M17 17a2 2 0 100 4 2 2 0 000-4zM9 19a2 2 0 11-4 0 2 2 0 014 0z',
    },
    {
        label: 'Clientes', value: props.metrics.clientes ?? 0, hint: 'Registrados',
        href: route('clientes.index'), gradient: 'from-[#c026d3] to-[#db2777]',
        icon: 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-3-6.65',
    },
    {
        label: 'Preguntas frecuentes', value: props.metrics.faqs ?? 0, hint: 'Base de conocimiento del bot',
        href: route('faqs.index'), gradient: 'from-[#7c3aed] to-[#a855f7]',
        icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        label: 'Conversaciones', value: props.metrics.conversaciones ?? 0, hint: `${props.metrics.conversaciones_humano ?? 0} con asesor`,
        href: route('conversaciones.index'), gradient: 'from-[#a21caf] to-[#c026d3]',
        icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    },
]);

const estadoBadge = (estado) => ({
    iniciado: 'bg-slate-100 text-slate-700',
    en_pago: 'bg-amber-100 text-amber-700',
    pagado: 'bg-blue-100 text-blue-700',
    numero_asignado: 'bg-violet-100 text-violet-700',
    entregado: 'bg-green-100 text-green-700',
    cancelado: 'bg-red-100 text-red-700',
}[estado] ?? 'bg-slate-100 text-slate-700');
</script>

<template>
    <Head title="Inicio" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Panel de control</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-8">
            <!-- Hero -->
            <section
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#7c3aed] to-[#c026d3] p-8 text-white shadow-xl shadow-fuchsia-500/20 sm:p-10"
            >
                <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-fuchsia-300/20 blur-2xl"></div>
                <div class="relative">
                    <p class="text-sm font-medium uppercase tracking-widest text-white/70">Bot de ventas por WhatsApp</p>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight sm:text-4xl">
                        Hola<span v-if="userFirstName">, {{ userFirstName }}</span> 👋
                    </h1>
                    <p class="mt-3 max-w-2xl text-base text-white/85">
                        Bienvenido al sistema de <span class="font-semibold">{{ businessName }}</span>.
                        Administra tus planes de líneas telefónicas, el inventario de números, los pedidos
                        del bot y la atención a tus clientes, todo en un solo lugar.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <Link :href="route('pedidos.index')" class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-[#7c3aed] shadow-lg transition hover:bg-white/90">
                            Ver pedidos
                        </Link>
                        <Link :href="route('conectar')" class="rounded-xl border border-white/40 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                            Conectar WhatsApp
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Ingresos destacados -->
            <section class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
                    <p class="text-sm font-semibold text-slate-500">Ingresos confirmados</p>
                    <p class="mt-2 text-3xl font-extrabold text-slate-800">{{ money(metrics.ingresos) }}</p>
                    <p class="mt-1 text-xs text-slate-400">Pedidos pagados, asignados y entregados</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                    <p class="mb-3 text-sm font-semibold text-slate-500">Pedidos por estado</p>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="(total, estado) in pedidosPorEstado"
                            :key="estado"
                            class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold"
                            :class="estadoBadge(estado)"
                        >
                            {{ estado.replaceAll('_', ' ') }}
                            <span class="rounded-full bg-white/60 px-2 text-xs">{{ total }}</span>
                        </span>
                        <span v-if="Object.keys(pedidosPorEstado).length === 0" class="text-sm text-slate-400">Sin pedidos todavía.</span>
                    </div>
                </div>
            </section>

            <!-- Tarjetas por módulo -->
            <section>
                <h3 class="mb-4 text-lg font-bold text-slate-800">Tus módulos</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    <Link
                        v-for="card in cards"
                        :key="card.label"
                        :href="card.href"
                        class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <div class="flex items-start justify-between">
                            <span :class="['flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-md', card.gradient]">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="card.icon" />
                                </svg>
                            </span>
                            <span class="text-sm font-semibold text-[#7c3aed] opacity-0 transition group-hover:opacity-100">Administrar →</span>
                        </div>
                        <p class="mt-4 text-3xl font-extrabold text-slate-800">{{ card.value }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-600">{{ card.label }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ card.hint }}</p>
                    </Link>
                </div>
            </section>

            <!-- Últimos pedidos -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Últimos pedidos</h3>
                    <Link :href="route('pedidos.index')" class="text-sm font-semibold text-[#7c3aed] hover:text-[#c026d3]">Ver todos →</Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-400">
                                <th class="py-2 pr-4">#</th>
                                <th class="py-2 pr-4">Cliente</th>
                                <th class="py-2 pr-4">Plan</th>
                                <th class="py-2 pr-4">Número</th>
                                <th class="py-2 pr-4">Estado</th>
                                <th class="py-2 pr-4 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="p in ultimosPedidos" :key="p.id" class="text-slate-700">
                                <td class="py-3 pr-4 font-mono text-xs text-slate-400">#{{ p.id }}</td>
                                <td class="py-3 pr-4 font-semibold">{{ p.cliente }}</td>
                                <td class="py-3 pr-4">{{ p.plan ?? '—' }}</td>
                                <td class="py-3 pr-4 font-mono text-xs">{{ p.numero ?? '—' }}</td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="estadoBadge(p.estado)">
                                        {{ p.estado_label }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-right font-semibold">{{ money(p.total) }}</td>
                            </tr>
                            <tr v-if="ultimosPedidos.length === 0">
                                <td colspan="6" class="py-6 text-center text-slate-400">Aún no hay pedidos registrados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <p class="text-center text-xs text-slate-400">
                Sistema desarrollado por <span class="font-semibold text-slate-500">Overcloud</span> para {{ businessName }}.
            </p>
        </div>
    </AuthenticatedLayout>
</template>
