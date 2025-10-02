<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { getCurrentUrlQuery } from "@/lib/utils";

import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";

const fileTypeFilter = ref(
    new URLSearchParams(window.location.search).get("file_filter") ?? null
);

watch(fileTypeFilter, (newValue) => {
    if (newValue === "all") {
        router.get(route("file-manager.index"), {
            ...getCurrentUrlQuery(),
            file_filter: null,
        });
    } else {
        router.get(
            route("file-manager.index"),
            {
                ...getCurrentUrlQuery(["page"]),
                file_filter: newValue,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    }
});
</script>

<template>
    <Select v-model="fileTypeFilter">
        <SelectTrigger>
            <SelectValue placeholder="File Type" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectLabel>Filter</SelectLabel>
                <SelectItem value="all" class="cursor-pointer">
                    Semua
                </SelectItem>
                <SelectItem value="pdf" class="cursor-pointer">
                    PDF
                </SelectItem>
                <SelectItem value="xlsx" class="cursor-pointer">
                    Excel
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>
