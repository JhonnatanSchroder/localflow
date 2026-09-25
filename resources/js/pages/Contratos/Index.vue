<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { Pencil, Trash2 } from "@lucide/vue";
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

import {
    create,
    destroy,
    devolver,
    edit,
    finalizar,
    quitar,
    update,
} from "@/routes/contratos";

import { create as createMovimentacao } from "@/routes/movimentacoes";
import { edit as editMovimentacao } from "@/routes/movimentacoes";
import { edit as editPagamento } from "@/routes/pagamentos";

import { computed, ref } from "vue";

import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

type Movimentacao = {
    id: number;
    data: string;
    tipo: "RETIRADA" | "DEVOLUCAO";
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

    total_calculado: number;
    desconto: number;
    total_final: number;
    total_pago: string | number;

    status: string;

    ultima_cobranca: string;
    proxima_cobranca: string;

    obs: string;

    movimentacoes: Movimentacao[];
    pagamentos: Pagamento[];
};

type PageProps = {
    flash?: {
        toast?: {
            type: "success" | "info" | "warning" | "error";
            message: string;
        };
    };
};

const page = usePage<PageProps>();

const contratoRelatorio = ref<Contrato | null>(null);

const contratoDevolucao = ref<Contrato | null>(null);

const dataDevolucao = ref("");

const quantidadeDevolucao = ref(0);

const processandoDevolucao = ref(false);

const descontoInput = ref("");

const salvandoDesconto = ref(false);

const showRelatorio = computed(() => Boolean(contratoRelatorio.value));

const successMessage = computed(() =>
    page.props.flash?.toast?.type === "success"
        ? page.props.flash.toast.message
        : null,
);

/*
|--------------------------------------------------------------------------
| DEVOLUÇÃO
|--------------------------------------------------------------------------
*/

function abrirDevolucao(contrato: Contrato): void {
    contratoDevolucao.value = contrato;

    const hoje = new Date();

    const ano = hoje.getFullYear();

    const mes = String(hoje.getMonth() + 1).padStart(2, "0");

    const dia = String(hoje.getDate()).padStart(2, "0");

    dataDevolucao.value = `${ano}-${mes}-${dia}`;

    quantidadeDevolucao.value = contrato.pecas_atuais;
}

function fecharDevolucao(): void {
    contratoDevolucao.value = null;

    dataDevolucao.value = "";

    quantidadeDevolucao.value = 0;
}

/*
|--------------------------------------------------------------------------
| RELATÓRIO
|--------------------------------------------------------------------------
*/

function abrirRelatorio(contrato: Contrato): void {
    contratoRelatorio.value = contrato;

    descontoInput.value = Number(contrato.desconto ?? 0).toFixed(2);
}

function fecharRelatorio(): void {
    contratoRelatorio.value = null;

    descontoInput.value = "";
}

/*
|--------------------------------------------------------------------------
| DESCONTO
|--------------------------------------------------------------------------
*/

function salvarDesconto(): void {
    if (!contratoRelatorio.value) {
        return;
    }

    const desconto = Number(String(descontoInput.value).replace(",", "."));

    if (Number.isNaN(desconto) || desconto < 0) {
        window.alert("Informe um desconto válido.");

        return;
    }

    const totalCalculado = Number(contratoRelatorio.value.total_calculado);

    if (desconto > totalCalculado) {
        window.alert("O desconto não pode ser maior que o total calculado.");

        return;
    }

    salvandoDesconto.value = true;

    console.log("5. enviando PATCH");

    router.patch(
        update.url(contratoRelatorio.value.id),
        {
            desconto: desconto,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                console.log("6. PATCH sucesso");
                fecharRelatorio();
            },

            onError: (errors) => {
                console.error(
                    "7. ERRO DO LARAVEL:",
                    JSON.stringify(errors, null, 2),
                );
            },

            onFinish: () => {
                console.log("8. PATCH terminou");
                salvandoDesconto.value = false;
            },
        },
    );
}

/*
|--------------------------------------------------------------------------
| SALDO
|--------------------------------------------------------------------------
*/

function saldo(contrato: Contrato): number {
    return Math.max(
        Number(contrato.total_final) - Number(contrato.total_pago),
        0,
    );
}

/*
|--------------------------------------------------------------------------
| FORMATAÇÃO
|--------------------------------------------------------------------------
*/

function formatCurrency(valor: number | string): string {
    return new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL",
    }).format(Number(valor));
}

