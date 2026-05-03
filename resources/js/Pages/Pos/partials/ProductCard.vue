<script setup>
import { Button } from '@/Components/ui/button';
import { Plus } from 'lucide-vue-next';
import { Card, CardContent } from '@/Components/ui/card';
import { formatPrice } from '@/lib/utils';
import axios from 'axios';
import { toast } from 'vue-sonner';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    activePromotions: {
        type: Array,
        default: () => [],
    },
});

import { computed } from 'vue';
import { Badge } from '@/Components/ui/badge';

const applicablePromo = computed(() => {
    if (!props.activePromotions || props.activePromotions.length === 0)
        return null;

    const productPromo = props.activePromotions.find(
        (p) => p.product_id === props.product.id,
    );
    if (productPromo) return productPromo;

    const categoryPromo = props.activePromotions.find(
        (p) => p.category_id === props.product.category_id,
    );
    if (categoryPromo) return categoryPromo;

    const generalPromo = props.activePromotions.find(
        (p) => !p.product_id && !p.category_id,
    );
    if (generalPromo) return generalPromo;

    return null;
});

const promoLabel = computed(() => {
    const promo = applicablePromo.value;
    if (!promo) return null;

    if (promo.type === 'percentage')
        return `Diskon ${Number(promo.discount_value)}%`;
    if (promo.type === 'nominal')
        return `Diskon ${formatPrice(Number(promo.discount_value))}`;
    if (promo.type === 'bogo')
        return `Beli ${promo.buy_qty} Gratis ${promo.get_qty}`;
    return 'Promo';
});

const emit = defineEmits(['cart-updated']);
const addToCart = (producId) => {
    axios
        .post(route('pos.add-to-cart', { product_id: producId }))
        .then((response) => {
            toast.success(response.data.message);
            emit('cart-updated');
        })
        .catch((error) => {
            toast.error(error.data.message);
        });
};
</script>

<template>
    <Card
        class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer p-0"
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

                <Badge
                    v-if="promoLabel"
                    class="absolute top-2 right-2 shadow-sm whitespace-nowrap bg-red-600 hover:bg-red-700 text-white border-0 z-10 px-2 py-0.5 text-[10px]"
                >
                    {{ promoLabel }}
                </Badge>
            </div>
            <div class="p-3">
                <h3 class="font-semibold text-sm truncate">
                    {{ product.name }}
                </h3>
                <p class="font-bold text-lg">
                    {{ formatPrice(Number(product.selling_price)) }}
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
