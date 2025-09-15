<script setup>
const props = defineProps({
    row: { type: Object, required: true },
});

const formatQty = (row) => {
    if (row.type == "in") {
        return "+" + row.qty;
    } else if (row.type == "out") {
        return "-" + row.qty;
    } else if (row.type == "adjust") {
        return "±" + row.qty;
    }
    return row.qty;
};
</script>

<template>
    <span class="flex items-center justify-center">
        {{ formatQty(props.row) }}
    </span>
</template>
