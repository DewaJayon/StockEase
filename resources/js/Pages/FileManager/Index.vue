<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import FileCard from "./partials/FileCard.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { Separator } from "@/Components/ui/separator";
import { Input } from "@/Components/ui/input";
import { Plus, Search } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";
import { computed } from "vue";
import FilePagination from "./partials/FilePagination.vue";

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";

const props = defineProps({
    files: {
        type: Object,
        required: true,
    },
});

const pagination = computed(() => props.files);
const files = computed(() => props.files.data ?? []);

const reloadPage = () => {
    router.reload({
        preserveScroll: true,
        preserveState: true,
        only: ["files"],
    });
};

console.log(files.value);
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

                <template v-if="files.length > 0">
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                    >
                        <FileCard
                            v-for="(file, index) in files"
                            :key="index"
                            :file="file"
                            @delete="reloadPage"
                        />
                    </div>
                </template>

                <template v-else>
                    <div class="flex items-center justify-center">
                        <p class="text-sm text-muted-foreground">
                            Tidak ada data
                        </p>
                    </div>
                </template>

                <Separator class="my-4" />

                <div class="mt-4">
                    <FilePagination :files="pagination" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
