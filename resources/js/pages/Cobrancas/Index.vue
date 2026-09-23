<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import {
    AlertCircle,
    ArrowRight,
    CalendarClock,
    CircleDollarSign,
} from "@lucide/vue";
import { edit as editContrato } from "@/routes/contratos";
import { confirmar } from "@/routes/cobrancas";
import { dashboard } from "@/routes";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { computed, ref } from "vue";

type Cobranca = {
    id: number;
    cliente: {
        nome: string;
        telefone: string | null;
    };
    endereco: string;
    proxima_cobranca: string;
    pecas_atuais: number;
    total: number;
    total_pago: number;
    saldo: number;
};

type PageProps = {
    flash?: {
        toast?: {
            type: 'success' | 'info' | 'warning' | 'error';
            message: string;
        };
    };
};

const page = usePage<PageProps>();
const successMessage = computed(() =>
    page.props.flash?.toast?.type === 'success'
        ? page.props.flash.toast.message
        : null,
);


const props = defineProps<{
    cobrancas: Cobranca[];
}>();

const today = new Date().toISOString().slice(0, 10);
const defaultNextChargeDate = new Date();
defaultNextChargeDate.setDate(defaultNextChargeDate.getDate() + 15);
const quinzeDiasAposHoje = defaultNextChargeDate.toISOString().slice(0, 10);
const totalEmAberto = props.cobrancas.reduce(
    (total, cobranca) => total + cobranca.saldo,
    0,
);
const vencidas = props.cobrancas.filter(
    (cobranca) => cobranca.proxima_cobranca < today,
).length;

function formatCurrency(value: number): string {
    return new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL",
    }).format(value);
}

function formatDate(value: string): string {
    const [year, month, day] = value.split("-");

    return `${day}/${month}/${year}`;
}

function dueLabel(value: string): string {
    if (value === today) {
        return "Vence hoje";
    }

    return "Em atraso";
}

const enviandoRelatorio = ref(false);
const cobrancaSelecionada = ref<Cobranca | null>(null);
const cobrancaForm = useForm({
    proxima_cobranca: quinzeDiasAposHoje,
});

function abrirConfirmacaoCobranca(cobranca: Cobranca): void {
    cobrancaSelecionada.value = cobranca;
    cobrancaForm.clearErrors();
    cobrancaForm.proxima_cobranca = quinzeDiasAposHoje;
}

function fecharConfirmacaoCobranca(): void {
    cobrancaSelecionada.value = null;
    cobrancaForm.clearErrors();
}

function confirmarCobranca(): void {
    if (!cobrancaSelecionada.value) {
        return;
    }

    cobrancaForm.post(confirmar(cobrancaSelecionada.value.id).url, {
        preserveScroll: true,
        onSuccess: () => fecharConfirmacaoCobranca(),
    });
}

const enviarRelatorioWhatsApp = () => {
    enviandoRelatorio.value = true;

    router.post('/cobrancas/enviar-whatsapp',
        {},
        {
            onFinish: () => {
                enviandoRelatorio.value = false;
            },
        }
    );    
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: "Dashboard", href: dashboard() },
            { title: "Cobranças", href: "/cobrancas" },
        ],
    },
});
</script>

