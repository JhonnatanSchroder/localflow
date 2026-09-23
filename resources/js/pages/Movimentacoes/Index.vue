<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { edit as editContrato } from '@/routes/contratos';
import { create, destroy, edit } from '@/routes/movimentacoes';

type Movimentacao = {
    id: number;
    contrato: {
        id: number;
        endereco: string;
        cliente: {
            nome: string;
        };
    };
    data: string;
    tipo: 'RETIRADA' | 'DEVOLUCAO';
    qtd: number;
};

defineProps<{
    movimentacoes: Movimentacao[];
}>();

function formatDate(value: string): string {
    const [year, month, day] = value.split('-');

    return `${day}/${month}/${year}`;
}

function confirmDelete(): boolean {
    return window.confirm('Deseja excluir esta movimentação?');
}

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Movimentações', href: '/movimentacoes' }],
    },
});
</script>

<template>
    <Head title="Movimentações" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-muted-foreground">Operação</p>
                <h1 class="text-2xl font-semibold tracking-tight">Movimentações</h1>
                <p class="text-sm text-muted-foreground">
                    Registre retiradas e devoluções de peças.
                </p>
            </div>
            <Button as-child>
                <Link :href="create()"><Plus class="size-4" />Nova movimentação</Link>
            </Button>
        </div>

        <div v-if="movimentacoes.length" class="overflow-hidden rounded-xl border bg-card shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead class="border-b bg-muted/40 text-left">
                        <tr>
                            <th class="px-5 py-3 font-medium text-muted-foreground">Contrato</th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">Data</th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">Tipo</th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">Quantidade</th>
                            <th class="px-5 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="movimentacao in movimentacoes" :key="movimentacao.id" class="transition-colors hover:bg-muted/30">
                            <td class="px-5 py-4">
                               <Link :href="editContrato(movimentacao.contrato.id)">
                                 <div class="font-medium">{{ movimentacao.contrato.cliente.nome }}</div>
                                <div class="text-xs text-muted-foreground">{{ movimentacao.contrato.endereco }}</div>
                            </Link>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-muted-foreground">{{ formatDate(movimentacao.data) }}</td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="movimentacao.tipo === 'RETIRADA' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300'"
                                >
                                    {{ movimentacao.tipo === 'RETIRADA' ? 'Retirada' : 'Devolução' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-medium">{{ movimentacao.qtd }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-1">
                                    <Link :href="edit(movimentacao.id)" class="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="Editar movimentação" title="Editar movimentação">
                                        <Pencil class="size-4" />
                                    </Link>
                                    <Link
                                        :href="destroy(movimentacao.id).url"
                                        method="delete"
                                        as="button"
                                        class="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                                        aria-label="Excluir movimentação"
                                        title="Excluir movimentação"
                                        @click="(event) => { if (!confirmDelete()) event.preventDefault(); }"
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

        <div v-else class="flex min-h-56 items-center justify-center rounded-xl border border-dashed bg-muted/10 p-8 text-center">
            <div>
                <h2 class="font-medium">Nenhuma movimentação encontrada</h2>
                <p class="mt-1 text-sm text-muted-foreground">Cadastre a primeira movimentação para começar.</p>
            </div>
        </div>
    </div>
</template>
