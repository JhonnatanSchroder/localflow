<script setup lang="ts">
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps<{ labels: string[]; data: number[]; label?: string }>();
const canvas = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

onMounted(() => {
    if (!canvas.value) return;
    chart = new Chart(canvas.value.getContext('2d')!, {
        type: 'line',
        data: {
            labels: props.labels,
            datasets: [
                {
                    label: props.label || 'Valor',
                    data: props.data,
                    fill: true,
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    borderColor: 'rgba(59,130,246,1)',
                    tension: 0.2,
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
