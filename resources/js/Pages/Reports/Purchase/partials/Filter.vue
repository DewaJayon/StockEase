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
import { Checkbox } from "@/Components/ui/checkbox";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Check, FileSpreadsheet, Printer, Search } from "lucide-vue-next";

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

const searchSupplier = ref("");
const searchSupplierData = ref([]);

watchDebounced(
    searchSupplier,
    (newSearchSupplier) => {
        axios
            .get(
                route("reports.purchase.search-supplier", {
                    search: newSearchSupplier,
                })
            )
            .then((response) => {
                searchSupplierData.value = response.data.data;
            })
            .catch((error) => {
                searchSupplierData.value = [];
            });
    },
    300
);

const searchUser = ref("");
const searchUserData = ref([]);

watchDebounced(
    searchUser,
    (newSearchUser) => {
        axios
            .get(
                route("reports.purchase.search-user", {
                    search: newSearchUser,
                })
            )
            .then((response) => {
                searchUserData.value = response.data.data;
            })
            .catch((error) => {
                searchUserData.value = [];
            });
    },
    300
);

const urlParams = new URLSearchParams(window.location.search);

const getDateParam = (key) => {
    const val = new URLSearchParams(window.location.search).get(key);
    return val ? new Date(val) : null;
};

const allUserParam = urlParams.get("user") === "semua-user" ? true : false;
const allSupplierParam =
    urlParams.get("supplier") === "semua-supplier" ? true : false;

const startDate = ref(getDateParam("start_date"));
const endDate = ref(getDateParam("end_date"));
const supplier = ref(urlParams.get("supplier") || null);
const user = ref(urlParams.get("user") || null);
const allUser = ref(allUserParam);
const allSupplier = ref(allSupplierParam);

const formatDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

const checkFilter = () => {
    if (!startDate.value || !endDate.value) {
        toast.error("Tanggal mulai dan tanggal selesai wajib diisi!");
        return false;
    }

    if (!allSupplier.value && !supplier.value) {
        toast.error("Silahkan pilih supplier atau centang Semua Supplier!");
        return false;
    }

    if (!allUser.value && !user.value) {
        toast.error("Silahkan pilih user atau centang Semua User!");
        return false;
    }

    return true;
};

const handleFilter = () => {
    if (!checkFilter()) return;

    let supplierParam = null;
    let userParam = null;

    if (allSupplier.value) {
        supplierParam = "semua-supplier";
    } else if (supplier.value) {
        supplierParam = supplier.value.value;
    }

    if (allUser.value) {
        userParam = "semua-user";
    } else if (user.value) {
        userParam = user.value.value;
    }

    router.get(route("reports.purchase.index"), {
        start_date: formatDate(startDate.value),
        end_date: formatDate(endDate.value),
        supplier: supplierParam,
        user: userParam,
    });
};

const handleExportPdf = () => {
    alert("print pdf");
};

const handleExportExcel = () => {
    alert("export excel");
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>
                <h1 class="text-2xl font-bold">Laporan Pembelian</h1>
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
                <Label html-for="supplier">Supplier</Label>
                <Combobox by="label" v-model="supplier">
                    <ComboboxAnchor class="w-full">
                        <div class="relative w-full max-w-sm items-center">
                            <ComboboxInput
                                class="pl-9"
                                :display-value="(val) => val?.label ?? ''"
                                placeholder="Cari Supplier..."
                                v-model="searchSupplier"
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
                            Tidak ada supplier ditemukan.
                        </ComboboxEmpty>

                        <ComboboxGroup>
                            <ComboboxItem
                                v-for="supplier in searchSupplierData"
                                :key="supplier.value"
                                :value="supplier"
                                class="cursor-pointer"
                            >
                                {{ supplier.label }}

                                <ComboboxItemIndicator>
                                    <Check :class="cn('ml-auto h-4 w-4')" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <div class="items-top flex gap-x-2 pt-2">
                    <Checkbox id="all-supplier" v-model="allSupplier" />
                    <div class="grid gap-1.5 leading-none">
                        <label
                            for="all-supplier"
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Semua Supplier
                        </label>
                    </div>
                </div>
            </div>
            <div class="space-y-1">
                <Label html-for="user">User</Label>

                <Combobox by="label" v-model="user" html-id="user">
                    <ComboboxAnchor class="w-full">
                        <div class="relative w-full max-w-sm items-center">
                            <ComboboxInput
                                class="pl-9"
                                :display-value="(val) => val?.label ?? ''"
                                placeholder="Cari Supplier..."
                                v-model="searchUser"
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
                            Tidak ada user ditemukan.
                        </ComboboxEmpty>

                        <ComboboxGroup>
                            <ComboboxItem
                                v-for="user in searchUserData"
                                :key="user.value"
                                :value="user"
                                class="cursor-pointer"
                            >
                                {{ user.label }}

                                <ComboboxItemIndicator>
                                    <Check :class="cn('ml-auto h-4 w-4')" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>

                <div class="items-top flex gap-x-2 pt-2">
                    <Checkbox id="all-user" v-model="allUser" />
                    <div class="grid gap-1.5 leading-none">
                        <label
                            for="all-user"
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Semua User
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex space-x-2">
                <Button @click="handleFilter">
                    <Search class="h-4 w-4" />
                    <span>Lihat Laporan</span>
                </Button>
                <Button
                    @click="handleExportPdf()"
                    class="disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <Printer class="h-4 w-4" />
                    <span>Export PDF</span>
                </Button>
                <Button
                    @click="handleExportExcel()"
                    class="disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <FileSpreadsheet class="h-4 w-4" />
                    <span>Export Excel</span>
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
