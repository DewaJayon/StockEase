<script setup>
import DatePicker from '@/Components/DatePicker.vue';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { watchDebounced } from '@vueuse/core';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { Checkbox } from '@/Components/ui/checkbox';

import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

import {
    Check,
    FileSpreadsheet,
    Loader2,
    Printer,
    Search,
} from 'lucide-vue-next';

import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';

import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
} from '@/Components/ui/combobox';

const searchCashier = ref('');
const cashierData = ref([]);

watchDebounced(
    searchCashier,
    (newsearchCashier) => {
        axios
            .get(
                route('reports.sale.search-cashier', {
                    search: newsearchCashier,
                }),
            )
            .then((response) => {
                cashierData.value = response.data.data;
            })
            .catch((error) => {
                console.log(error);
                cashierData.value = [];
            });
    },
    300,
);

const getDateParam = (key) => {
    const val = new URLSearchParams(window.location.search).get(key);
    return val ? new Date(val) : null;
};

const urlParams = new URLSearchParams(window.location.search);

const paymentParam = urlParams.get('payment') || null;

const cashierParam = urlParams.get('cashier') || null;

const startDate = ref(getDateParam('start'));
const endDate = ref(getDateParam('end'));
const cashier = ref(null);
const payment = ref(paymentParam === 'qris' ? 'midtrans' : paymentParam);

const allCashierParam =
    urlParams.get('cashier') === 'semua-cashier' ? true : false;
const allCashier = ref(allCashierParam);

watch(allCashier, (newVal) => {
    if (newVal) {
        cashier.value = null;
    }
});

watch(cashier, (newVal) => {
    if (newVal) {
        allCashier.value = false;
    }
});

if (cashierParam) {
    axios
        .get(route('reports.sale.search-cashier', { search: cashierParam }))
        .then((response) => {
            const foundCashier = response.data.data.find(
                (item) => String(item.value) === String(cashierParam),
            );
            if (foundCashier) {
                cashier.value = foundCashier;
            }
        })
        .catch(() => {
            cashier.value = null;
        });
}

const formatDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const checkFilter = () => {
    if (!startDate.value || !endDate.value) {
        toast.error('Tanggal mulai dan tanggal selesai wajib diisi!');
        return false;
    }

    if (!allCashier.value && !cashier.value) {
        toast.error('Silahkan pilih cashier atau centang Semua Cashier!');
        return false;
    }

    return true;
};

const isFilterLoading = ref(false);

const handleFilter = () => {
    if (!checkFilter()) return;

    isFilterLoading.value = true;

    if (payment.value == 'midtrans') {
        payment.value = 'qris';
    }

    let allCashierParam = null;

    if (allCashier.value) {
        allCashierParam = 'semua-cashier';
    } else if (cashier.value) {
        allCashierParam = cashier.value.value;
    }

    router.get(
        route('reports.sale.index'),
        {
            ...Object.fromEntries(new URLSearchParams(window.location.search)),
            start: formatDate(startDate.value),
            end: formatDate(endDate.value),
            cashier: allCashierParam,
            payment: payment.value,
            page: 1,
        },
        {
            onFinish: () => {
                isFilterLoading.value = false;
            },
        },
    );
};

const handlePrintPdf = () => {
    if (!checkFilter()) return;

    if (payment.value == 'midtrans') {
        payment.value = 'qris';
    }

    let allCashierParam = null;

    if (allCashier.value) {
        allCashierParam = 'semua-cashier';
    } else if (cashier.value) {
        allCashierParam = cashier.value.value;
    }

    window.open(
        route('reports.sale.export-to-pdf', {
            start: formatDate(startDate.value),
            end: formatDate(endDate.value),
            cashier: allCashierParam,
            payment: payment.value,
        }),
        '_blank',
    );
};

