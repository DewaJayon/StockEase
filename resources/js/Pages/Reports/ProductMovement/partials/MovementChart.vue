<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';

const props = defineProps({
    chartData: {
        type: Object,
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

const fastLabels = computed(() =>
    props.chartData.fast.map((item) => item.name),
);
const slowLabels = computed(() =>
    props.chartData.slow.map((item) => item.name),
);

const fastSeries = computed(() => [
    {
        name: 'Qty Terjual',
        data: props.chartData.fast.map((item) => item.qty),
    },
]);

const slowSeries = computed(() => [
    {
        name: 'Qty Terjual',
        data: props.chartData.slow.map((item) => item.qty),
    },
    {
        name: 'Stok Tersisa',
        data: props.chartData.slow.map((item) => item.stock),
    },
]);

const baseOptions = computed(() => ({
    theme: {
        mode: isDarkMode.value ? 'dark' : 'light',
    },
    chart: {
        type: 'bar',
        background: 'transparent',
        toolbar: { show: false },
    },
    plotOptions: {
        bar: {
            horizontal: true,
            borderRadius: 6,
            dataLabels: { position: 'top' },
        },
    },
    dataLabels: { enabled: false },
    grid: {
        borderColor: isDarkMode.value ? '#334155' : '#e2e8f0',
        strokeDashArray: 4,
    },
    legend: { position: 'top' },
}));

const fastChartOptions = computed(() => ({
    ...baseOptions.value,
    colors: ['#10b981'],
    xaxis: {
        categories: fastLabels.value,
        labels: { formatter: (v) => `${v} pcs` },
    },
    tooltip: { y: { formatter: (v) => `${v} pcs` } },
}));

const slowChartOptions = computed(() => ({
    ...baseOptions.value,
    colors: ['#f97316', '#6366f1'],
    xaxis: {
        categories: slowLabels.value,
        labels: { formatter: (v) => `${v} pcs` },
    },
    tooltip: { y: { formatter: (v) => `${v} pcs` } },
}));
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Fast Moving Chart -->
        <div class="space-y-2">
            <h3
                class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider"
            >
                Fast Moving (Top 10)
            </h3>
            <div class="h-72">
                <apexchart
                    v-if="chartData.fast.length > 0"
                    type="bar"
                    height="100%"
                    :options="fastChartOptions"
                    :series="fastSeries"
                />
                <div
                    v-else
                    class="flex items-center justify-center h-full text-muted-foreground text-sm"
                >
                    Tidak ada data penjualan dalam periode ini.
                </div>
            </div>
        </div>

        <!-- Slow Moving Chart -->
        <div class="space-y-2">
            <h3
                class="text-sm font-semibold text-orange-500 dark:text-orange-400 uppercase tracking-wider"
            >
                Slow Moving (Top 10)
            </h3>
            <div class="h-72">
                <apexchart
                    v-if="chartData.slow.length > 0"
                    type="bar"
                    height="100%"
                    :options="slowChartOptions"
                    :series="slowSeries"
                />
                <div
                    v-else
                    class="flex items-center justify-center h-full text-muted-foreground text-sm"
                >
                    Tidak ada produk slow moving ditemukan.
                </div>
            </div>
        </div>
    </div>
</template>
