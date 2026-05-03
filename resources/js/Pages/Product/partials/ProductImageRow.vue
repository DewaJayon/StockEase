<script setup>
import { Avatar, AvatarImage, AvatarFallback } from '@/Components/ui/avatar';
import { Package } from 'lucide-vue-next';

const props = defineProps({
    row: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center justify-center">
        <Avatar class="h-12 w-12 rounded-lg border shadow-sm">
            <AvatarImage
                v-if="props.row.image_path"
                :src="`/${props.row.image_path}`"
                :alt="props.row.name"
                class="object-cover"
            />
            <AvatarFallback class="rounded-lg bg-muted">
                <Package class="h-5 w-5 text-muted-foreground/50" />
            </AvatarFallback>
        </Avatar>
    </div>
</template>
