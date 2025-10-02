<script setup>
import { onBeforeUnmount, onMounted, ref, computed } from "vue";

import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { AlertTriangle, Package, PlusCircle, Truck } from "lucide-vue-next";

const props = defineProps({
    warehouseSummary: Object,
    activityLogWarehouse: Array,
    warehouseChart: Object,
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

const summary = [
    {
        title: "Total Produk",
        value: props.warehouseSummary.totalProduct,
        icon: Package,
        color: "bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300",
    },
    {
        title: "Low Stock",
        value: props.warehouseSummary.lowStock,
        icon: AlertTriangle,
        color: "bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300",
    },
    {
        title: "Produk Baru Bulan Ini",
        value: props.warehouseSummary.newProductThisMonth,
        icon: PlusCircle,
        color: "bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300",
    },
    {
        title: "Supplier Aktif",
        value: props.warehouseSummary.activeSupplier,
        icon: Truck,
        color: "bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300",
    },
];
const categories = props.warehouseChart.stockMovement.map((item) => item.date);
const masukData = props.warehouseChart.stockMovement.map((item) => item.masuk);
const keluarData = props.warehouseChart.stockMovement.map(
    (item) => item.keluar
);

const stockSeries = ref([
    {
        name: "Stok Masuk",
        data: masukData,
    },
    {
        name: "Stok Keluar",
        data: keluarData,
    },
    {
        name: "Stok Keluar",
        data: [30, 20, 35, 50, 40, 60, 80],
    },
]);

const stockOptions = computed(() => ({
    theme: {
        mode: isDarkMode.value ? "dark" : "light",
    },
    chart: {
        type: "bar",
        height: 300,
        toolbar: { show: false },
        background: "transparent",
    },
    plotOptions: {
        bar: { horizontal: false, columnWidth: "50%" },
    },
    dataLabels: { enabled: false },
    xaxis: { categories },
    colors: ["#3b82f6", "#ef4444"],
}));

const categoryDistributionOptions =
    props.warehouseChart.categoryDistribution.map((item) => item.category);

const categoryDistributionSeries =
    props.warehouseChart.categoryDistribution.map((item) => item.total);

const categorySeries = ref(categoryDistributionSeries);

const categoryOptions = computed(() => ({
    chart: {
        type: "pie",
        background: "transparent",
    },
    labels: categoryDistributionOptions,
    legend: {
        position: "bottom",
    },
    theme: {
        mode: isDarkMode.value ? "dark" : "light",
    },
}));
</script>

<template>
    <div class="flex flex-col gap-6 p-4">
        <!-- Ringkasan -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card
                v-for="(item, i) in summary"
                :key="i"
                class="shadow-sm p-4 flex flex-col justify-between"
            >
                <div class="flex items-center justify-between">
                    <CardTitle class="text-sm font-medium">{{
                        item.title
                    }}</CardTitle>
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center"
                        :class="item.color"
                    >
                        <component :is="item.icon" class="w-4 h-4" />
                    </div>
                </div>
                <CardContent class="pt-4">
                    <p class="text-2xl font-bold">{{ item.value }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- Chart -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Card>
                <CardHeader>
                    <CardTitle>Pergerakan Stok (Mingguan)</CardTitle>
                </CardHeader>
                <CardContent>
                    <apexchart
                        type="bar"
                        height="300"
                        :options="stockOptions"
                        :series="stockSeries"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Distribusi Stok per Kategori</CardTitle>
                </CardHeader>
                <CardContent>
                    <apexchart
                        type="pie"
                        height="300"
                        :options="categoryOptions"
                        :series="categorySeries"
                    />
                </CardContent>
            </Card>
        </div>

        <!-- Log Aktivitas Gudang -->
        <Card>
            <CardHeader>
                <CardTitle>Aktivitas Gudang Terbaru</CardTitle>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Produk</TableHead>
                            <TableHead>Jenis</TableHead>
                            <TableHead>Kuantitas</TableHead>
                            <TableHead>Tanggal</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="(log, index) in props.activityLogWarehouse"
                            :key="index"
                        >
                            <TableCell>{{ log.product_name }}</TableCell>
                            <TableCell>
                                <Badge
                                    class="capitalize"
                                    :variant="
                                        log.type === 'Masuk'
                                            ? 'default'
                                            : 'destructive'
                                    "
                                >
                                    {{ log.type }}
                                </Badge>
                            </TableCell>
                            <TableCell>{{ log.qty }}</TableCell>
                            <TableCell>{{ log.date }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>
