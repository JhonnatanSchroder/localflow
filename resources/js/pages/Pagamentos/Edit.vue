<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import PagamentoController from '@/actions/App/Http/Controllers/PagamentoController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/contratos';

type Pagamento = {
    id: number;
    data: string;
    valor: string | number;
    contrato: {
        cliente: { nome: string };
        endereco: string;
    };
};

const props = defineProps<{
    pagamento: Pagamento;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Editar pagamento', href: '/contratos' }],
    },
});
</script>

<template>
    <Head title="Editar pagamento" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Financeiro</p>
            <h1 class="text-2xl font-semibold tracking-tight">Editar pagamento</h1>
            <p class="text-sm text-muted-foreground">
                Atualize o pagamento de {{ props.pagamento.contrato.cliente.nome }}.
            </p>
        </div>

        <div class="max-w-2xl rounded-xl border bg-card p-6 shadow-sm">
            <Form
                v-bind="PagamentoController.update.form(props.pagamento.id)"
                class="grid gap-6"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="contrato">Contrato</Label>
                    <div id="contrato" class="border-input bg-muted text-muted-foreground flex min-h-9 items-center rounded-md border px-3 py-1 text-sm">
                        {{ props.pagamento.contrato.cliente.nome }} -
                        {{ props.pagamento.contrato.endereco }}
                    </div>
                </div>
                <div class="grid gap-2">
                    <Label for="data">Data</Label>
                    <Input id="data" type="date" name="data" :default-value="props.pagamento.data" required />
                    <InputError :message="errors.data" />
                </div>
                <div class="grid gap-2">
                    <Label for="valor">Valor</Label>
                    <Input id="valor" type="number" name="valor" step="0.01" min="0" :default-value="props.pagamento.valor" required />
                    <InputError :message="errors.valor" />
                </div>
                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Salvando...' : 'Salvar alterações' }}
                    </Button>
                    <Link :href="index()" class="text-sm text-muted-foreground hover:text-foreground">
                        Cancelar
                    </Link>
                </div>
            </Form>
        </div>
    </div>
</template>
