<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { edit as contratoEdit } from '@/routes/contratos';
import { edit as clienteEdit } from '@/routes/clientes';

type SearchResult = {
    id: number;
    nome: string;
    telefone?: string;
    endereco?: string;
    type: 'cliente' | 'contrato';
};

const open = ref(false);
const loading = ref(false);
const searchTerm = ref('');
const results = ref<{
    clientes: SearchResult[];
    contratos: SearchResult[];
}>({ clientes: [], contratos: [] });

const allResults = computed(() => [
    ...results.value.clientes,
    ...results.value.contratos,
]);

watch(searchTerm, async (term) => {
    if (term.length < 2) {
        results.value = { clientes: [], contratos: [] };
        return;
    }

    loading.value = true;
    try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(term)}`);
        const data = await response.json();
        results.value = data;
    } catch (error) {
        console.error('Erro ao buscar:', error);
        results.value = { clientes: [], contratos: [] };
    } finally {
        loading.value = false;
    }
});

function getResultLink(result: SearchResult): string {
    if (result.type === 'cliente') {
        return clienteEdit(result.id).url;
    }
    return contratoEdit(result.id).url;
}

function getResultSubtitle(result: SearchResult): string {
    if (result.type === 'cliente') {
        return result.telefone || 'Sem telefone';
    }
    return result.endereco || 'Sem endereço';
}

function onOpenChange(value: boolean): void {
    open.value = value;
    if (!value) {
        searchTerm.value = '';
        results.value = { clientes: [], contratos: [] };
    }
}

function handleSelect(result: SearchResult): void {
    onOpenChange(false);
}
</script>

<template>
    <Dialog :open="open" @update:open="onOpenChange">
        <Button
            type="button"
            variant="outline"
            size="sm"
            class="gap-2 text-muted-foreground"
            @click="onOpenChange(true)"
        >
            <Search class="size-4" />
            <span class="hidden sm:inline">Pesquisar cliente ou contrato</span>
            <span class="inline sm:hidden">Pesquisar</span>
        </Button>

        <DialogContent class="sm:max-w-xl">
            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold">Pesquisar</h2>
                    <p class="text-sm text-muted-foreground">
                        Busque por nome de cliente ou número de contrato
                    </p>
                </div>

                <Input
                    v-model="searchTerm"
                    placeholder="Digite o nome do cliente ou contrato..."
                    class="w-full"
                    autofocus
                />

                <div class="max-h-96 space-y-4 overflow-y-auto">
                    <div v-if="loading" class="text-center text-sm text-muted-foreground">
                        Buscando...
                    </div>

                    <div v-else-if="searchTerm && allResults.length === 0" class="text-center text-sm text-muted-foreground">
                        Nenhum resultado encontrado
                    </div>

                    <div v-else-if="searchTerm" class="space-y-3">
                        <!-- Clientes section -->
                        <div v-if="results.clientes.length > 0">
                            <p class="text-xs font-semibold text-muted-foreground uppercase">
                                Clientes
                            </p>
                            <div class="space-y-1">
                                <Link
                                    v-for="cliente in results.clientes"
                                    :key="`cliente-${cliente.id}`"
                                    :href="getResultLink(cliente)"
                                    class="flex flex-col gap-1 rounded-lg border border-transparent p-3 transition hover:bg-muted hover:border-border"
                                    @click="handleSelect(cliente)"
                                >
                                    <p class="text-sm font-medium">{{ cliente.nome }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ getResultSubtitle(cliente) }}
                                    </p>
                                </Link>
                            </div>
                        </div>

                        <!-- Contratos section -->
                        <div v-if="results.contratos.length > 0">
                            <p class="text-xs font-semibold text-muted-foreground uppercase">
                                Contratos
                            </p>
                            <div class="space-y-1">
                                <Link
                                    v-for="contrato in results.contratos"
                                    :key="`contrato-${contrato.id}`"
                                    :href="getResultLink(contrato)"
                                    class="flex flex-col gap-1 rounded-lg border border-transparent p-3 transition hover:bg-muted hover:border-border"
                                    @click="handleSelect(contrato)"
                                >
                                    <p class="text-sm font-medium">{{ contrato.nome }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ getResultSubtitle(contrato) }}
                                    </p>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="!searchTerm && !loading" class="text-center text-sm text-muted-foreground">
                        Digite para começar a busca
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
