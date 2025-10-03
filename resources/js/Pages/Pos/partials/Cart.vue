<script setup>
import { Button } from "@/Components/ui/button";
import { Loader2, Trash2 } from "lucide-vue-next";
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

if (props.cart?.sale_items?.length) {
    props.cart.sale_items.forEach((item) => {
        qtyRefs.value[item.product_id] = item.qty;
    });
}

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

const isClearCartLoading = ref(false);
const clearCart = () => {
    isClearCartLoading.value = true;

    if (cartItems.value.length > 0) {
        axios
            .delete(route("pos.empty-cart"))
            .then((response) => {
                toast.success(response.data.message);
                totalCart.value = response.data.total;
                cartItems.value = response.data.cart.sale_items;
            })
            .catch((error) => {
                toast.error(error.data.message);
                console.log(error);
            })
            .finally(() => {
                isClearCartLoading.value = false;
            });
    } else {
        toast.error("Keranjang belanja masih kosong");
        isClearCartLoading.value = false;
    }
};

const cashPayment = ref(0);
const change = ref(0);
const paymentMethod = ref("cash");

watch(cashPayment, (newValue) => {
    change.value = newValue - totalCart.value;
});

watch(paymentMethod, (newValue) => {
    paymentMethod.value = newValue;
});

const customerName = ref(null);

const isCheckoutLoading = ref(false);

const emit = defineEmits(["checkout-success"]);

const checkout = () => {
    isCheckoutLoading.value = true;

    if (paymentMethod.value === "cash") {
        if (cashPayment.value < totalCart.value) {
            toast.error("Uang pembayaran kurang");
            isCheckoutLoading.value = false;
            return;
        }

        axios;
        axios
            .put(
                route("pos.checkout"),
                {
                    payment_method: paymentMethod.value,
                    customer_name: customerName.value,
                    paid: cashPayment.value,
                    change: change.value,
                },
                {
                    headers: { Accept: "application/json" },
                }
            )
            .then((response) => {
                toast.success(response.data.message);
                totalCart.value = response.data.total;
                cartItems.value = response.data.cart.sale_items;
                cashPayment.value = 0;
                change.value = 0;
                customerName.value = null;

                emit("checkout-success");
            })
            .catch((error) => {
                toast.error("Gagal checkout");
                console.log(error);
            })
            .finally(() => {
                isCheckoutLoading.value = false;
            });
    } else if (paymentMethod.value === "qris") {
        axios
            .post(
                route("pos.qris-token", {
                    amount: totalCart.value,
                    customer_name: customerName.value,
                })
            )
            .then((response) => {
                const snapToken = response.data.snap_token;

                window.snap.pay(snapToken, {
                    onSuccess: function (result) {
                        axios;
                        axios
                            .put(
                                route("pos.checkout"),
                                {
                                    payment_method: paymentMethod.value,
                                    customer_name: customerName.value,
                                    paid: cashPayment.value,
                                    change: change.value,
                                    order_id: result.order_id,
                                },
                                {
                                    headers: { Accept: "application/json" },
                                }
                            )
                            .then((response) => {
                                toast.success(response.data.message);
                                totalCart.value = response.data.total;
                                cartItems.value = response.data.cart.sale_items;
                                cashPayment.value = 0;
                                change.value = 0;
                                customerName.value = null;

                                emit("checkout-success");
                            })
                            .catch((error) => {
                                toast.error("Gagal checkout");
                                console.log(error);
                            })
                            .finally(() => {
                                isCheckoutLoading.value = false;
                            });
                        console.log(result);
                    },
                    onPending: function (result) {
                        toast.info("Menunggu pembayaran QRIS");
                        console.log(result);
                    },
                    onError: function (result) {
                        toast.error("Pembayaran gagal");
                        console.error(result);
                    },
                });
            })
            .catch((error) => {
                toast.error("Gagal mendapatkan token pembayaran QRIS");
                console.log(error);
                isCheckoutLoading.value = false;
            });
    }
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
                    <RadioGroup v-model="paymentMethod">
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

            <div v-if="paymentMethod === 'cash'" class="flex mt-2">
                <Input
                    v-model="cashPayment"
                    name="cashPayment"
                    id="cashPayment"
                    type="number"
                    class="w-full mt-2 [&::-webkit-inner-spin-button]:appearance-none"
                    placeholder="Uang Pembayaran "
                    autocomplete="off"
                />
            </div>

            <div class="flex mt-2">
                <Input
                    v-model="customerName"
                    name="customer_name"
                    id="customer_name"
                    type="text"
                    class="w-full mt-2 [&::-webkit-inner-spin-button]:appearance-none"
                    placeholder="Nama Pelanggan (Opsional)"
                    autocomplete="off"
                />
            </div>

            <div class="flex justify-between text-lg font-bold mt-2">
                <span class="text-muted-foreground">Kembalian:</span>
                <span>{{ formatPrice(change) }}</span>
            </div>

            <Button
                class="w-full disabled:cursor-not-allowed"
                :disabled="
                    !cart.payment_method ||
                    cart.sale_items.length === 0 ||
                    isCheckoutLoading
                "
                @click="checkout"
            >
                <Loader2
                    v-if="isCheckoutLoading"
                    class="w-4 h-4 animate-spin"
                />
                {{ isCheckoutLoading ? "Loading..." : "Proses Pembayaran" }}
            </Button>

            <div class="grid grid-cols-3 gap-2 mt-3">
                <TooltipProvider :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button
                                size="icon"
                                class="w-full"
                                @click="clearCart"
                                :disabled="
                                    !cart.payment_method ||
                                    cart.sale_items.length === 0 ||
                                    isClearCartLoading
                                "
                            >
                                <Loader2
                                    v-if="isClearCartLoading"
                                    class="w-4 h-4 animate-spin"
                                />
                                <Trash2 v-else />
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
