<script setup>
import { Badge } from "@/Components/ui/badge";
import { computed } from "vue";

const props = defineProps({
    row: {
        required: true,
        type: String,
    },
});

const borderColor = computed(() => {
    switch (props.row) {
        case "settlement":
            return "border-green-500";
        case "pending":
            return "border-yellow-500";
        case "expire":
            return "border-red-500";
        case "cancel":
            return "border-red-500";
        default:
            return "border-gray-500";
    }
});
</script>

<template>
    <div class="capitalize flex items-center justify-center">
        <Badge variant="outline" class="border" :class="borderColor">
            {{ props.row }}
        </Badge>
    </div>
</template>
