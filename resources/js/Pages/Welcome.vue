<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
    laravelVersion: {
        type: String,
        default: '',
    },
    phpVersion: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <Head :title="$page.props.name" />

    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 px-4 py-12 text-white"
    >
        <div
            class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-violet-600/30 blur-3xl"
        ></div>
        <div
            class="pointer-events-none absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-fuchsia-600/30 blur-3xl"
        ></div>

        <main
            class="relative w-full max-w-xl rounded-2xl border border-white/10 bg-white/5 px-8 py-12 text-center shadow-2xl shadow-violet-900/40 backdrop-blur"
        >
            <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-2xl font-black text-white shadow-lg"
            >
                {{ ($page.props.name || 'O').charAt(0) }}
            </div>

            <h1
                class="mt-6 bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-4xl font-extrabold tracking-tight text-transparent"
            >
                {{ $page.props.name }}
            </h1>

            <p class="mt-4 text-base text-slate-300">
                Tu sitio está en línea y funcionando. Bienvenido.
            </p>

            <nav v-if="canLogin" class="mt-8 flex items-center justify-center gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-600/30 transition hover:from-violet-500 hover:to-fuchsia-500"
                >
                    Ir al panel
                </Link>

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-600/30 transition hover:from-violet-500 hover:to-fuchsia-500"
                    >
                        Iniciar sesión
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-xl border border-white/15 px-6 py-3 text-sm font-semibold text-slate-200 transition hover:border-white/30 hover:text-white"
                    >
                        Crear cuenta
                    </Link>
                </template>
            </nav>
        </main>

        <footer class="relative mt-8 text-xs text-slate-400">
            Desarrollado por
            <span class="font-semibold text-slate-300">Overcloud</span>
        </footer>
    </div>
</template>
