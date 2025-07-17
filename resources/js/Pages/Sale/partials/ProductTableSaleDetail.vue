<script setup>
import { formatPrice } from "@/lib/utils";

const props = defineProps({
    sale: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <div class="max-w-full overflow-x-auto">
            <div class="min-w-[1026px]">
                <div class="grid grid-cols-11 px-5 py-3">
                    <div class="flex items-center col-span-2">
                        <p
                            class="text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            #
                        </p>
                    </div>
                    <div class="flex items-center col-span-3">
                        <p
                            class="text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Produk
                        </p>
                    </div>
                    <div class="flex items-center justify-center col-span-2">
                        <p
                            class="text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Quantity
                        </p>
                    </div>
                    <div class="flex items-center justify-center col-span-2">
                        <p
                            class="text-sm font-medium text-gray-700 dark:text-gray-400"
                        >
                            Harga
                        </p>
                    </div>
                    <div class="flex items-center col-span-2">
                        <p
                            class="w-full text-sm font-medium text-right text-gray-700 dark:text-gray-400"
                        >
                            Subtotal
                        </p>
                    </div>
                </div>

                <div
                    v-for="(saleItem, index) in props.sale.sale_items"
                    :key="saleItem.id"
                    class="grid grid-cols-11 border-t border-gray-100 px-5 py-3.5 dark:border-gray-800"
                >
                    <div class="flex items-center col-span-2">
                        <p
                            class="text-gray-500 text-theme-sm dark:text-gray-400"
                        >
                            {{ index + 1 }}
                        </p>
                    </div>
                    <div class="flex items-center col-span-3">
                        <p
                            class="text-gray-500 text-theme-sm dark:text-gray-400"
                        >
                            {{ saleItem.product.name }}
                        </p>
                    </div>
                    <div class="flex items-center justify-center col-span-2">
                        <p
                            class="text-center text-gray-500 text-theme-sm dark:text-gray-400"
                        >
                            {{ saleItem.qty }}
                        </p>
                    </div>
                    <div class="flex items-center justify-center col-span-2">
                        <p
                            class="text-center text-gray-500 text-theme-sm dark:text-gray-400"
                        >
                            {{ formatPrice(saleItem.price) }}
                        </p>
                    </div>
                    <div class="flex items-center justify-end col-span-2">
                        <p
                            class="text-right text-gray-500 text-theme-sm dark:text-gray-400"
                        >
                            {{ formatPrice(saleItem.price * saleItem.qty) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
