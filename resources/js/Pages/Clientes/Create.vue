<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nombre: '',
    telefono: '',
    email: '',
    notas: '',
});

const submit = () => form.post(route('clientes.store'));

const inputClass = 'mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]';
</script>

<template>
    <Head title="Nuevo cliente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Nuevo cliente</h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Nombre</label>
                    <input v-model="form.nombre" type="text" :class="inputClass" placeholder="Nombre del cliente" />
                    <InputError class="mt-1" :message="form.errors.nombre" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Teléfono</label>
                        <input v-model="form.telefono" type="text" :class="inputClass" placeholder="5215512345678" />
                        <InputError class="mt-1" :message="form.errors.telefono" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Correo</label>
                        <input v-model="form.email" type="email" :class="inputClass" placeholder="cliente@correo.com" />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Notas</label>
                    <textarea v-model="form.notas" rows="3" :class="inputClass"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route('clientes.index')" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-5 py-2 text-sm font-semibold text-white shadow-lg transition hover:opacity-90 disabled:opacity-50">Guardar cliente</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
