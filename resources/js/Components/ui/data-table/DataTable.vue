<script setup>
import {
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from "@tanstack/vue-table";

import { router } from "@inertiajs/vue3";
import { watchDebounced } from "@vueuse/core";

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

import { computed, ref, watch } from "vue";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";

import { Input } from "@/Components/ui/input";

import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from "@/Components/ui/pagination";
import { Search } from "lucide-vue-next";
import { Button } from "../button";
import { DoubleArrowLeftIcon, DoubleArrowRightIcon } from "@radix-icons/vue";

const props = defineProps({
    data: Array,
    columns: Array,
    routeName: String,
    pagination: Object,
    routeParams: {
        type: Object,
        required: false,
    },
    dateFilterEnd: {
        type: Boolean,
        required: false,
    },
    dateFilterStart: {
        type: Boolean,
        required: false,
    },
});

const pagination = ref({
    pageIndex: props.pagination.current_page - 1,
    pageSize: props.pagination.per_page,
});

watch(
    () => props.pagination,
    (newPagination) => {
        pagination.value.pageIndex = newPagination.current_page - 1;
        pagination.value.pageSize = newPagination.per_page;
    },
    { immediate: true, deep: true }
);

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    manualPagination: true,
    onPaginationChange: (updater) => {
        if (typeof updater === "function") {
            pagination.value = updater(pagination.value);
        } else {
            pagination.value = updater;
        }
        router.get(
            route(props.routeName, props.routeParams),
            {
                page: pagination.value.pageIndex + 1,
                per_page: pagination.value.pageSize,
                seaarch: search.value,
            },
            { preseaaarveState: false, preserveScroll: true, replace: true }
        );
    },

    state: {
        get pagination() {
            return pagination.value;
        },
    },
});

const currentPage = computed({
    get: () => props.pagination.current_page,
    set: (val) => {
        pagination.value.pageIndex = val - 1;
    },
});
const goToNextPage = () => {
    if (!isLastPage.value) {
        pagination.value.pageIndex += 1;
    }
};

const goToFirstPage = () => {
    pagination.value.pageIndex = 0;
};

const goToLastPage = () => {
    pagination.value.pageIndex = props.pagination.last_page - 1;
};

const isLastPage = computed(() => {
    return pagination.value.pageIndex + 1 >= props.pagination.last_page;
});

watch(
    () => pagination.value.pageIndex,
    (newPage) => {
        const query = {
            page: newPage + 1,
            per_page: pagination.value.pageSize,
            search: search.value,
        };

        if (props.dateFilterStart) {
            query.start = new URLSearchParams(window.location.search).get(
                "start"
            );
        }
        if (props.dateFilterEnd) {
            query.end = new URLSearchParams(window.location.search).get("end");
        }

        router.get(route(props.routeName, props.routeParams), query, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }
);

const search = ref("");

watchDebounced(
    search,
    (newSearch) => {
        pagination.value.pageIndex = 0;

        const query = {
            page: 1,
            per_page: pagination.value.pageSize,
            search: newSearch,
        };

        if (props.dateFilterStart) {
            query.start = new URLSearchParams(window.location.search).get(
                "start"
            );
        }
        if (props.dateFilterEnd) {
            query.end = new URLSearchParams(window.location.search).get("end");
        }

        router.get(route(props.routeName, props.routeParams), query, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    },
    { debounce: 300 }
);
</script>

<template>
    <div class="w-full">
        <div class="relative w-full max-w-sm items-center mb-4">
            <Input
                id="search"
                type="text"
                v-model="search"
                placeholder="Search..."
                autocomplete="off"
                class="pl-10 shadow-md focus:ring-0 focus:ring-offset-0"
            />
            <span
                class="absolute start-0 inset-y-0 flex items-center justify-center px-2"
            >
                <Search class="w-5 h-5 text-muted-foreground" />
            </span>
        </div>

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow
                        class="hover:bg-transparent"
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            class="border-b text-center"
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                        >
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody class="divide-y">
                    <template v-if="table.getRowModel().rows?.length">
                        <template
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                        >
                            <TableRow
                                :data-state="row.getIsSelected() && 'selected'"
                                class="hover:bg-transparent"
                            >
                                <TableCell
                                    class="border-b"
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                >
                                    <FlexRender
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="row.getIsExpanded()">
                                <TableCell :colspan="row.getAllCells().length">
                                    {{ JSON.stringify(row.original) }}
                                </TableCell>
                            </TableRow>
                        </template>
                    </template>

                    <TableRow v-else>
                        <TableCell
                            :colspan="columns.length"
                            class="h-24 text-center"
                        >
                            No results.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
        <div class="flex items-center justify-end space-x-2 py-4 sm:space-x-6">
            <div class="flex items-center space-x-2">
                <p class="text-sm font-medium">Rows per page</p>
                <Select
                    :model-value="`${table.getState().pagination.pageSize}`"
                    @update:model-value="table.setPageSize(Number($event))"
                >
                    <SelectTrigger class="h-8 w-[70px]">
                        <SelectValue
                            :placeholder="`${
                                table.getState().pagination.pageSize
                            }`"
                        />
                    </SelectTrigger>
                    <SelectContent side="top">
                        <SelectItem
                            class="cursor-pointer"
                            v-for="pageSize in [10, 20, 30, 40, 50]"
                            :key="pageSize"
                            :value="`${pageSize}`"
                        >
                            {{ pageSize }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div class="overflow-auto sm:overflow-visible max-w-full">
                <div class="flex items-center space-x-2">
                    <Pagination
                        v-slot="{ page }"
                        :items-per-page="props.pagination.per_page"
                        :total="props.pagination.total"
                        v-model:page="currentPage"
                    >
                        <PaginationContent v-slot="{ items }" class="flex">
                            <Button
                                variant="outline"
                                class="hidden w-8 h-8 p-0 lg:flex"
                                :disabled="!table.getCanPreviousPage()"
                                @click="goToFirstPage"
                            >
                                <span class="sr-only">Go to first page</span>
                                <DoubleArrowLeftIcon class="w-4 h-4" />
                            </Button>

                            <PaginationPrevious
                                class="border"
                                :disabled="!table.getCanPreviousPage()"
                                @click="table.previousPage()"
                            />

                            <template
                                v-for="(item, index) in items"
                                :key="index"
                            >
                                <PaginationItem
                                    class="border disabled:opacity-50 disabled:cursor-not-allowed"
                                    v-if="item.type === 'page'"
                                    :value="item.value"
                                    :is-active="item.value === page"
                                    :disabled="item.value === page"
                                    @click="table.setPageIndex(item.value - 1)"
                                >
                                    {{ item.value }}
                                </PaginationItem>
                            </template>

                            <PaginationNext
                                class="border"
                                :disabled="isLastPage"
                                @click="goToNextPage"
                            />
                            <Button
                                variant="outline"
                                class="hidden w-8 h-8 p-0 lg:flex"
                                :disabled="isLastPage"
                                @click="goToLastPage"
                            >
                                <span class="sr-only">Go to last page</span>
                                <DoubleArrowRightIcon class="w-4 h-4" />
                            </Button>
                        </PaginationContent>
                    </Pagination>
                </div>
            </div>
        </div>
    </div>
</template>
