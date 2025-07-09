<script setup>
import { Button } from "@/Components/ui/button";
import { Pencil, Trash2 } from "lucide-vue-next";
</script>

<template>
    <div class="flex items-center justify-center">
        <Button variant="ghost" size="icon" class="group">
            <Pencil class="w-4 h-4 text-blue-500 dark:group-hover:text-white" />
        </Button>

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
