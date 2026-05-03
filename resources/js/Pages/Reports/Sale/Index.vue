<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { BarChart3 } from 'lucide-vue-next';

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';

import Filter from './partials/Filter.vue';
import Summary from './partials/Summary.vue';
import Chart from './partials/Chart.vue';

const props = defineProps({
    sales: {
        type: [Object, Array],
        required: true,
    },
});

const hasData = computed(
    () => props.sales && props.sales.sumTotalSale !== undefined,
);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Laporan Penjualan" />

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
                        <BreadcrumbPage> Laporan Penjualan </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>

        <div class="flex flex-col gap-6 p-6 pb-12">
            <div
                class="flex flex-col md:flex-row md:items-center justify-between gap-4"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Laporan Penjualan
                    </h1>
                    <p class="text-muted-foreground">
                        Monitoring performa penjualan bisnis Anda.
                    </p>
                </div>
            </div>

            <Filter />

            <template v-if="hasData">
                <Summary :summary="props.sales" />
                <Chart :chart="props.sales" />
            </template>
            <template v-else>
                <div
                    class="flex flex-col items-center justify-center p-12 mt-6 border-2 border-dashed rounded-xl bg-card text-center"
                >
                    <div class="rounded-full bg-muted p-4 mb-4">
                        <BarChart3 class="w-8 h-8 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-semibold mb-1">Belum ada data</h3>
                    <p class="text-sm text-muted-foreground max-w-md mx-auto">
                        Silahkan isi filter tanggal, kasir, dan metode
                        pembayaran di atas untuk melihat ringkasan laporan
                        penjualan.
                    </p>
                </div>
            </template>
        </div>
    </AuthenticatedLayout>
</template>
