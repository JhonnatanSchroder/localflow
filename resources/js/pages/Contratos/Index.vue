<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    create,
    destroy,
    devolver,
    edit,
    finalizar,
    quitar,
} from '@/routes/contratos';
import { create as createMovimentacao } from '@/routes/movimentacoes';
import { edit as editMovimentacao } from '@/routes/movimentacoes';
import { edit as editPagamento } from '@/routes/pagamentos';
import { ref } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Movimentacao = {
    id: number;
    data: string;
    tipo: 'RETIRADA' | 'DEVOLUCAO';
    qtd: number;
};

type Pagamento = {
    id: number;
    data: string;
    valor: string | number;
};

type Contrato = {
    id: number;
    cliente: {
        nome: string;
    };
    endereco: string;
    data_inicio: string;
    data_fim: string | null;
    pecas_atuais: number;
    valor_pc_dia: string | number;
    qtd_frete: string | number;
    total: string | number;
    total_pago: string | number;
    status: string;
    ultima_cobranca: string;
    proxima_cobranca: string;
    obs: string;
    movimentacoes: Movimentacao[];
    pagamentos: Pagamento[];
};

defineProps<{
    contratos: Contrato[];
}>();



const contratoRelatorio = ref<Contrato | null>(null);

const contratoDevolucao = ref<Contrato | null>(null);
const dataDevolucao = ref('');
const quantidadeDevolucao = ref(0);
const processandoDevolucao = ref(false);

function abrirDevolucao(contrato: Contrato): void {
    contratoDevolucao.value = contrato;

    // Data de hoje
    const hoje = new Date();
    const ano = hoje.getFullYear();
    const mes = String(hoje.getMonth() + 1).padStart(2, '0');
    const dia = String(hoje.getDate()).padStart(2, '0');

    dataDevolucao.value = `${ano}-${mes}-${dia}`;

    // Por padrão, devolve todas as peças atuais
    quantidadeDevolucao.value = contrato.pecas_atuais;
}

function fecharDevolucao(): void {
    contratoDevolucao.value = null;
    dataDevolucao.value = '';
    quantidadeDevolucao.value = 0;
}

function abrirRelatorio(contrato: Contrato): void {
    contratoRelatorio.value = contrato;
}

function fecharRelatorio(): void {
    contratoRelatorio.value = null;
}

function saldo(contrato: Contrato): number {
    return Number(contrato.total) - Number(contrato.total_pago);
}

function confirmDelete(endereco: string): boolean {
    return window.confirm(`Deseja excluir o contrato de ${endereco}?`);
}

function confirmDevolucao(): boolean {
    return window.confirm(
        'Confirma que o cliente devolveu todas as peças deste contrato?',
    );
}

function confirmFinalizacao(): boolean {
    return window.confirm(
        'Confirma que o contrato foi pago integralmente e deve ser finalizado?',
    );
}

function registrarDevolucao(): void {
    if (!contratoDevolucao.value) {
        return;
    }

    const contrato = contratoDevolucao.value;

    if (!dataDevolucao.value) {
        return;
    }

    if (
        quantidadeDevolucao.value <= 0 ||
        quantidadeDevolucao.value > contrato.pecas_atuais
    ) {
        window.alert(
            `A quantidade deve estar entre 1 e ${contrato.pecas_atuais} peças.`,
        );

        return;
    }

    processandoDevolucao.value = true;

    router.post(
        devolver(contrato.id).url,
        {
            data: dataDevolucao.value,
            qtd: quantidadeDevolucao.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                processandoDevolucao.value = false;
                fecharDevolucao();
            },
        },
    );
}

function registrarFinalizacao(id: number): void {
    if (confirmFinalizacao()) {
        router.post(finalizar(id).url);
    }
}

function registrarQuitacao(id: number): void {
    if (
        window.confirm(
            'Confirma a quitação do saldo restante e a finalização do contrato?',
        )
    ) {
        router.post(quitar(id).url);
    }
}

function formatDate(value: string | null): string {
    if (!value) {
        return 'Em aberto';
    }

    const [year, month, day] = value.split('-');

    return `${day}/${month}/${year}`;
}

function formatCurrency(value: string | number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(Number(value));
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Contratos',
                href: '/contratos',
            },
        ],
    },
});
</script>

