<script setup>
import { ref, watch, computed } from "vue";
import {
    Popover,
    PopoverTrigger,
    PopoverContent,
} from "@/Components/ui/popover";
import { Button } from "@/Components/ui/button";
import { CalendarIcon } from "lucide-vue-next";
import {
    startOfMonth,
    endOfMonth,
    startOfWeek,
    addDays,
    format,
    getDate,
    getMonth,
    getYear,
} from "date-fns";

const props = defineProps({
    modelValue: Date,
    placeholder: {
        type: String,
        default: "Pilih tanggal",
    },
    dropdownClass: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

const selectedDate = ref(props.modelValue || null);

const currentMonth = ref(getMonth(props.modelValue || new Date()));
const currentYear = ref(getYear(props.modelValue || new Date()));

const months = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
];

const years = Array.from(
    { length: 100 },
    (_, i) => new Date().getFullYear() - 50 + i
);

const days = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];

const monthStart = computed(() =>
    startOfMonth(new Date(currentYear.value, currentMonth.value))
);

const monthEnd = computed(() => endOfMonth(monthStart.value));

const calendarDates = computed(() => {
    const start = startOfWeek(monthStart.value, { weekStartsOn: 0 });
    const daysArray = [];

    for (let i = 0; i < 42; i++) {
        daysArray.push(addDays(start, i));
    }

    return daysArray;
});

const isSameDay = (date1, date2) => {
    return (
        date1 &&
        date2 &&
        date1.getDate() === date2.getDate() &&
        date1.getMonth() === date2.getMonth() &&
        date1.getFullYear() === date2.getFullYear()
    );
};

const selectDate = (date) => {
    selectedDate.value = date;
    emit("update:modelValue", date);
};

const formattedDate = computed(() =>
    selectedDate.value ? format(selectedDate.value, "dd MMMM yyyy") : ""
);

watch(
    () => props.modelValue,
    (val) => {
        selectedDate.value = val;
        if (val) {
            currentMonth.value = getMonth(val);
            currentYear.value = getYear(val);
        }
    }
);
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                class="w-full justify-start text-left font-normal bg-transparent hover:bg-transparent"
            >
                <CalendarIcon class="mr-2 h-4 w-4" />
                <span class="text-sm">{{ formattedDate || placeholder }}</span>
            </Button>
        </PopoverTrigger>
        <PopoverContent :class="dropdownClass" class="w-[300px] p-4 space-y-2">
            <!-- Header: Dropdown Bulan dan Tahun -->
            <div class="flex gap-2">
                <select
                    v-model="currentMonth"
                    class="w-1/2 rounded border px-2 py-1 text-sm cursor-pointer dark:bg-background dark:text-white"
                >
                    <option
                        v-for="(month, index) in months"
                        :key="index"
                        :value="index"
                    >
                        {{ month }}
                    </option>
                </select>
                <select
                    v-model="currentYear"
                    class="w-1/2 rounded border px-2 py-1 text-sm cursor-pointer dark:bg-background dark:text-white"
                >
                    <option v-for="year in years" :key="year" :value="year">
                        {{ year }}
                    </option>
                </select>
            </div>

            <!-- Hari -->
            <div
                class="grid grid-cols-7 text-center text-xs text-muted-foreground font-medium"
            >
                <div v-for="day in days" :key="day">{{ day }}</div>
            </div>

            <!-- Tanggal -->
            <div class="grid grid-cols-7 text-center text-sm">
                <div
                    v-for="(day, i) in calendarDates"
                    :key="i"
                    @click="selectDate(day)"
                    class="py-1.5 cursor-pointer rounded hover:bg-primary hover:text-primary-foreground"
                    :class="{
                        'bg-primary text-primary-foreground': isSameDay(
                            day,
                            selectedDate
                        ),
                        'text-gray-400': day.getMonth() !== currentMonth,
                    }"
                >
                    {{ getDate(day) }}
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