function formatDate(data: string | null): string {
    if (!data) {
        return "-";
    }

    const [ano, mes, dia] = data.split("-");

    if (!ano || !mes || !dia) {
        return data;
    }

    return `${dia}/${mes}/${ano}`;
}

/*
|--------------------------------------------------------------------------
| MOVIMENTAÇÕES
|--------------------------------------------------------------------------
*/

function registrarMovimentacao(contratoId: number): void {
    router.visit(
        createMovimentacao.url({
            contrato: contratoId,
        }),
    );
}

function editarMovimentacao(movimentacaoId: number): void {
    router.visit(
        editMovimentacao.url({
            movimentacao: movimentacaoId,
        }),
    );
}

/*
|--------------------------------------------------------------------------
| PAGAMENTOS
|--------------------------------------------------------------------------
*/

function editarPagamento(pagamentoId: number): void {
    router.visit(
        editPagamento.url({
            pagamento: pagamentoId,
        }),
    );
}

/*
|--------------------------------------------------------------------------
| DEVOLVER
|--------------------------------------------------------------------------
*/

function registrarDevolucao(contratoId: number): void {
    router.post(
        devolver.url({
            contrato: contratoId,
        }),
    );
}

/*
|--------------------------------------------------------------------------
| QUITAÇÃO
|--------------------------------------------------------------------------
*/

function registrarQuitacao(contratoId: number): void {
    router.post(
        quitar.url({
            contrato: contratoId,
        }),
    );
}

/*
|--------------------------------------------------------------------------
| FINALIZAÇÃO
|--------------------------------------------------------------------------
*/

function finalizarContrato(contratoId: number): void {
    router.post(
        finalizar.url({
            contrato: contratoId,
        }),
    );
}

/*
|--------------------------------------------------------------------------
| EXCLUSÃO
|--------------------------------------------------------------------------
*/

function excluirContrato(contratoId: number): void {
    if (!window.confirm("Tem certeza que deseja excluir este contrato?")) {
        return;
    }

    router.delete(
        destroy.url({
            contrato: contratoId,
        }),
    );
}

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/

const props = defineProps<{
    contratos: Contrato[];
}>();
</script>

