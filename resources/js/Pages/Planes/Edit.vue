<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    plan: { type: Object, required: true },
});

const form = useForm({
    nombre: props.plan.nombre ?? '',
    precio: props.plan.precio ?? '',
    datos: props.plan.datos ?? '',
    minutos: props.plan.minutos ?? '',
    sms: props.plan.sms ?? '',
    vigencia_dias: props.plan.vigencia_dias ?? 30,
    descripcion: props.plan.descripcion ?? '',
    activo: !!props.plan.activo,
    orden: props.plan.orden ?? 0,
});

const submit = () => form.put(route('planes.update', props.plan.id));

const inputClass = 'mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]';
</script>

<template>
    <Head title="Editar plan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Editar plan</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Nombre del plan</label>
                    <input v-model="form.nombre" type="text" :class="inputClass" />
                    <InputError class="mt-1" :message="form.errors.nombre" />
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Precio (MXN)</label>
                        <input v-model="form.precio" type="number" step="0.01" min="0" :class="inputClass" />
                        <InputError class="mt-1" :message="form.errors.precio" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Vigencia (días)</label>
                        <input v-model="form.vigencia_dias" type="number" min="1" :class="inputClass" />
                        <InputError class="mt-1" :message="form.errors.vigencia_dias" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Datos</label>
                        <input v-model="form.datos" type="text" :class="inputClass" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Minutos</label>
                        <input v-model="form.minutos" type="text" :class="inputClass" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">SMS</label>
                        <input v-model="form.sms" type="text" :class="inputClass" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Descripción</label>
                    <textarea v-model="form.descripcion" rows="3" :class="inputClass"></textarea>
                </div>

                <div class="flex items-center gap-3">
                    <input id="activo" v-model="form.activo" type="checkbox" class="rounded border-slate-300 text-[#7c3aed] focus:ring-[#7c3aed]" />
                    <label for="activo" class="text-sm font-medium text-slate-700">Plan activo (visible para el bot)</label>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route('planes.index')" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-5 py-2 text-sm font-semibold text-white shadow-lg transition hover:opacity-90 disabled:opacity-50"
                    >
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
