<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    planes: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');

const money = (cents) =>
    '$' + Number((cents ?? 0) / 100).toLocaleString('es-MX', { minimumFractionDigits: 0 }) + ' MXN';

const buscar = () => {
    router.get(route('planes.index'), { search: search.value }, { preserveState: true, replace: true });
};

const eliminar = (plan) => {
    if (confirm(`¿Eliminar el plan "${plan.nombre}"?`)) {
        router.delete(route('planes.destroy', plan.id));
    }
};
</script>

<template>
    <Head title="Planes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Planes de líneas</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">{{ planes.length }} plan(es) configurados para vender por WhatsApp.</p>
                <Link
                    :href="route('planes.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:opacity-90"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Nuevo plan
                </Link>
            </div>

            <form @submit.prevent="buscar" class="flex gap-2">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Buscar plan…"
                    class="w-full max-w-sm rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]"
                />
                <button type="submit" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Buscar</button>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                <th class="px-6 py-3">Plan</th>
                                <th class="px-6 py-3">Incluye</th>
                                <th class="px-6 py-3">Precio</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="plan in planes" :key="plan.id" class="hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-800">{{ plan.nombre }}</p>
                                    <p class="text-xs text-slate-400">{{ plan.descripcion }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span v-if="plan.datos" class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700">📶 {{ plan.datos }}</span>
                                        <span v-if="plan.minutos" class="rounded-full bg-fuchsia-50 px-2 py-0.5 text-xs font-medium text-fuchsia-700">📞 {{ plan.minutos }}</span>
                                        <span v-if="plan.sms" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">💬 {{ plan.sms }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-800">{{ money(plan.precio) }}</td>
                                <td class="px-6 py-4">
                                    <span :class="plan.activo ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
                                        {{ plan.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <Link :href="route('planes.edit', plan.id)" class="font-semibold text-[#7c3aed] hover:text-[#c026d3]">Editar</Link>
                                        <button @click="eliminar(plan)" class="font-semibold text-red-500 hover:text-red-700">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="planes.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400">No hay planes. Crea el primero con "Nuevo plan".</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
