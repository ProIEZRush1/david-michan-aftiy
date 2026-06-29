<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    pregunta: '',
    respuesta: '',
    palabras_clave: '',
    activo: true,
    orden: 0,
});

const submit = () => form.post(route('faqs.store'));

const inputClass = 'mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]';
</script>

<template>
    <Head title="Nueva pregunta frecuente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Nueva pregunta frecuente</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Pregunta</label>
                    <input v-model="form.pregunta" type="text" :class="inputClass" placeholder="¿Tienen cobertura en mi zona?" />
                    <InputError class="mt-1" :message="form.errors.pregunta" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Respuesta del bot</label>
                    <textarea v-model="form.respuesta" rows="4" :class="inputClass" placeholder="Respuesta que enviará el bot…"></textarea>
                    <InputError class="mt-1" :message="form.errors.respuesta" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Palabras clave (separadas por coma)</label>
                    <input v-model="form.palabras_clave" type="text" :class="inputClass" placeholder="cobertura, señal, alcance" />
                    <p class="mt-1 text-xs text-slate-400">El bot usa estas palabras para detectar cuándo enviar esta respuesta.</p>
                    <InputError class="mt-1" :message="form.errors.palabras_clave" />
                </div>
                <div class="flex items-center gap-3">
                    <input id="activo" v-model="form.activo" type="checkbox" class="rounded border-slate-300 text-[#7c3aed] focus:ring-[#7c3aed]" />
                    <label for="activo" class="text-sm font-medium text-slate-700">Activa (el bot la usará)</label>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route('faqs.index')" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-5 py-2 text-sm font-semibold text-white shadow-lg transition hover:opacity-90 disabled:opacity-50">Guardar pregunta</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
