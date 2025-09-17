<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Info } from "lucide-vue-next";

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import Filter from "./partials/Filter.vue";
import { DataTable } from "@/Components/ui/data-table";
import { filteredStockColumns } from "./partials/filtered-stock-column";

const props = defineProps({
    filteredStocks: {
        type: Object,
        required: true,
    },
});

console.log(props.filteredStocks);
</script>

<template>
    <AuthenticatedLayout>
        <Head>
            <title>Laporan Stock</title>
        </Head>
        <template #breadcrumb>
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <Link :href="route('home')">
                            <BreadcrumbLink> Dashboard </BreadcrumbLink>
                        </Link>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage> Data Laporan Stock </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>
        <div class="flex flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Penting!</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <div
                        class="flex items-center space-x-4 rounded-md border p-4"
                    >
                        <Info />
                        <div class="flex-1 space-y-1">
                            <p class="text-sm font-medium leading-none">
                                Silahkan isi form filter dibawah ini untuk
                                melihat laporan
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Laporan stock akan muncul ketika filter sudah
                                diisi
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Filter />
            <template v-if="filteredStocks.data?.length > 0">
                <DataTable
                    :data="filteredStocks.data"
                    :columns="filteredStockColumns"
                    :route-name="'reports.stock.index'"
                    :pagination="filteredStocks"
                />
            </template>
            <template v-else>
                <div
                    class="flex flex-col items-center justify-center mt-4 text-center"
                >
                    <h1 class="text-2xl font-semibold">Tidak ada data</h1>
                    <p class="text-sm text-muted-foreground">
                        Tidak ada data laporan stock, silahkan lakukan filter
                        lebih lanjut.
                    </p>
                </div>
            </template>
        </div>
    </AuthenticatedLayout>
</template>
