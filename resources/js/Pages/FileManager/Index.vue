<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import FileCard from "./partials/FileCard.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { Separator } from "@/Components/ui/separator";
import { computed } from "vue";
import FilePagination from "./partials/FilePagination.vue";
import FileUploadForm from "./partials/FileUploadForm.vue";

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb";
import FileSearch from "./partials/FileSearch.vue";
import FileTypeFilter from "./partials/FileTypeFilter.vue";

const props = defineProps({
    files: {
        type: Object,
        required: true,
    },
});

const files = computed(() => Object.values(props.files.data ?? {}));
const pagination = computed(() => props.files);

const reloadPage = () => {
    router.reload({
        preserveScroll: true,
        preserveState: true,
        only: ["files"],
    });
};
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
                    <div class="flex items-center gap-2 w-full max-w-lg">
                        <div class="flex-1 relative">
                            <FileSearch />
                        </div>

                        <div class="shrink-0">
                            <FileTypeFilter />
                        </div>
                    </div>

                    <FileUploadForm />
                </div>
                <template v-if="files.length">
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
                    >
                        <FileCard
                            v-for="(file, index) in Object.values(files)"
                            :key="index"
                            :file="file"
                            @delete="reloadPage"
                        />
                    </div>
                </template>

                <template v-else>
                    <Separator class="my-4" />
                    <div class="flex items-center justify-center">
                        <p class="text-sm font-bold text-muted-foreground">
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
