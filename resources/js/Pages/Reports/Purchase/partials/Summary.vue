<script setup>
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { formatPrice } from "@/lib/utils";

const props = defineProps({
    summary: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card class="shadow-md">
            <CardHeader class="gap-y-0 p-4">
                <CardTitle>Total Pembelian Produk</CardTitle>
            </CardHeader>
            <CardContent class="p-4 pt-0">
                <h2 class="text-2xl font-bold">
                    {{ formatPrice(props.summary.sumTotalPurchase ?? 0) }}
                </h2>
            </CardContent>
        </Card>
        <Card class="shadow-md">
            <CardHeader class="gap-y-0 p-4">
                <CardTitle>Jumlah Transaksi</CardTitle>
            </CardHeader>
            <CardContent class="p-4 pt-0">
                <h2 class="text-2xl font-bold">
                    <!-- {{ props.summary.transactionCount ?? 0 }} -->
                </h2>
            </CardContent>
        </Card>
        <Card class="shadow-md">
            <CardHeader class="gap-y-0 p-4">
                <CardTitle>Produk Terjual</CardTitle>
            </CardHeader>
            <CardContent class="p-4 pt-0">
                <h2 class="text-2xl font-bold">
                    <!-- {{ props.summary.countProductSale ?? 0 }} -->
                </h2>
            </CardContent>
        </Card>
        <Card class="shadow-md">
            <CardHeader class="gap-y-0 p-4">
                <CardTitle>Produk Terlaris</CardTitle>
            </CardHeader>
            <CardContent class="p-4 pt-0">
                <h2 class="text-2xl font-bold">
                    <!-- {{ props.summary.bestSellingProduct?.product_name ?? "-" }} -->
                </h2>
            </CardContent>
        </Card>
    </div>
</template>
