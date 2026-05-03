<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FileCard from './partials/FileCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Separator } from '@/Components/ui/separator';
import { computed } from 'vue';
import FilePagination from './partials/FilePagination.vue';
import FileUploadForm from './partials/FileUploadForm.vue';

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import FileSearch from './partials/FileSearch.vue';
import FileTypeFilter from './partials/FileTypeFilter.vue';

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
        only: ['files'],
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
                        <Link :href="route('dashboard')">
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

        <div
            class="flex flex-wrap items-center justify-between gap-4 p-4 lg:p-6 pb-0 lg:pb-0"
        >
            <h2
                class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
            >
                File Manager
            </h2>
        </div>

        <div class="flex flex-1 flex-col gap-4 p-4 lg:p-6">
            <div
                class="rounded-xl bg-card text-card-foreground border shadow-sm min-h-full p-4 lg:p-6"
            >
                <!-- Toolbar: Search, Filter, Upload -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6"
                >
                    <div
                        class="flex flex-col sm:flex-row items-center gap-3 w-full sm:max-w-lg"
                    >
                        <div class="relative w-full sm:w-2/3">
                            <FileSearch />
                        </div>
                        <div class="w-full sm:w-1/3 shrink-0">
                            <FileTypeFilter />
                        </div>
                    </div>

                    <div class="w-full sm:w-auto flex sm:justify-end shrink-0">
                        <FileUploadForm class="w-full sm:w-auto" />
                    </div>
                </div>

                <template v-if="files.length">
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5"
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
                    <div
                        class="flex flex-col items-center justify-center py-12 border-2 border-dashed rounded-lg mt-4"
                    >
                        <div class="rounded-full bg-muted p-3 mb-4">
                            <svg
                                class="w-6 h-6 text-muted-foreground"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-muted-foreground">
                            Tidak ada file ditemukan
                        </p>
                    </div>
                </template>

                <Separator v-if="files.length" class="my-6" />

                <div v-if="files.length" class="mt-4">
                    <FilePagination :files="pagination" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
