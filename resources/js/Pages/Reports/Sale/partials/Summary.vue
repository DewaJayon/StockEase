<script setup>
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { formatPrice } from '@/lib/utils';
import {
    CircleDollarSign,
    ShoppingCart,
    Package,
    Trophy,
} from 'lucide-vue-next';

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <Card
            class="shadow-sm border-l-4 border-l-blue-500 overflow-hidden group"
        >
            <CardHeader
                class="flex flex-row items-center justify-between space-y-0 pb-2"
            >
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    Total Penjualan
                </CardTitle>
                <div
                    class="bg-blue-100 dark:bg-blue-900/40 rounded-full group-hover:scale-110 transition-transform"
                >
                    <CircleDollarSign
                        class="w-4 h-4 text-blue-600 dark:text-blue-400"
                    />
                </div>
            </CardHeader>
            <CardContent>
                <h2 class="text-2xl font-bold tracking-tight">
                    {{ formatPrice(props.summary.sumTotalSale ?? 0) }}
                </h2>
                <p class="text-xs text-muted-foreground mt-1">
                    Total pendapatan kotor
                </p>
            </CardContent>
        </Card>

        <Card
            class="shadow-sm border-l-4 border-l-green-500 overflow-hidden group"
        >
            <CardHeader
                class="flex flex-row items-center justify-between space-y-0 pb-2"
            >
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    Jumlah Transaksi
                </CardTitle>
                <div
                    class="bg-green-100 dark:bg-green-900/40 rounded-full group-hover:scale-110 transition-transform"
                >
                    <ShoppingCart
                        class="w-4 h-4 text-green-600 dark:text-green-400"
                    />
                </div>
            </CardHeader>
            <CardContent>
                <h2 class="text-2xl font-bold tracking-tight">
                    {{ (props.summary.transactionCount ?? 0).toLocaleString() }}
                </h2>
                <p class="text-xs text-muted-foreground mt-1">
                    Transaksi berhasil
                </p>
            </CardContent>
        </Card>

        <Card
            class="shadow-sm border-l-4 border-l-orange-500 overflow-hidden group"
        >
            <CardHeader
                class="flex flex-row items-center justify-between space-y-0 pb-2"
            >
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    Produk Terjual
                </CardTitle>
                <div
                    class="bg-orange-100 dark:bg-orange-900/40 rounded-full group-hover:scale-110 transition-transform"
                >
                    <Package
                        class="w-4 h-4 text-orange-600 dark:text-orange-400"
                    />
                </div>
            </CardHeader>
            <CardContent>
                <h2 class="text-2xl font-bold tracking-tight">
                    {{ (props.summary.countProductSale ?? 0).toLocaleString() }}
                </h2>
                <p class="text-xs text-muted-foreground mt-1">
                    Total kuantitas barang
                </p>
            </CardContent>
        </Card>

        <Card
            class="shadow-sm border-l-4 border-l-purple-500 overflow-hidden group"
        >
            <CardHeader
                class="flex flex-row items-center justify-between space-y-0 pb-2"
            >
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    Produk Terlaris
                </CardTitle>
                <div
                    class="bg-purple-100 dark:bg-purple-900/40 rounded-full group-hover:scale-110 transition-transform"
                >
                    <Trophy
                        class="w-4 h-4 text-purple-600 dark:text-purple-400"
                    />
                </div>
            </CardHeader>
            <CardContent>
                <h2
                    class="text-xl font-bold tracking-tight truncate"
                    :title="
                        props.summary.bestSellingProduct?.product_name ?? '-'
                    "
                >
                    {{ props.summary.bestSellingProduct?.product_name ?? '-' }}
                </h2>
                <p
                    v-if="props.summary.bestSellingProduct?.total_sold"
                    class="text-xs text-muted-foreground mt-1"
                >
                    Terjual
                    {{
                        props.summary.bestSellingProduct.total_sold.toLocaleString()
                    }}
                    pcs
                </p>
                <p v-else class="text-xs text-muted-foreground mt-1">
                    Belum ada penjualan
                </p>
            </CardContent>
        </Card>
    </div>
</template>
