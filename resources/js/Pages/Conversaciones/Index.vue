<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    conversaciones: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    resumen: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');

const buscar = () => {
    router.get(route('conversaciones.index'), { search: search.value }, { preserveState: true, replace: true });
};

const tomar = (c) => router.patch(route('conversaciones.tomar', c.id), {}, { preserveScroll: true });
const devolver = (c) => router.patch(route('conversaciones.devolver', c.id), {}, { preserveScroll: true });

const stepBadge = (s) => ({
    new: 'bg-slate-100 text-slate-600',
    choosing: 'bg-blue-100 text-blue-700',
    confirming: 'bg-amber-100 text-amber-700',
    paying: 'bg-amber-100 text-amber-700',
    done: 'bg-green-100 text-green-700',
    human: 'bg-fuchsia-100 text-fuchsia-700',
}[s] ?? 'bg-slate-100 text-slate-600');

const formatPhone = (phone) => {
    const digits = String(phone).split('@')[0].split(':')[0];
    return digits ? '+' + digits : phone;
};
</script>

<template>
    <Head title="Conversaciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Conversaciones del bot</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <p class="text-2xl font-extrabold text-slate-800">{{ resumen.total ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-500">Total</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <p class="text-2xl font-extrabold text-amber-600">{{ resumen.en_proceso ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-500">En proceso de venta</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                    <p class="text-2xl font-extrabold text-fuchsia-600">{{ resumen.con_asesor ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-500">Con asesor humano</p>
                </div>
            </div>

            <form @submit.prevent="buscar" class="flex gap-2">
                <input v-model="search" type="text" placeholder="Buscar por nombre o teléfono…" class="w-full max-w-sm rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]" />
                <button type="submit" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Buscar</button>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                <th class="px-6 py-3">Contacto</th>
                                <th class="px-6 py-3">Etapa</th>
                                <th class="px-6 py-3">Pedidos</th>
                                <th class="px-6 py-3">Actividad</th>
                                <th class="px-6 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="c in conversaciones" :key="c.id" class="hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-800">{{ c.name ?? 'Sin nombre' }}</p>
                                    <p class="font-mono text-xs text-slate-400">{{ formatPhone(c.phone) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="stepBadge(c.step)" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">{{ c.step_label }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">{{ c.pedidos_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ c.actualizado }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <button v-if="c.step !== 'human'" @click="tomar(c)" class="font-semibold text-fuchsia-600 hover:text-fuchsia-800">Tomar conversación</button>
                                        <button v-else @click="devolver(c)" class="font-semibold text-[#7c3aed] hover:text-[#c026d3]">Devolver al bot</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="conversaciones.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400">Aún no hay conversaciones. Aparecerán cuando los clientes escriban al WhatsApp.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
