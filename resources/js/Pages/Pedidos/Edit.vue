<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    pedido: { type: Object, required: true },
    planes: { type: Array, default: () => [] },
    estados: { type: Array, default: () => [] },
    numeros: { type: Array, default: () => [] },
});

const form = useForm({
    cliente: props.pedido.cliente ?? '',
    telefono: props.pedido.telefono ?? '',
    email: props.pedido.email ?? '',
    plan_id: props.pedido.plan_id ?? '',
    numero_id: props.pedido.numero_id ?? '',
    estado: props.pedido.estado ?? 'iniciado',
    notas: props.pedido.notas ?? '',
});

const submit = () => form.put(route('pedidos.update', props.pedido.id));

const inputClass = 'mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-[#7c3aed] focus:ring-[#7c3aed]';
</script>

<template>
    <Head title="Gestionar pedido" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Pedido #{{ pedido.id }}</h2>
        </template>

        <div class="mx-auto max-w-2xl space-y-6">
            <div v-if="pedido.link_pago" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                <span class="font-semibold">Link de pago:</span>
                <span class="break-all font-mono">{{ pedido.link_pago }}</span>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Cliente</label>
                        <input v-model="form.cliente" type="text" :class="inputClass" />
                        <InputError class="mt-1" :message="form.errors.cliente" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Teléfono</label>
                        <input v-model="form.telefono" type="text" :class="inputClass" />
                        <InputError class="mt-1" :message="form.errors.telefono" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Correo</label>
                        <input v-model="form.email" type="email" :class="inputClass" />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Plan</label>
                        <select v-model="form.plan_id" :class="inputClass">
                            <option v-for="p in planes" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.plan_id" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Estado</label>
                        <select v-model="form.estado" :class="inputClass">
                            <option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.estado" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Número asignado</label>
                        <select v-model="form.numero_id" :class="inputClass">
                            <option :value="''">Sin asignar</option>
                            <option v-for="n in numeros" :key="n.id" :value="n.id">{{ n.numero }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.numero_id" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Notas</label>
                    <textarea v-model="form.notas" rows="3" :class="inputClass"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <Link :href="route('pedidos.index')" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-5 py-2 text-sm font-semibold text-white shadow-lg transition hover:opacity-90 disabled:opacity-50">Guardar cambios</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
