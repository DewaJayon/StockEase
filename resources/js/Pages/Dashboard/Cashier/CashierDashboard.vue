<script setup>
import { onBeforeUnmount, onMounted, ref, computed } from "vue";
import { formatPrice } from "@/lib/utils";

import { Badge } from "@/Components/ui/badge";
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";
import { ShoppingCart, DollarSign, Star, Users } from "lucide-vue-next";

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

const props = defineProps({
    cashierSalesSummary: Object,
    cashierRecentTrasactions: Array,
    cashierWeeklySalesChart: Object,
});

const salesSummary = ref({
    totalTransactions: props.cashierSalesSummary.totalTransactionPerWeek,
    totalRevenue: formatPrice(props.cashierSalesSummary.todaysIncome),
    bestProduct: props.cashierSalesSummary.bestSellingProduct,
    avgPerCustomer: formatPrice(props.cashierSalesSummary.averagePerCustomer),
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

const salesChartOptions = computed(() => ({
    chart: {
        type: "bar",
        toolbar: { show: false },
        background: "transparent",
    },
    plotOptions: {
        bar: {
            borderRadius: 6,
            columnWidth: "50%",
        },
    },
    dataLabels: {
        enabled: true,
        formatter: (val) => `Rp ${val.toLocaleString()}`,
    },
    xaxis: {
        categories: props.cashierWeeklySalesChart?.categories ?? [],
    },
    yaxis: {
        labels: {
            formatter: (val) => "Rp " + val.toLocaleString(),
        },
    },
    tooltip: {
        y: {
            formatter: (val) => "Rp " + val.toLocaleString(),
        },
    },
    colors: ["#3b82f6"],
    grid: {
        show: false,
    },
    theme: {
        mode: isDarkMode.value ? "dark" : "light",
    },
}));

const salesChartSeries = ref([
    {
        name: "Penjualan",
        data: props.cashierWeeklySalesChart?.data ?? [],
    },
]);

const summaryItems = computed(() => [
    {
        key: "Total Transaksi Perminggu",
        value: salesSummary.value.totalTransactions,
        icon: ShoppingCart,
    },
    {
        key: "Pendapatan Hari Ini",
        value: salesSummary.value.totalRevenue.toLocaleString(),
        icon: DollarSign,
    },
    {
        key: "Produk Terlaris Hari Ini",
        value: salesSummary.value.bestProduct,
        icon: Star,
    },
    {
        key: "Rata-rata / Pelanggan",
        value: salesSummary.value.avgPerCustomer.toLocaleString(),
        icon: Users,
    },
]);
</script>

<template>
    <div class="flex flex-col gap-6 p-4">
        <!-- Ringkasan Penjualan -->
        <div class="grid gap-4 md:grid-cols-4">
            <Card v-for="item in summaryItems" :key="item.key">
                <CardHeader
                    class="p-3 flex flex-row items-center justify-between"
                >
                    <CardTitle class="text-sm font-medium">
                        {{ item.key }}
                    </CardTitle>
                    <component
                        :is="item.icon"
                        class="h-5 w-5 text-muted-foreground"
                    />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">
                        {{ item.value }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Riwayat Transaksi Terbaru -->
        <Card>
            <CardHeader>
                <CardTitle>Transaksi Terbaru</CardTitle>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>No</TableHead>
                            <TableHead>Customer</TableHead>
                            <TableHead>Total</TableHead>
                            <TableHead>Metode</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="(
                                trx, index
                            ) in props.cashierRecentTrasactions"
                            :key="index"
                        >
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell>{{ trx.customer }}</TableCell>
                            <TableCell>
                                {{ formatPrice(trx.total) }}
                            </TableCell>
                            <TableCell>
                                <Badge class="capitalize" variant="outline">
                                    {{ trx.payment_method }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <!-- Chart -->
        <Card>
            <CardHeader>
                <CardTitle>Penjualan Minggu Ini</CardTitle>
            </CardHeader>
            <CardContent>
                <apexchart
                    type="bar"
                    height="300"
                    :options="salesChartOptions"
                    :series="salesChartSeries"
                />
            </CardContent>
        </Card>
    </div>
</template>
