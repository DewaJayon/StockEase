<script setup>
import { CalendarIcon } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import { cn } from '@/lib/utils';
import { Button } from '@/Components/ui/button';
import { RangeCalendar } from '@/Components/ui/range-calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/Components/ui/popover';
import {
    CalendarDate,
    DateFormatter,
    getLocalTimeZone,
    parseDate,
    today,
} from '@internationalized/date';
import { Separator } from '@/Components/ui/separator';

const props = defineProps({
    start: {
        type: String,
        default: null,
    },
    end: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: 'Pilih rentang tanggal',
    },
});

const emit = defineEmits(['update:start', 'update:end']);

const df = new DateFormatter('id-ID', {
    dateStyle: 'medium',
});

const localTimeZone = getLocalTimeZone();

// Temp value for selection before applying
const selectedRange = ref({
    start: props.start ? parseDate(props.start) : null,
    end: props.end ? parseDate(props.end) : null,
});

// Actual value that is displayed and emitted to parent
const value = ref({
    start: props.start ? parseDate(props.start) : null,
    end: props.end ? parseDate(props.end) : null,
});

const isOpen = ref(false);

const presets = [
    {
        label: 'Pilih Tanggal Sendiri',
        value: 'custom',
    },
    {
        label: '30 Hari Terakhir',
        value: 'last30',
        getRange: () => {
            const t = today(localTimeZone);
            return { start: t.subtract({ days: 30 }), end: t };
        },
    },
    {
        label: '90 Hari Terakhir',
        value: 'last90',
        getRange: () => {
            const t = today(localTimeZone);
            return { start: t.subtract({ days: 90 }), end: t };
        },
    },
];

const selectedPreset = ref('custom');

function selectPreset(preset) {
    selectedPreset.value = preset.value;
    if (preset.getRange) {
        selectedRange.value = preset.getRange();
    }
}

function handleApply() {
    value.value = { ...selectedRange.value };
    emit(
        'update:start',
        value.value.start ? value.value.start.toString() : null,
    );
    emit('update:end', value.value.end ? value.value.end.toString() : null);
    isOpen.value = false;
}

// Watch for external changes
watch(
    () => [props.start, props.end],
    ([newStart, newEnd]) => {
        const currentStart = value.value.start
            ? value.value.start.toString()
            : null;
        const currentEnd = value.value.end ? value.value.end.toString() : null;

        if (newStart !== currentStart || newEnd !== currentEnd) {
            const newVal = {
                start: newStart ? parseDate(newStart) : null,
                end: newEnd ? parseDate(newEnd) : null,
            };
            value.value = newVal;
            selectedRange.value = { ...newVal };
        }
    },
);

const isApplyDisabled = computed(() => {
    return !selectedRange.value.start || !selectedRange.value.end;
});
</script>

<template>
  <Popover v-model:open="isOpen">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="
          cn(
            'w-full sm:w-80 justify-start text-left font-normal bg-card h-10 border-muted-foreground/20',
            !value.start && 'text-muted-foreground',
          )
        "
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        <template v-if="value.start">
          <template v-if="value.end">
            {{ df.format(value.start.toDate(localTimeZone)) }}
            -
            {{ df.format(value.end.toDate(localTimeZone)) }}
          </template>
          <template v-else>
            {{ df.format(value.start.toDate(localTimeZone)) }}
          </template>
        </template>
        <template v-else>
          {{ placeholder }}
        </template>
      </Button>
    </PopoverTrigger>
    <PopoverContent
      class="w-auto p-0 flex flex-col sm:flex-row overflow-hidden rounded-lg border shadow-xl bg-background"
      align="end"
    >
      <!-- Sidebar -->
      <div class="w-full sm:w-64 flex flex-col p-6 space-y-4 border-r">
        <div class="text-xl font-bold text-foreground">
          Rentang Waktu
        </div>
        <div class="flex flex-col space-y-4">
          <button
            v-for="preset in presets"
            :key="preset.value"
            :class="
              cn(
                'text-left text-sm transition-colors cursor-pointer',
                selectedPreset === preset.value
                  ? 'text-foreground font-bold'
                  : 'text-muted-foreground hover:text-foreground',
              )
            "
            @click="selectPreset(preset)"
          >
            {{ preset.label }}
          </button>
        </div>
      </div>

      <!-- Main Calendar Area -->
      <div class="flex flex-col bg-background">
        <RangeCalendar
          v-model="selectedRange"
          initial-focus
          :number-of-months="2"
          class="p-4"
          @update:start-value="
            (startDate) => (selectedRange.start = startDate)
          "
          @update:model-value="() => (selectedPreset = 'custom')"
        />

        <!-- Footer -->
        <div class="p-6 pt-2 flex items-center justify-end">
          <Button
            size="lg"
            :disabled="isApplyDisabled"
            class="px-8 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg border-none"
            @click="handleApply"
          >
            Terapkan
          </Button>
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>
