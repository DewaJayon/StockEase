<script setup>
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { formatPrice } from '@/lib/utils';
import { CircleDollarSign, ShoppingCart, Package } from 'lucide-vue-next';

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
        <Card
            class="shadow-sm border-l-4 border-l-blue-500 overflow-hidden group"
        >
            <CardHeader
                class="flex flex-row items-center justify-between space-y-0 pb-2"
            >
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    Total Transaksi Produk
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
                    {{ formatPrice(props.summary.sumTotalPurchase ?? 0) }}
                </h2>
                <p class="text-xs text-muted-foreground mt-1">
                    Total pengeluaran kotor
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
                    Total Item Dibeli
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
                    {{
                        (
                            props.summary.totalItemsPurchased ?? 0
                        ).toLocaleString()
                    }}
                </h2>
                <p class="text-xs text-muted-foreground mt-1">
                    Kuantitas barang dibeli
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
                    Total Pembelian
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
                    {{ (props.summary.totalTransaction ?? 0).toLocaleString() }}
                </h2>
                <p class="text-xs text-muted-foreground mt-1">
                    Jumlah nota pembelian
                </p>
            </CardContent>
        </Card>
    </div>
</template>
