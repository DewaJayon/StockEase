<script setup>
const props = defineProps({
    row: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center justify-center">
        <img
            :src="`https://dummyimage.com/80x80/000/fff`"
            :alt="`${props.row.name}`"
            class="w-20 h-20 object-cover rounded"
            loading="lazy"
        />
    </div>
</template>
