<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    numero: '',
    lada: '',
    tipo: 'nuevo',
    estado: 'disponible',
});

const submit = () => form.post(route('numeros.store'));

const inputClass = 'mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]';
</script>

<template>
    <Head title="Agregar número" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Agregar número</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Número telefónico</label>
                    <input v-model="form.numero" type="text" :class="inputClass" placeholder="5512345678" />
                    <InputError class="mt-1" :message="form.errors.numero" />
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">LADA</label>
                        <input v-model="form.lada" type="text" :class="inputClass" placeholder="55" />
                        <InputError class="mt-1" :message="form.errors.lada" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Tipo</label>
                        <select v-model="form.tipo" :class="inputClass">
                            <option value="nuevo">Nuevo</option>
                            <option value="portabilidad">Portabilidad</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Estado</label>
                        <select v-model="form.estado" :class="inputClass">
                            <option value="disponible">Disponible</option>
                            <option value="reservado">Reservado</option>
                            <option value="asignado">Asignado</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route('numeros.index')" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-5 py-2 text-sm font-semibold text-white shadow-lg transition hover:opacity-90 disabled:opacity-50">Guardar número</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
