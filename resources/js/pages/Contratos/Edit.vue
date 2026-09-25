<script setup lang="ts">
import { Form, Head, Link } from "@inertiajs/vue3";
import ContratoController from "@/actions/App/Http/Controllers/ContratoController";
import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { index } from "@/routes/contratos";
import Checkbox from "@/components/ui/checkbox/Checkbox.vue";
import { ref } from "vue";

type ClienteOption = { id: number; nome: string };
type Contrato = {
    id: number;
    cliente_id: number;
    endereco: string;
    data_inicio: string;
    cobrar_sabado: boolean;
    data_fim: string | null;
    valor_pc_dia: string | number;
    qtd_frete: string | number | null;
    valor_frete: string | number;
    status: "ATIVO" | "BLOQUEADO" | "DEVOLVIDO" | "FINALIZADO";
    ultima_cobranca: string;
    proxima_cobranca: string;
    obs: string;
};

const props = defineProps<{ contrato: Contrato; clientes: ClienteOption[] }>();

const cobrarSabado = ref(props.contrato.cobrar_sabado);

defineOptions({
    layout: { breadcrumbs: [{ title: "Editar contrato", href: "/contratos" }] },
});
</script>

<template>
    <Head title="Editar contrato" />
    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Cadastro</p>
            <h1 class="text-2xl font-semibold tracking-tight">
                Editar contrato
            </h1>
            <p class="text-sm text-muted-foreground">
                Atualize os dados do contrato.
            </p>
        </div>
        <div class="max-w-4xl rounded-xl border bg-card p-6 shadow-sm">
            <Form
                v-bind="ContratoController.update.form(props.contrato.id)"
                class="grid gap-6 md:grid-cols-2"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="cliente_id">Cliente</Label>
                    <select
                        id="cliente_id"
                        name="cliente_id"
                        required
                        class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 text-sm outline-none focus-visible:ring-[3px]"
                    >
                        <option
                            v-for="cliente in clientes"
                            :key="cliente.id"
                            :value="cliente.id"
                            :selected="cliente.id === props.contrato.cliente_id"
                        >
                            {{ cliente.nome }}
                        </option>
                    </select>
                    <InputError :message="errors.cliente_id" />
                </div>
                <div class="grid gap-2">
                    <Label for="endereco">Endereço</Label>
                    <Input
                        id="endereco"
                        name="endereco"
                        :default-value="props.contrato.endereco"
                        required
                    />
                    <InputError :message="errors.endereco" />
                </div>
                <div class="grid gap-2">
                    <Label for="data_inicio">Data de início</Label
                    ><Input
                        id="data_inicio"
                        type="date"
                        name="data_inicio"
                        :default-value="props.contrato.data_inicio"
                        required
                    /><InputError :message="errors.data_inicio" />
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox
                        id="cobrar_sabado"
                        name="cobrar_sabado"
                        v-model="cobrarSabado"
                        @update:model-value="
                            (value) => console.log('COBRAR SÁBADO:', value)
                        "
                    />

                    <Label for="cobrar_sabado"> Cobrar sábados </Label>
                </div>

                <div class="grid gap-2">
                    <Label for="data_fim">Data de fim</Label
                    ><Input
                        id="data_fim"
                        type="date"
                        name="data_fim"
                        :default-value="props.contrato.data_fim ?? ''"
                    /><InputError :message="errors.data_fim" />
                </div>
                <div class="grid gap-2">
                    <Label for="valor_pc_dia">Valor da peça por dia</Label
                    ><Input
                        id="valor_pc_dia"
                        name="valor_pc_dia"
                        type="number"
                        step="0.01"
                        min="0"
                        :default-value="props.contrato.valor_pc_dia"
                        required
                    /><InputError :message="errors.valor_pc_dia" />
                </div>
                <div class="grid gap-2">
                    <Label for="qtd_frete">Quantidade de fretes</Label
                    ><Input
                        id="qtd_frete"
                        name="qtd_frete"
                        type="number"
                        min="0"
                        :default-value="props.contrato.qtd_frete ?? 0"
                    /><InputError :message="errors.qtd_frete" />
                </div>
                <div class="grid gap-2">
                    <Label for="valor_frete">Valor do frete</Label
                    ><Input
                        id="valor_frete"
                        name="valor_frete"
                        type="number"
                        step="0.01"
                        min="0"
                        :default-value="props.contrato.valor_frete"
                        required
                    /><InputError :message="errors.valor_frete" />
                </div>
                <div class="grid gap-2">
                    <Label for="status">Status</Label
                    ><select
                        id="status"
                        name="status"
                        class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 text-sm outline-none focus-visible:ring-[3px]"
                        required
                    >
                        <option
                            value="ATIVO"
                            :selected="props.contrato.status === 'ATIVO'"
                        >
                            Ativo
                        </option>
                        <option
                            value="BLOQUEADO"
                            :selected="props.contrato.status === 'BLOQUEADO'"
                        >
                            Bloqueado
                        </option></select
                    ><InputError :message="errors.status" />
                </div>
                <div class="grid gap-2">
                    <Label for="ultima_cobranca">Última cobrança</Label
                    ><Input
                        id="ultima_cobranca"
                        type="date"
                        name="ultima_cobranca"
                        :default-value="props.contrato.ultima_cobranca"
                        required
                    /><InputError :message="errors.ultima_cobranca" />
                </div>
                <div class="grid gap-2">
                    <Label for="proxima_cobranca">Próxima cobrança</Label
                    ><Input
                        id="proxima_cobranca"
                        type="date"
                        name="proxima_cobranca"
                        :default-value="props.contrato.proxima_cobranca"
                        required
                    /><InputError :message="errors.proxima_cobranca" />
                </div>
                <div class="grid gap-2 md:col-span-2">
                    <Label for="obs">Observação</Label
                    ><Input
                        id="obs"
                        name="obs"
                        :default-value="props.contrato.obs"
                    /><InputError :message="errors.obs" />
                </div>
                <div class="flex items-center gap-3 md:col-span-2">
                    <Button type="submit" :disabled="processing">{{
                        processing ? "Salvando..." : "Salvar alterações"
                    }}</Button
                    ><Link
                        :href="index()"
                        class="text-sm text-muted-foreground hover:text-foreground"
                        >Cancelar</Link
                    >
                </div>
            </Form>
        </div>
    </div>
</template>
