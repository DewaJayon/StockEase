<script setup>
import { Input } from "@/Components/ui/input";
import { cn, formatPrice } from "@/lib/utils";
import { Button } from "@/Components/ui/button";
import { Check, Plus, Search, Trash } from "lucide-vue-next";
import { ref, watch } from "vue";
import { watchDebounced } from "@vueuse/core";
import axios from "axios";

import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
} from "@/Components/ui/combobox";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    modelValue: Array,
    form: Object,
});

const searchProduct = ref({});
const selectedProductId = ref("");
const productOptions = ref({});

watchDebounced(
    searchProduct,
    (newSearchProduct) => {
        for (const index in newSearchProduct) {
            const search = newSearchProduct[index];
            axios
                .get(route("purcase.search-product", { search }))
                .then((response) => {
                    productOptions.value[index] = response.data.data;
                });
        }
    },
    { debounce: 300, deep: true }
);

watch(selectedProductId, (newValue) => {
    for (const index in productOptions.value) {
        const options = productOptions.value[index];
        console.log(options);

        const product = options.find((product) => product.id === newValue);
        if (product) {
            props.modelValue[index].product_id = product.id;
            props.modelValue[index].price = product.purchase_price;
        }
    }
});

const emit = defineEmits(["update:modelValue"]);

function addItem() {
    emit("update:modelValue", [
        ...props.modelValue,
        {
            product_id: "",
            qty: 1,
            price: 0,
        },
    ]);
}
function remove(index) {
    const updated = [...props.modelValue];
    updated.splice(index, 1);
    emit("update:modelValue", updated);
}
</script>

<template>
    <Table>
        <TableCaption> Produk </TableCaption>
        <TableHeader>
            <TableRow>
                <TableHead> Produk </TableHead>
                <TableHead>QTY</TableHead>
                <TableHead>Satuan Unit </TableHead>
                <TableHead>Harga Beli </TableHead>
                <TableHead>Harga Jual</TableHead>
                <TableHead> Subtotal </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="(item, index) in modelValue" :key="index">
                <TableCell>
                    <Combobox
                        :model-value="item.product_id"
                        @update:modelValue="
                            (val) => {
                                item.product_id = val.id;
                                item.purchase_price = val.purchase_price;
                                item.selling_price = val.selling_price;
                                item.unit = val.unit;
                            }
                        "
                    >
                        <ComboboxAnchor>
                            <div class="relative w-full max-w-sm items-center">
                                <ComboboxInput
                                    v-model="searchProduct[index]"
                                    class="pl-9"
                                    :display-value="
                                        () => {
                                            const found = (
                                                productOptions[index] || []
                                            ).find(
                                                (p) => p.id === item.product_id
                                            );
                                            return found?.label ?? '';
                                        }
                                    "
                                    placeholder="Cari Produk..."
                                />
                                <span
                                    class="absolute start-0 inset-y-0 flex items-center justify-center px-3"
                                >
                                    <Search
                                        class="size-4 text-muted-foreground"
                                    />
                                </span>
                            </div>
                        </ComboboxAnchor>

                        <ComboboxList>
                            <ComboboxEmpty>
                                Tidak ada produk ditemukan.
                            </ComboboxEmpty>
                            <ComboboxGroup>
                                <ComboboxItem
                                    class="cursor-pointer"
                                    v-for="product in productOptions[index] ||
                                    []"
                                    :key="product.id"
                                    :value="product"
                                >
                                    {{ product.label }}

                                    <ComboboxItemIndicator>
                                        <Check :class="cn('ml-auto h-4 w-4')" />
                                    </ComboboxItemIndicator>
                                </ComboboxItem>
                            </ComboboxGroup>
                        </ComboboxList>
                    </Combobox>
                    <p
                        v-if="
                            form?.errors?.[`product_items.${index}.product_id`]
                        "
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ form.errors[`product_items.${index}.product_id`] }}
                    </p>
                </TableCell>
                <TableCell>
                    <Input
                        type="number"
                        placeholder="Qty"
                        v-model.number="item.qty"
                        min="1"
                        class="[&::-webkit-inner-spin-button]:appearance-none"
                    />
                    <p
                        v-if="form?.errors?.[`product_items.${index}.qty`]"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ form.errors[`product_items.${index}.qty`] }}
                    </p>
                </TableCell>

                <TableCell> {{ item.unit ?? "-" }} </TableCell>

                <TableCell>
                    <Input
                        type="number"
                        placeholder="Harga Beli"
                        v-model.number="item.price"
                        class="[&::-webkit-inner-spin-button]:appearance-none"
                    />
                    <p
                        v-if="form?.errors?.[`product_items.${index}.price`]"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ form.errors[`product_items.${index}.price`] }}
                    </p>
                </TableCell>
                <TableCell>
                    <Input
                        type="number"
                        placeholder="Harga Jual"
                        v-model.number="item.selling_price"
                        class="[&::-webkit-inner-spin-button]:appearance-none"
                    />
                </TableCell>
                <TableCell>
                    {{ formatPrice(item.qty * item.price) }}
                </TableCell>
                <TableCell>
                    <Button
                        size="icon"
                        @click="remove(index)"
                        variant="ghost"
                        class="dark:hover:bg-red-900"
                    >
                        <Trash class="w-4 h-4" />
                    </Button>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>

    <Button @click.prevent="addItem">
        <Plus />
        Tambah Produk
    </Button>
</template>
