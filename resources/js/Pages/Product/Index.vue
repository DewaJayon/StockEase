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
import { Separator } from "@/Components/ui/separator";
import { DataTable } from "@/Components/ui/data-table";
import { Plus } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";
import { productColumns } from "./partials/product-columns";

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head>
            <title>Product</title>
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
                        <BreadcrumbPage> Product </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>
        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl bg-muted/50 h-full p-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">Product</h4>
                    <Link :href="route('product.create')">
                        <Button
                            variant="outline"
                            class="dark:border-white border-zinc-600"
                        >
                            <Plus />
                            Tambah Product
                        </Button>
                    </Link>
                </div>
                <Separator class="my-4" />

                <div class="mt-4">
                    <DataTable
                        :data="products.data"
                        :columns="productColumns"
                        :route-name="'product.index'"
                        :pagination="products"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
