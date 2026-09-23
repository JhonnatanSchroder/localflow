<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { create, destroy, edit } from '@/routes/clientes';

type Cliente = {
    id: number;
    nome: string;
    cpf: string;
    telefone: string;
    endereco: string;
    status: string;
};

defineProps<{
    clientes: Cliente[];
}>();

function confirmDelete(nome: string): boolean {
    return window.confirm(`Deseja excluir o cliente ${nome}?`);
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clientes',
                href: '/clientes',
            },
        ],
    },
});
</script>

<template>
    <Head title="Clientes" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    Cadastro
                </p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Clientes
                </h1>
                <p class="text-sm text-muted-foreground">
                    Consulte os clientes cadastrados.
                </p>
            </div>
            <Button as-child>
                <Link :href="create()">Novo cliente</Link>
            </Button>
        </div>

        <div
            v-if="clientes.length"
            class="overflow-hidden rounded-xl border bg-card shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead class="border-b bg-muted/40 text-left">
                        <tr>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Nome
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                CPF
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Telefone
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Endereço
                            </th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Status
                            </th>
                            <th class="px-5 py-3 text-right font-medium text-muted-foreground">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="cliente in clientes"
                            :key="cliente.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="px-5 py-4 font-medium">
                                {{ cliente.nome }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-muted-foreground">
                                {{ cliente.cpf }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-muted-foreground">
                                {{ cliente.telefone }}
                            </td>
                            <td class="max-w-80 px-5 py-4 text-muted-foreground">
                                <span class="block truncate" :title="cliente.endereco">
                                    {{ cliente.endereco }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="
                                        cliente.status === 'BLOQUEADO'
                                            ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                                            : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                    "
                                >
                                    {{ cliente.status }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-1">
                                    <Link
                                        :href="edit(cliente.id)"
                                        class="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        :aria-label="`Editar ${cliente.nome}`"
                                        :title="`Editar ${cliente.nome}`"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                    <Link
                                        :href="destroy(cliente.id).url"
                                        method="delete"
                                        as="button"
                                        class="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                        :aria-label="`Excluir ${cliente.nome}`"
                                        :title="`Excluir ${cliente.nome}`"
                                        @click="
                                            (event) => {
                                                if (!confirmDelete(cliente.nome)) {
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

        <div
            v-else
            class="flex min-h-56 items-center justify-center rounded-xl border border-dashed bg-muted/10 p-8 text-center"
        >
            <div>
                <h2 class="font-medium">Nenhum cliente encontrado</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Cadastre o primeiro cliente para começar.
                </p>
            </div>
        </div>
    </div>
</template>