<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Filter from "./partials/Filter.vue";
import Summary from "./partials/Summary.vue";
import Chart from "./partials/Chart.vue";

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";

const props = defineProps({
    sales: {
        type: Object,
        required: true,
    },
});

console.log(props.sales);
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
            <Filter />
            <Summary />
            <Chart />
        </div>
    </AuthenticatedLayout>
</template>
