<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import FileCard from "./partials/FileCard.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Separator } from "@/Components/ui/separator";

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";

import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from "@/Components/ui/pagination";

import { Input } from "@/Components/ui/input";
import { Plus, Search } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";
</script>

<template>
    <AuthenticatedLayout>
        <Head>
            <title>File Manager</title>
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
                        <BreadcrumbPage> File Manager </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>
        </template>

        <div class="flex flex-wrap items-center justify-between gap-4 pb-2 p-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                File Manager
            </h2>
        </div>

        <div class="flex flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl bg-muted/50 h-full p-4">
                <div class="flex justify-between items-center">
                    <h4 class="font-semibold">File Manager</h4>
                </div>
                <Separator class="my-4" />

                <div class="flex items-center justify-between w-full mb-4">
                    <div class="relative w-full max-w-sm">
                        <Input
                            id="search"
                            type="text"
                            placeholder="Search..."
                            autocomplete="off"
                            class="pl-10 shadow-md focus:ring-0 focus:ring-offset-0 w-full"
                        />
                        <span
                            class="absolute start-0 inset-y-0 flex items-center justify-center px-2"
                        >
                            <Search class="w-5 h-5 text-muted-foreground" />
                        </span>
                    </div>

                    <Button class="ml-3 flex items-center gap-2">
                        <Plus class="w-4 h-4" />
                        Upload File
                    </Button>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                >
                    <FileCard v-for="i in 20" :key="i" />
                </div>

                <Separator class="my-4" />

                <div class="mt-4">
                    <Pagination
                        v-slot="{ page }"
                        :items-per-page="10"
                        :total="30"
                        :default-page="2"
                    >
                        <PaginationContent v-slot="{ items }">
                            <PaginationPrevious />

                            <template
                                v-for="(item, index) in items"
                                :key="index"
                            >
                                <PaginationItem
                                    v-if="item.type === 'page'"
                                    :value="item.value"
                                    :is-active="item.value === page"
                                >
                                    {{ item.value }}
                                </PaginationItem>
                            </template>

                            <PaginationEllipsis :index="4" />

                            <PaginationNext />
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
