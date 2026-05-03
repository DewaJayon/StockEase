<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Info,
    BarChart3,
    Package,
    ShoppingCart,
    CircleDollarSign,
} from 'lucide-vue-next';

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/Components/ui/card';
import Filter from './partials/Filter.vue';
import Summary from './partials/Summary.vue';
import Chart from './partials/Chart.vue';

const props = defineProps({
    filters: {
        type: [Object, Array],
        required: true,
    },
});

const hasData = computed(
    () => props.filters && props.filters.sumTotalPurchase !== undefined,
);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Laporan Pembelian" />

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
                        <BreadcrumbPage> Laporan Pembelian </BreadcrumbPage>
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
                        Laporan Pembelian
                    </h1>
                    <p class="text-muted-foreground">
                        Analisis data pembelian barang dari supplier.
                    </p>
                </div>
            </div>

            <Filter />

            <template v-if="hasData">
                <Summary :summary="props.filters" />
                <Chart :chart="props.filters" />
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
                        Silahkan isi filter tanggal, supplier, dan user di atas
                        untuk melihat ringkasan laporan pembelian.
                    </p>
                </div>
            </template>
        </div>
    </AuthenticatedLayout>
</template>