<template>
    <Head title="Cobranças" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
        
            <div>
                <p class="text-sm font-medium text-primary">Financeiro</p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Cobranças pendentes
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Contratos ativos cuja próxima cobrança vence hoje ou já
                    venceu.
                </p>
            </div>
            <Link
                :href="dashboard()"
                class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline"
                prefetch
            >
                Voltar ao dashboard <ArrowRight class="size-4" />
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Cobranças pendentes
                    </p>
                    <CalendarClock class="size-5 text-primary" />
                </div>
                <p class="mt-3 text-3xl font-semibold">
                    {{ cobrancas.length }}
                </p>
            </div>
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">Em atraso</p>
                    <AlertCircle class="size-5 text-destructive" />
                </div>
                <p class="mt-3 text-3xl font-semibold">{{ vencidas }}</p>
            </div>
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">Saldo em aberto</p>
                    <CircleDollarSign
                        class="size-5 text-emerald-600 dark:text-emerald-400"
                    />
                </div>
                <p class="mt-3 text-3xl font-semibold">
                    {{ formatCurrency(totalEmAberto) }}
                </p>
            </div>
        </div>
        <div
                v-if="successMessage"
                class=" rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300"
                role="status"
            >
                {{ successMessage }}
            </div>

        <Button @click="enviarRelatorioWhatsApp" class="w-fit">
            Receber Todas as cobranças pendentes
        </Button>

        

        <div
            v-if="cobrancas.length"
            class="overflow-hidden rounded-xl border bg-card shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead class="border-b bg-muted/40 text-left">
                        <tr>
                            <th
                                class="px-5 py-3 font-medium text-muted-foreground"
                            >
                                Cliente / contrato
                            </th>
                            <th
                                class="px-5 py-3 font-medium text-muted-foreground"
                            >
                                Vencimento
                            </th>
                            <th
                                class="px-5 py-3 text-right font-medium text-muted-foreground"
                            >
                                Total
                            </th>
                            <th
                                class="px-5 py-3 text-right font-medium text-muted-foreground"
                            >
                                Pago
                            </th>
                            <th
                                class="px-5 py-3 text-right font-medium text-muted-foreground"
                            >
                                Saldo
                            </th>
                            <th
                                class="px-5 py-3 text-right font-medium text-muted-foreground"
                            >
                                Ação
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="cobranca in cobrancas"
                            :key="cobranca.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <Link
                                    :href="editContrato(cobranca.id)"
                                    >
                                    <p class="font-medium hover:text-primary">
                                        {{ cobranca.cliente.nome }} 
                                    </p>
                                </Link>
                                <span>
                                    -
                                </span>
                                <a
                                    :href="'https://wa.me/55' + cobranca.cliente.telefone?.replace(/\D/g, '')"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <p class="font-medium hover:text-primary">
                                        {{ cobranca.cliente.telefone }}
                                    </p>
                                </a>
                                </div>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        #{{ cobranca.id }} ·
                                        {{ cobranca.endereco }} ·
                                        {{ cobranca.pecas_atuais }} peças
                                    </p>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    :class="[
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                        cobranca.proxima_cobranca === today
                                            ? 'bg-amber-500/10 text-amber-700 dark:text-amber-400'
                                            : 'bg-destructive/10 text-destructive',
                                    ]"
                                    >{{ dueLabel(cobranca.proxima_cobranca) }} ·
                                    {{
                                        formatDate(cobranca.proxima_cobranca)
                                    }}</span
                                >
                            </td>
                            <td class="px-5 py-4 text-right">
                                {{ formatCurrency(cobranca.total) }}
                            </td>
                            <td
                                class="px-5 py-4 text-right text-muted-foreground"
                            >
                                {{ formatCurrency(cobranca.total_pago) }}
                            </td>
                            <td class="px-5 py-4 text-right font-semibold">
                                {{ formatCurrency(cobranca.saldo) }}
                            </td>
                            <td class="px-5 gap-2 py-4 text-right flex items-center justify-end">
                                
                                    <Button
                                    class="cursor-pointer"
                                    @click="abrirConfirmacaoCobranca(cobranca)"
                                >
                                    Cobrar
                                </Button>
                                <div>
                                    <Link
                                    :href="editContrato(cobranca.id)"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                                    prefetch
                                    >Ver contrato <ArrowRight class="size-4"
                                /></Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="!cobrancas.length"
            class="flex min-h-72 items-center justify-center rounded-xl border border-dashed bg-muted/10 p-8 text-center"
        >
            <div>
                <CalendarClock class="mx-auto size-8 text-muted-foreground" />
                <h2 class="mt-3 font-medium">Nenhuma cobrança pendente</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Não há contratos ativos com vencimento até hoje.
                </p>
            </div>
        </div>

        <Dialog
            :open="Boolean(cobrancaSelecionada)"
            @update:open="(open) => !open && fecharConfirmacaoCobranca()"
        >
            <DialogContent
                v-if="cobrancaSelecionada"
                class="max-h-[90vh] overflow-y-auto sm:max-w-xl"
            >
                <DialogHeader>
                    <DialogTitle>
                        Confirmar cobrança #{{ cobrancaSelecionada.id }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ cobrancaSelecionada.cliente.nome }} ·
                        {{ cobrancaSelecionada.endereco }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Saldo</p>
                        <p class="mt-1 text-lg font-semibold">
                            {{ formatCurrency(cobrancaSelecionada.saldo) }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Vencimento</p>
                        <p class="mt-1 text-lg font-semibold">
                            {{ formatDate(cobrancaSelecionada.proxima_cobranca) }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Peças</p>
                        <p class="mt-1 text-lg font-semibold">
                            {{ cobrancaSelecionada.pecas_atuais }}
                        </p>
                    </div>
                </div>

                <form class="grid gap-2" @submit.prevent="confirmarCobranca">
                    <Label for="proxima_cobranca">
                        Próxima cobrança
                    </Label>
                    <Input
                        id="proxima_cobranca"
                        v-model="cobrancaForm.proxima_cobranca"
                        type="date"
                    />
                    <p
                        v-if="cobrancaForm.errors.proxima_cobranca"
                        class="text-sm text-destructive"
                    >
                        {{ cobrancaForm.errors.proxima_cobranca }}
                    </p>
                </form>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline">Cancelar</Button>
                    </DialogClose>
                    <Button
                        :disabled="cobrancaForm.processing"
                        @click="confirmarCobranca"
                    >
                        {{
                            cobrancaForm.processing
                                ? "Confirmando..."
                                : "Confirmar cobrança"
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
