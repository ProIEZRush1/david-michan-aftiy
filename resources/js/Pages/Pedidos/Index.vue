<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    pedidos: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    estados: { type: Array, default: () => [] },
});

const search = ref(props.filters.search ?? '');
const estado = ref(props.filters.estado ?? '');

const money = (cents) =>
    '$' + Number((cents ?? 0) / 100).toLocaleString('es-MX', { minimumFractionDigits: 0 }) + ' MXN';

const buscar = () => {
    router.get(route('pedidos.index'), { search: search.value, estado: estado.value }, { preserveState: true, replace: true });
};

const eliminar = (p) => {
    if (confirm(`¿Eliminar el pedido #${p.id}?`)) {
        router.delete(route('pedidos.destroy', p.id));
    }
};

const estadoBadge = (e) => ({
    iniciado: 'bg-slate-100 text-slate-700',
    en_pago: 'bg-amber-100 text-amber-700',
    pagado: 'bg-blue-100 text-blue-700',
    numero_asignado: 'bg-violet-100 text-violet-700',
    entregado: 'bg-green-100 text-green-700',
    cancelado: 'bg-red-100 text-red-700',
}[e] ?? 'bg-slate-100 text-slate-700');
</script>

<template>
    <Head title="Pedidos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Pedidos</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">Flujo: iniciado → en pago → pagado → número asignado → entregado.</p>
                <Link :href="route('pedidos.create')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:opacity-90">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Nuevo pedido
                </Link>
            </div>

            <form @submit.prevent="buscar" class="flex flex-wrap gap-2">
                <input v-model="search" type="text" placeholder="Buscar cliente o teléfono…" class="w-full max-w-xs rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]" />
                <select v-model="estado" class="rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]">
                    <option value="">Todos los estados</option>
                    <option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option>
                </select>
                <button type="submit" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Filtrar</button>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                <th class="px-6 py-3">#</th>
                                <th class="px-6 py-3">Cliente</th>
                                <th class="px-6 py-3">Plan</th>
                                <th class="px-6 py-3">Número</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3 text-right">Total</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="p in pedidos" :key="p.id" class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 font-mono text-xs text-slate-400">#{{ p.id }}</td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-800">{{ p.cliente }}</p>
                                    <p class="font-mono text-xs text-slate-400">{{ p.telefono }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ p.plan ?? '—' }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-600">{{ p.numero ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span :class="estadoBadge(p.estado)" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">{{ p.estado_label }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-slate-800">{{ money(p.total) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <Link :href="route('pedidos.edit', p.id)" class="font-semibold text-[#7c3aed] hover:text-[#c026d3]">Gestionar</Link>
                                        <button @click="eliminar(p)" class="font-semibold text-red-500 hover:text-red-700">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="pedidos.length === 0">
                                <td colspan="7" class="px-6 py-10 text-center text-slate-400">No hay pedidos. Llegarán automáticamente desde el bot de WhatsApp.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