<template>
    <Head title="Contratos" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <!-- CABEÇALHO -->

        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Contratos</h1>

                <p class="text-sm text-muted-foreground">
                    Gerencie os contratos de locação.
                </p>
            </div>

            <Button as-child>
                <Link :href="create.url()"> Novo contrato </Link>
            </Button>
        </div>

        <!-- MENSAGEM -->

        <div
            v-if="successMessage"
            class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
        >
            {{ successMessage }}
        </div>

        <!-- LISTA -->

        <div class="overflow-hidden rounded-xl border bg-background">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/100 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">
                                Cliente
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Início
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Peças
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Fretes
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Total
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Desconto
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Pago
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Saldo
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Status
                            </th>

                            <th class="px-4 py-3 text-right font-medium">
                                Ações
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="contrato in props.contratos"
                            :key="contrato.id"
                            class="border-b last:border-0 cursor-pointer hover:bg-muted/50"
                            @click="abrirRelatorio(contrato)"
                        >
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ contrato.cliente?.nome }}
                                </div>

                                <div class="text-xs text-muted-foreground">
                                    {{ contrato.endereco }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                {{ formatDate(contrato.data_inicio) }}
                            </td>

                            <td class="px-4 py-3">
                                {{ contrato.pecas_atuais }}
                            </td>

                            <td class="px-4 py-3">
                                {{ contrato.qtd_frete || 0 }}
                            </td>

                            <td class="px-4 py-3">
                                {{ formatCurrency(contrato.total_calculado) }}
                            </td>

                            <td class="px-4 py-3">
                                <span
                                    v-if="Number(contrato.desconto || 0) > 0"
                                    class="font-medium text-red-600"
                                >
                                    -{{ formatCurrency(contrato.desconto) }}
                                </span>

                                <span v-else class="text-muted-foreground">
                                    —
                                </span>
                            </td>

                            <td class="px-4 py-3 font-medium text-green-600">
                                {{ formatCurrency(contrato.total_pago) }}
                            </td>

                            <td class="px-4 py-3 font-medium">
                                {{ formatCurrency(saldo(contrato)) }}
                            </td>

                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-700':
                                            contrato.status === 'ATIVO',

                                        'bg-yellow-100 text-yellow-700':
                                            contrato.status === 'DEVOLVIDO',

                                        'bg-blue-100 text-blue-700':
                                            contrato.status === 'FINALIZADO',
                                    }"
                                >
                                    {{ contrato.status }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        class="hover:bg-sky-500 text-sky-500"
                                        size="icon"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                edit.url({
                                                    contrato: contrato.id,
                                                })
                                            "
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>

                                    <Button
                                        variant="outline"
                                        class="hover:bg-red-500 text-red-500"
                                        size="icon"
                                        @click="excluirContrato(contrato.id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="props.contratos.length === 0">
                            <td
                                colspan="9"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Nenhum contrato encontrado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- RELATÓRIO DO CONTRATO -->
        <!-- ===================================================== -->

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
                        Contrato #{{ contratoRelatorio.id }}
                    </DialogTitle>

                    <DialogDescription>
                        {{ contratoRelatorio.cliente?.nome }}
                    </DialogDescription>
                </DialogHeader>

                <!-- INFORMAÇÕES -->

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- PEÇAS -->

                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">
                            Peças atuais
                        </p>

                        <p class="mt-1 text-lg font-semibold">
                            {{ contratoRelatorio.pecas_atuais }}
                        </p>
                    </div>

                    <!-- FRETES -->

                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Fretes</p>

                        <p class="mt-1 text-lg font-semibold">
                            {{ contratoRelatorio.qtd_frete || 0 }}
                        </p>
                    </div>

                    <!-- TOTAL CALCULADO -->

                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">
                            Valor calculado
                        </p>

                        <p class="mt-1 text-lg font-semibold">
                            {{
                                formatCurrency(
                                    contratoRelatorio.total_calculado,
                                )
                            }}
                        </p>
                    </div>

                    <!-- TOTAL FINAL -->

                    <div class="rounded-lg bg-muted/50 p-4">
                        <p class="text-xs text-muted-foreground">Total final</p>

                        <p class="mt-1 text-lg font-semibold">
                            {{ formatCurrency(contratoRelatorio.total_final) }}
                        </p>
                    </div>
                </div>

                <!-- ================================================= -->
                <!-- DESCONTO -->
                <!-- ================================================= -->

                <div class="rounded-lg border p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <Label for="desconto" class="mb-2 block">
                                Desconto
                            </Label>

                            <Input
                                id="desconto"
                                v-model="descontoInput"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0,00"
                            />
                        </div>

                        <Button
                            type="button"
                            :disabled="salvandoDesconto"
                            @click="salvarDesconto"
                        >
                            {{
                                salvandoDesconto
                                    ? "Salvando..."
                                    : "Aplicar desconto"
                            }}
                        </Button>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <!-- CALCULADO -->

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Valor calculado
                            </p>

                            <p class="font-medium">
                                {{
                                    formatCurrency(
                                        contratoRelatorio.total_calculado,
                                    )
                                }}
                            </p>
                        </div>

                        <!-- DESCONTO -->

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Desconto
                            </p>

                            <p class="font-medium text-emerald-600">
                                -
                                {{
                                    formatCurrency(
                                        Number(contratoRelatorio.desconto || 0),
                                    )
                                }}
                            </p>
                        </div>

                        <!-- FINAL -->

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Total final
                            </p>

                            <p class="font-semibold">
                                {{
                                    formatCurrency(
                                        contratoRelatorio.total_final,
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ================================================= -->
                <!-- PAGAMENTOS -->
                <!-- ================================================= -->

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold">Pagamentos</h3>

                        <div class="text-sm">
                            Pago:
                            <span class="font-semibold">
                                {{
                                    formatCurrency(contratoRelatorio.total_pago)
                                }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="contratoRelatorio.pagamentos.length === 0"
                        class="rounded-lg border p-4 text-sm text-muted-foreground"
                    >
                        Nenhum pagamento registrado.
                    </div>

                    <div v-else class="divide-y rounded-lg border">
                        <div
                            v-for="pagamento in contratoRelatorio.pagamentos"
                            :key="pagamento.id"
                            class="flex items-center justify-between p-3"
                        >
                            <div>
                                <p class="font-medium">
                                    {{ formatCurrency(pagamento.valor) }}
                                </p>

                                <p class="text-xs text-muted-foreground">
                                    {{ formatDate(pagamento.data) }}
                                </p>
                            </div>

                            <Button
                                variant="outline"
                                size="sm"
                                @click="editarPagamento(pagamento.id)"
                            >
                                Editar
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- ================================================= -->
                <!-- MOVIMENTAÇÕES -->
                <!-- ================================================= -->

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold">Movimentações</h3>

                        <Button
                            variant="outline"
                            size="sm"
                            @click="registrarMovimentacao(contratoRelatorio.id)"
                        >
                            Nova movimentação
                        </Button>
                    </div>

                    <div
                        v-if="contratoRelatorio.movimentacoes.length === 0"
                        class="rounded-lg border p-4 text-sm text-muted-foreground"
                    >
                        Nenhuma movimentação registrada.
                    </div>

                    <div v-else class="divide-y rounded-lg border">
                        <div
                            v-for="movimentacao in contratoRelatorio.movimentacoes"
                            :key="movimentacao.id"
                            class="flex items-center justify-between p-3"
                        >
                            <div>
                                <p class="font-medium">
                                    {{
                                        movimentacao.tipo === "RETIRADA"
                                            ? "Retirada"
                                            : "Devolução"
                                    }}
                                </p>

                                <p class="text-xs text-muted-foreground">
                                    {{ formatDate(movimentacao.data) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="font-semibold">
                                    {{ movimentacao.qtd }} peças
                                </span>

                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="editarMovimentacao(movimentacao.id)"
                                >
                                    Editar
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================================================= -->
                <!-- RESUMO FINANCEIRO -->
                <!-- ================================================= -->

                <div class="rounded-lg border p-4">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Total final
                            </p>

                            <p class="text-lg font-semibold">
                                {{
                                    formatCurrency(
                                        contratoRelatorio.total_final,
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Total pago
                            </p>

                            <p class="text-lg font-semibold">
                                {{
                                    formatCurrency(contratoRelatorio.total_pago)
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">Saldo</p>

                            <p class="text-lg font-semibold">
                                {{ formatCurrency(saldo(contratoRelatorio)) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ================================================= -->
                <!-- RODAPÉ -->
                <!-- ================================================= -->

                <DialogFooter class="flex flex-wrap gap-2">
                    <!-- DEVOLVER -->

                    <Button
                        v-if="
                            contratoRelatorio.status === 'ATIVO' &&
                            contratoRelatorio.pecas_atuais > 0
                        "
                        variant="outline"
                        @click="registrarDevolucao(contratoRelatorio.id)"
                    >
                        Devolver peças
                    </Button>

                    <!-- QUITAR -->

                    <Button
                        v-if="
                            contratoRelatorio.status === 'DEVOLVIDO' &&
                            saldo(contratoRelatorio) > 0
                        "
                        variant="default"
                        @click="registrarQuitacao(contratoRelatorio.id)"
                    >
                        Quitar contrato
                    </Button>

                    <!-- FINALIZAR -->

                    <Button
                        v-if="
                            contratoRelatorio.status === 'DEVOLVIDO' &&
                            saldo(contratoRelatorio) <= 0
                        "
                        variant="default"
                        @click="finalizarContrato(contratoRelatorio.id)"
                    >
                        Finalizar contrato
                    </Button>

                    <DialogClose as-child>
                        <Button variant="outline"> Fechar </Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ===================================================== -->
        <!-- DIALOG DEVOLUÇÃO -->
        <!-- ===================================================== -->

        <Dialog
            :open="Boolean(contratoDevolucao)"
            @update:open="(open) => !open && fecharDevolucao()"
        >
            <DialogContent v-if="contratoDevolucao" class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle> Devolver peças </DialogTitle>

                    <DialogDescription>
                        Contrato #{{ contratoDevolucao.id }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div>
                        <Label> Data da devolução </Label>

                        <Input
                            v-model="dataDevolucao"
                            type="date"
                            class="mt-2"
                        />
                    </div>

                    <div>
                        <Label> Quantidade </Label>

                        <Input
                            v-model.number="quantidadeDevolucao"
                            type="number"
                            min="1"
                            :max="contratoDevolucao.pecas_atuais"
                            class="mt-2"
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="fecharDevolucao">
                        Cancelar
                    </Button>

                    <Button
                        :disabled="
                            processandoDevolucao ||
                            quantidadeDevolucao <= 0 ||
                            quantidadeDevolucao > contratoDevolucao.pecas_atuais
                        "
                        @click="registrarDevolucao(contratoDevolucao.id)"
                    >
                        {{
                            processandoDevolucao
                                ? "Salvando..."
                                : "Confirmar devolução"
                        }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
