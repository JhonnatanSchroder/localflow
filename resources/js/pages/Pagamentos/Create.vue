<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import PagamentoController from '@/actions/App/Http/Controllers/PagamentoController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/pagamentos';
import { ref } from 'vue';

type ContratoOption = {
    id: number;
    label: string;
};

const dataHoje = ref(new Date().toISOString().split('T')[0]);

defineProps<{
    contratos: ContratoOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Novo pagamento',
                href: '/pagamentos/create',
            },
        ],
    },
});
</script>

<template>
    <Head title="Novo pagamento" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Cadastro</p>
            <h1 class="text-2xl font-semibold tracking-tight">Novo pagamento</h1>
            <p class="text-sm text-muted-foreground">
                Registre um novo pagamento para um contrato.
            </p>
        </div>

        <div class="max-w-2xl rounded-xl border bg-card p-6 shadow-sm">
            <Form
                v-bind="PagamentoController.store.form()"
                class="grid gap-6"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="contrato_id">Contrato</Label>
                    <select
                        id="contrato_id"
                        name="contrato_id"
                        required
                        class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 text-sm outline-none focus-visible:ring-[3px]"
                    >
                        <option value="" disabled selected>Selecione um contrato</option>
                        <option v-for="contrato in contratos" :key="contrato.id" :value="contrato.id">
                            {{ contrato.label }}
                        </option>
                    </select>
                    <InputError :message="errors.contrato_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="data">Data do pagamento</Label>
                    <Input
                        id="data"
                        type="date"
                        name="data"
                        :default-value="dataHoje"
                        required
                    />
                    <InputError :message="errors.data" />
                </div>

                <div class="grid gap-2">
                    <Label for="valor">Valor</Label>
                    <Input
                        id="valor"
                        type="number"
                        name="valor"
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                        required
                    />
                    <InputError :message="errors.valor" />
                </div>

                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Salvando...' : 'Registrar pagamento' }}
                    </Button>
                    <Link :href="index()" class="text-sm text-muted-foreground hover:text-foreground">
                        Cancelar
                    </Link>
                </div>
            </Form>
        </div>
    </div>
</template>
