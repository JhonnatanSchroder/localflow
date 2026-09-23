<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClienteController from '@/actions/App/Http/Controllers/ClienteController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { create } from '@/routes/clientes';
import { dashboard } from '@/routes';

type PageProps = {
    flash?: {
        toast?: {
            type: 'success' | 'info' | 'warning' | 'error';
            message: string;
        };
    };
};

const page = usePage<PageProps>();
const successMessage = computed(() =>
    page.props.flash?.toast?.type === 'success'
        ? page.props.flash.toast.message
        : null,
);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Novo cliente',
                href: create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Novo cliente" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <p class="text-sm font-medium text-muted-foreground">
                Cadastro
            </p>
            <h1 class="text-2xl font-semibold tracking-tight">
                Novo cliente
            </h1>
            <p class="text-sm text-muted-foreground">
                Preencha os dados para cadastrar um cliente.
            </p>
        </div>

        <div class="max-w-3xl rounded-xl border bg-card p-6 shadow-sm">
            <div
                v-if="successMessage"
                class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300"
                role="status"
            >
                {{ successMessage }}
            </div>

            <Form
                v-bind="ClienteController.store.form()"
                class="grid gap-6 md:grid-cols-2"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="nome">Nome</Label>
                    <Input
                        id="nome"
                        name="nome"
                        required
                        autocomplete="name"
                        placeholder="Nome completo"
                    />
                    <InputError :message="errors.nome" />
                </div>

                <div class="grid gap-2">
                    <Label for="cpf">CPF</Label>
                    <Input
                        id="cpf"
                        name="cpf"
                        maxlength="14"
                        placeholder="000.000.000-00"
                    />
                    <InputError :message="errors.cpf" />
                </div>

                <div class="grid gap-2">
                    <Label for="telefone">Telefone</Label>
                    <Input
                        id="telefone"
                        name="telefone"
                        required
                        maxlength="15"
                        autocomplete="tel"
                        placeholder="(00) 00000-0000"
                    />
                    <InputError :message="errors.telefone" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="endereco">Endereço</Label>
                    <Input
                        id="endereco"
                        name="endereco"
                        autocomplete="street-address"
                        placeholder="Rua, número e complemento"
                    />
                    <InputError :message="errors.endereco" />
                </div>

                <div class="flex items-center gap-3 md:col-span-2">
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Salvando...' : 'Cadastrar cliente' }}
                    </Button>
                    <Link
                        :href="dashboard()"
                        class="text-sm text-muted-foreground hover:text-foreground"
                    >
                        Cancelar
                    </Link>
                </div>
            </Form>
        </div>
    </div>
</template>