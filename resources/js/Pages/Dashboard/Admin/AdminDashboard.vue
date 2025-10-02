<script setup>
import { formatPrice } from "@/lib/utils";
import { onBeforeUnmount, onMounted, ref, computed } from "vue";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

import { TrendingUp, Calendar } from "lucide-vue-next";

const props = defineProps({
    salesSummary: Object,
    lowStock: Array,
    activities: Array,
    weeklySalesChart: Object,
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

const chartOptions = computed(() => ({
    theme: {
        mode: isDarkMode.value ? "dark" : "light",
    },
    chart: {
        type: "bar",
        toolbar: { show: false },
        background: "transparent",
    },
    colors: ["#3b82f6"],
    dataLabels: {
        enabled: true,
        formatter: (val) => `Rp ${val.toLocaleString()}`,
    },
    plotOptions: {
        bar: {
            borderRadius: 6,
            columnWidth: "50%",
        },
    },
    legend: { show: false },
    grid: { show: false },
    xaxis: {
        categories: props.weeklySalesChart?.categories ?? [],
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

const chartSeries = computed(() => [
    {
        name: "Penjualan",
        data: props.weeklySalesChart?.data ?? [],
    },
]);
</script>

<template>
    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <!-- Penjualan Hari Ini -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        Penjualan Hari Ini
                    </CardTitle>

                    <TrendingUp class="h-5 w-5 text-blue-500" />
                </CardHeader>
                <CardContent class="text-2xl font-bold">
                    {{ formatPrice(salesSummary.today) }}
                </CardContent>
            </Card>

            <!-- Penjualan Bulan Ini -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        Penjualan Bulan Ini
                    </CardTitle>

                    <Calendar class="h-5 w-5 text-green-500" />
                </CardHeader>
                <CardContent class="text-2xl font-bold">
                    {{ formatPrice(salesSummary.month) }}
                </CardContent>
            </Card>
        </div>

        <!-- Stok Kritis & Aktivitas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <template v-if="lowStock.length">
                <Card>
                    <CardHeader><CardTitle>Stok Kritis</CardTitle></CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead> Nama Produk </TableHead>
                                    <TableHead> Stok </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="item in lowStock"
                                    :key="item.name"
                                >
                                    <TableCell class="font-medium">
                                        {{ item.name }}
                                    </TableCell>
                                    <TableCell>{{ item.stock }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </template>

            <template v-else>
                <Card>
                    <CardContent class="h-full">
                        <div
                            class="w-full h-full flex justify-center items-center"
                        >
                            <p class="text-sm text-muted-foreground">
                                Tidak ada stok kritis
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </template>

            <Card>
                <CardHeader>
                    <CardTitle> Aktivitas Terbaru </CardTitle>
                </CardHeader>
                <CardContent>
                    <ul class="space-y-2 text-sm">
                        <li
                            v-for="(act, i) in activities"
                            :key="i"
                            class="border-b pb-1"
                        >
                            {{ act.desc }}
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>

        <!-- Grafik Penjualan -->
        <Card class="mt-6">
            <CardHeader>
                <CardTitle> Grafik Penjualan Mingguan </CardTitle>
            </CardHeader>
            <CardContent>
                <apexchart
                    type="bar"
                    height="300"
                    :options="chartOptions"
                    :series="chartSeries"
                />
            </CardContent>
        </Card>
    </div>
</template>
