```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import {
    ArrowRight,
    BarChart3,
    Building2,
    CheckCircle2,
    ClipboardList,
    Package,
    ShieldCheck,
    Users,
    ChevronRight,
    TrendingUp,
} from '@lucide/vue';

import { dashboard, login } from '@/routes';

const modules = [
    {
        title: 'Clientes',
        description: 'Cadastro centralizado com contatos, histórico e informações essenciais.',
        icon: Users,
    },
    {
        title: 'Contratos',
        description: 'Acompanhe prazos, valores, status e condições em um único lugar.',
        icon: ClipboardList,
    },
    {
        title: 'Cobranças',
        description: 'Tenha uma visão clara dos recebimentos, vencimentos e pendências.',
        icon: BarChart3,
    },
    {
        title: 'Movimentações',
        description: 'Registre retiradas e devoluções mantendo cada contrato rastreável.',
        icon: Package,
    },
];

const commitments = [
    {
        number: '01',
        title: 'Mais organização',
        description: 'Informações centralizadas para reduzir perda de dados e retrabalho.',
    },
    {
        number: '02',
        title: 'Mais controle',
        description: 'Acompanhe contratos, movimentações e financeiro com clareza.',
    },
    {
        number: '03',
        title: 'Mais previsibilidade',
        description: 'Uma rotina estruturada para decisões mais rápidas e seguras.',
    },
];
</script>

<template>
    <Head title="Início">
        <meta
            head-key="description"
            name="description"
            content="LocalFlow centraliza clientes, contratos, cobranças e movimentações para uma gestão profissional da operação."
        />
    </Head>

    <div class="min-h-svh bg-[#f8fafc] text-slate-950">

        <!-- HEADER -->
        <header
            class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl"
        >
            <div
                class="mx-auto flex h-[76px] w-full max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-10"
            >
                <Link
                    href="/"
                    class="flex items-center"
                    aria-label="Página inicial"
                >
                    <img
                        src="/logo_america.png"
                        alt="América Materiais para Construção"
                        class="h-10 w-auto max-w-[175px] object-contain"
                    />
                </Link>

                <nav class="hidden items-center gap-9 md:flex">
                    <a
                        href="#solucao"
                        class="text-sm font-medium text-slate-500 transition hover:text-[#0755a0]"
                    >
                        Visão geral
                    </a>

                    <a
                        href="#gestao"
                        class="text-sm font-medium text-slate-500 transition hover:text-[#0755a0]"
                    >
                        Gestão
                    </a>

                    <a
                        href="#recursos"
                        class="text-sm font-medium text-slate-500 transition hover:text-[#0755a0]"
                    >
                        Recursos
                    </a>
                </nav>

                <Link
                    :href="$page.props.auth.user ? dashboard() : login()"
                    class="group inline-flex items-center gap-2 rounded-lg bg-[#0755a0] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#064580] hover:shadow-lg active:scale-[0.98]"
                    prefetch
                >
                    {{ $page.props.auth.user ? 'Dashboard' : 'Entrar' }}

                    <ArrowRight
                        class="size-4 transition-transform duration-200 group-hover:translate-x-0.5"
                    />
                </Link>
            </div>
        </header>


        <main>

            <!-- HERO -->
            <section
                id="solucao"
                class="relative overflow-hidden border-b border-slate-200 bg-white"
            >
                <!-- background grid -->
                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.45]"
                    style="
                        background-image:
                            linear-gradient(to right, #e2e8f0 1px, transparent 1px),
                            linear-gradient(to bottom, #e2e8f0 1px, transparent 1px);
                        background-size: 64px 64px;
                        mask-image: linear-gradient(to bottom, black, transparent 85%);
                    "
                />

                <div
                    class="pointer-events-none absolute -right-40 -top-40 h-[600px] w-[600px] rounded-full bg-blue-100/60 blur-3xl"
                />

                <div
                    class="relative mx-auto grid w-full max-w-7xl items-center gap-16 px-5 py-24 sm:px-8 lg:grid-cols-[1.05fr_0.95fr] lg:px-10 lg:py-32"
                >

                    <!-- Hero text -->
                    <div class="max-w-2xl">

                        <div
                            class="mb-7 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3.5 py-2 text-xs font-bold uppercase tracking-[0.16em] text-[#0755a0]"
                        >
                            <span
                                class="flex size-2 rounded-full bg-emerald-500"
                            />

                            Gestão operacional
                        </div>

                        <h1
                            class="text-5xl font-black leading-[1.03] tracking-[-0.04em] text-[#073c78] sm:text-6xl lg:text-[72px]"
                        >
                            Sua operação.
                            <br />

                            <span class="text-[#0755a0]">
                                Mais organizada.
                            </span>
                        </h1>

                        <p
                            class="mt-7 max-w-xl text-lg leading-8 text-slate-500"
                        >
                            O LocalFlow centraliza clientes, contratos,
                            movimentações e cobranças para transformar uma
                            rotina operacional em um processo claro,
                            rastreável e profissional.
                        </p>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">

                            <Link
                                :href="$page.props.auth.user ? dashboard() : login()"
                                class="group inline-flex items-center justify-center gap-2 rounded-lg bg-[#0755a0] px-7 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-900/10 transition duration-200 hover:-translate-y-0.5 hover:bg-[#064580] hover:shadow-xl"
                                prefetch
                            >
                                {{
                                    $page.props.auth.user
                                        ? 'Abrir dashboard'
                                        : 'Acessar sistema'
                                }}

                                <ArrowRight
                                    class="size-4 transition-transform group-hover:translate-x-0.5"
                                />
                            </Link>

                            <a
                                href="#recursos"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-7 py-3.5 text-sm font-bold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50"
                            >
                                Conhecer recursos

                                <ChevronRight class="size-4" />
                            </a>

                        </div>

                        <!-- metrics -->
                        <div
                            class="mt-14 flex flex-wrap items-center gap-x-10 gap-y-5 border-t border-slate-200 pt-7"
                        >
                            <div>
                                <strong
                                    class="text-2xl font-black text-[#073c78]"
                                >
                                    04
                                </strong>

                                <p
                                    class="mt-1 text-[11px] font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Módulos
                                </p>
                            </div>

                            <div class="h-10 w-px bg-slate-200" />

                            <div>
                                <strong
                                    class="text-2xl font-black text-[#073c78]"
                                >
                                    360°
                                </strong>

                                <p
                                    class="mt-1 text-[11px] font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Visão
                                </p>
                            </div>

                            <div class="h-10 w-px bg-slate-200" />

                            <div>
                                <strong
                                    class="text-2xl font-black text-[#073c78]"
                                >
                                    01
                                </strong>

                                <p
                                    class="mt-1 text-[11px] font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Fluxo integrado
                                </p>
                            </div>
                        </div>
                    </div>


                    <!-- Dashboard visual -->
                    <div class="relative">

                        <div
                            class="absolute -inset-8 rounded-[32px] bg-blue-100/50 blur-3xl"
                        />

                        <div
                            class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_30px_80px_-25px_rgba(15,23,42,0.25)]"
                        >

                            <!-- fake dashboard header -->
                            <div
                                class="flex items-center justify-between border-b border-slate-200 px-6 py-5"
                            >
                                <div>
                                    <p
                                        class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400"
                                    >
                                        LocalFlow
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-bold text-slate-800"
                                    >
                                        Visão geral
                                    </p>
                                </div>

                                <div
                                    class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-[#0755a0]"
                                >
                                    <TrendingUp class="size-4" />
                                </div>
                            </div>

                            <div class="p-6">

                                <!-- KPI -->
                                <div class="grid grid-cols-2 gap-3">

                                    <div
                                        class="rounded-xl border border-slate-200 bg-slate-50 p-4"
                                    >
                                        <p
                                            class="text-xs font-medium text-slate-400"
                                        >
                                            Contratos ativos
                                        </p>

                                        <p
                                            class="mt-2 text-2xl font-black text-[#073c78]"
                                        >
                                            Acompanhados
                                        </p>

                                        <div
                                            class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200"
                                        >
                                            <div
                                                class="h-full w-[78%] rounded-full bg-[#0755a0]"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-slate-50 p-4"
                                    >
                                        <p
                                            class="text-xs font-medium text-slate-400"
                                        >
                                            Cobranças
                                        </p>

                                        <p
                                            class="mt-2 text-2xl font-black text-[#073c78]"
                                        >
                                            Previsíveis
                                        </p>

                                        <div
                                            class="mt-3 flex items-center gap-1"
                                        >
                                            <span
                                                class="size-1.5 rounded-full bg-emerald-500"
                                            />
                                            <span
                                                class="text-[10px] font-semibold text-emerald-600"
                                            >
                                                Em dia
                                            </span>
                                        </div>
                                    </div>

                                </div>

                                <!-- activity -->
                                <div class="mt-4 rounded-xl border border-slate-200">

                                    <div
                                        class="flex items-center justify-between border-b border-slate-200 px-4 py-3"
                                    >
                                        <span
                                            class="text-xs font-bold text-slate-700"
                                        >
                                            Operação recente
                                        </span>

                                        <span
                                            class="text-[10px] font-semibold text-slate-400"
                                        >
                                            Hoje
                                        </span>
                                    </div>

                                    <div class="divide-y divide-slate-100">

                                        <div
                                            class="flex items-center gap-3 px-4 py-4"
                                        >
                                            <div
                                                class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-[#0755a0]"
                                            >
                                                <ClipboardList class="size-4" />
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="truncate text-xs font-bold text-slate-700"
                                                >
                                                    Novo contrato registrado
                                                </p>

                                                <p
                                                    class="mt-1 text-[10px] text-slate-400"
                                                >
                                                    Contrato atualizado
                                                </p>
                                            </div>

                                            <CheckCircle2
                                                class="size-4 text-emerald-500"
                                            />
                                        </div>

                                        <div
                                            class="flex items-center gap-3 px-4 py-4"
                                        >
                                            <div
                                                class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
                                            >
                                                <Package class="size-4" />
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="truncate text-xs font-bold text-slate-700"
                                                >
                                                    Movimentação registrada
                                                </p>

                                                <p
                                                    class="mt-1 text-[10px] text-slate-400"
                                                >
                                                    Devolução processada
                                                </p>
                                            </div>

                                            <CheckCircle2
                                                class="size-4 text-emerald-500"
                                            />
                                        </div>

                                        <div
                                            class="flex items-center gap-3 px-4 py-4"
                                        >
                                            <div
                                                class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                                            >
                                                <BarChart3 class="size-4" />
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="truncate text-xs font-bold text-slate-700"
                                                >
                                                    Cobrança acompanhada
                                                </p>

                                                <p
                                                    class="mt-1 text-[10px] text-slate-400"
                                                >
                                                    Financeiro atualizado
                                                </p>
                                            </div>

                                            <CheckCircle2
                                                class="size-4 text-emerald-500"
                                            />
                                        </div>

                                    </div>
                                </div>

                            </div>

                            <!-- bottom brand -->
                            <div
                                class="border-t border-slate-200 bg-slate-50 px-6 py-4"
                            >
                                <div class="flex items-center justify-between">

                                    <div class="flex items-center gap-3">
                                        <img
                                            src="/logo_america.png"
                                            alt="América Materiais"
                                            class="h-7 w-auto max-w-[110px] object-contain"
                                        />

                                        <div
                                            class="h-5 w-px bg-slate-200"
                                        />

                                        <span
                                            class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"
                                        >
                                            Gestão operacional
                                        </span>
                                    </div>

                                    <ShieldCheck
                                        class="size-5 text-[#0755a0]"
                                    />

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>


            <!-- GESTÃO -->
            <section
                id="gestao"
                class="border-b border-slate-200 bg-[#f8fafc]"
            >
                <div
                    class="mx-auto grid w-full max-w-7xl gap-14 px-5 py-24 sm:px-8 lg:grid-cols-[0.8fr_1.2fr] lg:px-10 lg:py-28"
                >

                    <div class="max-w-xl">

                        <p
                            class="text-xs font-black uppercase tracking-[0.2em] text-[#0755a0]"
                        >
                            Gestão profissional
                        </p>

                        <h2
                            class="mt-5 text-4xl font-black leading-tight tracking-[-0.03em] text-[#073c78] lg:text-5xl"
                        >
                            Menos improviso.
                            <br />
                            Mais controle.
                        </h2>

                        <p
                            class="mt-6 text-base leading-7 text-slate-500"
                        >
                            Uma estrutura simples para acompanhar a operação
                            com mais clareza, reduzir retrabalho e manter as
                            informações importantes sempre acessíveis.
                        </p>

                    </div>


                    <div class="grid gap-4 sm:grid-cols-3">

                        <article
                            v-for="commitment in commitments"
                            :key="commitment.number"
                            class="group rounded-xl border border-slate-200 bg-white p-6 transition duration-200 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-slate-200/50"
                        >

                            <span
                                class="text-xs font-black tracking-widest text-blue-300"
                            >
                                {{ commitment.number }}
                            </span>

                            <div
                                class="mt-8 flex size-10 items-center justify-center rounded-lg bg-blue-50 text-[#0755a0]"
                            >
                                <CheckCircle2 class="size-5" />
                            </div>

                            <h3
                                class="mt-6 text-base font-black text-slate-800"
                            >
                                {{ commitment.title }}
                            </h3>

                            <p
                                class="mt-3 text-sm leading-6 text-slate-500"
                            >
                                {{ commitment.description }}
                            </p>

                        </article>

                    </div>
                </div>
            </section>


            <!-- RECURSOS -->
            <section
                id="recursos"
                class="bg-white"
            >
                <div
                    class="mx-auto w-full max-w-7xl px-5 py-24 sm:px-8 lg:px-10 lg:py-28"
                >

                    <div
                        class="flex flex-col justify-between gap-7 lg:flex-row lg:items-end"
                    >

                        <div>

                            <p
                                class="text-xs font-black uppercase tracking-[0.2em] text-[#0755a0]"
                            >
                                Recursos
                            </p>

                            <h2
                                class="mt-5 max-w-2xl text-4xl font-black leading-tight tracking-[-0.03em] text-[#073c78] lg:text-5xl"
                            >
                                Tudo que sua operação precisa,
                                em um só fluxo.
                            </h2>

                        </div>

                        <p
                            class="max-w-md text-base leading-7 text-slate-500"
                        >
                            Registre, acompanhe e consulte cada etapa sem
                            perder o contexto do cliente ou do contrato.
                        </p>

                    </div>


                    <div
                        class="mt-14 grid gap-4 md:grid-cols-2 lg:grid-cols-4"
                    >

                        <article
                            v-for="module in modules"
                            :key="module.title"
                            class="group relative rounded-xl border border-slate-200 bg-white p-7 transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-[0_20px_50px_-25px_rgba(7,85,160,0.3)]"
                        >

                            <div
                                class="flex size-12 items-center justify-center rounded-lg bg-blue-50 text-[#0755a0] transition duration-300 group-hover:bg-[#0755a0] group-hover:text-white"
                            >
                                <component
                                    :is="module.icon"
                                    class="size-5"
                                />
                            </div>

                            <h3
                                class="mt-7 text-base font-black text-slate-900"
                            >
                                {{ module.title }}
                            </h3>

                            <p
                                class="mt-3 text-sm leading-6 text-slate-500"
                            >
                                {{ module.description }}
                            </p>

                            <div
                                class="mt-7 flex items-center gap-1 text-xs font-bold text-[#0755a0] opacity-0 transition group-hover:opacity-100"
                            >
                                Acessar módulo
                                <ArrowRight class="size-3.5" />
                            </div>

                        </article>

                    </div>
                </div>
            </section>


            <!-- CTA -->
            <section
                class="relative overflow-hidden bg-[#073c78]"
            >

                <div
                    class="absolute inset-0 opacity-20"
                    style="
                        background-image:
                            linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px),
                            linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px);
                        background-size: 48px 48px;
                    "
                />

                <div
                    class="relative mx-auto flex w-full max-w-7xl flex-col gap-10 px-5 py-20 sm:px-8 lg:flex-row lg:items-center lg:justify-between lg:px-10 lg:py-24"
                >

                    <div class="max-w-2xl">

                        <div
                            class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-blue-100"
                        >
                            <Building2 class="size-3.5" />

                            LocalFlow
                        </div>

                        <h2
                            class="text-4xl font-black leading-tight tracking-[-0.03em] text-white lg:text-5xl"
                        >
                            Uma operação mais clara
                            começa com informação organizada.
                        </h2>

                        <p
                            class="mt-5 max-w-xl text-base leading-7 text-blue-100/75"
                        >
                            Centralize sua rotina e tenha mais segurança para
                            acompanhar cada etapa da operação.
                        </p>

                    </div>

                    <Link
                        :href="$page.props.auth.user ? dashboard() : login()"
                        class="group inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-white px-7 py-3.5 text-sm font-bold text-[#073c78] shadow-xl transition duration-200 hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-2xl"
                        prefetch
                    >
                        {{
                            $page.props.auth.user
                                ? 'Ir para dashboard'
                                : 'Entrar no sistema'
                        }}

                        <ArrowRight
                            class="size-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </Link>

                </div>
            </section>

        </main>


        <!-- FOOTER -->
        <footer class="border-t border-slate-200 bg-white">

            <div
                class="mx-auto flex w-full max-w-7xl flex-col gap-5 px-5 py-9 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10"
            >

                <div class="flex items-center gap-4">

                    <img
                        src="/logo_america.png"
                        alt="América Materiais para Construção"
                        class="h-7 w-auto max-w-[125px] object-contain"
                    />

                    <span class="h-5 w-px bg-slate-200" />

                    <span class="text-xs font-medium text-slate-400">
                        LocalFlow
                    </span>

                </div>

                <span class="text-xs font-medium text-slate-400">
                    © {{ new Date().getFullYear() }} América Materiais para Construção
                </span>

            </div>

        </footer>

    </div>
</template>
```
