<script setup>
const props = defineProps({
    row: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center justify-center">
        <img
            v-if="props.row.image_path"
            :src="`${props.row.image_path}`"
            :alt="`${props.row.name}`"
            class="w-20 h-20 object-cover rounded"
            loading="lazy"
        />

        <img
            v-else
            class="w-20 h-20 object-cover rounded"
            src="/img/StockEase-Logo.png"
            :alt="`${props.row.name}`"
            loading="lazy"
        />
    </div>
</template>
