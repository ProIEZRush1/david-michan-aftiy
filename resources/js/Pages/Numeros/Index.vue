<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    numeros: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    resumen: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const estado = ref(props.filters.estado ?? '');

const buscar = () => {
    router.get(route('numeros.index'), { search: search.value, estado: estado.value }, { preserveState: true, replace: true });
};

const eliminar = (n) => {
    if (confirm(`¿Eliminar el número ${n.numero} del inventario?`)) {
        router.delete(route('numeros.destroy', n.id));
    }
};

const estadoBadge = (e) => ({
    disponible: 'bg-green-100 text-green-700',
    reservado: 'bg-amber-100 text-amber-700',
    asignado: 'bg-violet-100 text-violet-700',
}[e] ?? 'bg-slate-100 text-slate-600');

const estadoLabel = (e) => ({ disponible: 'Disponible', reservado: 'Reservado', asignado: 'Asignado' }[e] ?? e);
</script>

<template>
    <Head title="Números" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Inventario de números</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <p class="text-2xl font-extrabold text-green-600">{{ resumen.disponible ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-500">Disponibles</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <p class="text-2xl font-extrabold text-amber-600">{{ resumen.reservado ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-500">Reservados</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <p class="text-2xl font-extrabold text-violet-600">{{ resumen.asignado ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-500">Asignados</p>
                </div>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">El bot asigna automáticamente un número disponible al confirmarse el pago.</p>
                <Link
                    :href="route('numeros.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:opacity-90"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Agregar número
                </Link>
            </div>

            <form @submit.prevent="buscar" class="flex flex-wrap gap-2">
                <input v-model="search" type="text" placeholder="Buscar número o LADA…" class="w-full max-w-xs rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]" />
                <select v-model="estado" class="rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]">
                    <option value="">Todos los estados</option>
                    <option value="disponible">Disponibles</option>
                    <option value="reservado">Reservados</option>
                    <option value="asignado">Asignados</option>
                </select>
                <button type="submit" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Filtrar</button>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                <th class="px-6 py-3">Número</th>
                                <th class="px-6 py-3">LADA</th>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="n in numeros" :key="n.id" class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 font-mono font-semibold text-slate-800">{{ n.numero }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ n.lada ?? '—' }}</td>
                                <td class="px-6 py-4 capitalize text-slate-600">{{ n.tipo }}</td>
                                <td class="px-6 py-4">
                                    <span :class="estadoBadge(n.estado)" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">{{ estadoLabel(n.estado) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <Link :href="route('numeros.edit', n.id)" class="font-semibold text-[#7c3aed] hover:text-[#c026d3]">Editar</Link>
                                        <button @click="eliminar(n)" class="font-semibold text-red-500 hover:text-red-700">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="numeros.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400">No hay números en el inventario.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
