
<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, ClipboardList, CreditCard, FileText, Package, Users, UsersIcon } from '@lucide/vue';
import BarChart from '@/components/BarChart.vue';
import LineChart from '@/components/LineChart.vue';
import SearchBar from '@/components/SearchBar.vue';
import { create as clienteCreate, index as clientesIndex } from '@/routes/clientes';
import { create as contratoCreate, edit as contratoEdit, index as contratosIndex } from '@/routes/contratos';
import { index as movimentacoesIndex } from '@/routes/movimentacoes';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

type DashboardStats = {
    total_clients: number;
    total_contracts: number;
    active_contracts: number;
    payments_this_month: number;
    revenue_this_month: number;
    pieces_in_circulation: number;
    overdue_contracts: number;
    revenue_by_month: Record<string, number>;
    contracts_by_status: Record<string, number>;
};

type RecentContract = {
    id: number;
    cliente: string;
    endereco: string;
    status: string;
};

type RecentPayment = {
    id: number;
    contrato_id: number;
    cliente: string;
    data: string;
    valor: number;
};

const defaultStats: DashboardStats = {
    total_clients: 0,
    total_contracts: 0,
    active_contracts: 0,
    payments_this_month: 0,
    revenue_this_month: 0,
    pieces_in_circulation: 0,
    overdue_contracts: 0,
    revenue_by_month: {},
    contracts_by_status: {},
};

const page = usePage<{
    stats?: DashboardStats;
    recentContracts?: RecentContract[];
    recentPayments?: RecentPayment[];
}>();
const stats = page.props.stats ?? defaultStats;
const recentContracts = page.props.recentContracts ?? [];
const recentPayments = page.props.recentPayments ?? [];
const revenueLabels = Object.keys(stats.revenue_by_month).map(formatMonth);
const revenueData = Object.values(stats.revenue_by_month).map(Number);
const statusLabels = Object.keys(stats.contracts_by_status);
const statusData = Object.values(stats.contracts_by_status).map(Number);

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

function formatMonth(value: string): string {
    const [year, month] = value.split('-').map(Number);

    return new Intl.DateTimeFormat('pt-BR', { month: 'short', year: 'numeric' })
        .format(new Date(year, month - 1, 1))
        .replace('.', '');
}

