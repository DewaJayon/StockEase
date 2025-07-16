<script setup>
import { Search } from "lucide-vue-next";
import { Input } from "@/Components/ui/input";
import { ref, watch } from "vue";
import { watchDebounced } from "@vueuse/core";
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

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
    categories: {
        type: Object,
        required: true,
    },
});

const category = ref(
    new URLSearchParams(window.location.search).get("category") ?? null
);

watch(category, (newCategory) => {
    router.get(
        route("pos.index"),
        {
            category: newCategory,
            search: search.value,
            page: 1,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
});

const search = ref(
    new URLSearchParams(window.location.search).get("search") ?? ""
);

watchDebounced(
    search,
    (newSearch) => {
        router.get(
            route("pos.index"),
            {
                search: newSearch,
                category: category.value,
                page: 1,
            },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            }
        );
    },
    { debounce: 300 }
);
</script>

<template>
    <div class="flex justify-between mb-4">
        <div class="relative w-full max-w-sm items-center mb-4">
            <Input
                v-model="search"
                id="search"
                type="text"
                placeholder="Cari Produk..."
                autocomplete="off"
                autofocus
                class="pl-10 shadow-md focus:ring-0 focus:ring-offset-0"
            />
            <span
                class="absolute start-0 inset-y-0 flex items-center justify-center px-2"
            >
                <Search class="w-5 h-5 text-muted-foreground" />
            </span>
        </div>
        <Select v-model="category">
            <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Pilih Kategori" />
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    <SelectLabel>Kategori</SelectLabel>
                    <SelectItem :value="null" class="capitalize cursor-pointer">
                        Semua Kategori
                    </SelectItem>
                    <SelectItem
                        v-for="category in categories"
                        :key="category.value"
                        :value="category.value"
                        class="capitalize cursor-pointer"
                    >
                        {{ category.label }}
                    </SelectItem>
                </SelectGroup>
            </SelectContent>
        </Select>
    </div>
</template>
