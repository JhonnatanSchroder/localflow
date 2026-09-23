<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import MovimentacaoController from '@/actions/App/Http/Controllers/MovimentacaoController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/movimentacoes';

type Contrato = {
    id: number;
    endereco: string;
    cliente: { nome: string };
};

type Movimentacao = {
    id: number;
    contrato_id: number;
    contrato: Contrato;
    data: string;
    tipo: 'RETIRADA' | 'DEVOLUCAO';
    qtd: number;
};

const props = defineProps<{
    movimentacao: Movimentacao;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Editar movimentação', href: '/movimentacoes' }],
    },
});
</script>

<template>
    <Head title="Editar movimentação" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Operação</p>
            <h1 class="text-2xl font-semibold tracking-tight">Editar movimentação</h1>
            <p class="text-sm text-muted-foreground">Atualize os dados da movimentação.</p>
        </div>

        <div class="max-w-2xl rounded-xl border bg-card p-6 shadow-sm">
            <Form v-bind="MovimentacaoController.update.form(props.movimentacao.id)" class="grid gap-6" v-slot="{ errors, processing }">
                <div class="grid gap-2">
                    <Label for="contrato">Contrato</Label>
                    <div
                        id="contrato"
                        class="border-input bg-muted text-muted-foreground flex min-h-9 items-center rounded-md border px-3 py-1 text-sm"
                    >
                        {{ props.movimentacao.contrato.cliente.nome }} -
                        {{ props.movimentacao.contrato.endereco }}
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="data">Data</Label>
                    <Input id="data" type="date" name="data" :default-value="props.movimentacao.data" required />
                    <InputError :message="errors.data" />
                </div>
                <div class="grid gap-2">
                    <Label for="tipo">Tipo</Label>
                    <select id="tipo" name="tipo" required class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 text-sm outline-none focus-visible:ring-[3px]">
                        <option value="RETIRADA" :selected="props.movimentacao.tipo === 'RETIRADA'">Retirada</option>
                        <option value="DEVOLUCAO" :selected="props.movimentacao.tipo === 'DEVOLUCAO'">Devolução</option>
                    </select>
                    <InputError :message="errors.tipo" />
                </div>
                <div class="grid gap-2">
                    <Label for="qtd">Quantidade</Label>
                    <Input id="qtd" name="qtd" type="number" min="1" :default-value="props.movimentacao.qtd" required />
                    <InputError :message="errors.qtd" />
                </div>
                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="processing">{{ processing ? 'Salvando...' : 'Salvar alterações' }}</Button>
                    <Link :href="index()" class="text-sm text-muted-foreground hover:text-foreground">Cancelar</Link>
                </div>
            </Form>
        </div>
    </div>
</template>
