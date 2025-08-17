<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Filter from "./partials/Filter.vue";
import Summary from "./partials/Summary.vue";
import Chart from "./partials/Chart.vue";
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

const props = defineProps({
    sales: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head>
            <title>Laporan Penjualan</title>
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
                        <BreadcrumbPage>
                            Data Laporan Penjualan
                        </BreadcrumbPage>
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
                                Laporan penjualan akan muncul ketika filter
                                sudah diisi
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Filter />
            <Summary :summary="props.sales" />
            <Chart :chart="props.sales" />
        </div>
    </AuthenticatedLayout>
</template>
