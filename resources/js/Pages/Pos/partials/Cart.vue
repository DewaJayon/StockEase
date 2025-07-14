<script setup>
import { Button } from "@/Components/ui/button";
import { Loader2, Printer, Trash2 } from "lucide-vue-next";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { Label } from "@/Components/ui/label";
import { formatPrice } from "@/lib/utils";
import { Input } from "@/Components/ui/input";
import { ref, watch } from "vue";
import axios from "axios";

import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from "@/Components/ui/number-field";

import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/Components/ui/tooltip";
import { toast } from "vue-sonner";

const props = defineProps({
    cart: {
        type: Object || null,
        required: true,
    },
});

const qtyRefs = ref({});
const totalCart = ref(props.cart.total);
const cartItems = ref(props.cart.sale_items);
const cartData = ref(props.cart);
const loadingItemId = ref(null);

watch(
    () => props.cart,
    (newCart) => {
        cartData.value = newCart;
        if (newCart?.sale_items) {
            cartItems.value = newCart.sale_items;
            totalCart.value = newCart.total;

            qtyRefs.value = {};
            newCart.sale_items.forEach((item) => {
                qtyRefs.value[item.product_id] = item.qty;
            });
        }
    },
    { immediate: true }
);

props.cart.sale_items.forEach((item) => {
    qtyRefs.value[item.product_id] = item.qty;
});

const changeQty = (id, qty) => {
    axios
        .patch(route("pos.change-qty", { product_id: id, qty: qty }))
        .then((response) => {
            if (response.data.cart) {
                cartItems.value = response.data.cart.sale_items;
            }
            totalCart.value = response.data.total;
        })
        .catch((error) => {
            console.log(error);
        });
};

const removeItemFromCart = (productId) => {
    loadingItemId.value = productId;
    axios
        .delete(route("pos.remove-from-cart", { product_id: productId }))
        .then((response) => {
            toast.success(response.data.message);
            totalCart.value = response.data.total;
            cartItems.value = response.data.cart.sale_items;
        })
        .catch((error) => {
            console.log(error);
        })
        .finally(() => {
            loadingItemId.value = null;
        });
};
</script>

<template>
    <div class="lg:w-1/3 rounded-lg shadow p-4 border dark:border-white/30">
        <h2 class="text-xl font-bold mb-4">Keranjang Belanja</h2>

        <div class="space-y-3 mb-4" style="max-height: 50vh; overflow-y: auto">
            <div
                v-if="cartItems.length > 0"
                v-for="cartItem in cartItems"
                class="flex justify-between items-center border-b pb-2"
            >
                <div>
                    <h4 class="font-medium">{{ cartItem.product.name }}</h4>
                    <p class="text-gray-500 text-sm">
                        {{ formatPrice(cartItem.price) }} x
                        {{ qtyRefs[cartItem.product_id] }}
                    </p>
                </div>
                <div class="flex items-center">
                    <NumberField
                        :model-value="qtyRefs[cartItem.product_id]"
                        :min="0"
                    >
                        <NumberFieldContent>
                            <NumberFieldDecrement
                                @click="
                                    qtyRefs[cartItem.product_id]--;
                                    changeQty(
                                        cartItem.product_id,
                                        qtyRefs[cartItem.product_id]
                                    );
                                "
                            />
                            <NumberFieldInput
                                class="w-24 border rounded"
                                readonly
                            />
                            <NumberFieldIncrement
                                @click="
                                    qtyRefs[cartItem.product_id]++;
                                    changeQty(
                                        cartItem.product_id,
                                        qtyRefs[cartItem.product_id]
                                    );
                                "
                            />
                        </NumberFieldContent>
                    </NumberField>
                    <Button
                        variant="destructive"
                        size="icon"
                        class="ml-2 disabled:cursor-not-allowed"
                        :disabled="loadingItemId === cartItem.product_id"
                        @click="removeItemFromCart(cartItem.product_id)"
                    >
                        <Loader2
                            v-if="loadingItemId === cartItem.product_id"
                            class="w-4 h-4 animate-spin"
                        />
                        <Trash2 v-else class="w-4 h-4" />
                    </Button>
                </div>
            </div>

            <div v-else>
                <p class="text-center">Keranjang belanja kosong</p>
            </div>
        </div>

        <div class="space-y-2 border-t pt-3">
            <div class="flex justify-between text-lg font-bold mt-2">
                <span>TOTAL:</span>
                <span>{{ formatPrice(totalCart) }}</span>
            </div>

            <div>
                <span>Metode Pembayaran:</span>
                <div class="flex items-center space-x-2 mt-2">
                    <RadioGroup v-model="cart.payment_method">
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="cash" value="cash" />
                            <Label for="cash">Cash</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="qris" value="qris" />
                            <Label for="qris">Qris</Label>
                        </div>
                    </RadioGroup>
                </div>
            </div>

            <div v-if="cart.payment_method === 'cash'" class="flex mt-2">
                <Input
                    type="number"
                    class="w-full mt-2 [&::-webkit-inner-spin-button]:appearance-none"
                    placeholder="Uang Pembayaran "
                />
            </div>

            <div class="flex justify-between text-lg font-bold mt-2">
                <span class="text-muted-foreground">Kembalian:</span>
                <span>{{ formatPrice(cart.change) }}</span>
            </div>

            <Button class="w-full"> PROSES PEMBAYARAN </Button>

            <div class="grid grid-cols-3 gap-2 mt-3">
                <TooltipProvider :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button size="icon" class="w-full">
                                <Printer />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="bottom">
                            <p>Cetak</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>

                <TooltipProvider :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button size="icon" class="w-full">
                                <Trash2 />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="bottom">
                            <p>Hapus Semua</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>
        </div>
    </div>
</template>
