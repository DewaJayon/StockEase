<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import { Head, Link, router } from "@inertiajs/vue3";
import { Separator } from "@/Components/ui/separator";
import { DataTable } from "@/Components/ui/data-table";
import { Badge } from "@/Components/ui/badge";
import { h, ref, watch } from "vue";
import dayjs from "dayjs";
import { Input } from "@/Components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { watchDebounced } from "@vueuse/core";

const props = defineProps({
    expiryData: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const status = ref(props.filters.status || "all");

watchDebounced(
    search,
    (val) => {
        router.get(
            route("reports.expiry.index"),
            { search: val, status: status.value },
            { preserveState: true, replace: true },
        );
    },
    { debounce: 300 },
);

watch(status, (val) => {
    router.get(
        route("reports.expiry.index"),
        { search: search.value, status: val },
        { preserveState: true, replace: true },
    );
});

const columns = [
    {
        accessorKey: "expiry_date",
        header: "Tanggal Kedaluwarsa",
        cell: ({ row }) => {
            const date = dayjs(row.getValue("expiry_date"));
            const isExpired = date.isBefore(dayjs());
            const isNear = date.isBefore(dayjs().add(30, "day")) && !isExpired;

            let variant = "outline";
            if (isExpired) variant = "destructive";
            else if (isNear) variant = "warning"; // Assuming warning variant exists or use custom class

            return h("div", { class: "flex items-center gap-2" }, [
                h("span", date.format("DD MMMM YYYY")),
                isExpired
                    ? h(Badge, { variant: "destructive" }, "Kedaluwarsa")
                    : null,
                isNear
                    ? h(
                          Badge,
                          {
                              class: "bg-yellow-500 hover:bg-yellow-600 text-white",
                          },
                          "Mendekati",
                      )
                    : null,
            ]);
        },
    },
    {
        accessorKey: "product.name",
        header: "Produk",
        cell: ({ row }) => h("div", row.original.product.name),
    },
    {
        accessorKey: "product.sku",
        header: "SKU",
        cell: ({ row }) => h("div", row.original.product.sku),
    },
    {
        accessorKey: "purchase.supplier.name",
        header: "Supplier",
        cell: ({ row }) =>
            h("div", row.original.purchase?.supplier?.name ?? "-"),
    },
    {
        accessorKey: "qty",
        header: "Stok Masuk",
    },
];
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Laporan Kedaluwarsa" />
        <template #breadcrumb>
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <Link :href="route('dashboard')">
                            <BreadcrumbLink> Dashboard </BreadcrumbLink>
                        </Link>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage> Laporan Kedaluwarsa </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>

        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl bg-muted/50 h-full p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="font-semibold text-lg">
                            Laporan Kedaluwarsa
                        </h4>
                        <p class="text-sm text-muted-foreground">
                            Monitoring tanggal kedaluwarsa produk dari
                            pembelian.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Input
                            v-model="search"
                            placeholder="Cari Produk..."
                            class="w-[250px]"
                        />
                        <Select v-model="status">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Pilih Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua</SelectItem>
                                <SelectItem value="near_expired"
                                    >Mendekati (30 Hari)</SelectItem
                                >
                                <SelectItem value="expired"
                                    >Kedaluwarsa</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <Separator class="my-4" />

                <div class="mt-4">
                    <DataTable
                        :data="expiryData.data"
                        :columns="columns"
                        :route-name="'reports.expiry.index'"
                        :pagination="expiryData"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
