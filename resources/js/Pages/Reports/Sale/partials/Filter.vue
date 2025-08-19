<script setup>
import DatePicker from "@/Components/DatePicker.vue";
import { Button } from "@/Components/ui/button";
import { Label } from "@/Components/ui/label";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { cn } from "@/lib/utils";
import { watchDebounced } from "@vueuse/core";
import axios from "axios";
import { toast } from "vue-sonner";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Check, FileSpreadsheet, Printer, Search } from "lucide-vue-next";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";

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

const searchCashier = ref("");
const cashierData = ref([]);

watchDebounced(
    searchCashier,
    (newsearchCashier) => {
        axios
            .get(
                route("reports.sale.search-cashier", {
                    search: newsearchCashier,
                })
            )
            .then((response) => {
                cashierData.value = response.data.data;
            })
            .catch((error) => {
                console.log(error);
                cashierData.value = [];
            });
    },
    300
);

const getDateParam = (key) => {
    const val = new URLSearchParams(window.location.search).get(key);
    return val ? new Date(val) : null;
};

const urlParams = new URLSearchParams(window.location.search);

const paymentParam = urlParams.get("payment") || null;

const cashierParam = urlParams.get("cashier") || null;

const startDate = ref(getDateParam("start_date"));
const endDate = ref(getDateParam("end_date"));
const cashier = ref(null);
const payment = ref(paymentParam === "qris" ? "midtrans" : paymentParam);

if (cashierParam) {
    axios
        .get(route("reports.sale.search-cashier", { search: cashierParam }))
        .then((response) => {
            const foundCashier = response.data.data.find(
                (item) => String(item.value) === String(cashierParam)
            );
            if (foundCashier) {
                cashier.value = foundCashier;
            }
        })
        .catch(() => {
            cashier.value = null;
        });
}

const formatDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

const checkFilter = () => {
    if (
        startDate.value == null ||
        endDate.value == null ||
        cashier.value == null ||
        payment.value == null
    ) {
        return false;
    }
    return true;
};
const handleFilter = () => {
    if (
        startDate.value == null ||
        endDate.value == null ||
        cashier.value == null ||
        payment.value == null
    ) {
        toast.error("Silahkan lengkapi data terlebih dahulu!");
        return;
    }

    if (payment.value == "midtrans") {
        payment.value = "qris";
    }

    router.get(route("reports.sale.index"), {
        start_date: formatDate(startDate.value),
        end_date: formatDate(endDate.value),
        cashier: cashier.value.value,
        payment: payment.value,
    });
};

const handlePrintPdf = () => {
    if (!checkFilter()) {
        toast.error("Silahkan filter terlebih dahulu!");
        return;
    }

    if (payment.value == "midtrans") {
        payment.value = "qris";
    }

    window.open(
        route("reports.sale.export-to-pdf", {
            start_date: formatDate(startDate.value),
            end_date: formatDate(endDate.value),
            cashier: cashier.value.value,
            payment: payment.value,
        }),
        "_blank"
    );
};

const handleExportExcel = () => {
    if (!checkFilter()) {
        toast.error("Silahkan filter terlebih dahulu!");
        return;
    }

    if (payment.value == "midtrans") {
        payment.value = "qris";
    }

    window.open(
        route("reports.sale.export-to-excel", {
            start_date: formatDate(startDate.value),
            end_date: formatDate(endDate.value),
            cashier: cashier.value.value,
            payment: payment.value,
        }),
        "_blank"
    );
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>
                <h1 class="text-2xl font-bold">Laporan Penjualan</h1>
            </CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="space-y-1">
                <Label html-for="startDate">Tanggal Mulai</Label>
                <DatePicker
                    label="Tanggal"
                    class="w-full"
                    id="startDate"
                    v-model="startDate"
                />
            </div>
            <div class="space-y-1">
                <Label html-for="endDate">Tanggal Selesai</Label>
                <DatePicker
                    label="Tanggal"
                    class="w-full"
                    id="endDate"
                    v-model="endDate"
                />
            </div>
            <div class="space-y-1">
                <Label html-for="cashier">Kasir</Label>
                <Combobox by="label" v-model="cashier">
                    <ComboboxAnchor class="w-full">
                        <div class="relative w-full max-w-sm items-center">
                            <ComboboxInput
                                class="pl-9"
                                :display-value="(val) => val?.label ?? ''"
                                placeholder="Cari Kasir..."
                                v-model="searchCashier"
                            />
                            <span
                                class="absolute start-0 inset-y-0 flex items-center justify-center px-3"
                            >
                                <Search class="size-4 text-muted-foreground" />
                            </span>
                        </div>
                    </ComboboxAnchor>

                    <ComboboxList>
                        <ComboboxEmpty>
                            Tidak ada kasir ditemukan.
                        </ComboboxEmpty>

                        <ComboboxGroup>
                            <ComboboxItem
                                v-for="cashier in cashierData"
                                :key="cashier.value"
                                :value="cashier"
                                class="cursor-pointer"
                            >
                                {{ cashier.label }}

                                <ComboboxItemIndicator>
                                    <Check :class="cn('ml-auto h-4 w-4')" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
            </div>
            <div class="space-y-1">
                <Label html-for="payment">Metode Pembayaran</Label>
                <Select id="payment" v-model="payment">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Pilih Metode Pembayaran" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectLabel>Metode Pembayaran</SelectLabel>
                            <SelectItem value="cash" class="cursor-pointer">
                                Cash
                            </SelectItem>
                            <SelectItem value="midtrans" class="cursor-pointer">
                                Midtrans
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>

            <div class="flex space-x-2">
                <Button @click="handleFilter">
                    <Search class="h-4 w-4" />
                    <span>Lihat Laporan</span>
                </Button>
                <Button
                    @click="handlePrintPdf()"
                    :disabled="!checkFilter()"
                    class="disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <Printer class="h-4 w-4" />
                    <span>Print PDF</span>
                </Button>
                <Button
                    @click="handleExportExcel()"
                    :disabled="!checkFilter()"
                    class="disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <FileSpreadsheet class="h-4 w-4" />
                    <span>Export Excel</span>
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
