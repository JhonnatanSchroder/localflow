<script setup lang="ts">
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { rapido } from '@/routes/clientes';

type ClienteOption = {
    id: number;
    nome: string;
};

const emit = defineEmits<{
    created: [cliente: ClienteOption];
}>();

const open = ref(false);
const processing = ref(false);
const errors = ref<Record<string, string>>({});
const form = ref({
    nome: '',
    cpf: '',
    telefone: '',
    endereco: '',
});

function resetForm(): void {
    form.value = { nome: '', cpf: '', telefone: '', endereco: '' };
    errors.value = {};
}

function onOpenChange(value: boolean): void {
    open.value = value;

    if (!value) {
        resetForm();
    }
}

function csrfToken(): string {
    const cookie = document.cookie
        .split('; ')
        .find((item) => item.startsWith('XSRF-TOKEN='));

    return cookie ? decodeURIComponent(cookie.split('=')[1]) : '';
}

async function submit(): Promise<void> {
    if (processing.value) {
        return;
    }

    processing.value = true;
    errors.value = {};

    try {
        const response = await fetch(rapido().url, {
            method: 'post',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(form.value),
        });

        if (response.status === 422) {
            const payload = await response.json();
            errors.value = Object.fromEntries(
                Object.entries(
                    payload.errors as Record<string, string[]>,
                ).map(([field, messages]) => [field, messages[0]]),
            );

            return;
        }

        if (!response.ok) {
            errors.value = {
                nome: 'Não foi possível cadastrar o cliente. Tente novamente.',
            };

            return;
        }

        const { cliente } = (await response.json()) as {
            cliente: ClienteOption;
        };

        emit('created', cliente);
        onOpenChange(false);
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="onOpenChange">
        <Button type="button" variant="outline" size="sm" @click="onOpenChange(true)">
            Novo cliente
        </Button>

        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>Novo cliente</DialogTitle>
                <DialogDescription>
                    Cadastre o cliente sem sair do contrato. Ele será
                    selecionado automaticamente.
                </DialogDescription>
            </DialogHeader>

            <form class="grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="novo_cliente_nome">Nome</Label>
                    <Input
                        id="novo_cliente_nome"
                        v-model="form.nome"
                        required
                        autocomplete="name"
                        placeholder="Nome completo"
                    />
                    <InputError :message="errors.nome" />
                </div>

                <div class="grid gap-2">
                    <Label for="novo_cliente_cpf">CPF</Label>
                    <Input
                        id="novo_cliente_cpf"
                        v-model="form.cpf"
                        maxlength="14"
                        placeholder="000.000.000-00"
                    />
                    <InputError :message="errors.cpf" />
                </div>

                <div class="grid gap-2">
                    <Label for="novo_cliente_telefone">Telefone</Label>
                    <Input
                        id="novo_cliente_telefone"
                        v-model="form.telefone"
                        required
                        maxlength="15"
                        autocomplete="tel"
                        placeholder="(00) 00000-0000"
                    />
                    <InputError :message="errors.telefone" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="novo_cliente_endereco">Endereço</Label>
                    <Input
                        id="novo_cliente_endereco"
                        v-model="form.endereco"
                        autocomplete="street-address"
                        placeholder="Rua, número e complemento"
                    />
                    <InputError :message="errors.endereco" />
                </div>

                <DialogFooter class="md:col-span-2">
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="processing"
                        @click="onOpenChange(false)"
                    >
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Salvando...' : 'Cadastrar cliente' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
