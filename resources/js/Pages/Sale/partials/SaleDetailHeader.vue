<script setup>
import { formatDateTime } from "@/lib/utils";
import { Badge } from "@/Components/ui/badge";
import { Separator } from "@/Components/ui/separator";

const props = defineProps({
    sale: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div
        class="flex flex-col gap-6 mb-9 sm:flex-row sm:items-center sm:justify-between"
    >
        <div>
            <span
                class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-400"
            >
                Nama Kasir
            </span>

            <h5
                class="mb-2 text-base font-semibold text-gray-800 dark:text-white/90"
            >
                {{ props.sale.user.name }}
            </h5>

            <span
                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
            >
                Tanggal Penjualan
            </span>

            <span class="block text-sm text-gray-500 dark:text-gray-400">
                {{ formatDateTime(props.sale.updated_at) }}
            </span>
        </div>

        <Separator
            orientation="vertical"
            class="h-px w-full bg-gray-200 dark:bg-gray-800 sm:h-[158px] sm:w-px"
        />

        <div class="sm:text-right">
            <span
                class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-400"
            >
                Nama Pelanggan
            </span>

            <h5
                class="mb-2 text-base font-semibold text-gray-800 dark:text-white/90"
            >
                {{ props.sale.customer_name ?? "-" }}
            </h5>

            <span
                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400 capitalize"
            >
                Medote Pembayaran:
                {{ props.sale.payment_method }}
            </span>
            <span
                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400 capitalize"
            >
                Status Pembayaran:
                <Badge variant="outline" class="border border-green-500">
                    {{ props.sale.status }}
                </Badge>
            </span>

            <template v-if="props.sale.payment_transaction !== null">
                <span
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400 capitalize"
                >
                    Status Midtrans:
                    <Badge variant="outline" class="border border-green-500">
                        {{ props.sale.payment_transaction.status }}
                    </Badge>
                </span>
                <span
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400 capitalize"
                >
                    Order ID Midtrans:
                    {{ props.sale.payment_transaction.external_id }}
                </span>
            </template>
        </div>
    </div>
</template>
