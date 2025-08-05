<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Separator } from "@/Components/ui/separator";
import { DataTable } from "@/Components/ui/data-table";
import { Info } from "lucide-vue-next";
import { purcaseColumns } from "./partials/purcase-columns";
import PurcaseCreateForm from "./form/PurcaseCreateForm.vue";
import DateFilter from "./partials/DateFilter.vue";

import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";

const props = defineProps({
    purcases: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head>
            <title>Pembelian</title>
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
                        <BreadcrumbPage> Data Pembelian </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl bg-muted/50 h-full p-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">Data Pembelian</h4>
                    <PurcaseCreateForm />
                </div>
                <Separator class="my-4" />

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
                                    Jangan tambahkan data pembelian jika produk
                                    belum ada!
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Silahkan tambahkan produk terlebih dahulu!
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="mt-4">
                    <div class="mb-4">
                        <DateFilter />
                    </div>
                    <DataTable
                        :data="purcases.data"
                        :columns="purcaseColumns"
                        :route-name="'purcase.index'"
                        :pagination="purcases"
                        :date-filter-end="true"
                        :date-filter-start="true"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
