<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';

const props = defineProps({
    chartData: {
        type: Array,
        required: true,
    },
});

const isDarkMode = ref(document.documentElement.classList.contains('dark'));
let observer = null;

onMounted(() => {
    observer = new MutationObserver(() => {
        isDarkMode.value = document.documentElement.classList.contains('dark');
    });
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
});

onBeforeUnmount(() => {
    if (observer) observer.disconnect();
});

const series = computed(() => [
    {
        name: 'Pendapatan',
        data: props.chartData.map((item) => item.revenue),
    },
    {
        name: 'HPP',
        data: props.chartData.map((item) => item.cost),
    },
    {
        name: 'Laba Kotor',
        data: props.chartData.map((item) => item.profit),
    },
]);

const chartOptions = computed(() => ({
    theme: {
        mode: isDarkMode.value ? 'dark' : 'light',
    },
    chart: {
        type: 'area',
        background: 'transparent',
        stacked: false,
        zoom: {
            enabled: false,
        },
        toolbar: {
            show: false,
        },
    },
    colors: ['#6366f1', '#f97316', '#10b981'],
    dataLabels: {
        enabled: false,
    },
    stroke: {
        curve: 'smooth',
        width: [3, 3, 4],
        dashArray: [0, 0, 0],
    },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100, 100, 100],
        },
    },
    xaxis: {
        categories: props.chartData.map((item) => item.day),
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
    },
    yaxis: {
        labels: {
            formatter: (value) => `Rp ${value.toLocaleString()}`,
        },
    },
    tooltip: {
        shared: true,
        intersect: false,
        y: {
            formatter: (value) => `Rp ${value.toLocaleString()}`,
        },
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
    },
    grid: {
        borderColor: isDarkMode.value ? '#334155' : '#e2e8f0',
        strokeDashArray: 4,
    },
}));
</script>

<template>
    <div class="w-full h-full">
        <apexchart
            v-if="chartData.length > 0"
            type="area"
            height="100%"
            :options="chartOptions"
            :series="series"
        />
        <div
            v-else
            class="flex flex-col items-center justify-center h-full text-muted-foreground gap-2"
        >
            <p>Tidak ada data grafik untuk ditampilkan.</p>
        </div>
    </div>
</template>
