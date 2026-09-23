<script setup lang="ts">
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps<{ labels: string[]; data: number[]; label?: string }>();
const canvas = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

onMounted(() => {
    if (!canvas.value) return;
    chart = new Chart(canvas.value.getContext('2d')!, {
        type: 'bar',
        data: {
            labels: props.labels,
            datasets: [
                {
                    label: props.label || 'Contagem',
                    data: props.data,
                    backgroundColor: 'rgba(16,185,129,0.8)',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true },
            },
        },
    });
});
</script>

<template>
    <div class="h-56">
        <canvas ref="canvas" />
    </div>
</template>
