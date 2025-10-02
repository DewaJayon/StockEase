<script setup>
import DatePicker from "@/Components/DatePicker.vue";
import { Button } from "@/Components/ui/button";
import { Label } from "@/Components/ui/label";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { cn } from "@/lib/utils";
import { watchDebounced } from "@vueuse/core";
import axios from "axios";
import { toast } from "vue-sonner";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import {
    Check,
    FileSpreadsheet,
    Loader2,
    Printer,
    Search,
} from "lucide-vue-next";

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
import { Checkbox } from "@/Components/ui/checkbox";

const formatDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

const getDateParam = (key) => {
    const val = new URLSearchParams(window.location.search).get(key);
    return val ? new Date(val) : null;
};

const urlParams = new URLSearchParams(window.location.search);

const allCategoryParam =
    urlParams.get("category") === "semua-kategori" ? true : false;
const allSupplierParam =
    urlParams.get("supplier") === "semua-supplier" ? true : false;

const selectedCategoryParam = urlParams.get("category") || null;
const selectedSupplierParam = urlParams.get("supplier") || null;

const startDate = ref(getDateParam("start_date") || null);
const endDate = ref(getDateParam("end_date") || null);
const selectedCategory = ref(selectedCategoryParam);
const selectedSupplier = ref(selectedSupplierParam);
const allCategory = ref(allCategoryParam);
const allSupplier = ref(allSupplierParam);

if (selectedCategoryParam) {
    axios
        .get(
            route("reports.stock.searchCategory", {
                search: selectedCategoryParam,
            })
        )
        .then((response) => {
            const categoriesData = response.data.data.map((item) => ({
                label: item.name,
                value: item.id,
            }));

            const foundCategory = categoriesData.find(
                (item) => String(item.value) === String(selectedCategoryParam)
            );

            if (foundCategory) {
                selectedCategory.value = foundCategory;
            }
        })
        .catch(() => {
            selectedCategory.value = null;
        });
}

if (selectedSupplierParam) {
    axios
        .get(
            route("reports.stock.searchSupplier", {
                search: selectedSupplierParam,
            })
        )
        .then((response) => {
            const suppliersData = response.data.data.map((item) => ({
                label: item.name,
                value: item.id,
            }));

            const foundSupplier = suppliersData.find(
                (item) => String(item.value) === String(selectedSupplierParam)
            );

            if (foundSupplier) {
                selectedSupplier.value = foundSupplier;
            }
        })
        .catch(() => {
            selectedSupplier.value = null;
        });
}

watch(selectedCategory, (newVal) => {
    if (newVal) {
        allCategory.value = false;
    }
});
watch(allCategory, (newVal) => {
    if (newVal) {
        selectedCategory.value = null;
    }
});
watch(selectedSupplier, (newVal) => {
    if (newVal) {
        allSupplier.value = false;
    }
});
watch(allSupplier, (newVal) => {
    if (newVal) {
        selectedSupplier.value = null;
    }
});

const categories = ref([]);
const searchCategory = ref("");

watchDebounced(
    searchCategory,
    (newSearchCategory) => {
        axios
            .get(
                route("reports.stock.searchCategory", {
                    search: newSearchCategory,
                })
            )
            .then((response) => {
                categories.value = response.data.data.map((item) => {
                    return {
                        label: item.name,
                        value: item.id,
                    };
                });
            })
            .catch((error) => {
                categories.value = [];
            });
    },
    { debounce: 500 }
);

const searchSupplier = ref("");
const suppliers = ref([]);

watchDebounced(
    searchSupplier,
    (newSearchSupplier) => {
        axios
            .get(
                route("reports.stock.searchSupplier", {
                    search: newSearchSupplier,
                })
            )
            .then((response) => {
                suppliers.value = response.data.data.map((item) => {
                    return {
                        label: item.name,
                        value: item.id,
                    };
                });
            })
            .catch((error) => {
                suppliers.value = [];
            });
    },
    { debounce: 500 }
);

const checkFilter = () => {
    if (!startDate.value || !endDate.value) {
        toast.error("Tanggal mulai dan tanggal selesai wajib diisi!");
        return false;
    }

    if (!allSupplier.value && !selectedSupplier.value) {
        toast.error("Silahkan pilih supplier atau centang Semua Supplier!");
        return false;
    }

    if (!allCategory.value && !selectedCategory.value) {
        toast.error("Silahkan pilih kategori atau centang Semua Kategori!");
        return false;
    }

    return true;
};

