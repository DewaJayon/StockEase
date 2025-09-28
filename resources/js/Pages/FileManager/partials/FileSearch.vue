<script setup>
import { Input } from "@/Components/ui/input";
import { watchDebounced } from "@vueuse/core";
import { Search } from "lucide-vue-next";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { getCurrentUrlQuery } from "@/lib/utils";

const urlParams = new URLSearchParams(window.location.search);
const search = ref(urlParams.get("search") ?? "");

watchDebounced(
    search,
    (newSearch) => {
        router.get(
            route("file-manager.index"),
            {
                ...getCurrentUrlQuery(["page"]),
                search: newSearch,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    },
    { debounce: 300 }
);
</script>

<template>
    <Input
        id="search"
        type="text"
        v-model="search"
        placeholder="Search..."
        autocomplete="off"
        class="pl-10 shadow-md focus:ring-0 focus:ring-offset-0 w-full"
    />
    <span
        class="absolute start-0 inset-y-0 flex items-center justify-center px-2"
    >
        <Search class="w-5 h-5 text-muted-foreground" />
    </span>
</template>
