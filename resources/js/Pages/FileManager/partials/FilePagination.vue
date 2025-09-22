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
    files: {
        type: Object,
    },
});
</script>

<template>
    <Pagination
        v-slot="{ page }"
        :items-per-page="props.files?.per_page"
        :total="props.files?.total"
        :default-page="props.files?.current_page"
    >
        <PaginationContent v-slot="{ items }">
            <PaginationPrevious
                :disabled="!props.files?.prev_page_url"
                @click="$inertia.visit(props.files?.prev_page_url)"
            />

            <template v-for="(item, index) in items" :key="index">
                <PaginationItem
                    class="border disabled:opacity-50 disabled:cursor-not-allowed"
                    v-if="item.type === 'page'"
                    :value="item.value"
                    :is-active="item.value === page"
                    :disabled="item.value === page"
                    @click="
                        $inertia.get(route('file-manager.index'), {
                            page: item.value,
                        })
                    "
                >
                    {{ item.value }}
                </PaginationItem>
            </template>

            <PaginationEllipsis :index="4" />

            <PaginationNext
                :disabled="!props.files?.next_page_url"
                @click="$inertia.visit(props.files?.next_page_url)"
            />
        </PaginationContent>
    </Pagination>
</template>
