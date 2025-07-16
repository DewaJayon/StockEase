<script setup>
import { Button } from "@/Components/ui/button";
import { Plus } from "lucide-vue-next";
import { Card, CardContent } from "@/Components/ui/card";
import { formatPrice } from "@/lib/utils";
import axios from "axios";
import { toast } from "vue-sonner";

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["cart-updated"]);
const addToCart = (producId) => {
    axios
        .post(route("pos.add-to-cart", { product_id: producId }))
        .then((response) => {
            toast.success(response.data.message);
            emit("cart-updated");
        })
        .catch((error) => {
            toast.error(error.data.message);
        });
};
</script>

<template>
    <Card
        class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer"
        :class="{ 'opacity-50': product.stock === 0 }"
        @click="addToCart(product.id)"
    >
        <CardContent class="p-0">
            <div class="h-32 flex items-center justify-center relative">
                <span
                    v-if="product.stock === 0"
                    class="absolute bg-red-500 text-white text-xs px-2 py-1 rounded"
                >
                    Habis
                </span>

                <img
                    v-if="product.image_path"
                    :src="product.image_path"
                    :alt="product.name"
                    class="h-full object-cover w-full"
                    :class="{ 'opacity-50': product.stock === 0 }"
                />

                <img
                    v-else
                    src="/img/StockEase-Logo.png"
                    :alt="product.name"
                    class="h-full object-cover w-full"
                    :class="{ 'opacity-50': product.stock === 0 }"
                />
            </div>
            <div class="p-3">
                <h3 class="font-semibold text-sm truncate">
                    {{ product.name }}
                </h3>
                <p class="font-bold text-lg">
                    {{ formatPrice(product.selling_price) }}
                </p>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-gray-500">
                        Stok: {{ product.stock }}
                    </span>
                    <Button
                        v-if="product.stock > 0"
                        class="p-1 rounded-full w-6 h-6 flex items-center justify-center"
                    >
                        <Plus class="w-4 h-4" />
                    </Button>
                    <Button
                        v-else
                        class="bg-gray-300 text-gray-500 p-1 rounded-full w-6 h-6 flex items-center justify-center"
                        disabled
                    >
                        <Plus class="w-4 h-4" />
                    </Button>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
