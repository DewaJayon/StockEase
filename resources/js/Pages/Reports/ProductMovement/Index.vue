<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import MovementChart from './partials/MovementChart.vue';
import { Badge } from '@/Components/ui/badge';

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/Components/ui/card';

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';

import {
    Zap,
    PackageSearch,
    ShoppingBag,
    BarChart2,
    AlertCircle,
} from 'lucide-vue-next';

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';

const props = defineProps({
    fastMoving: Array,
    slowMoving: Array,
    chartData: Object,
    summary: Object,
    filters: Object,
});

const chartReady = ref(false);

onMounted(() => {
    chartReady.value = true;
});

const form = useForm({
    start: props.filters.start,
    end: props.filters.end,
});

const submit = () => {
    form.get(route('reports.product-movement'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Analisis Produk Fast & Slow Moving" />

        <template #breadcrumb>
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <Link :href="route('dashboard')">
                            <BreadcrumbLink>Dashboard</BreadcrumbLink>
                        </Link>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Analisis Produk</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>

        <div class="flex flex-1 flex-col gap-6 p-6">
            <!-- Header Section -->
            <div
                class="flex flex-col md:flex-row md:items-center justify-between gap-4"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Analisis Produk
                    </h1>
                    <p class="text-muted-foreground">
                        Identifikasi produk fast moving &amp; slow moving untuk
                        optimasi stok.
                    </p>
                </div>

                <form
                    class="flex flex-col sm:flex-row items-center gap-3 bg-card p-4 rounded-xl border shadow-sm"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-1.5 w-full sm:w-auto">
                        <label
                            class="text-xs font-semibold uppercase text-muted-foreground px-1"
                        >
                            Rentang Tanggal
                        </label>
                        <DateRangePicker
                            v-model:start="form.start"
                            v-model:end="form.end"
                        />
                    </div>
                    <div class="pt-5 w-full sm:w-auto">
                        <Button
                            type="submit"
                            class="w-full"
                            :disabled="form.processing"
                        >
                            Filter
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card class="overflow-hidden border-l-4 border-l-primary">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Total Produk
                        </CardTitle>
                        <div class="bg-primary/10 p-2 rounded-lg">
                            <PackageSearch class="h-4 w-4 text-primary" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatNumber(summary.total_products_analyzed) }}
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Produk dianalisis
                        </p>
                    </CardContent>
                </Card>

                <Card class="overflow-hidden border-l-4 border-l-blue-500">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Total Terjual
                        </CardTitle>
                        <div class="bg-blue-500/10 p-2 rounded-lg">
                            <ShoppingBag class="h-4 w-4 text-blue-500" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">
                            {{ formatNumber(summary.total_qty_sold) }} pcs
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Total unit terjual pada periode ini
                        </p>
                    </CardContent>
                </Card>

                <Card class="overflow-hidden border-l-4 border-l-emerald-500">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Fast Moving
                        </CardTitle>
                        <div class="bg-emerald-500/10 p-2 rounded-lg">
                            <Zap class="h-4 w-4 text-emerald-500" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-emerald-600">
                            {{ formatNumber(summary.fast_moving_count) }}
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Produk dengan penjualan aktif
                        </p>
                    </CardContent>
                </Card>

                <Card class="overflow-hidden border-l-4 border-l-orange-500">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Tidak Terjual
                        </CardTitle>
                        <div class="bg-orange-500/10 p-2 rounded-lg">
                            <AlertCircle class="h-4 w-4 text-orange-500" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-orange-500">
                            {{ formatNumber(summary.unsold_products_count) }}
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Produk ber-stok tapi tidak terjual
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Chart Section -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <BarChart2 class="h-5 w-5 text-primary" />
                        <CardTitle>Perbandingan Pergerakan Produk</CardTitle>
                    </div>
                    <CardDescription>
                        Top 10 produk terlaris vs top 10 produk paling lambat
                        terjual dalam periode ini.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <!-- ApexCharts only renders after DOM is mounted -->
                    <MovementChart v-if="chartReady" :chart-data="chartData" />
                    <div
                        v-else
                        class="h-72 animate-pulse bg-muted rounded-lg"
                    />
                </CardContent>
            </Card>

            <!-- Tables Row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Fast Moving Table -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <CardTitle>Fast Moving</CardTitle>
                        </div>
                        <CardDescription>
                            Produk dengan penjualan tertinggi — prioritaskan
                            restock.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="fastMoving.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-muted-foreground gap-2"
                        >
                            <ShoppingBag class="h-8 w-8 opacity-30" />
                            <p class="text-sm">
                                Tidak ada penjualan dalam periode ini.
                            </p>
                        </div>
                        <div v-else class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow class="hover:bg-transparent">
                                        <TableHead class="w-8"> # </TableHead>
                                        <TableHead>Produk</TableHead>
                                        <TableHead class="text-center">
                                            Qty Terjual
                                        </TableHead>
                                        <TableHead class="text-center">
                                            Stok
                                        </TableHead>
                                        <TableHead class="text-right">
                                            Pendapatan
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="(item, i) in fastMoving"
                                        :key="item.product_id"
                                        class="hover:bg-muted/40"
                                    >
                                        <TableCell
                                            class="font-bold text-muted-foreground"
                                        >
                                            {{ i + 1 }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{
                                                    item.product_name
                                                }}</span>
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                    >{{ item.sku }}</span
                                                >
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <Badge
                                                class="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 font-mono"
                                            >
                                                {{ item.total_qty_sold }} pcs
                                            </Badge>
                                        </TableCell>
                                        <TableCell
                                            class="text-center text-muted-foreground"
                                        >
                                            {{ item.current_stock }} pcs
                                        </TableCell>
                                        <TableCell
                                            class="text-right font-medium"
                                        >
                                            {{
                                                formatCurrency(
                                                    item.total_revenue,
                                                )
                                            }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Slow Moving Table -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <CardTitle>Slow Moving</CardTitle>
                        </div>
                        <CardDescription>
                            Produk ber-stok yang paling sedikit terjual — perlu
                            perhatian.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="slowMoving.length === 0"
                            class="flex flex-col items-center justify-center py-10 text-muted-foreground gap-2"
                        >
                            <PackageSearch class="h-8 w-8 opacity-30" />
                            <p class="text-sm">
                                Tidak ada produk slow moving ditemukan.
                            </p>
                        </div>
                        <div v-else class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow class="hover:bg-transparent">
                                        <TableHead class="w-8"> # </TableHead>
                                        <TableHead>Produk</TableHead>
                                        <TableHead class="text-center">
                                            Stok Mengendap
                                        </TableHead>
                                        <TableHead class="text-center">
                                            Qty Terjual
                                        </TableHead>
                                        <TableHead class="text-center">
                                            Terakhir Terjual
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="(item, i) in slowMoving"
                                        :key="item.product_id"
                                        class="hover:bg-muted/40"
                                    >
                                        <TableCell
                                            class="font-bold text-muted-foreground"
                                        >
                                            {{ i + 1 }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{
                                                    item.product_name
                                                }}</span>
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                    >{{ item.sku }}</span
                                                >
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <Badge
                                                class="bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300 font-mono"
                                            >
                                                {{ item.current_stock }} pcs
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <span
                                                :class="
                                                    item.total_qty_sold === 0
                                                        ? 'text-red-500 font-medium'
                                                        : 'text-muted-foreground'
                                                "
                                            >
                                                {{
                                                    item.total_qty_sold === 0
                                                        ? 'Belum terjual'
                                                        : `${item.total_qty_sold} pcs`
                                                }}
                                            </span>
                                        </TableCell>
                                        <TableCell
                                            class="text-center text-sm text-muted-foreground"
                                        >
                                            {{ formatDate(item.last_sold_at) }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
