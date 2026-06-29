<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    faqs: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');

const buscar = () => {
    router.get(route('faqs.index'), { search: search.value }, { preserveState: true, replace: true });
};

const eliminar = (f) => {
    if (confirm('¿Eliminar esta pregunta frecuente?')) {
        router.delete(route('faqs.destroy', f.id));
    }
};
</script>

<template>
    <Head title="Preguntas frecuentes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Preguntas frecuentes</h2>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">El bot responde estas preguntas automáticamente según sus palabras clave.</p>
                <Link :href="route('faqs.create')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:opacity-90">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Nueva pregunta
                </Link>
            </div>

            <form @submit.prevent="buscar" class="flex gap-2">
                <input v-model="search" type="text" placeholder="Buscar pregunta o palabra clave…" class="w-full max-w-sm rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]" />
                <button type="submit" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Buscar</button>
            </form>

            <div class="space-y-4">
                <div v-for="f in faqs" :key="f.id" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-800">{{ f.pregunta }}</h3>
                                <span v-if="!f.activo" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">Inactiva</span>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ f.respuesta }}</p>
                            <div v-if="f.palabras_clave" class="mt-3 flex flex-wrap gap-1.5">
                                <span v-for="kw in f.palabras_clave.split(',')" :key="kw" class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700">{{ kw.trim() }}</span>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3 text-sm">
                            <Link :href="route('faqs.edit', f.id)" class="font-semibold text-[#7c3aed] hover:text-[#c026d3]">Editar</Link>
                            <button @click="eliminar(f)" class="font-semibold text-red-500 hover:text-red-700">Eliminar</button>
                        </div>
                    </div>
                </div>
                <div v-if="faqs.length === 0" class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center text-slate-400">
                    No hay preguntas frecuentes todavía.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
