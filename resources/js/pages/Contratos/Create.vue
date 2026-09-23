<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ContratoController from '@/actions/App/Http/Controllers/ContratoController';
import InputError from '@/components/InputError.vue';
import NovoClienteDialog from '@/components/NovoClienteDialog.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/contratos';
import { ref, watch } from 'vue';

type ClienteOption = {
    id: number;
    nome: string;
    endereco?: string;
};

const dataFimManual = ref(false);
const props = defineProps<{
    clientes: ClienteOption[];
}>();

const clientes = ref<ClienteOption[]>([...props.clientes]);
const clienteSelecionado = ref<number | ''>('');
const endereco = ref('');

// Quando cliente é selecionado, preenche endereço automaticamente
watch(clienteSelecionado, (clienteId) => {
    if (!clienteId) {
        endereco.value = '';
        return;
    }

    const cliente = clientes.value.find((c) => c.id === clienteId);
    if (cliente?.endereco) {
        endereco.value = cliente.endereco;
    }
});

function onClienteCriado(cliente: ClienteOption): void {
    clientes.value = [...clientes.value, cliente].sort((a, b) =>
        a.nome.localeCompare(b.nome),
    );
    clienteSelecionado.value = cliente.id;
}

function formatDate(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

const dataHoje = formatDate(new Date());
const dataProximaCobranca = formatDate(
    new Date(Date.now() + 15 * 24 * 60 * 60 * 1000),
);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Novo contrato',
                href: '/contratos/create',
            },
        ],
    },
});
</script>

<template>
    <Head title="Novo contrato" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Cadastro</p>
            <h1 class="text-2xl font-semibold tracking-tight">
                Novo contrato
            </h1>
            <p class="text-sm text-muted-foreground">
                Informe os dados do contrato e do período de cobrança.
            </p>
        </div>

        <div class="max-w-4xl rounded-xl border bg-card p-6 shadow-sm">
            <Form
                v-bind="ContratoController.store.form()"
                class="grid gap-6 md:grid-cols-2"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <div class="flex items-center justify-between gap-3">
                        <Label for="cliente_id">Cliente</Label>
                        <NovoClienteDialog @created="onClienteCriado" />
                    </div>
                    <select
                        id="cliente_id"
                        name="cliente_id"
                        v-model="clienteSelecionado"
                        required
                        class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 text-sm outline-none focus-visible:ring-[3px]"
                    >
                        <option value="" disabled>Selecione um cliente</option>
                        <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                            {{ cliente.nome }}
                        </option>
                    </select>
                    <InputError :message="errors.cliente_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="endereco">Endereço</Label>
                    <Input
                        id="endereco"
                        v-model="endereco"
                        name="endereco"
                        placeholder="Endereço do contrato"
                    />
                    <InputError :message="errors.endereco" />
                </div>

                <div class="grid gap-2">
                    <Label for="data_inicio">Data de início</Label>
                    <Input id="data_inicio" type="date" name="data_inicio" :default-value="dataHoje" required />
                    <InputError :message="errors.data_inicio" />
                </div>
                <div class="grid gap-2">
                    <Label for="data_fim">Data de fim</Label>
                    <Input
                        id="data_fim"
                        type="date"
                        name="data_fim"
                        @change="dataFimManual = true"
                    />
                    <input
                        type="hidden"
                        name="data_fim_manual"
                        :value="dataFimManual ? '1' : '0'"
                    />
                    <InputError :message="errors.data_fim" />
                </div>

                <div class="grid gap-2">
                    <Label for="valor_pc_dia">Valor da peça por dia</Label>
                    <Input id="valor_pc_dia" name="valor_pc_dia" type="number" step="0.01" min="0" default-value="0.60" required />
                    <InputError :message="errors.valor_pc_dia" />
                </div>
                <div class="grid gap-2">
                    <Label for="qtd_inicial">Peças iniciais</Label>
                    <Input id="qtd_inicial" name="qtd_inicial" type="number" min="0" default-value="0" required />
                    <InputError :message="errors.qtd_inicial" />
                </div>
                <div class="grid gap-2">
                    <Label for="qtd_frete">Quantidade de fretes</Label>
                    <Input id="qtd_frete" name="qtd_frete" type="number" min="0" default-value="0" />
                    <InputError :message="errors.qtd_frete" />
                </div>
                <div class="grid gap-2">
                    <Label for="valor_frete">Valor do frete</Label>
                    <Input id="valor_frete" name="valor_frete" type="number" step="0.01" min="0" default-value="15.00" required />
                    <InputError :message="errors.valor_frete" />
                </div>
                <div class="grid gap-2">
                    <Label for="status">Status</Label>
                    <select id="status" name="status" class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 text-sm outline-none focus-visible:ring-[3px]" required>
                        <option value="ATIVO" selected>Ativo</option>
                        <option value="BLOQUEADO">Bloqueado</option>
                    </select>
                    <InputError :message="errors.status" />
                </div>
                <div class="grid gap-2">
                    <Label for="ultima_cobranca">Última cobrança</Label>
                    <Input id="ultima_cobranca" type="date" name="ultima_cobranca" :default-value="dataHoje" required />
                    <InputError :message="errors.ultima_cobranca" />
                </div>
                <div class="grid gap-2">
                    <Label for="proxima_cobranca">Próxima cobrança</Label>
                    <Input id="proxima_cobranca" type="date" name="proxima_cobranca" :default-value="dataProximaCobranca" required />
                    <InputError :message="errors.proxima_cobranca" />
                </div>
                <div class="grid gap-2 md:col-span-2">
                    <Label for="obs">Observação</Label>
                    <Input id="obs" name="obs" placeholder="Observações do contrato" />
                    <InputError :message="errors.obs" />
                </div>

                <div class="flex items-center gap-3 md:col-span-2">
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Salvando...' : 'Cadastrar contrato' }}
                    </Button>
                    <Link :href="index()" class="text-sm text-muted-foreground hover:text-foreground">Cancelar</Link>
                </div>
            </Form>
        </div>
    </div>
</template>

