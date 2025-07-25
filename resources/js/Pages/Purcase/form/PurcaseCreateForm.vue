<script setup>
import { Button } from "@/Components/ui/button";
import { CalendarIcon, Check, Loader2, Plus, Search } from "lucide-vue-next";
import { Label } from "@/Components/ui/label";
import { toast } from "vue-sonner";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { cn } from "@/lib/utils";
import { watchDebounced } from "@vueuse/core";
import axios from "axios";
import ProductTable from "./ProductTable.vue";
import { Calendar } from "@/Components/ui/calendar";

import {
    DateFormatter,
    getLocalTimeZone,
    today,
} from "@internationalized/date";

import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";

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

import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";

const isDialogOpen = ref(false);
const searchSupplier = ref("");
const suppliers = ref([]);
const selectedSupplier = ref(null);

watchDebounced(
    searchSupplier,
    (newSearchSupplier) => {
        axios
            .get(route("purcase.search-supplier"), {
                params: {
                    search: newSearchSupplier,
                },
            })
            .then((response) => {
                suppliers.value = response.data.data;
            })
            .catch((error) => {
                suppliers.value = [];
            });
    },
    { debounce: 200 }
);

watch(selectedSupplier, (newSelectedSupplier) => {
    form.supplier_id = newSelectedSupplier.value;
});

const df = new DateFormatter("id-ID", {
    dateStyle: "long",
});

const formatDate = (date) => {
    return df.format(date.toDate(getLocalTimeZone()));
};

const date = ref(today(getLocalTimeZone()));

const form = useForm({
    supplier_id: "",
    date: formatDate(date.value),
    product_items: [],
});

const user = usePage().props.auth.user.name;

const submit = () => {
    form.post(route("purcase.store"), {
        showProgress: false,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success("Pembelian berhasil ditambahkan", {
                description: `Pembelian berhasil ditambahkan oleh ${user}`,
            });
            isDialogOpen.value = false;
        },
        onError: () => {
            toast.error("Pembelian gagal ditambahkan");
        },
    });
};
</script>

<template>
    <Dialog v-model:open="isDialogOpen">
        <DialogTrigger as-child>
            <Button variant="outline" class="dark:border-white border-zinc-600">
                <Plus />
                Tambah Pembelian Produk
            </Button>
        </DialogTrigger>
        <DialogContent class="max-w-4xl">
            <DialogHeader>
                <DialogTitle>Form tambah pembelian produk</DialogTitle>
                <DialogDescription>
                    Silahkan isi form dibawah ini untuk menambahkan pembelian
                    produk
                </DialogDescription>
            </DialogHeader>
            <form id="form" @submit.prevent="submit">
                <div class="flex items-center space-x-2">
                    <div class="grid flex-1 gap-2">
                        <Label for="supplier"> Supplier </Label>
                        <Combobox
                            by="label"
                            v-model="selectedSupplier"
                            html-id="supplier"
                        >
                            <ComboboxAnchor class="w-full">
                                <div
                                    class="relative w-full max-w-sm items-center"
                                >
                                    <ComboboxInput
                                        v-model="searchSupplier"
                                        class="pl-9"
                                        :display-value="
                                            (val) => val?.label ?? ''
                                        "
                                        placeholder="Cari Supplier..."
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

                            <ComboboxList class="w-full">
                                <ComboboxEmpty>
                                    Tidak ada supplier ditemukan.
                                </ComboboxEmpty>

                                <ComboboxGroup>
                                    <ComboboxItem
                                        v-for="supplier in suppliers"
                                        :key="supplier.value"
                                        :value="supplier"
                                        class="cursor-pointer"
                                    >
                                        {{ supplier.label }}

                                        <ComboboxItemIndicator>
                                            <Check
                                                :class="cn('ml-auto h-4 w-4')"
                                            />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                    </div>
                    <div class="grid flex-1 gap-2">
                        <Label for="date"> Tanggal </Label>
                        <Popover>
                            <PopoverTrigger as-child>
                                <Button
                                    variant="outline"
                                    :class="
                                        cn(
                                            'w-[280px] justify-start text-left font-normal',
                                            !date && 'text-muted-foreground'
                                        )
                                    "
                                >
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {{
                                        date
                                            ? df.format(
                                                  date.toDate(
                                                      getLocalTimeZone()
                                                  )
                                              )
                                            : "Pilih tanggal"
                                    }}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0">
                                <Calendar v-model="date" initial-focus />
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>
                <div class="mt-6">
                    <Label> Produk </Label>
                    <ProductTable v-model="form.product_items" />
                </div>
            </form>
            <DialogFooter class="flex justify-between">
                <DialogClose as-child>
                    <Button type="button" variant="secondary"> Batal </Button>
                </DialogClose>

                <Button
                    type="submit"
                    form="form"
                    :class="{ 'opacity-25 ': form.processing }"
                    :disabled="form.processing"
                    class="disabled:cursor-not-allowed"
                >
                    <Loader2
                        v-if="form.processing"
                        class="w-4 h-4 animate-spin"
                    />
                    {{ form.processing ? "Loading..." : "Simpan" }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
