<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import MovimentacaoController from '@/actions/App/Http/Controllers/MovimentacaoController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/movimentacoes';

type ContratoOption = {
    id: number;
    endereco: string;
    cliente: { nome: string };
};

defineProps<{
    contratos: ContratoOption[];
}>();

function formatDate(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

const dataHoje = formatDate(new Date());

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Nova movimentação', href: '/movimentacoes/create' }],
    },
});
</script>

<template>
    <Head title="Nova movimentação" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Operação</p>
            <h1 class="text-2xl font-semibold tracking-tight">Nova movimentação</h1>
            <p class="text-sm text-muted-foreground">Registre uma retirada ou devolução.</p>
        </div>

        <div class="max-w-2xl rounded-xl border bg-card p-6 shadow-sm">
            <Form v-bind="MovimentacaoController.store.form()" class="grid gap-6" v-slot="{ errors, processing }">
                <div class="grid gap-2">
                    <Label for="contrato_id">Contrato</Label>
                    <select id="contrato_id" name="contrato_id" required class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 text-sm outline-none focus-visible:ring-[3px]">
                        <option value="" disabled selected>Selecione um contrato</option>
                        <option v-for="contrato in contratos" :key="contrato.id" :value="contrato.id">
                            {{ contrato.cliente.nome }} - {{ contrato.endereco }}
                        </option>
                    </select>
                    <InputError :message="errors.contrato_id" />
                </div>
                <div class="grid gap-2">
                    <Label for="data">Data</Label>
                    <Input id="data" type="date" name="data" :default-value="dataHoje" required />
                    <InputError :message="errors.data" />
                </div>
                <div class="grid gap-2">
                    <Label for="tipo">Tipo</Label>
                    <select id="tipo" name="tipo" required class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 text-sm outline-none focus-visible:ring-[3px]">
                        <option value="RETIRADA">Retirada</option>
                        <option value="DEVOLUCAO">Devolução</option>
                    </select>
                    <InputError :message="errors.tipo" />
                </div>
                <div class="grid gap-2">
                    <Label for="qtd">Quantidade</Label>
                    <Input id="qtd" name="qtd" type="number" min="1" required />
                    <InputError :message="errors.qtd" />
                </div>
                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="processing">{{ processing ? 'Salvando...' : 'Cadastrar movimentação' }}</Button>
                    <Link :href="index()" class="text-sm text-muted-foreground hover:text-foreground">Cancelar</Link>
                </div>
            </Form>
        </div>
    </div>
</template>
