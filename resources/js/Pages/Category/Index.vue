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
import { Head, Link } from "@inertiajs/vue3";
import { Button } from "@/Components/ui/button";
import { Plus } from "lucide-vue-next";
import { Separator } from "@/Components/ui/separator";
import { DataTable } from "@/Components/ui/data-table";
import { categoryColumns } from "./partials/category-column";
import CategoryCreateForm from "./form/CategoryCreateForm.vue";

const props = defineProps({
    categories: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head>
            <title>Kategori</title>
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
                        <BreadcrumbPage> Kategori </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl bg-muted/50 h-full p-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">Kategori</h4>
                    <CategoryCreateForm />
                </div>
                <Separator class="my-4" />

                <div class="mt-4">
                    <DataTable
                        :data="categories.data"
                        :columns="categoryColumns"
                        :route-name="'category.index'"
                        :pagination="categories"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
