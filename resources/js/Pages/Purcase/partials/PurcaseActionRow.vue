<script setup>
import { Button } from "@/Components/ui/button";
import { Eye, Trash2 } from "lucide-vue-next";
import PurcaseEditForm from "../form/PurcaseEditForm.vue";

const props = defineProps({
    row: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center justify-center">
        <PurcaseEditForm :purcase="row" />

        <Button
            variant="ghost"
            size="icon"
            class="dark:hover:bg-red-900 hover:bg-red-500 group"
        >
            <Trash2
                class="w-4 h-4 text-red-500 dark:group-hover:text-white group-hover:text-black"
            />
        </Button>
    </div>
</template>
