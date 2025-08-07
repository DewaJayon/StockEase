<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Separator } from "@/Components/ui/separator";
import { DataTable } from "@/Components/ui/data-table";
import { midtransTransactionColumns } from "./partials/midtrans-transaction-column";

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import DateFilter from "./partials/DateFilter.vue";

const props = defineProps({
    midtransTransactions: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head>
            <title>Transaksi Midtrans</title>
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
                        <BreadcrumbPage> Transaksi Midtrans </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl bg-muted/50 h-full p-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">Transaksi Midtrans</h4>
                </div>
                <Separator class="my-4" />

                <div class="mt-4">
                    <div class="flex justify-between items-center my-3">
                        <span class="text-muted-foreground text-sm">
                            Cari berdasarkan ID transaksi:
                        </span>
                        <DateFilter />
                    </div>
                    <DataTable
                        :data="midtransTransactions.data"
                        :columns="midtransTransactionColumns"
                        :route-name="'midtrans.index'"
                        :pagination="midtransTransactions"
                        :date-filter-end="true"
                        :date-filter-start="true"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