const isFilterLoading = ref(false);

const handleFilter = () => {
    if (!checkFilter()) return;

    isFilterLoading.value = true;

    let supplierParam = null;
    let categoryParam = null;

    allSupplier.value == true
        ? (supplierParam = "semua-supplier")
        : (supplierParam = selectedSupplier.value.value);
    allCategory.value == true
        ? (categoryParam = "semua-kategori")
        : (categoryParam = selectedCategory.value.value);

    router
        .get(route("reports.stock.index"), {
            start_date: formatDate(startDate.value),
            end_date: formatDate(endDate.value),
            supplier: supplierParam,
            category: categoryParam,
        })
        .then(() => {
            isFilterLoading.value = false;
        });
};

const handleExportPdf = () => {
    if (!checkFilter()) return;

    let supplierParam = null;
    let categoryParam = null;

    allSupplier.value == true
        ? (supplierParam = "semua-supplier")
        : (supplierParam = selectedSupplier.value.value);
    allCategory.value == true
        ? (categoryParam = "semua-kategori")
        : (categoryParam = selectedCategory.value.value);

    window.open(
        route("reports.stock.export-to-pdf", {
            start_date: formatDate(startDate.value),
            end_date: formatDate(endDate.value),
            supplier: supplierParam,
            category: categoryParam,
        }),
        "_blank"
    );
};

const handleExportExcel = () => {
    if (!checkFilter()) return;

    let supplierParam = null;
    let categoryParam = null;

    allSupplier.value == true
        ? (supplierParam = "semua-supplier")
        : (supplierParam = selectedSupplier.value.value);
    allCategory.value == true
        ? (categoryParam = "semua-kategori")
        : (categoryParam = selectedCategory.value.value);

    window.open(
        route("reports.stock.export-to-excel", {
            start_date: formatDate(startDate.value),
            end_date: formatDate(endDate.value),
            supplier: supplierParam,
            category: categoryParam,
        }),
        "_blank"
    );
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>
                <h1 class="text-2xl font-bold">Laporan Stock</h1>
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
                <Label html-for="category">Kategori</Label>
                <Combobox
                    by="label"
                    v-model="selectedCategory"
                    html-id="category"
                >
                    <ComboboxAnchor class="w-full">
                        <div class="relative w-full max-w-sm items-center">
                            <ComboboxInput
                                class="pl-9"
                                :display-value="(val) => val?.label ?? ''"
                                placeholder="Cari Kategori..."
                                v-model="searchCategory"
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
                            Tidak ada Kategori ditemukan.
                        </ComboboxEmpty>

                        <ComboboxGroup>
                            <ComboboxItem
                                v-for="category in categories"
                                :key="category.value"
                                :value="category"
                                class="cursor-pointer"
                            >
                                {{ category.label }}

                                <ComboboxItemIndicator>
                                    <Check :class="cn('ml-auto h-4 w-4')" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <div class="items-top flex gap-x-2 pt-2">
                    <Checkbox id="all-category" v-model="allCategory" />
                    <div class="grid gap-1.5 leading-none">
                        <label
                            for="all-category"
                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Semua Kategori
                        </label>
                    </div>
                </div>
            </div>
            <div class="space-y-1">
                <Label html-for="supplier">Supplier</Label>
                <Combobox
                    by="label"
                    v-model="selectedSupplier"
                    html-id="supplier"
                >
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
                            Tidak ada Supplier ditemukan.
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

            <div class="flex space-x-2">
                <Button
                    @click="handleFilter"
                    :disabled="isFilterLoading || !checkFilter()"
                    class="disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <Loader2
                        v-if="isFilterLoading"
                        class="w-4 h-4 animate-spin"
                    />

                    <Search class="h-4 w-4" v-else />
                    <span>Lihat Laporan</span>
                </Button>
                <Button
                    @click="handleExportPdf()"
                    :disabled="!checkFilter()"
                    class="disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <Printer class="h-4 w-4" />
                    <span>Export PDF</span>
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
