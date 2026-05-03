<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Clock, CalendarDays } from 'lucide-vue-next';

const props = defineProps({
    routeName: {
        type: String,
        required: true,
    },
    startDate: {
        type: Object,
        required: true,
    },
    endDate: {
        type: Object,
        required: true,
    },
    onUpdate: {
        type: Function,
        required: true,
    },
});

const presets = [
    { label: 'Hari Ini', value: 'today' },
    { label: 'Kemarin', value: 'yesterday' },
    { label: '7 Hari Terakhir', value: 'last7days' },
    { label: '30 Hari Terakhir', value: 'last30days' },
    { label: 'Bulan Ini', value: 'thisMonth' },
    { label: 'Bulan Lalu', value: 'lastMonth' },
];

const selectedPreset = ref('');

const applyPreset = () => {
    const today = new Date();

    let startDate, endDate;

    switch (selectedPreset.value) {
        case 'today':
            startDate = endDate = today;
            break;
        case 'yesterday':
            startDate = endDate = new Date(today);
            startDate.setDate(today.getDate() - 1);
            endDate.setDate(today.getDate() - 1);
            break;
        case 'last7days':
            endDate = today;
            startDate = new Date(today);
            startDate.setDate(today.getDate() - 6); // Including today
            break;
        case 'last30days':
            endDate = today;
            startDate = new Date(today);
            startDate.setDate(today.getDate() - 29); // Including today
            break;
        case 'thisMonth':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = today;
            break;
        case 'lastMonth':
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        default:
            return;
    }

    // Format dates as YYYY-MM-DD
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    // Update the dates
    props.onUpdate(formatDate(startDate), formatDate(endDate));

    // Reset selection
    selectedPreset.value = '';
};

const handleSelectChange = (value) => {
    selectedPreset.value = value;
};
</script>

<template>
    <div class="flex items-center gap-3">
        <span class="text-xs font-medium text-muted-foreground">Cepat:</span>
        <Select
            v-model="selectedPreset"
            class="w-48"
            @update:value="handleSelectChange"
        >
            <SelectTrigger>
                <SelectValue placeholder="Pilih preset..." />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="preset in presets"
                    :key="preset.value"
                    :value="preset.value"
                >
                    {{ preset.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <Button
            variant="outline"
            size="sm"
            :disabled="!selectedPreset.value"
            @click="applyPreset"
        >
            <CalendarDays class="mr-2 h-3 w-3" /> Apply
        </Button>
    </div>
</template>
