<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const status = ref('connecting');
const qrDataUrl = ref(null);
const me = ref(null);

let pollTimer = null;

const connectedNumber = computed(() => {
    if (!me.value) return '';
    // me viene como un JID tipo "5215512345678:12@s.whatsapp.net"
    const digits = String(me.value).split('@')[0].split(':')[0];
    return digits ? `+${digits}` : '';
});

async function fetchQr() {
    try {
        const res = await fetch('/api/wa/qr', {
            headers: { Accept: 'application/json' },
            cache: 'no-store',
        });
        if (!res.ok) {
            status.value = 'disconnected';
            return;
        }
        const data = await res.json();
        status.value = data.status ?? 'disconnected';
        qrDataUrl.value = data.qrDataUrl ?? null;
        me.value = data.me ?? null;
    } catch (e) {
        status.value = 'disconnected';
    }
}

onMounted(() => {
    fetchQr();
    pollTimer = setInterval(fetchQr, 3000);
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
});
</script>

<template>
    <Head title="Conectar WhatsApp" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold text-slate-800">Conectar WhatsApp</h1>
        </template>

        <div class="mx-auto max-w-xl">
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <!-- Cabecera con gradiente de marca -->
                <div
                    class="bg-gradient-to-r from-[#7c3aed] to-[#c026d3] px-6 py-5 text-white"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15"
                        >
                            <svg
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path
                                    d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.86 9.86 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.13h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.11.82.83-3.04-.2-.31a8.16 8.16 0 0 1-1.25-4.36c0-4.54 3.7-8.23 8.24-8.23 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.82c0 4.54-3.69 8.24-8.24 8.24Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.16.25-.64.81-.78.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.51.11-.11.25-.29.37-.43.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.23.25-.86.85-.86 2.07s.89 2.4 1.01 2.56c.12.17 1.74 2.66 4.22 3.73.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.22-.17-.47-.29Z"
                                />
                            </svg>
                        </span>
                        <div>
                            <p class="text-base font-bold leading-tight">
                                Vincula tu WhatsApp
                            </p>
                            <p class="text-sm text-white/80">
                                Escanea el código para activar el bot
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-8">
                    <!-- Conectado -->
                    <div
                        v-if="status === 'connected'"
                        class="flex flex-col items-center text-center"
                    >
                        <span
                            class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100"
                        >
                            <svg
                                class="h-9 w-9 text-green-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </span>
                        <h2 class="mt-4 text-lg font-bold text-green-700">
                            WhatsApp conectado
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Tu número está vinculado y el bot está activo.
                        </p>
                        <span
                            v-if="connectedNumber"
                            class="mt-4 inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700"
                        >
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            {{ connectedNumber }}
                        </span>
                    </div>

                    <!-- Código QR -->
                    <div
                        v-else-if="status === 'qr' && qrDataUrl"
                        class="flex flex-col items-center text-center"
                    >
                        <div
                            class="rounded-2xl border-2 border-slate-100 bg-white p-3 shadow-inner"
                        >
                            <img
                                :src="qrDataUrl"
                                alt="Código QR de WhatsApp"
                                class="h-64 w-64"
                            />
                        </div>
                        <h2 class="mt-5 text-base font-bold text-slate-800">
                            Escanea este código con WhatsApp
                        </h2>
                        <ol
                            class="mt-3 space-y-1 text-left text-sm text-slate-600"
                        >
                            <li>
                                1. Abre <span class="font-semibold">WhatsApp</span> en tu teléfono
                            </li>
                            <li>
                                2. Toca
                                <span class="font-semibold">Dispositivos vinculados</span>
                            </li>
                            <li>
                                3. Toca
                                <span class="font-semibold">Vincular un dispositivo</span>
                                y escanea este código
                            </li>
                        </ol>
                    </div>

                    <!-- Conectando -->
                    <div
                        v-else-if="status === 'connecting'"
                        class="flex flex-col items-center py-10 text-center"
                    >
                        <svg
                            class="h-12 w-12 animate-spin text-[#7c3aed]"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"
                            />
                        </svg>
                        <p class="mt-4 text-sm font-semibold text-slate-600">
                            Conectando…
                        </p>
                        <p class="mt-1 text-sm text-slate-400">
                            Estamos preparando tu conexión, espera un momento.
                        </p>
                    </div>

                    <!-- Desconectado -->
                    <div
                        v-else
                        class="flex flex-col items-center py-10 text-center"
                    >
                        <span
                            class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100"
                        >
                            <svg
                                class="h-8 w-8 text-slate-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M18.364 5.636a9 9 0 010 12.728M5.636 18.364a9 9 0 010-12.728M12 12h.01"
                                />
                            </svg>
                        </span>
                        <h2 class="mt-4 text-lg font-bold text-slate-700">
                            Desconectado
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Espera unos segundos o recarga la página para volver a
                            generar el código.
                        </p>
                    </div>
                </div>
            </div>

            <p class="mt-4 text-center text-xs text-slate-400">
                Esta página se actualiza automáticamente cada pocos segundos.
            </p>
        </div>
    </AuthenticatedLayout>
</template>
