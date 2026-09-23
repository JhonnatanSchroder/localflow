<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { create, edit, destroy } from '@/routes/pagamentos';
import { index as contratoIndex } from '@/routes/contratos';

interface Pagamento {
    id: number;
    contrato_id: number;
    data: string;
    valor: number;
    contrato?: {
        id: number;
        cliente?: {
            nome: string;
        };
        endereco: string;
    };
}

interface Props {
    pagamentos: {
        data: Pagamento[];
        current_page: number;
        last_page: number;
        total: number;
    };
}

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pagamentos',
                href: '/pagamentos',
            },
        ],
    },
});

function deletePagamento(id: number): void {
    if (confirm('Tem certeza que deseja remover este pagamento?')) {
        router.delete(destroy(id).url);
    }
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('pt-BR');
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}
</script>

<template>
    <Head title="Pagamentos" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-muted-foreground">Gestão</p>
                <h1 class="text-2xl font-semibold tracking-tight">Pagamentos</h1>
                <p class="text-sm text-muted-foreground">
                    {{ pagamentos.total }} pagamento(s) registrado(s)
                </p>
            </div>
            <Link :href="create()">
                <Button>Novo pagamento</Button>
            </Link>
        </div>

        <div class="rounded-xl border bg-card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-6 py-3 text-left text-sm font-semibold">
                                Contrato
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">
                                Cliente
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">
                                Data
                            </th>
                            <th class="px-6 py-3 text-right text-sm font-semibold">
                                Valor
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-semibold">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="pagamento in pagamentos.data"
                            :key="pagamento.id"
                            class="border-b hover:bg-muted/50 transition-colors"
                        >
                            <td class="px-6 py-4 text-sm">
                                <Link
                                    :href="contratoIndex()"
                                    class="text-primary hover:underline font-medium"
                                >
                                    #{{ pagamento.contrato_id }}
                                </Link>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ pagamento.contrato?.cliente?.nome || '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ formatDate(pagamento.data) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-semibold">
                                {{ formatCurrency(pagamento.valor) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="edit(pagamento.id)">
                                        <Button variant="outline" size="sm">
                                            Editar
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="deletePagamento(pagamento.id)"
                                    >
                                        Remover
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div
                    v-if="pagamentos.data.length === 0"
                    class="px-6 py-8 text-center text-muted-foreground"
                >
                    Nenhum pagamento registrado
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagamentos.last_page > 1" class="flex items-center justify-center gap-2">
            <Link
                v-if="pagamentos.current_page > 1"
                :href="`/pagamentos?page=${pagamentos.current_page - 1}`"
            >
                <Button variant="outline">Anterior</Button>
            </Link>

            <span class="text-sm text-muted-foreground">
                Página {{ pagamentos.current_page }} de {{ pagamentos.last_page }}
            </span>

            <Link
                v-if="pagamentos.current_page < pagamentos.last_page"
                :href="`/pagamentos?page=${pagamentos.current_page + 1}`"
            >
                <Button variant="outline">Próxima</Button>
            </Link>
        </div>
    </div>
</template>
