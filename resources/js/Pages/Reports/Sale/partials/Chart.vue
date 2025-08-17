<script setup>
import { Card, CardContent } from "@/Components/ui/card";
import { ref, onMounted, onBeforeUnmount, computed } from "vue";

const props = defineProps({
    chart: {
        type: Object,
        required: true,
    },
});

const isDarkMode = ref(document.documentElement.classList.contains("dark"));
let observer = null;

onMounted(() => {
    observer = new MutationObserver(() => {
        isDarkMode.value = document.documentElement.classList.contains("dark");
    });
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ["class"],
    });
});

onBeforeUnmount(() => {
    if (observer) observer.disconnect();
});

const salesSeries = [
    {
        name: "Penjualan",
        data: props.chart.salesTrend?.data ?? [],
    },
];

const chartOptions = computed(() => ({
    theme: {
        mode: isDarkMode.value ? "dark" : "light",
    },
    chart: {
        background: "transparent",
    },
    toolbar: {
        show: true,
    },
    colors: ["#4f46e5"],
    stroke: {
        curve: "smooth",
        width: 3,
    },
    xaxis: {
        categories: props.chart.salesTrend?.labels ?? [],
    },
    yaxis: {
        labels: {
            formatter: (value) => `Rp ${value.toLocaleString()}`,
        },
    },
    tooltip: {
        y: {
            formatter: (value) => `Rp ${value.toLocaleString()}`,
        },
    },
}));

const productSaleSeries =
    props.chart.productSalesShare?.map((item) => item.total_sold) ?? [];

const productSaleOptions = computed(() => ({
    chart: {
        type: "pie",
        background: "transparent",
    },
    labels:
        props.chart?.productSalesShare?.map((item) => item.product_name) ?? [],
    responsive: [
        {
            breakpoint: 480,
            options: {
                chart: {
                    width: 200,
                },
                legend: {
                    position: "bottom",
                },
            },
        },
    ],
    theme: {
        mode: isDarkMode.value ? "dark" : "light",
    },
}));
</script>

<template>
    <template v-if="salesSeries[0].data.length > 0">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Card class="shadow-md">
                <CardContent class="pb-0 pt-3">
                    <apexchart
                        type="line"
                        height="350"
                        :options="chartOptions"
                        :series="salesSeries"
                    />
                </CardContent>
            </Card>
            <Card class="shadow-md">
                <CardContent
                    class="flex justify-center items-center h-full w-full"
                >
                    <apexchart
                        type="pie"
                        width="470"
                        :options="productSaleOptions"
                        :series="productSaleSeries"
                    />
                </CardContent>
            </Card>
        </div>
    </template>
</template>
