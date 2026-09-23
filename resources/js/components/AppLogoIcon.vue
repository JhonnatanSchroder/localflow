<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/logo_america.png"
        alt="América Materiais para Construção"
        :class="className"
        class="object-contain"
        v-bind="$attrs"
    />
</template>
