<script setup>
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
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

const purchaseTrendSeries = [
    {
        name: "Total Nilai Pembelian",
        data: props.chart.purchaseTrends?.data ?? [],
    },
];

const purchaseTrendChartOptions = computed(() => ({
    theme: {
        mode: isDarkMode.value ? "dark" : "light",
    },
    chart: {
        background: "transparent",
    },
    colors: ["#4f46e5"],
    dataLabels: {
        enabled: true,
        formatter: (val) => `Rp ${val.toLocaleString()}`,
    },
    stroke: {
        curve: "smooth",
    },
    yaxis: {
        labels: {
            formatter: (value) => `Rp ${value.toLocaleString()}`,
        },
    },
    xaxis: {
        categories: props.chart.purchaseTrends?.labels ?? [],
    },
    tooltip: {
        y: {
            formatter: (value) => `Rp ${value.toLocaleString()}`,
        },
    },
}));

const purchaseTopSupplierSeries = [
    {
        name: "Total Nilai Pembelian",
        data: props.chart.topSupplier?.map((item) => item.total_purchase) ?? [],
    },
];

const purchaseTopSupplierSeriesOptions = computed(() => ({
    theme: {
        mode: isDarkMode.value ? "dark" : "light",
    },
    colors: ["#4f46e5"],
    dataLabels: {
        enabled: true,
        formatter: (val) => `Rp ${val.toLocaleString()}`,
    },
    chart: {
        background: "transparent",
    },
    colors: ["#10b981"],
    yaxis: {
        labels: {
            formatter: (value) => `Rp ${value.toLocaleString()}`,
        },
    },
    xaxis: {
        categories:
            props.chart.topSupplier?.map((item) => item.supplier_name) ?? [],
    },
    tooltip: {
        y: {
            formatter: (val) => `Rp ${val.toLocaleString()}`,
        },
    },
}));
</script>

<template>
    <template v-if="purchaseTrendSeries[0].data.length > 0">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Card class="shadow-md">
                <CardHeader class="gap-y-0 p-4">
                    <CardTitle>Trend Pembelian per Bulan</CardTitle>
                </CardHeader>
                <CardContent class="pb-0 pt-3">
                    <apexchart
                        type="line"
                        height="350"
                        :options="purchaseTrendChartOptions"
                        :series="purchaseTrendSeries"
                    />
                </CardContent>
            </Card>
            <Card class="shadow-md">
                <CardHeader class="gap-y-0 p-4">
                    <CardTitle>Top Supplier</CardTitle>
                </CardHeader>
                <CardContent class="h-[350px]">
                    <apexchart
                        type="bar"
                        height="100%"
                        width="100%"
                        :options="purchaseTopSupplierSeriesOptions"
                        :series="purchaseTopSupplierSeries"
                    />
                </CardContent>
            </Card>
        </div>
    </template>
</template>