<template>
    <Head title="Contratos" />

    <div
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6"
    >
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-muted-foreground">
                Gestão operacional
            </p>
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Contratos
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Acompanhe clientes, períodos e cobranças contratadas.
                    </p>
                </div>
                <span
                    class="shrink-0 rounded-full bg-muted px-3 py-1 text-sm font-medium text-muted-foreground"
                >
                    {{ contratos.length }} contratos
                </span>
            </div>
            <Button as-child>
                <Link prefetch :href="create()">Novo contrato</Link>
            </Button>
        </div>

        <div
            v-if="contratos.length"
            class="overflow-hidden rounded-xl border bg-card shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1160px] text-sm">
                    <thead class="border-b bg-muted/40 text-left">
                        <tr>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Cliente
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Data de início
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Data de fim
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Valor/dia
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Quantidade de fretes
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Peças atuais
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Total
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Total pago
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Última cobrança
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Próxima cobrança
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Status
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Observação
                            </th>
                            <th class="px-5 py-3 text-right font-medium text-muted-foreground">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="contrato in contratos"
                            :key="contrato.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="px-5 py-4">
                                <div class="font-medium">
                                    {{ contrato.cliente.nome }}
                                </div>
                                <div class="max-w-56 truncate text-xs text-muted-foreground">
                                    {{ contrato.endereco }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-muted-foreground">
                                <time :datetime="contrato.data_inicio">
                                    {{ formatDate(contrato.data_inicio) }}
                                </time>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-muted-foreground">
                                <time :datetime="contrato.data_fim ?? ''">
                                    {{ formatDate(contrato.data_fim) }}
                                </time>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 font-medium">
                                {{ formatCurrency(contrato.valor_pc_dia) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 font-medium">
                                {{ contrato.qtd_frete || 0 }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 font-medium">
                                {{ contrato.pecas_atuais }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 font-medium">
                                {{ formatCurrency(contrato.total) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-emerald-700 dark:text-emerald-300">
                                {{ formatCurrency(contrato.total_pago) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-muted-foreground">
                                {{ formatDate(contrato.ultima_cobranca) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-muted-foreground">
                                {{ formatDate(contrato.proxima_cobranca) }}
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300': contrato.status === 'BLOQUEADO',
                                        'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300': contrato.status === 'DEVOLVIDO',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300': contrato.status === 'FINALIZADO',
                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300': contrato.status === 'ATIVO',
                                    }"
                                >
                                    {{ contrato.status }}
                                </span>
                            </td>
                            <td class="max-w-64 px-5 py-4 text-muted-foreground">
                                <span class="block truncate" :title="contrato.obs">
                                    {{ contrato.obs }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="abrirRelatorio(contrato)"
                                    >
                                        Relatório
                                    </Button>
                                    <Button
                                        v-if="!['DEVOLVIDO', 'FINALIZADO'].includes(contrato.status)"
                                        variant="outline"
                                        size="sm"
                                        @click="abrirDevolucao(contrato)"
                                    >
                                        Devolver
                                    </Button>
                                    <Link
                                        :href="edit(contrato.id)"
                                        class="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        :aria-label="`Editar contrato de ${contrato.cliente.nome}`"
                                        title="Editar contrato"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                    <Link
                                        :href="destroy(contrato.id).url"
                                        method="delete"
                                        as="button"
                                        class="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                        aria-label="Excluir contrato"
                                        title="Excluir contrato"
                                        @click="
                                            (event) => {
                                                if (!confirmDelete(contrato.endereco)) {
                                                    event.preventDefault();
                                                }
                                            }
                                        "
                                    >
                                        <Trash2 class="size-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog
            :open="Boolean(contratoRelatorio)"
            @update:open="(open) => !open && fecharRelatorio()"
        >
            <DialogContent
                v-if="contratoRelatorio"
                class="max-h-[90vh] overflow-y-auto sm:max-w-3xl"
            >
                <DialogHeader>
                    <DialogTitle>
                        Relatório do contrato #{{ contratoRelatorio.id }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ contratoRelatorio.cliente.nome }} ·
                        {{ contratoRelatorio.endereco }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-3 sm:grid-cols-4">
                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Peças atuais</p>
                        <p class="mt-1 text-lg font-semibold">
                            {{ contratoRelatorio.pecas_atuais }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Fretes</p>
                        <p class="mt-1 text-lg font-semibold">
                            {{ contratoRelatorio.qtd_frete || 0 }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Total</p>
                        <p class="mt-1 text-lg font-semibold">
                            {{ formatCurrency(contratoRelatorio.total) }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Saldo</p>
                        <p class="mt-1 text-lg font-semibold">
                            {{ formatCurrency(saldo(contratoRelatorio)) }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border p-4">
                        <p class="text-xs uppercase text-muted-foreground">Início</p>
                        <p class="mt-1 font-medium">{{ formatDate(contratoRelatorio.data_inicio) }}</p>
                    </div>
                    <div class="rounded-lg border p-4">
                        <p class="text-xs uppercase text-muted-foreground">Fim</p>
                        <p class="mt-1 font-medium">{{ formatDate(contratoRelatorio.data_fim) }}</p>
                    </div>
                    <div class="rounded-lg border p-4">
                        <p class="text-xs uppercase text-muted-foreground">Última cobrança</p>
                        <p class="mt-1 font-medium">{{ formatDate(contratoRelatorio.ultima_cobranca) }}</p>
                    </div>
                    <div class="rounded-lg border p-4">
                        <p class="text-xs uppercase text-muted-foreground">Próxima cobrança</p>
                        <p class="mt-1 font-medium">{{ formatDate(contratoRelatorio.proxima_cobranca) }}</p>
                    </div>
                </div>

                <section>
                    <h3 class="mb-3 font-semibold">Movimentações</h3>
                    <div v-if="contratoRelatorio.movimentacoes.length" class="divide-y rounded-lg border">
                        <Link
                            v-for="movimentacao in contratoRelatorio.movimentacoes"
                            :key="movimentacao.id"
                            :href="editMovimentacao(movimentacao.id)"
                            class="flex items-center justify-between px-4 py-3 text-sm transition-colors hover:bg-muted/50"
                        >
                            <div>
                                <p class="font-medium">{{ movimentacao.tipo === 'RETIRADA' ? 'Retirada' : 'Devolução' }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatDate(movimentacao.data) }}</p>
                            </div>
                            <span class="font-semibold">{{ movimentacao.qtd }}</span>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">Nenhuma movimentação.</p>
                </section>

                <section>
                    <h3 class="mb-3 font-semibold">Pagamentos</h3>
                    <div v-if="contratoRelatorio.pagamentos.length" class="divide-y rounded-lg border">
                        <Link
                            v-for="pagamento in contratoRelatorio.pagamentos"
                            :key="pagamento.id"
                            :href="editPagamento(pagamento.id)"
                            class="flex items-center justify-between px-4 py-3 text-sm transition-colors hover:bg-muted/50"
                        >
                            <span class="text-muted-foreground">{{ formatDate(pagamento.data) }}</span>
                            <span class="font-semibold">{{ formatCurrency(pagamento.valor) }}</span>
                        </Link>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">Nenhum pagamento.</p>
                </section>

                <DialogFooter>
                    <Button
                        v-if="!['DEVOLVIDO', 'FINALIZADO'].includes(contratoRelatorio.status)"
                        variant="outline"
                        @click="abrirDevolucao(contratoRelatorio)"
                    >
                        Marcar devolução
                    </Button>
                    <Button
                        v-if="contratoRelatorio.status === 'DEVOLVIDO'"
                        variant="default"
                        @click="registrarFinalizacao(contratoRelatorio.id)"
                    >
                        Finalizar contrato
                    </Button>
                    <Button
                        v-if="contratoRelatorio.status === 'DEVOLVIDO' && saldo(contratoRelatorio) > 0"
                        variant="default"
                        @click="registrarQuitacao(contratoRelatorio.id)"
                    >
                        Quitar contrato
                    </Button>
                    <Button as-child>
                        <Link :href="createMovimentacao()">
                            Movimentação
                        </Link>
                    </Button>
                    <DialogClose as-child>
                        <Button variant="outline">Fechar</Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <Dialog
    :open="Boolean(contratoDevolucao)"
    @update:open="(open) => !open && fecharDevolucao()"
>
    <DialogContent
        v-if="contratoDevolucao"
        class="sm:max-w-md"
    >
        <DialogHeader>
            <DialogTitle>
                Registrar devolução
            </DialogTitle>

            <DialogDescription>
                Registre a devolução das peças do contrato
                #{{ contratoDevolucao.id }}.
            </DialogDescription>
        </DialogHeader>

        <div class="grid gap-5 py-2">

            <div class="grid gap-2">
                <Label for="data_devolucao">
                    Data da devolução
                </Label>

                <Input
                    id="data_devolucao"
                    v-model="dataDevolucao"
                    type="date"
                />
            </div>

            <div class="grid gap-2">
                <Label for="quantidade_devolucao">
                    Quantidade de peças
                </Label>

                <Input
                    id="quantidade_devolucao"
                    v-model.number="quantidadeDevolucao"
                    type="number"
                    min="1"
                    :max="contratoDevolucao.pecas_atuais"
                />

                <p class="text-xs text-muted-foreground">
                    Peças atualmente com o cliente:
                    {{ contratoDevolucao.pecas_atuais }}
                </p>
            </div>

        </div>

        <DialogFooter>
            <DialogClose as-child>
                <Button
                    variant="outline"
                    :disabled="processandoDevolucao"
                >
                    Cancelar
                </Button>
            </DialogClose>

            <Button
                :disabled="
                    processandoDevolucao ||
                    !dataDevolucao ||
                    quantidadeDevolucao <= 0 ||
                    quantidadeDevolucao > contratoDevolucao.pecas_atuais
                "
                @click="registrarDevolucao"
            >
                {{
                    processandoDevolucao
                        ? 'Registrando...'
                        : 'Confirmar devolução'
                }}
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>

        <div
            v-if="!contratos.length"
            class="flex min-h-56 items-center justify-center rounded-xl border border-dashed bg-muted/10 p-8 text-center"
        >
            <div>
                <h2 class="font-medium">Nenhum contrato encontrado</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Os contratos cadastrados aparecerão aqui.
                </p>
            </div>
        </div>
    </div>
</template>