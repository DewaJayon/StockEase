<script setup>
import { Button } from "@/Components/ui/button";
import { Printer, Trash2 } from "lucide-vue-next";

import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from "@/Components/ui/number-field";

import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/Components/ui/tooltip";

import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { Label } from "@/Components/ui/label";
</script>

<template>
    <div class="lg:w-1/3 rounded-lg shadow p-4 border dark:border-white/30">
        <h2 class="text-xl font-bold mb-4">Keranjang Belanja</h2>

        <div class="space-y-3 mb-4" style="max-height: 50vh; overflow-y: auto">
            <!-- Cart Item -->
            <div class="flex justify-between items-center border-b pb-2">
                <div>
                    <h4 class="font-medium">Produk A</h4>
                    <p class="text-gray-500 text-sm">Rp 25.000</p>
                </div>
                <div class="flex items-center">
                    <NumberField :default-value="2" :min="0">
                        <NumberFieldContent>
                            <NumberFieldDecrement />
                            <NumberFieldInput class="w-24 border rounded" />
                            <NumberFieldIncrement />
                        </NumberFieldContent>
                    </NumberField>
                    <Button variant="destructive" size="icon" class="ml-2">
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </div>
            </div>
        </div>

        <div class="space-y-2 border-t pt-3">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span class="font-semibold">Rp 68.000</span>
            </div>
            <div class="flex justify-between text-lg font-bold mt-2">
                <span>TOTAL:</span>
                <span>Rp 68.000</span>
            </div>

            <div>
                <span>Metode Pembayaran:</span>
                <div class="flex items-center space-x-2 mt-2">
                    <RadioGroup default-value="cash">
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="cash" value="cash" />
                            <Label for="cash">Cash</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="qris" value="qris" />
                            <Label for="qris">Qris</Label>
                        </div>
                    </RadioGroup>
                </div>
            </div>

            <Button class="w-full"> PROSES PEMBAYARAN </Button>

            <div class="grid grid-cols-3 gap-2 mt-3">
                <TooltipProvider :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button size="icon" class="w-full">
                                <Printer />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="bottom">
                            <p>Cetak</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>

                <TooltipProvider :delay-duration="0">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button size="icon" class="w-full">
                                <Trash2 />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="bottom">
                            <p>Hapus Semua</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            </div>
        </div>
    </div>
</template>
