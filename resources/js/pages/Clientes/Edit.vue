<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ClienteController from '@/actions/App/Http/Controllers/ClienteController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/clientes';

type Cliente = {
    id: number;
    nome: string;
    cpf: string;
    telefone: string;
    endereco: string;
    status: 'ATIVO' | 'BLOQUEADO';
};

const props = defineProps<{
    cliente: Cliente;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Editar cliente',
                href: '/clientes',
            },
        ],
    },
});
</script>

<template>
    <Head title="Editar cliente" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Cadastro</p>
            <h1 class="text-2xl font-semibold tracking-tight">
                Editar cliente
            </h1>
            <p class="text-sm text-muted-foreground">
                Atualize os dados de {{ props.cliente.nome }}.
            </p>
        </div>

        <div class="max-w-3xl rounded-xl border bg-card p-6 shadow-sm">
            <Form
                v-bind="ClienteController.update.form(props.cliente.id)"
                class="grid gap-6 md:grid-cols-2"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="nome">Nome</Label>
                    <Input
                        id="nome"
                        name="nome"
                        :default-value="props.cliente.nome"
                        required
                        autocomplete="name"
                    />
                    <InputError :message="errors.nome" />
                </div>

                <div class="grid gap-2">
                    <Label for="cpf">CPF</Label>
                    <Input
                        id="cpf"
                        name="cpf"
                        :default-value="props.cliente.cpf"
                        maxlength="14"
                    />
                    <InputError :message="errors.cpf" />
                </div>

                <div class="grid gap-2">
                    <Label for="telefone">Telefone</Label>
                    <Input
                        id="telefone"
                        name="telefone"
                        :default-value="props.cliente.telefone"
                        required
                        maxlength="15"
                        autocomplete="tel"
                    />
                    <InputError :message="errors.telefone" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="endereco">Endereço</Label>
                    <Input
                        id="endereco"
                        name="endereco"
                        :default-value="props.cliente.endereco"
                        required
                        autocomplete="street-address"
                    />
                    <InputError :message="errors.endereco" />
                </div>

                <div class="grid gap-2">
                    <Label for="status">Status</Label>
                    <select
                        id="status"
                        name="status"
                        class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 text-sm outline-none focus-visible:ring-[3px]"
                    >
                        <option
                            value="ATIVO"
                            :selected="props.cliente.status === 'ATIVO'"
                        >
                            Ativo
                        </option>
                        <option
                            value="BLOQUEADO"
                            :selected="props.cliente.status === 'BLOQUEADO'"
                        >
                            Bloqueado
                        </option>
                    </select>
                    <InputError :message="errors.status" />
                </div>

                <div class="flex items-center gap-3 md:col-span-2">
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Salvando...' : 'Salvar alterações' }}
                    </Button>
                    <Link
                        :href="index()"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        Cancelar
                    </Link>
                </div>
            </Form>
        </div>
    </div>
</template>