const handleExportExcel = () => {
    if (!checkFilter()) return;

    if (payment.value == 'midtrans') {
        payment.value = 'qris';
    }

    let allCashierParam = null;

    if (allCashier.value) {
        allCashierParam = 'semua-cashier';
    } else if (cashier.value) {
        allCashierParam = cashier.value.value;
    }

    window.open(
        route('reports.sale.export-to-excel', {
            start: formatDate(startDate.value),
            end: formatDate(endDate.value),
            cashier: allCashierParam,
            payment: payment.value,
        }),
        '_blank',
    );
};
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>
        <h1 class="text-2xl font-bold">
          Laporan Penjualan
        </h1>
      </CardTitle>
    </CardHeader>
    <CardContent class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="space-y-1">
        <Label html-for="startDate">Tanggal Mulai</Label>
        <DatePicker
          id="startDate"
          v-model="startDate"
          label="Tanggal"
          class="w-full"
        />
      </div>
      <div class="space-y-1">
        <Label html-for="endDate">Tanggal Selesai</Label>
        <DatePicker
          id="endDate"
          v-model="endDate"
          label="Tanggal"
          class="w-full"
        />
      </div>
      <div class="space-y-1">
        <Label html-for="cashier">Kasir</Label>
        <Combobox
          v-model="cashier"
          by="label"
        >
          <ComboboxAnchor class="w-full">
            <div class="relative w-full max-sm items-center">
              <ComboboxInput
                v-model="searchCashier"
                class="pl-9"
                :display-value="(val) => val?.label ?? ''"
                placeholder="Cari Kasir..."
              />
              <span
                class="absolute start-0 inset-y-0 flex items-center justify-center px-3"
              >
                <Search class="size-4 text-muted-foreground" />
              </span>
            </div>
          </ComboboxAnchor>

          <ComboboxList>
            <ComboboxEmpty>
              Tidak ada kasir ditemukan.
            </ComboboxEmpty>

            <ComboboxGroup>
              <ComboboxItem
                v-for="c in cashierData"
                :key="c.value"
                :value="c"
                class="cursor-pointer"
              >
                {{ c.label }}

                <ComboboxItemIndicator>
                  <Check :class="cn('ml-auto h-4 w-4')" />
                </ComboboxItemIndicator>
              </ComboboxItem>
            </ComboboxGroup>
          </ComboboxList>
        </Combobox>
        <div class="items-top flex gap-x-2 pt-2">
          <Checkbox
            id="all-supplier"
            v-model="allCashier"
          />
          <div class="grid gap-1.5 leading-none">
            <label
              for="all-supplier"
              class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
            >
              Semua Kasir
            </label>
          </div>
        </div>
      </div>
      <div class="space-y-1">
        <Label html-for="payment">Metode Pembayaran</Label>
        <Select
          id="payment"
          v-model="payment"
        >
          <SelectTrigger class="w-full">
            <SelectValue placeholder="Pilih Metode Pembayaran" />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>Metode Pembayaran</SelectLabel>
              <SelectItem
                value="semua-metode"
                class="cursor-pointer"
              >
                Semua Metode
              </SelectItem>
              <SelectItem
                value="cash"
                class="cursor-pointer"
              >
                Cash
              </SelectItem>
              <SelectItem
                value="midtrans"
                class="cursor-pointer"
              >
                Midtrans
              </SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
      </div>

      <div class="flex space-x-2">
        <Button
          :disabled="isFilterLoading || !checkFilter()"
          class="disabled:cursor-not-allowed disabled:opacity-50"
          @click="handleFilter"
        >
          <Loader2
            v-if="isFilterLoading"
            class="w-4 h-4 animate-spin"
          />

          <Search
            v-else
            class="h-4 w-4"
          />
          <span>Lihat Laporan</span>
        </Button>
        <Button
          :disabled="!checkFilter()"
          class="disabled:cursor-not-allowed disabled:opacity-50"
          @click="handlePrintPdf()"
        >
          <Printer class="h-4 w-4" />
          <span>Export PDF</span>
        </Button>
        <Button
          :disabled="!checkFilter()"
          class="disabled:cursor-not-allowed disabled:opacity-50"
          @click="handleExportExcel()"
        >
          <FileSpreadsheet class="h-4 w-4" />
          <span>Export Excel</span>
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
