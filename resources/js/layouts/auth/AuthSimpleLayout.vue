<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { CardDescription, CardTitle } from '@/components/ui/card';
import { home } from '@/routes';

const name = usePage().props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative flex min-h-svh items-center justify-center overflow-hidden bg-muted p-4 sm:p-6 lg:p-10">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-2xl border bg-card shadow-xl shadow-primary/10 lg:grid-cols-[0.9fr_1.1fr]">
            <aside class="relative hidden min-h-[620px] flex-col justify-between overflow-hidden bg-linear-to-br from-primary via-cyan-700 to-sky-900 p-10 text-primary-foreground lg:flex">
                <div class="relative z-10 flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/25">
                        <AppLogoIcon class="size-8" />
                    </div>
                    <span class="font-semibold tracking-tight">{{ name }}</span>
                </div>
                <div class="relative z-10 max-w-sm">
                    <p class="mb-3 text-sm font-medium uppercase tracking-[0.18em] text-cyan-100">Controle da operação</p>
                    <h2 class="text-3xl font-semibold leading-tight">Tudo o que sua equipe precisa para acompanhar o negócio.</h2>
                    <p class="mt-4 text-sm leading-6 text-cyan-50/80">Contratos, clientes, cobranças e movimentações reunidos em um só lugar.</p>
                </div>
                <div class="absolute -right-20 -bottom-24 size-72 rounded-full border-[40px] border-white/10" />
            </aside>

            <main class="flex flex-col justify-center p-6 sm:p-10 lg:p-14">
                <Link :href="home()" class="mb-8 flex items-center gap-3 self-start lg:hidden">
                    <span class="flex size-10 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-sm">
                        <AppLogoIcon class="size-8" />
                    </span>
                    <span class="font-semibold tracking-tight">{{ name }}</span>
                </Link>

                <div class="mx-auto flex w-full max-w-md flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <CardTitle class="text-2xl tracking-tight">{{ title }}</CardTitle>
                        <CardDescription>{{ description }}</CardDescription>
                    </div>
                    <div>
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