function statusClass(status: string): string {
    return {
        ATIVO: 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
        DEVOLVIDO: 'bg-amber-500/10 text-amber-700 dark:text-amber-400',
        FINALIZADO: 'bg-sky-500/10 text-sky-700 dark:text-sky-400',
    }[status] ?? 'bg-muted text-muted-foreground';
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
        <section class="flex flex-col justify-between gap-4 rounded-2xl border bg-linear-to-br from-primary/12 via-background to-background p-6 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm font-medium text-primary">Visão geral</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight">Controle da operação em um só lugar.</h1>
                <p class="mt-2 text-sm text-muted-foreground">Acompanhe contratos, cobranças e movimentações sem perder o contexto.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <SearchBar />
                <Link :href="contratoCreate()" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition hover:bg-primary/90" prefetch>
                <FileText class="size-3" />
                Novo contrato
            </Link>
            <Link :href="clienteCreate()" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition hover:bg-primary/90" prefetch>
                <UsersIcon class="size-3" />
                Novo cliente
            </Link>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Link :href="clientesIndex()" class="group rounded-xl border bg-card p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-400 hover:shadow-md" prefetch>
                <div class="flex items-start justify-between"><div class="rounded-lg bg-violet-500/10 p-2.5 text-violet-700 dark:text-violet-400"><Users class="size-5" /></div><ArrowRight class="size-4 text-muted-foreground transition group-hover:translate-x-1 group-hover:text-foreground" /></div>
                <div class="mt-5 text-3xl font-semibold tracking-tight">{{ stats.total_clients }}</div><div class="mt-1 text-sm text-muted-foreground">Clientes cadastrados</div>
            </Link>
            <Link :href="contratosIndex()" class="group rounded-xl border bg-card p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary/50 hover:shadow-md" prefetch>
                <div class="flex items-start justify-between"><div class="rounded-lg bg-blue-500/10 p-2.5 text-blue-700 dark:text-blue-400"><ClipboardList class="size-5" /></div><ArrowRight class="size-4 text-muted-foreground transition group-hover:translate-x-1 group-hover:text-foreground" /></div>
                <div class="mt-5 text-3xl font-semibold tracking-tight">{{ stats.total_contracts }}</div><div class="mt-1 text-sm text-muted-foreground">Contratos cadastrados</div>
            </Link>
            <Link :href="contratosIndex()" class="group rounded-xl border bg-card p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-400 hover:shadow-md" prefetch>
                <div class="flex items-start justify-between"><div class="rounded-lg bg-emerald-500/10 p-2.5 text-emerald-700 dark:text-emerald-400"><FileText class="size-5" /></div><ArrowRight class="size-4 text-muted-foreground transition group-hover:translate-x-1 group-hover:text-foreground" /></div>
                <div class="mt-5 text-3xl font-semibold tracking-tight">{{ stats.active_contracts }}</div><div class="mt-1 text-sm text-muted-foreground">Contratos ativos</div>
            </Link>
            <Link :href="movimentacoesIndex()" class="group rounded-xl border bg-card p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-400 hover:shadow-md" prefetch>
                <div class="flex items-start justify-between"><div class="rounded-lg bg-amber-500/10 p-2.5 text-amber-700 dark:text-amber-400"><Package class="size-5" /></div><ArrowRight class="size-4 text-muted-foreground transition group-hover:translate-x-1 group-hover:text-foreground" /></div>
                <div class="mt-5 text-3xl font-semibold tracking-tight">{{ stats.pieces_in_circulation }}</div><div class="mt-1 text-sm text-muted-foreground">Peças em circulação</div>
            </Link>
        </section>

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <Link :href="contratosIndex()" class="group rounded-xl border bg-card p-5 shadow-sm transition hover:border-primary/50 hover:shadow-md" prefetch>
                <div class="flex items-start justify-between"><div><p class="text-sm text-muted-foreground">Receita do mês</p><p class="mt-2 text-2xl font-semibold">{{ formatCurrency(stats.revenue_this_month) }}</p></div><CreditCard class="size-5 text-primary" /></div><p class="mt-3 text-sm text-muted-foreground">{{ stats.payments_this_month }} pagamentos registrados</p>
            </Link>
            <Link href="/cobrancas" class="group rounded-xl border bg-card p-5 shadow-sm transition hover:border-destructive hover:shadow-md" prefetch>
                <div class="flex items-start justify-between"><div><p class="text-sm text-muted-foreground">Cobranças em atraso</p><p class="mt-2 text-2xl font-semibold">{{ stats.overdue_contracts }}</p></div><span class="rounded-full bg-destructive/10 px-2.5 py-1 text-xs font-medium text-destructive">Atenção</span></div><p class="mt-3 text-sm text-muted-foreground">Verifique os contratos com vencimento pendente.</p>
            </Link>
            <Link :href="movimentacoesIndex()" class="group rounded-xl border bg-card p-5 shadow-sm transition hover:border-primary/50 hover:shadow-md" prefetch>
                <div class="flex items-start justify-between"><div><p class="text-sm text-muted-foreground">Movimentações</p><p class="mt-2 text-2xl font-semibold">Acompanhar estoque</p></div><Package class="size-5 text-primary" /></div><p class="mt-3 text-sm text-muted-foreground">Registre retiradas e devoluções por contrato.</p>
            </Link>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="rounded-xl border bg-card p-5 shadow-sm"><h2 class="text-base font-semibold">Receita nos últimos 6 meses</h2><p class="mt-1 text-sm text-muted-foreground">Valores recebidos por período.</p><div class="mt-4"><LineChart :labels="revenueLabels" :data="revenueData" label="Receita (R$)" /></div></div>
            <div class="rounded-xl border bg-card p-5 shadow-sm"><h2 class="text-base font-semibold">Contratos por status</h2><p class="mt-1 text-sm text-muted-foreground">Distribuição atual da operação.</p><div class="mt-4"><BarChart :labels="statusLabels" :data="statusData" label="Contratos" /></div></div>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="rounded-xl border bg-card shadow-sm">
                <div class="flex items-center justify-between border-b p-5"><div><h2 class="font-semibold">Contratos recentes</h2><p class="mt-1 text-sm text-muted-foreground">Acesse um contrato para ver seus detalhes.</p></div><Link :href="contratosIndex()" class="text-sm font-medium text-primary hover:underline" prefetch>Ver todos</Link></div>
                <div v-if="recentContracts.length" class="divide-y"><Link v-for="contract in recentContracts" :key="contract.id" :href="contratoEdit(contract.id)" class="flex items-center justify-between gap-4 p-4 transition hover:bg-muted/60" prefetch><div class="min-w-0"><p class="truncate text-sm font-medium">{{ contract.cliente }}</p><p class="mt-1 truncate text-xs text-muted-foreground">#{{ contract.id }} · {{ contract.endereco }}</p></div><span :class="['shrink-0 rounded-full px-2.5 py-1 text-xs font-medium', statusClass(contract.status)]">{{ contract.status }}</span></Link></div>
                <div v-else class="p-8 text-center text-sm text-muted-foreground">Nenhum contrato recente.</div>
            </div>
            <div class="rounded-xl border bg-card shadow-sm">
                <div class="flex items-center justify-between border-b p-5"><div><h2 class="font-semibold">Pagamentos recentes</h2><p class="mt-1 text-sm text-muted-foreground">Últimos valores registrados no sistema.</p></div><Link :href="contratosIndex()" class="text-sm font-medium text-primary hover:underline" prefetch>Ver contratos</Link></div>
                <div v-if="recentPayments.length" class="divide-y"><Link v-for="payment in recentPayments" :key="payment.id" :href="contratoEdit(payment.contrato_id)" class="flex items-center justify-between gap-4 p-4 transition hover:bg-muted/60" prefetch><div class="min-w-0"><p class="truncate text-sm font-medium">{{ payment.cliente }}</p><p class="mt-1 text-xs text-muted-foreground">Contrato #{{ payment.contrato_id }} · {{ payment.data }}</p></div><span class="shrink-0 text-sm font-semibold text-emerald-700 dark:text-emerald-400">{{ formatCurrency(payment.valor) }}</span></Link></div>
                <div v-else class="p-8 text-center text-sm text-muted-foreground">Nenhum pagamento recente.</div>
            </div>
        </section>
    </div>
</template>
