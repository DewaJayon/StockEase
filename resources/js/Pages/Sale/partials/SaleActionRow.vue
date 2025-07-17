<script setup>
import { Button } from "@/Components/ui/button";
import { Eye } from "lucide-vue-next";
import { Link, router } from "@inertiajs/vue3";

import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/Components/ui/tooltip";

const props = defineProps({
    row: { type: Object, required: true },
});
</script>

<template>
    <div class="flex items-center justify-center">
        <TooltipProvider :delay-duration="0">
            <Tooltip>
                <TooltipTrigger>
                    <Link :href="route('sale.show', props.row.id)">
                        <Button variant="ghost" size="icon">
                            <Eye
                                class="w-4 h-4 text-blue-500 dark:group-hover:text-white"
                            />
                        </Button>
                    </Link>
                </TooltipTrigger>
                <TooltipContent>
                    <p>Detail</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    </div>
</template>
