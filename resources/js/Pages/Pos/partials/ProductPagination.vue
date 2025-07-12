<script setup>
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from "@/Components/ui/pagination";

const props = defineProps({
    products: Object,
});
</script>

<template>
    <Pagination
        v-slot="{ page }"
        :items-per-page="props.products.per_page"
        :total="props.products.total"
        :default-page="props.products.current_page"
    >
        <PaginationContent v-slot="{ items }" class="flex">
            <PaginationPrevious
                class="border"
                :disabled="!props.products.prev_page_url"
                @click="$inertia.visit(props.products.prev_page_url)"
            />

            <template v-for="(item, index) in items" :key="index">
                <PaginationItem
                    class="border disabled:opacity-50 disabled:cursor-not-allowed"
                    v-if="item.type === 'page'"
                    :value="item.value"
                    :is-active="item.value === page"
                    :disabled="item.value === page"
                    @click="
                        $inertia.get(route('pos.index'), { page: item.value })
                    "
                >
                    {{ item.value }}
                </PaginationItem>
            </template>

            <PaginationEllipsis :index="4" />

            <PaginationNext
                class="border"
                :disabled="!props.products.next_page_url"
                @click="$inertia.visit(props.products.next_page_url)"
            />
        </PaginationContent>
    </Pagination>
</template>
