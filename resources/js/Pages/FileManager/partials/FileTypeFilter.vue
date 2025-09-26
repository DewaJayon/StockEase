<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";

const FileTypeFilter = ref(
    new URLSearchParams(window.location.search).get("file_filter") ?? null
);

watch(FileTypeFilter, (newValue) => {
    if (newValue === "all") {
        router.get(route("file-manager.index"));
    } else {
        router.get(route("file-manager.index"), {
            file_filter: FileTypeFilter.value,
        });
    }
});
</script>

<template>
    <Select v-model="FileTypeFilter">
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
