<script setup>
import { CalendarIcon } from "lucide-vue-next";
import { ref } from "vue";
import { cn } from "@/lib/utils";
import { Button } from "@/Components/ui/button";
import { RangeCalendar } from "@/Components/ui/range-calendar";
import { router } from "@inertiajs/vue3";

import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";

import {
    CalendarDate,
    DateFormatter,
    getLocalTimeZone,
} from "@internationalized/date";

const df = new DateFormatter("en-US", {
    dateStyle: "medium",
});

const today = new Date();

const value = ref({
    start: new CalendarDate(
        today.getFullYear(),
        today.getMonth() + 1,
        today.getDate()
    ).add({ days: -20 }),

    end: new CalendarDate(
        today.getFullYear(),
        today.getMonth() + 1,
        today.getDate()
    ),
});

const formatDate = (date) => {
    return df.format(date.toDate(getLocalTimeZone()));
};

const handleDateFilter = () => {
    router.get(
        route("purcase.index", {
            start: formatDate(value.value.start),
            end: formatDate(value.value.end),
        }),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};
</script>

<template>
    <div class="flex gap-2">
        <Popover>
            <PopoverTrigger as-child>
                <Button
                    variant="outline"
                    :class="
                        cn(
                            'w-[280px] justify-start text-left font-normal',
                            !value && 'text-muted-foreground'
                        )
                    "
                >
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    <template v-if="value.start">
                        <template v-if="value.end">
                            {{
                                df.format(
                                    value.start.toDate(getLocalTimeZone())
                                )
                            }}
                            -
                            {{
                                df.format(value.end.toDate(getLocalTimeZone()))
                            }}
                        </template>

                        <template v-else>
                            {{
                                df.format(
                                    value.start.toDate(getLocalTimeZone())
                                )
                            }}
                        </template>
                    </template>
                    <template v-else> Pick a date </template>
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0">
                <RangeCalendar
                    v-model="value"
                    initial-focus
                    :number-of-months="2"
                    @update:start-value="
                        (startDate) => (value.start = startDate)
                    "
                />
            </PopoverContent>
        </Popover>
        <Button size="sm" @click="handleDateFilter" class="ml-2 text-xs">
            Filter Tanggal
        </Button>
    </div>
</template>
