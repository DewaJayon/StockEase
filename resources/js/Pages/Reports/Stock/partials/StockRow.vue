<script setup>
import { Badge } from "@/Components/ui/badge";

const props = defineProps({
    row: {
        type: Object,
        required: true,
    },
});

const statusColor = (row) => {
    if (row.stock <= row.alert_stock) {
        return "border-red-500";
    } else {
        return "border-green-500";
    }
};

const statusText = (row) => {
    if (row.stock <= row.alert_stock) {
        return "Stock Kurang";
    } else {
        return "Stock Tersedia";
    }
};
</script>

<template>
    <Badge variant="outline" :class="statusColor(row)">
        {{ statusText(row) }}
    </Badge>
</template>
