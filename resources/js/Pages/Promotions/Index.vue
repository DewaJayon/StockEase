<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Separator } from '@/Components/ui/separator';
import { DataTable } from '@/Components/ui/data-table';
import PromotionCreateForm from './form/PromotionCreateForm.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import { promotionColumns } from './partials/promotion-column';
import { ref, watch, computed } from 'vue';

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';

const props = defineProps({
    promotions: { type: Object, required: true },
    categories: { type: Array, required: true },
    products: { type: Array, required: true },
});

const columns = computed(() =>
    promotionColumns(props.categories, props.products),
);

const urlParams = new URLSearchParams(window.location.search);
const startDate = ref(urlParams.get('start') || null);
const endDate = ref(urlParams.get('end') || null);

const applyDateFilter = () => {
    const query = {
        ...Object.fromEntries(new URLSearchParams(window.location.search)),
    };

    if (startDate.value && endDate.value) {
        query.start = startDate.value;
        query.end = endDate.value;
        query.page = 1;

        router.get(route('promotions.index'), query, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }
};

watch([startDate, endDate], ([newStart, newEnd]) => {
    if (newStart && newEnd) {
        applyDateFilter();
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manajemen Promo" />

        <template #breadcrumb>
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <Link :href="route('dashboard')">
                            <BreadcrumbLink>Dashboard</BreadcrumbLink>
                        </Link>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem>
                        <BreadcrumbPage>Manajemen Promo</BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>

        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl bg-muted/50 h-full p-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">Promo & Diskon</h4>
                    <div class="flex items-center gap-2">
                        <DateRangePicker
                            v-model:start="startDate"
                            v-model:end="endDate"
                            placeholder="Filter berdasarkan periode"
                        />
                        <PromotionCreateForm
                            :categories="categories"
                            :products="products"
                        />
                    </div>
                </div>
                <Separator class="my-4" />

                <div class="mt-4">
                    <DataTable
                        :data="promotions.data"
                        :columns="columns"
                        :route-name="'promotions.index'"
                        :pagination="promotions"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
